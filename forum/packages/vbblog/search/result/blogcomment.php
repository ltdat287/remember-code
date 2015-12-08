<?php if (!defined('VB_ENTRY')) die('Access denied.');

/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * @package vBulletin
 * @subpackage Search
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 28678 $
 * @since $Date: 2008-12-03 16:54:12 +0000 (Wed, 03 Dec 2008) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . '/vb/search/result.php');
require_once (DIR . '/includes/blog_functions_search.php');

/**
 * Enter description here...
 *
 * @package vBulletin
 * @subpackage Search
 */
class vBBlog_Search_Result_BlogComment extends vB_Search_Result
{
	public static function create($id)
	{
		$items = self::create_array(array($id));
		if (count($items))
		{
			return array_shift($items);
		}
		else
		{
			//invalid object.
			return new vBBlog_Search_Result_BlogComment();
		}
	}

	public static function create_array($ids)
	{
		global $vbulletin;
		//where going to punt a little.  The permissions logic is nasty and complex
		//and tied to the current user.  I don't want to try to rewrite it.
		//So we'll pull in the current user here and go with it.
		$perm_parts = build_blog_permissions_query($vbulletin->userinfo);

		$blog_user_join = "";
		if (strpos($perm_parts['join'], 'blog_user AS blog_user') === false)
		{
			$blog_user_join = "LEFT JOIN " . TABLE_PREFIX .
				"blog_user AS blog_user ON (blog_user.bloguserid = blog.userid)\n";
		}

		$set = $vbulletin->db->query_read_slave($q = "
			SELECT blog.*,
				IF(blog_user.title <> '', blog_user.title, blog.username) AS blogtitle,
				blog_user.memberids,
				blog_text.pagetext AS comment_pagetext,
				blog_text.username AS comment_username,
				blog_text.userid AS comment_userid,
				blog_text.title AS comment_title,
				blog_text.state AS comment_state,
				blog_text.dateline AS comment_dateline,
				blog_text.blogtextid
			FROM " . TABLE_PREFIX . "blog AS blog JOIN "  .
				TABLE_PREFIX ."blog_text AS blog_text ON blog.blogid = blog_text.blogid
				$blog_user_join $perm_parts[join]
			WHERE blog_text.blogtextid IN (" . implode(',', array_map('intval', $ids)) . ") AND ($perm_parts[where])
		");

		$items = array();
		while ($record = $vbulletin->db->fetch_array($set))
		{
			$item = new vBBlog_Search_Result_BlogComment();
			$item->record = $record;
			$items[$record['blogtextid']] = $item;
		}

		return $items;
	}

	protected function __construct() {}

	public function get_contenttype()
	{
		return vB_Search_Core::get_instance()->get_contenttypeid('vBBlog', 'BlogComment');
	}

	public function can_search($user)
	{
		//blog level permissions handled in lookup, if we don't have a record its because
		//we can't see it.
		if (!$this->record)
		{
			return false;
		}

		//check state
		//if it its visible, we're all good
		if ($this->record['comment_state'] == 'visible')
		{
			return true;
		}

		//otherwise we need to check permissions
		else
		{
			if (can_moderate_blog())
			{
				return true;
			}

			if ($this->record['comment_state'] == 'deleted')
			{
				if (can_moderate_blog())
				{
					return true;
				}
			}

			if ($this->record['comment_state'] == 'moderation')
			{
				if((can_moderate_blog('canmoderatecomments')))
				{
					return true;
				}
			}

			//otherwise we have to be a member.  We skip a couple of checks regarding
			//the owner permissions to avoid loading them (could be expensive for lots
			//of different blogs).  Essentially if a user is a member of a blog that is
			//no longer marked to allow group joins then they may be able to see deleted
			//or moderated comments in a search result for that blog.
			//Otherwise we follow the logic in is_member_of
			if ($this->record['userid'] == $user->getField('userid'))
			{
				return true;
			}

			$members = explode(',', str_replace(' ', '', $this->record['memberids']));
			$can_search = (in_array($user->getField('userid'), $members) AND
				$user->hasPermission('vbblog_general_permissions', 'blog_canjoingroupblog'));
			return $can_search;
		}
	}

	public function get_group_item()
	{
		return vBBlog_Search_Result_BlogEntry::create_from_record($this->record);
	}

	public function render($current_user, $criteria, $template_name = '')
	{
		if (!$this->record)
		{
			return "";
		}

		if (!strlen($template_name)) {
			$template_name = 'blog_comment_search_result';
		}

		global $vbulletin, $show;

		$comment = $this->record;
		$canmoderation = (can_moderate_blog('canmoderatecomments') OR $vbulletin->userinfo['userid'] == $blog['userid']);

		$comment['comment_date'] = vbdate($vbulletin->options['dateformat'], $comment['dateline'], true);
		$comment['comment_time'] = vbdate($vbulletin->options['timeformat'], $comment['dateline']);
		$comment['comment_summary'] = $this->get_summary_text($comment['comment_pagetext'], 200,
			$criteria->get_highlights());

		$templater = vB_Template::create($template_name);
		$templater->register('commentinfo', $comment);
		$templater->register('dateline', $this->message['dateline']);
		$templater->register('dateformat', $vbulletin->options['dateformat']);
		$templater->register('timeformat', $vbulletin->options['default_timeformat']);
		$text = $templater->render();

		return $text;
	}

	private function get_summary_text($text, $length, $highlightwords)
	{
		$strip_quotes = true;

		//strip quotes unless they contain a word that we are searching for
		$page_text = preg_replace('#\[quote(=(&quot;|"|\'|)??.*\\2)?\](((?>[^\[]*?|(?R)|.))*)\[/quote\]#siUe',
			"process_quote_removal('\\3', \$highlightwords)", $text);

		// Deal with the case that quote was the only content of the post
		if (trim($page_text) == '')
		{
			$page_text = $text;
			$strip_quotes = false;
		}

		return htmlspecialchars_uni(fetch_censored_text(
			trim(fetch_trimmed_title(strip_bbcode($page_text, $strip_quotes), $length))));
	}

	public function get_record()
	{
		return $this->record;
	}

	private $record = null;
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 28678 $
|| ####################################################################
\*======================================================================*/
