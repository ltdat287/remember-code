<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * Test Widget Controller
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 35350 $
 * @since $Date: 2010-02-05 18:49:05 -0600 (Fri, 05 Feb 2010) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Widget_RecentPosts extends vBCms_Widget
{
	/*Properties====================================================================*/

	/**
	 * A package identifier.
	 * This is used to resolve any related class names.
	 * It is also used by client code to resolve the class name of this widget.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * A class identifier.
	 * This is used to resolve any related class names.
	 * It is also used by client code to resolve the class name of this widget.
	 *
	 * @var string
	 */
	protected $class = 'RecentPosts';

	/**
	 * Whether the content is configurable with getConfigView().
	 * @see vBCms_Widget::getConfigView()
	 *
	 * @var bool
	 */
	protected $canconfig = false;

	protected $default_previewlen = 150;

	/*Render========================================================================*/

	/**
	 * Returns the config view for the widget.
	 *
	 * @return vBCms_View_Widget				- The view result
	 */
	public function getConfigView($widget = false)
	{
		require_once DIR . '/includes/functions_databuild.php';
		fetch_phrase_group('cpcms');
		fetch_phrase_group('search');

		$this->assertWidget();

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'do'      => vB_Input::TYPE_STR,
			'forumchoice' => vB_Input::TYPE_ARRAY,
			'template_name' => vB_Input::TYPE_STR,
			'days' => vB_Input::TYPE_INT,
			'count'    => vB_Input::TYPE_INT
		));

		$view = new vB_View_AJAXHTML('cms_widget_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_widget_x', $this->widget->getTitle());

		$config = $this->widget->getConfig();

		if ((vB::$vbulletin->GPC['do'] == 'config') AND $this->verifyPostId())
		{
			$widgetdm = new vBCms_DM_Widget($this->widget);

			if (vB::$vbulletin->GPC_exists['template_name'])
			{
				$config['template_name'] = vB::$vbulletin->GPC['template_name'];
			}

			$config['forumchoice'] = vB::$vbulletin->GPC_exists['forumchoice']?
				vB::$vbulletin->GPC['forumchoice'] : null;

			if (vB::$vbulletin->GPC_exists['count'])
			{
				$config['count'] = vB::$vbulletin->GPC['count'];
			}

			if (vB::$vbulletin->GPC_exists['template_name'])
			{
				$config['template_name'] = vB::$vbulletin->GPC['template_name'];
			}

			$widgetdm->set('config', $config);

			if ($this->content)
			{
				$widgetdm->setConfigNode($this->content->getNodeId());
			}

			$widgetdm->save();

			if (!$widgetdm->hasErrors())
			{
				if ($this->content)
				{
					$segments = array('node' => $this->content->getNodeURLSegment(),
										'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'EditPage'));
					$view->setUrl(vB_View_AJAXHTML::URL_FINISHED, vBCms_Route_Content::getURL($segments));
				}

				$view->setStatus(vB_View_AJAXHTML::STATUS_FINISHED, new vB_Phrase('vbcms', 'configuration_saved'));
			}
			else
			{
				if (vB::$vbulletin->debug)
				{
					$view->addErrors($widgetdm->getErrors());
				}

				// only send a message
				$view->setStatus(vB_View_AJAXHTML::STATUS_MESSAGE, new vB_Phrase('vbcms', 'configuration_failed'));
			}
		}
		else
		{
			// add the config content
			$configview = $this->createView('config');

			if (!isset($config['template_name']) OR ($config['template_name'] == '') )
			{
				$config['template_name'] = 'vbcms_widget_staticbb_page';
			}
			// add the config content
			$configview->template_name = $config['template_name'];
			$configview->forumchoice_select = $this->getForums($config);
			$configview->days = $config['days'];
			$configview->count = $config['count'];
			$this->addPostId($configview);

			$view->setContent($configview);

			// send the view
			$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_widget'));
		}

		return $view;
	}

	/**
	 * This lists the forums for the select list
	 *
	 @param mixed $config - array of current configuration for this widget
	 * @return
	 */
	private function getForums($config, $name = 'forumchoice')
	{
		global $vbulletin, $vbphrase, $show;
		require_once DIR . '/includes/functions_search.php';

		//this will fill out $searchforumids as well as set the depth param in $vbulletin->forumcache
		global $searchforumids;
		fetch_search_forumids_array();


		$options = "";
		foreach ($searchforumids AS $forumid)
		{
			$forum = $vbulletin->forumcache["$forumid"];

			if (trim($forum['link']))
			{
				continue;
			}

			$optionvalue = $forumid;
			$optiontitle = "$forum[depthmark] $forum[title_clean]";

			if ($vbulletin->options['fulltextsearch'] AND
				!($vbulletin->userinfo['forumpermissions'][$forumid] & $vbulletin->bf_ugp_forumpermissions['canviewthreads']))
			{
				$optiontitle .= '*';
				$show['cantsearchposts'] = true;
			}

			$optionselected = '';

			if ($config['forumchoice'] AND in_array($forumid, $config['forumchoice']))
			{
				$optionselected = 'selected="selected"';
				$haveforum = true;
			}

			$options .= render_option_template($optiontitle, $forumid, $optionselected,
				'fjdpth' . min(4, $forum['depth']));
		}

		$select = "<select name=\"" .$name."[]\" multiple=\"multiple\" size=\"4\" $style_string>\n" .
					render_option_template($vbphrase['search_all_open_forums'], '',
						$haveforum ? '' : 'selected="selected"') .
					render_option_template($vbphrase['search_subscribed_forums'], 'subscribed') .
					$options .
				 	"</select>\r";
		return $select;

	}



	/**
	 * Fetches the standard page view for a widget.
	 *
	 * @param bool $skip_errors					- If using a collection, omit widgets that throw errors
	 * @return vBCms_View_Widget				- The resolved view, or array of views
	 */
	public function getPageView()
	{
		require_once DIR . "/includes/functions_user.php";

		$this->assertWidget();

		// Create view
		$this->config = $this->widget->getConfig();
		if (!isset($this->config['template_name']) OR ($this->config['template_name'] == '') )
		{
			$this->config['template_name'] = 'vbcms_widget_recentthreads_page';
		}

		$hashkey = $this->getHash();

		//get the data
		$cache_data = vB_Cache::instance()->read($hashkey, false, false);
		if (!$cache_data)
		{
			$cache_data = $this->getPosts();
			vB_Cache::instance()->write($hashkey,
				   $cache_data, $this->cache_ttl, true, false);
		}

		// Create view
		$view = new vBCms_View_Widget($this->config['template_name']);
		$view->posts = $cache_data;
		$view->class = $this->widget->getClass();
		$view->title = $this->widget->getTitle();
		$view->description = $this->widget->getDescription();
		return $view;
	}

	/**
	 * Fetches the array of posts
	 *
	 * @return array				- the post information
	 */
	private function getPosts()
	{
		require_once DIR . "/includes/functions_user.php";

		$forumids = array_keys(vB::$vbulletin->forumcache);

		$datecut = TIMENOW - ($this->config['days'] * 86400);

		foreach ($forumids AS $forumid)
		{
			$forumperms =& vB::$vbulletin->userinfo['forumpermissions']["$forumid"];
			if ($forumperms & vB::$vbulletin->bf_ugp_forumpermissions['canview']
				AND ($forumperms & vB::$vbulletin->bf_ugp_forumpermissions['canviewothers'])
				AND (($forumperms & vB::$vbulletin->bf_ugp_forumpermissions['canviewthreads']))
				AND verify_forum_password($forumid, vB::$vbulletin->forumcache["$forumid"]['password'], false)
				)
			{
				$forumchoice[] = $forumid;
			}
		}

		if (empty($forumchoice))
		{
			return false;
		}
		$forumsql = "AND thread.forumid IN(" . implode(',', $forumchoice) . ")";

		$associatedthread = (vB::$vbulletin->options['vbcmsforumid'] ?
				" AND (thread.forumid <> " . vB::$vbulletin->options['vbcmsforumid'] . ")" : '');

		$posts = vB::$vbulletin->db->query_read_slave($sql = "
			SELECT post.dateline, post.pagetext, post.allowsmilie, post.postid,
				thread.threadid, thread.title, thread.prefixid, post.attach, thread.replycount,
				forum.forumid, post.title AS posttitle, post.dateline AS postdateline,
				user.*
				" . (vB::$vbulletin->options['avatarenabled'] ? ",avatar.avatarpath,
				(customavatar.userid) AS hascustomavatar, customavatar.dateline AS avatardateline,
				customavatar.width AS avwidth,customavatar.height AS avheight" : "") .
		 "	FROM " . TABLE_PREFIX . "post AS post
			JOIN " . TABLE_PREFIX . "thread AS thread ON (thread.threadid = post.threadid)
			JOIN " . TABLE_PREFIX . "forum AS forum ON(forum.forumid = thread.forumid)
			LEFT JOIN " . TABLE_PREFIX . "user AS user ON (post.userid = user.userid)
			" . (vB::$vbulletin->options['avatarenabled'] ? "LEFT JOIN " . TABLE_PREFIX . "avatar AS avatar ON(avatar.avatarid = user.avatarid) LEFT JOIN " . TABLE_PREFIX . "customavatar AS customavatar ON(customavatar.userid = user.userid)" : "") . "
			WHERE 1=1
				$forumsql
				$associatedthread
				AND thread.visible = 1
				AND post.visible = 1
				AND thread.open <> 10
				AND post.dateline > $datecut
				$globalignore
				" . ($this->userinfo['ignorelist'] ? "AND post.userid NOT IN (" . implode(',', explode(' ', $this->userinfo['ignorelist'])) . ")": '') . "
			ORDER BY post.dateline DESC
			LIMIT 0," . intval($this->config['count']) . "
		");

		$parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());

		while ($post = vB::$vbulletin->db->fetch_array($posts))
		{
			$post['title'] = fetch_trimmed_title($post['title'], $this->config['newposts_titlemaxchars']);

			$post['url'] = fetch_seo_url('thread', $post, array('p' => $post['postid'])) . '#post' . $post['postid'];
			$post['newposturl'] = fetch_seo_url('thread', $post, array('goto' => 'newpost'));

			$post['date'] = vbdate(vB::$vbulletin->options['dateformat'], $post['dateline'], true);
			$post['time'] = vbdate(vB::$vbulletin->options['timeformat'], $post['dateline']);
			$previewtext = $parser->get_preview($post['pagetext'], $this->default_previewlen, false);
			$previewtext = preg_replace('@<a href[^>]*?>.*?</a>@siu', '', $previewtext);
			$previewtext = strip_tags($previewtext);
			$post['previewtext'] = $previewtext;
			$post['pagetext'] = $parser->do_parse($post['pagetext']);

			if (intval($post['userid']))
			{
				$avatar = fetch_avatar_from_record($post);
			}

			if (!isset($avatar) OR (count($avatar) < 2))
			{
				$avatar = false;
			}
			$post['avatarurl'] = isset($avatar[0]) ? $avatar[0] : false;
			unset($avatar);
			$postarray[$post['postid']] = $post;
		}
		return $postarray;

	}



	/**
	 * Fetches the hash key for hashing
	 *
	 * @return string				- The hash key
	 */
	public function getHash()
	{
		$context = new vB_Context('widget' ,
		array(
			'widgetid' => $this->widget->getId(),
			'permissions' => vB::$vbulletin->userinfo['forumpermissions'],
			'ignorelist' => vB::$vbulletin->userinfo['ignorelist'],
			THIS_SCRIPT)
		);

		return strval($context);
	}

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 35350 $
|| ####################################################################
\*======================================================================*/