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
$bbcode_parser = false;

require_once (DIR . '/vb/search/result.php');
require_once (DIR . '/includes/blog_functions_search.php');

define('VBBLOG_PERMS', true);

/**
 *
 * @package vBulletin
 * @subpackage Search
 */
class vBBlog_Search_Result_BlogEntry extends vB_Search_Result
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
			return new vBBlog_Search_Result_BlogEntry();
		}
	}

	public static function create_array($ids)
	{
		global $vbulletin, $usercache;
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

		$set = $vbulletin->db->query_read_slave("
			SELECT blog.*, IF(blog_user.title <> '', blog_user.title, blog.username) AS blogtitle,
			blog_text.pagetext
			FROM " . TABLE_PREFIX ."blog AS blog
			LEFT JOIN " . TABLE_PREFIX ."blog_text AS blog_text on blog_text.blogtextid = blog.firstblogtextid
			$blog_user_join $perm_parts[join]
			WHERE blog.blogid IN (" . implode(',', array_map('intval', $ids)) . ") AND ($perm_parts[where])
		");

		$items = array();
		while ($record = $vbulletin->db->fetch_array($set))
		{
			$item = new vBBlog_Search_Result_BlogEntry();
			$item->record = $record;
			$items[$record['blogid']] = $item;
		}

		return $items;
	}

	public function create_from_record($record)
	{
		$item = new vBBlog_Search_Result_BlogEntry();
		$item->record = $record;
		return $item;
	}

	protected function __construct() {}

	public function get_contenttype()
	{
		return vB_Search_Core::get_instance()->get_contenttypeid('vBBlog', 'BlogEntry');
	}

	public function can_search($user)
	{
		//if we sucessfully loaded it, we can search on it.
		return (bool) $this->record;
	}

	public function render($current_user, $criteria, $template_name = '')
	{
		global $show;
		global $vbulletin;

		require_once(DIR . '/includes/class_bbcode.php');
		require_once(DIR . '/includes/class_bbcode_blog.php');
		require_once (DIR . '/includes/functions.php');
		require_once (DIR . '/includes/blog_functions.php');

		if (!$this->record)
		{
			return "";
		}

		if (!strlen($template_name)) {
			$template_name = 'blog_search_results_result';
		}

		if (! $this->bbcode_parser )
		{
			$this->bbcode_parser = new vB_BbCodeParser_Blog_Snippet($vbulletin, fetch_tag_list('', true));
//			$this->bbcode_parser->set_parse_userinfo($vbulletin->userinfo, $vbulletin->userinfo['permissions']);
		}


		$blog = $this->record;
		$canmoderation = (can_moderate_blog('canmoderatecomments') OR $vbulletin->userinfo['userid'] == $blog['userid']);
		$blog['trackbacks_total'] = $blog['trackback_visible'] + ($canmoderation ? $blog['trackback_moderation'] : 0);
		$blog['comments_total'] = $blog['comments_visible'] + ($canmoderation ? $blog['comments_moderation'] : 0);
		$blog['lastcommenter_encoded'] = urlencode($blog['lastcommenter']);
		$blog['blogtitle'] = htmlspecialchars_decode($blog['blogtitle']);
		$blog['title'] = htmlspecialchars_decode($blog['title']);
		$blog['lastposttime'] = vbdate($vbulletin->options['timeformat'], $blog['lastcomment']);
		$blog['lastpostdate'] = vbdate($vbulletin->options['dateformat'], $blog['lastcomment'], true);
		$blog['lastpostdate'] = vbdate($vbulletin->options['dateformat'], $blog['lastcomment'], true);
		$show['blogtitle'] = $blog['blogtitle'];

		$blog['pagetext'] = $this->bbcode_parser->do_parse($blog['pagetext'], true, false,
			 true , true, false);

		$blog['previewtext'] = vB_Search_Searchtools::getSummary(vB_Search_Searchtools::stripHtmlTags($blog['pagetext']),0, 120);

		$templater = vB_Template::create($template_name);
		$templater->register('blog', $blog);
		$templater->register('dateline', $blog['dateline']);
		$templater->register('dateformat', $vbulletin->options['dateformat']);
		$templater->register('timeformat', $vbulletin->options['default_timeformat']);
		return $templater->render();
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