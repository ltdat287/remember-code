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
 * Article Content Controller
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 28694 $
 * @since $Date: 2008-12-04 16:12:22 +0000 (Thu, 04 Dec 2008) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Content_Article extends vBCms_Content
{
	/*Properties====================================================================*/

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'Article';

	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * Controller Parameters.
	 *
	 * @var mixed
	 */
	protected $parameters = array('page' => 1);

	protected $parent_node = false;
	/*ViewInfo======================================================================*/
	protected $data_saved = false;

	/**
	 * Info required for view types.
	 *
	 * @var array
	 */
	protected $view_info = array(
		self::VIEW_LIST => vBCms_Item_Content::INFO_BASIC,
		self::VIEW_PREVIEW => /* vB_Item::INFO_BASIC | vBCms_Item_Content::INFO_NODE | vBCms_Item_Content::INFO_CONTENT */ 19,
		self::VIEW_PAGE => /* vB_Item::INFO_BASIC | vBCms_Item_Content::INFO_NODE | vBCms_Item_Content::INFO_CONTENT */ 19,
		self::VIEW_AGGREGATE => vBCms_Item_Content::INFO_NODE
	);

	protected $cache_ttl = 10;

	protected $editing = false;

	protected $default_previewlen = 120;
	/*Creation======================================================================*/


	/**
	 * Creates a new, empty content item to add to a node.
	 *
	 * @param vBCms_DM_Node $nodedm				- The DM of the node that the content is being created for
	 * @return int | false						- The id of the new content or false if not applicable
	 */
	public function createDefaultContent(vBCms_DM_Node $nodedm)
	{
		global $vbphrase;
		$contentdm = new vBCms_DM_Article();

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'nodeid'        => vB_Input::TYPE_UINT,
			'parentnode'    => vB_Input::TYPE_UINT,
			'parentid'      => vB_Input::TYPE_UINT,
			'blogcommentid' => vB_Input::TYPE_UINT,
			'postid'        => vB_Input::TYPE_UINT,
			'blogid'        => TYPE_UINT
			));

		//We should have a nodeid, but a parentnode is even better.
		($hook = vBulletinHook::fetch_hook('vbcms_article_defaultcontent_start')) ? eval($hook) : false;

		if ($this->parent_node)
		{
			$parentnode = $this->parent_node;
		}
		else if (vB::$vbulletin->GPC_exists['parentnode'] AND intval(vB::$vbulletin->GPC['parentnode'] ))
		{
			$parentnode = vB::$vbulletin->GPC['parentnode'];
		}
		else if (vB::$vbulletin->GPC_exists['parentid'] AND intval(vB::$vbulletin->GPC['parentid'] ))
		{
			$parentnode = vB::$vbulletin->GPC['parentid'];
		}
		else if (vB::$vbulletin->GPC_exists['nodeid'] AND intval(vB::$vbulletin->GPC['nodeid'] )
			and $record = vB::$vbulletin->db->query_first("SELECT contenttypeid, nodeid, parentnode FROM " .
			TABLE_PREFIX . "cms_node where nodeid = " . vB::$vbulletin->GPC['nodeid'] ))
		{
			$parentnode = vB_Types::instance()->getContentTypeID("vBCms_Section") == $record['contenttypeid'] ?
				$record['nodeid'] : $record['parentnode'];
		}
		else
		{
			throw (new vB_Exception_Content('No valid parent node'));
		}

		$nodedm->set('contenttypeid', vB_Types::instance()->getContentTypeID("vBCms_Article"));
		$nodedm->set('parentnode', $parentnode);
		$nodedm->set('publicpreview', 1);
		$nodedm->set('comments_enabled', 1);
		$nodedm->set('pagetext', $vbphrase['new_article']);
		$nodedm->set('title', $vbphrase['new_article']);

		if (vB::$vbulletin->GPC_exists['blogcommentid'] OR vB::$vbulletin->GPC_exists['blogid'])
		{
			$this->createFromBlogPost($nodedm);
		}
		else if (vB::$vbulletin->GPC_exists['postid'])
		{
			$this->createFromForumPost($nodedm);
		}
		else
		{
			$title = new vB_Phrase('vbcms', 'new_article');
			$nodedm->set('description', $title);
			$nodedm->set('title', $title);
			$nodedm->set('html_title', $title);
			$nodedm->set('userid', vB::$vbulletin->userinfo['userid']);
		}

		if (!($contentid = $nodedm->save()))
		{
			throw (new vB_Exception_Content('Failed to create default content for contenttype ' . get_class($this)));
		}
		($hook = vBulletinHook::fetch_hook('vbcms_article_defaultcontent_end')) ? eval($hook) : false;

		//at this point we have saved the data. We need to get the content id, which isn't easily available.
		if ($record = vB::$vbulletin->db->query_first("SELECT contentid FROM " . TABLE_PREFIX . "cms_node WHERE nodeid = $contentid"))
		{
			$nodedm->set('contentid', $record['contentid']);
		}

		return $contentid;
	}

	/*** This function sets the parent node for creating a new article
	****/
	public function setParentNode($parentnode)
	{

		$this->parent_node = $parentnode;
	}

	/*Configuration=================================================================*/

	/**
	 * Assigns a parameter value.
	 *
	 * @param string $parameter					- The key name of the parameter to set
	 * @param mixed $value						- The value to set it to
	 */
	protected function assignParameter($parameter, $value)
	{
		if ($parameter == 'page')
		{
			$this->parameters['page'] = max(intval($value), 1);
		}
		else
		{
			parent::assignParameter($parameter, $value);
		}
	}


	protected function createFromBlogPost($nodedm)
	{
		global $vbphrase;
		//make sure we are only called once;

		//let's confirm the rights
		$title = new vB_Phrase('vbcms', 'new_article');

		$sql = "
			SELECT
				starter.pagetext, starter.bloguserid, starter.title, blog.title AS blogtitle, blog.userid AS poststarter,
				txt.userid, txt.username, blog.postedby_username AS author, blog.blogid, txt.blogtextid, txt.dateline AS post_posted,
				blog.dateline AS post_started
			FROM " . TABLE_PREFIX . "blog_text AS starter
			INNER JOIN " . TABLE_PREFIX . "blog AS blog ON blog.firstblogtextid = starter.blogtextid
			INNER JOIN " . TABLE_PREFIX . "blog_text AS txt ";


		if (vB::$vbulletin->GPC_exists['blogcommentid'] )
		{
			$sql .= " ON blog.blogid = txt.blogid
		WHERE txt.blogtextid = "	. vB::$vbulletin->GPC['blogcommentid'];
		}
		else if (vB::$vbulletin->GPC_exists['blogid'])
		{
			$sql .= " ON blog.firstblogtextid = txt.blogtextid
		WHERE blog.blogid = " . vB::$vbulletin->GPC['blogid'];
		}
		else
		{
			return false;
		}

		if ($record = vB::$vbulletin->db->query_first($sql))
		{
			$nodedm->set('description', (strlen($record['title']) > 10 ? htmlspecialchars_decode($record['title']) : $tagline));
			$nodedm->set('userid', $record['userid']);
			$nodedm->set('title', $record['title']);
			$nodedm->set('html_title', $record['title']);
			$nodedm->set('url', vB_Friendly_Url::clean_entities($record['title']));
			$nodedm->set('contenttypeid', vB_Types::instance()->getContentTypeID("vBCms_Article"));
			$nodedm->info['skip_verify_pagetext'] = true;
			$nodedm->set('pagetext', $record['pagetext']);
			$nodedm->set('blogid', $record['blogid'] );
			$nodedm->set('posttitle', $record['blogtitle'] );
			$nodedm->set('poststarter', $record['poststarter'] );
			$nodedm->set('postauthor', $record['username'] );
			$nodedm->set('blogpostid', $record['blogtextid'] );
			$nodedm->set('post_started', $record['post_started'] );
			$nodedm->set('post_posted', $record['post_posted'] );
			($hook = vBulletinHook::fetch_hook('vbcms_articleblog_presave')) ? eval($hook) : false;

			$this->duplicateAttachments($nodedm, vB_Types::instance()->getContentTypeID('vBBlog_BlogEntry'), vB::$vbulletin->GPC['blogid']);
		}
	}

	protected function createFromForumPost($nodedm)
	{
		global $vbphrase;
		//make sure we are only called once;

		//let's confirm the rights

		if (vB::$vbulletin->GPC_exists['postid'] )
		{
			$sql = "
				SELECT
					post.pagetext, post.userid, post.title, post.username, post.threadid, post.dateline AS post_posted,
					thread.title AS threadtitle, thread.postuserid AS poststarter, thread.postusername AS author,
					thread.dateline AS post_started
				FROM " . TABLE_PREFIX . "post AS post
				INNER JOIN " . TABLE_PREFIX . "thread AS thread ON thread.threadid = post.threadid
				WHERE
					post.postid = " . vB::$vbulletin->GPC['postid'] . "
			";
		}
		else
		{
			return false;
		}

		if ($record = vB::$vbulletin->db->query_first($sql))
		{
			$title = strlen($record['title']) > 0 ? htmlspecialchars_decode($record['title']) : htmlspecialchars_decode($record['threadtitle']);

			$nodedm->set('description', $title);
			$nodedm->set('userid', $record['userid']);
			$nodedm->set('title', $title);
			$nodedm->set('html_title', $title);
			$url = vB_Friendly_Url::clean_entities($title );
			//$url = htmlspecialchars(str_replace(' ', '-', $title ));
			$nodedm->set('url', $url);
			$nodedm->set('contenttypeid', vB_Types::instance()->getContentTypeID("vBCms_Article"));
			$nodedm->info['skip_verify_pagetext'] = true;
			$nodedm->set('pagetext', $record['pagetext']);
			$nodedm->set('threadid', $record['threadid']);
			$nodedm->set('posttitle', $record['threadtitle'] );
			$nodedm->set('postauthor', $record['author'] );
			$nodedm->set('poststarter', $record['poststarter'] );
			$nodedm->set('postid', vB::$vbulletin->GPC['postid'] );
			$nodedm->set('post_started', $record['post_started'] );
			$nodedm->set('post_posted', $record['post_posted'] );
			($hook = vBulletinHook::fetch_hook('vbcms_articlepost_presave')) ? eval($hook) : false;

			$this->duplicateAttachments($nodedm, vB_Types::instance()->getContentTypeID('vBForum_Post'), vB::$vbulletin->GPC['postid']);
		}
	}

	protected function duplicateAttachments($nodedm, $sourceContenttypeid, $sourceContentid)
	{
		$attachids = array();
		$attachments = vB::$vbulletin->db->query_read("
			SELECT
				a.attachmentid, a.filedataid, a.state, a.filename, a.settings
			FROM " . TABLE_PREFIX . "attachment AS a
			WHERE
				a.contenttypeid = " . intval($sourceContenttypeid) . "
					AND
				a.contentid = " . intval($sourceContentid) . "
		");
		while ($attach = vB::$vbulletin->db->fetch_array($attachments))
		{
			$posthash = md5(TIMENOW . vB::$vbulletin->userinfo['userid'] . vB::$vbulletin->userinfo['salt']);
			vB::$vbulletin->db->query_write("
				INSERT INTO " . TABLE_PREFIX . "attachment
					(contenttypeid, userid, dateline, filedataid, state, filename, settings, posthash)
				VALUES
					(
						" . vB_Types::instance()->getContentTypeID("vBCms_Article") . ",
						" . vB::$vbulletin->userinfo['userid'] . ",
						" . TIMENOW . ",
						$attach[filedataid],
						'" . $attach['state'] . "',
						'" . vB::$vbulletin->db->escape_string($attach['filename']) . "',
						'" . vB::$vbulletin->db->escape_string($attach['settings']) . "',
						'" . $posthash . "'
					)
			");
			$attachids[$attach['attachmentid']] = $attachmentid = vB::$vbulletin->db->insert_id();
		}

		if (!empty($attachids))
		{
			$search = $replace = array();
			preg_match_all('#\[attach(?:=(right|left|config))?\](\d+)\[/attach\]#i', $nodedm->getField('pagetext'), $matches);
			foreach($matches[2] AS $key => $attachmentid)
			{
				if ($attachids[$attachmentid])
				{
					$align = $matches[1]["$key"];
					$search[] = '#\[attach' . (!empty($align) ? '=' . $align : '') . '\](' . $attachmentid . ')\[/attach\]#i';
					$replace[] = '[attach' . (!empty($align) ? '=' . $align : '') . ']' . $attachids[$attachmentid] . '[/attach]';
				}
			}
			if (!empty($search))
			{
				$pagetext = preg_replace($search, $replace, $nodedm->getField('pagetext'));
				$nodedm->set('pagetext', $pagetext);
			}
		}
	}

	/*Render========================================================================*/

	/**
	 * Populates a view with the expected info from a content item.
	 *
	 * @param vB_View $view
	 * @param int $viewtype
	 */
	protected function populateViewContent(vB_View $view, $viewtype = self::VIEW_PAGE, $increment_count = true)
	{
		global $show;

		if ($_REQUEST['goto'] == 'newcomment')
		{
			require_once DIR . '/includes/functions_bigthree.php' ;

			$record = vB::$vbulletin->db->query_first("SELECT associatedthreadid
				FROM " . TABLE_PREFIX . "cms_nodeinfo WHERE nodeid = " . $this->getNodeId());
			$threadid = $record['associatedthreadid'];
			$threadinfo = verify_id('thread', $threadid, 1, 1);

			if (vB::$vbulletin->options['threadmarking'] AND vB::$vbulletin->userinfo['userid'])
			{
				vB::$vbulletin->userinfo['lastvisit'] = max($threadinfo['threadread'], $threadinfo['forumread'], TIMENOW - (vB::$vbulletin->options['markinglimit'] * 86400));
			}
			else if (($tview = intval(fetch_bbarray_cookie('thread_lastview', $threadid))) > vB::$vbulletin->userinfo['lastvisit'])
			{
				vB::$vbulletin->userinfo['lastvisit'] = $tview;
			}

			$coventry = fetch_coventry('string');
			$posts = vB::$vbulletin->db->query_first("
				SELECT MIN(postid) AS postid
				FROM " . TABLE_PREFIX . "post
				WHERE threadid = $threadinfo[threadid]
					AND visible = 1
					AND dateline > " . intval(vB::$vbulletin->userinfo['lastvisit']) . "
					". ($coventry ? "AND userid NOT IN ($coventry)" : "") . "
				LIMIT 1
			");

			$target_url = vB_Router::getURL();
			$join_char = strpos($target_url,'?') ? '&amp;' : '?';
			if ($posts['postid'])
			{
				exec_header_redirect($target_url . $join_char . "commentid=" . $posts['postid'] . "#post$posts[postid]");
			}
			else
			{
				exec_header_redirect($target_url . $join_char . "commentid=" . $threadinfo['lastpostid'] . "#post$threadinfo[lastpostid]");
			}
		}
		if ($_REQUEST['commentid'])
		{
			vB::$vbulletin->input->clean_array_gpc('r', array(
				'commentid' => vB_Input::TYPE_INT,
			));
			$postinfo = verify_id('post', vB::$vbulletin->GPC['commentid'], 1, 1);
			$record = vB::$vbulletin->db->query_first("SELECT associatedthreadid
				FROM " . TABLE_PREFIX . "cms_nodeinfo WHERE nodeid = " . $this->getNodeId());
			$threadid = $record['associatedthreadid'];

			// if comment id and node id do not match, we ignore commentid
			if ($postinfo['threadid'] == $threadid)
			{
				$getpagenum = vB::$vbulletin->db->query_first("
					SELECT COUNT(*) AS posts
					FROM " . TABLE_PREFIX . "post AS post
					WHERE threadid = $threadid AND visible = 1
					AND dateline <= $postinfo[dateline]
				");
				$_REQUEST['commentpage'] = ceil($getpagenum['posts'] / 20);
			}
		}

		if ($_REQUEST['do']== 'apply' OR $_REQUEST['do'] == 'update' OR $_REQUEST['do'] == 'movenode')
		{
			$this->SaveData();
		}

		($hook = vBulletinHook::fetch_hook('vbcms_article_populate_start')) ? eval($hook) : false;

		//Now we need to get the settings for turning off content. There is the "settingsforboth" flag, which says whether we even apply
		// the settings to the current page, and there are the six "show" variables.

		if ($_REQUEST['do'] == 'delete' AND $this->content->canEdit())
		{
			$dm = $this->content->getDM();
			$dm->delete();
			$this->cleanContentCache();

			// Create route to redirect the user to
			$route = new vBCms_Route_Content();
			$route->node = $this->content->getParentId();
			$_REQUEST['do'] = '';
			throw (new vB_Exception_Reroute($route));
		}

		//When we come from the link to upgrade a blog post, blog, or forum post, the
		// router puts us here.
		$settings_for = $this->content->getSettingsForboth();
		$showfor_this = (((self::VIEW_PAGE == $viewtype)
			AND ($settings_for == 0)) OR ((self::VIEW_PREVIEW == $viewtype)
			AND ($settings_for == 2))) ? 0 : 1;

		$view->showtitle = (($showfor_this AND !$this->content->getShowTitle()))? 0 : 1;
		$view->showpreviewonly = (($showfor_this AND !$this->content->getShowPreviewonly()))? 0 : 1;
		$view->showuser = (($showfor_this AND !$this->content->getShowUser()))? 0 : 1;
		$view->showupdated = (($showfor_this AND !$this->content->getShowUpdated()))? 0 : 1;
		$view->showviewcount = (($showfor_this AND !$this->content->getShowViewcount()))? 0 : 1;
		$view->showpublishdate = (($showfor_this AND !$this->content->getShowPublishdate()))? 0 : 1;
		$view->lastupdated = $this->content->getLastUpdated();
		$showpreviewonly = (($showfor_this AND !$this->content->getShowPreviewonly()))? 0 : 1;

		parent::populateViewContent($view, $viewtype);

		$segments = array('node' => vBCms_Item_Content::buildUrlSegment($this->content->getNodeId(), $this->content->getUrl()), 'action' =>'view');
		$view->page_url =  vBCms_Route_Content::getURL($segments);

		if ($this->editing)
		{
			$view->pagetext = $this->content->getPageText();
		}
		else
		{
			$bbcode_parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());

			// Articles will generally have an attachment but they should still keep a counter so that this query isn't always running
			require_once(DIR . '/packages/vbattach/attach.php');
			$attach = new vB_Attach_Display_Content(vB::$vbulletin, 'vBCms_Article');
			$view->attachments = $attach->fetch_postattach(0, $this->content->getNodeId());
			$bbcode_parser->attachments = $view->attachments;

			$bbcode_parser->setOutputPage($this->parameters['page']);
			$view->pagetext = $bbcode_parser->do_parse($this->content->getPageText(),
				vBCMS_Permissions::canUseHtml($this->getNodeId(), $this->content->getContentTypeId(),
				$this->content->getUserId()));
			$view->parenttitle = $this->content->getParentTitle();
			$view->pagelist = $bbcode_parser->getPageTitles();
			$view->nodesegment = $this->content->getUrlSegment();
			$view->current_page = $this->parameters['page'];
			$show['lightbox'] = (vB::$vbulletin->options['lightboxenabled'] AND vB::$vbulletin->options['usepopups']);
			$view->attachments = $bbcode_parser->attachments;

			$viewinfo = array(
				'userid' => $view->userid
			);
			// The last false forces images to display as links if they aren't used inline
			$attach->process_attachments($viewinfo, $view->attachments, false, false, true, false);
			unset($viewinfo['userid']);
			foreach ($viewinfo AS $key => $value)
			{
				$view->$key = $value;
				if ($value)
				{
					$view->showattachments = true;
				}
			}
		}

		// Only break pages for the page view
		if ((self::VIEW_PAGE == $viewtype) OR (self::VIEW_PREVIEW == $viewtype))
		{
			if (self::VIEW_PAGE == $viewtype)
			{
				if ($increment_count)
				{
					//update the view count
					vB::$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX .
							"cms_nodeinfo set viewcount = viewcount + 1 where nodeid = " . $this->content->getNodeId());
				}

				//tagging code
				require_once DIR . '/includes/class_taggablecontent.php';
				$taggable = vB_Taggable_Content_Item::create(vB::$vbulletin, $this->content->getContentTypeId(),
					$this->content->getContentId(), $this->content);
				$view->tags = $taggable->fetch_rendered_tag_list();
				$view->tag_count = $taggable->fetch_existing_tag_count();
				$view->showtags = vB::$vbulletin->options['threadtagging'];

				// promoted threadid
				if ($promoted_threadid = $this->content->getThreadId())
				{
					if ($promoted_threadid = verify_id('thread', $promoted_threadid, false))
					{
						// get threadinfo
						$threadinfo = fetch_threadinfo($promoted_threadid);
						$forumperms = fetch_permissions($threadinfo['forumid']);

						// check permissions
						if ($threadinfo['visible'] != 1)
						{
							$promoted_threadid = false;
						}
						else if (!($forumperms & vB::$vbulletin->bf_ugp_forumpermissions['canview'])
							OR !($forumperms & vB::$vbulletin->bf_ugp_forumpermissions['canviewthreads'])
							OR (!($forumperms & vB::$vbulletin->bf_ugp_forumpermissions['canviewothers'])
								AND ($threadinfo['postuserid'] != vB::$vbulletin->userinfo['userid'] OR vB::$vbulletin->userinfo['userid'] == 0)
							))
						{
							$promoted_threadid = false;
						}
						else
						{
							// check forum password
							$foruminfo = fetch_foruminfo($threadinfo['forumid']);

							if ($foruminfo['password'] AND !verify_forum_password($foruminfo['forumid'], $foruminfo['password'], false))
							{
								$promoted_threadid = false;
							}
						}

						$view->promoted_threadid = $promoted_threadid;
					}
				}

				// get pagelist for navigation
				$view->postitle = $this->content->getPostTitle();
				$view->poststarter = $this->content->getPostStarter();
				$view->postauthor = $this->content->getPostAuthor();
				$view->promoted_blogid = $this->content->getBlogId();
				$view->comment_count = $this->content->getReplyCount();
				$join_char = strpos($view->page_url,'?') ? '&amp;' : '?';
				$view->newcomment_url = $view->page_url . "#new_comment";
				$view->authorid = ($this->content->getUserId());
				$view->authorname = ($this->content->getUsername());
				$view->viewcount = ($this->content->getViewCount());
				$view->replycount = ($this->content->getReplyCount());
				$view->postid = ($this->content->getPostId());
				$view->blogpostid = ($this->content->getBlogPostId());
				$view->post_started = ($this->content->getPostStarted());
				$view->post_posted = ($this->content->getPostPosted());
				$view->can_edit = $this->content->canEdit();
				$view->parentid = $this->content->getParentId();

				//check to see if there is an associated thread.
				if ($associatedthreadid = $this->content->getAssociatedThreadId()
					and $this->content->getComments_Enabled())
				{
					$comment_block = new vBCms_Comments();
					$view->comment_block = $comment_block->getPageView($this->content->getNodeId(),
						$view->page_url);
				}

			}
			else if (self::VIEW_PREVIEW == $viewtype)
			{

				if ($showpreviewonly)
				{
					$pagetext = $this->content->getPreviewText();

					if ($pagetext == '' OR ($pagetext = strip_bbcode($pagetext, false, false, false, true) ) == '')
					{
						$pagetext = strip_bbcode($this->content->getPageText(), false, false, false, true);
						$pagetext = nl2br(vbchop($pagetext, $this->preview_length));
					}

					$view->previewtext = $pagetext;
					$view->preview_chopped = 1;

				}
				else
				{
					$view->previewtext = $view->pagetext;

					if (count($view->pagelist) > 1)
					{
						$view->preview_chopped = 1;
					}
				}

				$segments = array('node' => $this->content->getNodeId() . '-' . $this->content->getUrl(), 'action' =>'edit');
				$view->edit_url =  vBCms_Route_Content::getURL($segments) ;
				$view->read_more_phrase = new vB_Phrase('vbcms', 'read_more');
				$view->parenttitle = $this->content->getParentTitle();
				$view->pagetext = $pagetext;
				$view->setpublish = $view->published = $this->content->getPublished();
				$view->publishdate = $this->content->getPublishDateLocal();
				$view->promoted_blogid = $this->content->getBlogId();
				$view->comment_count = $this->content->getReplyCount();
				$join_char = strpos($view->page_url,'?') ? '&amp;' : '?';
				$view->newcomment_url = $view->page_url . "#new_comment";
				$view->authorid = ($this->content->getUserId());
				$view->authorname = ($this->content->getUsername());
				$view->viewcount = ($this->content->getViewCount());
				$view->replycount = ($this->content->getReplyCount());
				$view->postid = ($this->content->getPostId());
				$view->blogpostid = $this->content->getBlogPostId();
				$view->can_edit = $this->content->canEdit();
				$view->parentid = $this->content->getParentId();
				$view->post_started = $this->content->getPostStarted();
				$view->post_posted = $this->content->getPostPosted();


				if ($view->previewimage= $this->content->getPreviewImage())
				{
					$view->haspreview = true;
					$view->imagewidth= $this->content->getImageWidth();
					$view->imageheight= $this->content->getImageHeight();
				}

				if ($view->previewvideo= $this->content->getPreviewVideo())
				{
					$view->haspreviewvideo = true;
				}

				if (($associatedthreadid = $this->content->getAssociatedThreadId())
					AND $this->content->getComments_Enabled() AND intval($this->content->getReplyCount()) > 0)
				{
					$view->echo_comments = 1;
					$view->comment_count = $this->content->getReplyCount();
				}
				else
				{
					$view->echo_comments = 0;
					$view->comment_count = 0;
				}
			}
		}

		//If this was promoted from a blog or post, we need to verify the permissions.
		if (intval($view->blogpostid))
		{
			$view->can_view_post =
				(!($vbulletin->userinfo['permissions']['vbblog_general_permissions'] & $vbulletin->bf_ugp_vbblog_general_permissions['blog_canviewothers'])) ?
				0 : 1 ;
		}
		else if (intval($view->postid))
		{
			$user = new vB_Legacy_CurrentUser();
			if ($post = vB_Legacy_Post::create_from_id($view->postid))
			{
				$view->can_view_post = $post->can_view($user) ? 1 : 0;
			}
		}


		$view->setpublish = $this->content->getSetPublish();
		$view->publishdate = $this->content->getPublishDate();
		$view->published = $this->content->getPublished() ?
			1 : 0;

		$view->publishdatelocal = vbdate(vB::$vbulletin->options['dateformat'], $this->content->getPublishDate());
		$view->publishtimelocal = vbdate( vB::$vbulletin->options['timeformat'], $this->content->getPublishDate() );

		//Get links to the author, section, and categories search pages
		//categories- this comes as an array
		$view->categories = $this->content->getCategories();
		//author search
		$route_info = 'author/' . $this->content->getUserid() .
			($this->content->getUsername() != '' ? '-' . str_replace(' ', '-',
				vB_Search_Searchtools::stripHtmlTags($this->content->getUsername())) : '');
		$view->author_url = vB_Route::create('vBCms_Route_List', "$route_info/1")->getCurrentURL();

		// prepare the member action drop-down menu
		$view->memberaction_dropdown = construct_memberaction_dropdown(fetch_userinfo($this->content->getUserId()));

		//Section
		$route_info = 'section/' .$this->content->getParentId() .
			($this->content->getParentURLSegment() != '' ? '-' . str_replace(' ', '-',
				vB_Search_Searchtools::stripHtmlTags($this->content->getParentURLSegment())) : '');
		$view->section_list_url = vB_Route::create('vBCms_Route_List', "$route_info")->getCurrentURL();
		//and the content
		$route_info = $this->content->getParentId() .
			($this->content->getParentURLSegment() != '' ? '-' . str_replace(' ', '-',
				vB_Search_Searchtools::stripHtmlTags($this->content->getParentURLSegment())) : '');
		$view->section_url = vB_Route::create('vBCms_Route_Content', $route_info)->getCurrentURL();

		$view->html_title = $this->content->getHtmlTitle();
		$view->title = $this->content->getTitle();
		$view->contenttypeid = vB_Types::instance()->getContentTypeID("vBCms_Article");
		$view->dateformat = vB::$vbulletin->options['dateformat'];
		$view->showrating = $this->content->getShowRating();
		($hook = vBulletinHook::fetch_hook('vbcms_article_populate_end')) ? eval($hook) : false;

		return $view;
	}

	//At this point some of the images may be in the form [IMG]<base url>/attachment.php?attachmentid=<number><stuff>[/IMG]
	//We need to fix the editor so this isn't necessary, but for now....
	protected function setAttachments($pagetext)
	{
		$offset = 0;

		while($offset < strlen($pagetext) AND ($i = stripos($pagetext, '[IMG]', $offset)) !== false AND $j = stripos($pagetext, '[/IMG]', $offset) AND $j > $i)
		{
			$extract = substr($pagetext, $i, $j-$i + 6);
			if ($k = stripos($extract, 'attachment.php?attachmentid='))
			{
				$attachmentid = intval(substr($extract, $k + 28, min(strpos($pagetext, '&'), strpos($pagetext, '[/IMG]')) - ($k + 28) ) );
				$replace = '[ATTACH=CONFIG]' . $attachmentid . '[/ATTACH]' ;
				$pagetext = str_replace($extract, $replace , $pagetext);
				$offset = $offset + $j + 6 + ( strlen($replace) - strlen($extract));
			}
			else
			{
				$offset = $j + 6;
			}

		}
		return $pagetext;
	}


	/**** This saves the data from the form. It takes no parameters and returns no values
	 *
	 ****/
	protected function saveData()
	{
		if ($this->data_saved)
		{
			return true;
		}
		$this->data_saved = true;

		if (!$this->content->canEdit())
		{
			return $vb_phrase['no_edit_permissions'];
		}

		// collect error messages
		$errors = array();
		vB::$vbulletin->input->clean_array_gpc('p', array(
			'do'               => vB_Input::TYPE_STR,
			'message'          => vB_Input::TYPE_STR,
			'url'              => vB_Input::TYPE_NOHTML,
			'title'            => vB_Input::TYPE_NOHTML,
			'setpublish'       => vB_Input::TYPE_UINT,
			'html_title'       => vB_Input::TYPE_NOHTML,
			'publicpreview'    => vB_Input::TYPE_UINT,
			'new_parentid'     => vB_Input::TYPE_UINT,
			'comments_enabled' => vB_Input::TYPE_UINT,
			'wysiwyg'          => vB_Input::TYPE_BOOL,
			'parseurl'         => vB_Input::TYPE_BOOL,
			'posthash'         => vB_Input::TYPE_NOHTML,
		));
		($hook = vBulletinHook::fetch_hook('vbcms_article_save_start')) ? eval($hook) : false;

		// get pagetext
		$pagetext = vB::$vbulletin->GPC['message'];
		$html_title = vB::$vbulletin->GPC['html_title'];
		$title = vB::$vbulletin->GPC['title'];

		// unwysiwygify the incoming data
		if (vB::$vbulletin->GPC['wysiwyg'])
		{
			$html_parser = new vBCms_WysiwygHtmlParser(vB::$vbulletin);
			$pagetext = $html_parser->parse($pagetext);
		}

		//At this point some of the images may be in the form [IMG]<base url>/attachment.php?attachmentid=<number><stuff>[/IMG]
		//We need to fix the editor so this isn't necessary, but for now....
		$pagetext = $this->setAttachments($pagetext);

		$dm = $this->content->getDM();

		$old_sectionid = $this->content->getParentId();

		//set the values, for the dm and update the content.
		if ( vB::$vbulletin->GPC_exists['new_parentid'] AND intval(vB::$vbulletin->GPC['new_parentid']))
		{
			vBCms_ContentManager::moveSection(array($this->content->getNodeId()), vB::$vbulletin->GPC['new_parentid']);
			$new_sectionid = vB::$vbulletin->GPC['new_parentid'];
		}

		$dm->set('contentid', $this->content->getId());

		if (vB::$vbulletin->GPC_exists['publicpreview'])
		{
			$new_values['publicpreview'] = vB::$vbulletin->GPC['publicpreview'];
			$dm->set('publicpreview', vB::$vbulletin->GPC['publicpreview']);
		}

		if (vB::$vbulletin->GPC_exists['comments_enabled'])
		{
			$new_values['comments_enabled'] = vB::$vbulletin->GPC['comments_enabled'];
			$dm->set('comments_enabled', vB::$vbulletin->GPC['comments_enabled']);
		}

		if (vB::$vbulletin->GPC_exists['setpublish'])
		{
			$new_values['setpublish'] = vB::$vbulletin->GPC['setpublish'];
			$dm->set('setpublish', vB::$vbulletin->GPC['setpublish']);
		}

		if (vB::$vbulletin->GPC_exists['html_title'])
		{
			$new_values['html_title'] = vB::$vbulletin->GPC['html_title'];
			$dm->set('html_title', vB::$vbulletin->GPC['html_title']);
		}

		if (vB::$vbulletin->GPC_exists['url'])
		{
			$new_values['url'] = vB::$vbulletin->GPC['url'];
			$dm->set('url', vB::$vbulletin->GPC['url']);
		}

		$new_values['pagetext'] = $pagetext;
		$dm->info['parseurl'] = true;
		$dm->set('pagetext',$pagetext);

		if ($title)
		{
			$new_values['title'] = vB::$vbulletin->GPC['title'];
			$dm->set('title', $pagetext);
		}

		$bbcodesearch = array();

		// populate the preview image field with [img] if we can find one
		if (($i = stripos($pagetext, '[IMG]')) !== false and ($j = stripos($pagetext, '[/IMG]')) AND $j > $i)
		{
			$previewimage = substr($pagetext, $i+5, $j - $i - 5);
			if ($size = @getimagesize($previewimage))
			{
				$dm->set('previewimage', $previewimage);
				$dm->set('imagewidth', $size[0]);
				$dm->set('imageheight', $size[1]);
				$bbcodesearch[] = substr($pagetext, $i, $j + 6);
			}
		}
		// or populate the preview image field with [attachment] if we can find one
		else if (($i = stripos($pagetext, "[ATTACH=CONFIG]")) !== false and ($j = stripos($pagetext, '[/ATTACH]')) AND $j > $i)
		{
			$attachmentid = substr($pagetext, $i + 15, $j - $i - 15);

			if ($record = vB::$vbulletin->db->query_first("
				SELECT
					data.thumbnail_width, data.thumbnail_height, data.width, data.height
				FROM " . TABLE_PREFIX . "attachment AS attach
				INNER JOIN "  . TABLE_PREFIX . "filedata AS data ON (data.filedataid = attach.filedataid)
				WHERE
					attach.attachmentid = $attachmentid
			"))
			{

				$image_template = new vB_View('vbcms_image_src');
				$image_template->attachmentid = $attachmentid;
				$image_template->contenttypeid = vB_Types::instance()->getContentTypeID("vBCms_Article");
				$image_tag = $image_template->render();

				// parse the src attribute value from the image tag
				// since that is what we want to store in the db
				if (preg_match('/src=\"([^"]*)\"/', $image_tag, $matches) && isset($matches[1]))
				{
					$dm->set('previewimage', $matches[1]);
					$bbcodesearch[] = substr($pagetext, $i, $j + 9);
				}

				if ($record['thumbnail_width'] AND $record['thumbnail_height'])
				{
					$dm->set('imagewidth', $record['thumbnail_width']);
					$dm->set('imageheight', $record['thumbnail_height']);
				}
				else
				{
					$dm->set('imagewidth', $record['width']);
					$dm->set('imageheight', $record['height']);
				}
			}
		}
		// if there are no images in the article body, make sure we unset the preview in the db
		else
		{
			$dm->set('previewimage', '');
			$dm->set('imagewidth', 0);
			$dm->set('imageheight', 0);
		}

		// Try to populate previewvideo html
		$parseurl = false;
		$providers = $search = $replace = $previewvideo = array();
		($hook = vBulletinHook::fetch_hook('data_preparse_bbcode_video_start')) ? eval($hook) : false;

		// Convert video bbcode with no option
		if (stripos($pagetext, '[video') !== false OR $parseurl)
		{
			if (!$providers)
			{
				$bbcodes = vB::$db->query_read_slave("
					SELECT
						provider, url, regex_url, regex_scrape, tagoption
					FROM " . TABLE_PREFIX . "bbcode_video
					ORDER BY priority
				");
				while ($bbcode = vB::$db->fetch_array($bbcodes))
				{
					$providers["$bbcode[tagoption]"] = $bbcode;
				}
			}

			$scraped = 0;
			if (!empty($providers) AND preg_match_all('#\[video[^\]]*\](.*?)\[/video\]#si', $pagetext, $matches))
			{
				foreach ($matches[1] AS $key => $url)
				{
					$match = false;
					foreach ($providers AS $provider)
					{
						$addcaret = ($provider['regex_url'][0] != '^') ? '^' : '';
						if (preg_match('#' . $addcaret . $provider['regex_url'] . '#si', $url, $match))
						{
							break;
						}
					}
					if ($match)
					{
						if (!$provider['regex_scrape'] AND $match[1])
						{
							$previewvideo['provider'] = $provider['tagoption'];
							$previewvideo['code'] = $match[1];
							$previewvideo['url'] = $url;
							$bbcodesearch[] = $matches[0][$key];
							break;
						}
						else if ($provider['regex_scrape'] AND vB::$vbulletin->options['bbcode_video_scrape'] > 0 AND $scraped < vB::$vbulletin->options['bbcode_video_scrape'])
						{
							require_once(DIR . '/includes/functions_file.php');
							$result = fetch_body_request($url);
							if (preg_match('#' . $provider['regex_scrape'] . '#si', $result, $scrapematch))
							{
								$previewvideo['provider'] = $provider['tagoption'];
								$previewvideo['code'] = $scrapematch[1];
								$previewvideo['url'] = $url;
								$bbcodesearch[] = $matches[0][$key];
								break;
							}
							$scraped++;
						}
					}
				}
			}
		}

		if ($previewvideo)
		{
			$templater = vB_Template::create('bbcode_video');
				$templater->register('url', $previewvideo['url']);
				$templater->register('provider', $previewvideo['provider']);
				$templater->register('code', $previewvideo['code']);
			$dm->set('previewvideo', $templater->render());
		}
		else
		{
			$dm->set('previewvideo', '');
		}

		// Remove preview video from the actual preview text since it appears next to it
		if ($bbcodesearch)
		{
			$pagetext_preview = str_ireplace($bbcodesearch, '', $pagetext);
		}
		else
		{
			$pagetext_preview = $pagetext;
		}

		$parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());
		$previewtext = $parser->get_preview($pagetext, $this->default_previewlen,
			 vBCMS_Permissions::canUseHtml($this->getNodeId(), $this->content->getContentTypeId(),
			$this->content->getUserId()));
		$dm->set('previewtext', $previewtext);
		$this->content->setInfo($new_values);

		//We may have some processing to do for public preview. Let's see if comments
		// are enabled. We never enable them for sections, and they might be turned off globally.
		vB::$vbulletin->input->clean_array_gpc('r', array(
			'publicpreview' => TYPE_UINT));


		$success = $dm->saveFromForm($this->content->getNodeId());
		$this->changed = true;

		if ($dm->hasErrors())
		{
			$fieldnames = array(
				'html_title' => new vB_Phrase('vbcms', 'html_title'),
				'title' => new vB_Phrase('global', 'title')
			);

			$view->errors = $dm->getErrors(array_keys($fieldnames));
			$view->error_summary = self::getErrorSummary($dm->getErrors(array_keys($fieldnames)), $fieldnames);
			$view->status = $view->error_view->title;
		}
		else
		{
			$view->status = new vB_Phrase('vbcms', 'content_saved');
			$this->cleanContentCache();
			vB::$vbulletin->db->query_write("
				UPDATE " . TABLE_PREFIX . "attachment
				SET
					posthash = '',
					contentid = " . intval($this->content->getNodeId()) . "
				WHERE
					posthash = '" . vB::$vbulletin->db->escape_string(vB::$vbulletin->GPC['posthash']) . "'
						AND
					contenttypeid = " . intval(vB_Types::instance()->getContentTypeID("vBCms_Article")) . "
			");
		}
		($hook = vBulletinHook::fetch_hook('vbcms_article_save_end')) ? eval($hook) : false;

		//invalidate the navigation cache.
		vB_Cache::instance()->event('sections_updated');
		vB_Cache::instance()->event($this->content->getContentCacheEvent());
		vB_Cache::instance()->cleanNow();
		$view->html_title = $html_title;
		$view->title = $title;
	}


	/**** This creates the edit user interface. It returns the edit view.
	 * @param none
	 *
	 * @return view
	 ****/
	public function getInlineEditBodyView()
	{
		global $vbphrase;
		require_once DIR . '/includes/functions_databuild.php';
		require_once DIR . '/includes/functions.php';
		fetch_phrase_group('cpcms');

		if ($styleid = $this->content->getStyleId())
		{
			$templates = array('editor_clientscript', 'editor_jsoptions_font',
			'editor_jsoptions_size','editor_smilie','editor_smilie_category','editor_smilie_row',
			'editor_smiliebox','editor_toolbar_colors','editor_toolbar_fontname',
			'editor_toolbar_fontsize','editor_toolbar_on','newpost_attachment',
			'newpost_disablesmiliesoption','tagbit_wrapper', 'vbcms_article_editor');
			$style = vB::$vbulletin->db->query_first_slave("
				SELECT * FROM " . TABLE_PREFIX . "style
				WHERE styleid = $styleid
			");
			fetch_stylevars($style, vB::$vbulletin->userinfo);
			cache_templates($templates, $style['templatelist'], true);
		}

		$this->editing = true;

		//confirm that the user has edit rights
		if (!$this->content->canEdit() AND !($this->getUserId() == vB::$vbulletin->userinfo['userid']))
		{
			return $vb_phrase['no_edit_permissions'];
		}


		vB::$vbulletin->input->clean_array_gpc('r', array(
			'postid' => vB_Input::TYPE_UINT,
			'blogcommentid' => vB_Input::TYPE_UINT,
			'do' => vB_Input::TYPE_STR,
			'blogid' => TYPE_UINT
		));

		if ($_REQUEST['do'] == 'delete')
		{
			$dm = $this->content->getDM();
			$dm->delete();
			$this->cleanContentCache();
			return $vbphrase['article_deleted'];
		}

		if ($_REQUEST['do'] == 'apply' OR $_REQUEST['do'] == 'update')
		{
			$this->SaveData();
		}



		require_once DIR . '/packages/vbcms/contentmanager.php';
		// Load the content item
		if (!$this->loadContent($this->getViewInfoFlags(self::VIEW_PAGE)))
		{
			throw (new vB_Exception_404());
		}

		global $show;

		$show['img_bbcode'] = true;
		// Get smiliecache and bbcodecache
		vB::$vbulletin->datastore->fetch(array('smiliecache','bbcodecache'));

		// Create view
		$view = $this->createView('inline', self::VIEW_PAGE);

		// Add the content to the view
		$view = $this->populateViewContent($view, self::VIEW_PAGE, false);
		$pagetext = $this->content->getPageText();

		// Get postings phrasegroup
		// need posting group
		require_once DIR . '/includes/functions_databuild.php';
		fetch_phrase_group('posting');

		// Build editor
		global $messagearea;
		require_once DIR . '/includes/functions_file.php';
		require_once DIR . '/includes/functions_editor.php';
		require_once(DIR . '/packages/vbattach/attach.php');

		$view->formid = "cms_article_data";
		//$attachinfo = array('nodeid' => $this->content->getNodeId(), 'url' => $this->content->getUrl());

		$attach = new vB_Attach_Display_Content(vB::$vbulletin, 'vBCms_Article');
		//this will set a number of its parameters if they are not already set.
		$posthash = null;
		$poststarttime = null;
		$postattach = array();
		$attachcount = 0;

		$values = "values[f]=" . $this->content->getNodeId() ;

		$attachmentoption = $attach->fetch_edit_attachments($posthash, $poststarttime, $postattach,
			$this->content->getNodeId(), $values, '', $attachcount);

		$attachinfo = array(
			'auth_type'     => (empty($_SERVER['AUTH_USER']) AND empty($_SERVER['REMOTE_USER'])) ? 0 : 1,
			'posthash'      => $posthash,
			'poststarttime' => $poststarttime,
			'userid'        => vB::$vbulletin->userinfo['userid'],
			'contenttypeid' => $this->getContentTypeId(),
			'max_file_size' => fetch_max_upload_size(),
			'values'        => array('f' => $this->content->getNodeId())
		);

		$view->editorid = construct_edit_toolbar(
			$pagetext,
			false,
			new vBCms_Editor_Override(vB::$vbulletin),
			true,
			true,
			true,
			'cms_article',
			'',
			$attachinfo
		);


		$templater = vB_Template::create('vbcms_article_editor');
		$templater->register('attachmentoption', $attachmentoption);

		$templater->register('posthash', $posthash);
		$templater->register('poststarttime', $poststarttime);
		$templater->register('contenttypeid', $this->getContentTypeId());
		$templater->register('values', $values);
		$templater->register('contentid', $this->content->getNodeId());


		$templater->register('checked', $checked);
		$templater->register('disablesmiliesoption', $disablesmiliesoption);
		$templater->register('editorid', $view->editorid);
		$templater->register('messagearea', $messagearea);
		$tag_delimiters = addslashes_js(vB::$vbulletin->options['tagdelimiter']);
		$templater->register('tag_delimiters', $tag_delimiters);
		$content = $templater->render();


		$view->editor = $content;
		$view->url = $this->content->getUrl();
		$view->type = new vB_Phrase('vbcms', 'content');
		$view->adding = 	new vB_Phrase('cpcms', 'adding_x', $vbphrase['article']);
		$view->html_title = $this->content->getHtmlTitle();
		$view->title = $this->content->getTitle();
		$view->metadata = $this->content->getMetadataEditor();
		$segments = array('node' => $this->content->getUrlSegment(),
							'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'View'));
		$view->view_url = vBCms_Route_Content::getURL($segments);
		// Add URL to submit to
		$segments = array('node' => $this->content->getUrlSegment(),
							'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'EditPage'));
		$view->submit_url = vBCms_Route_Content::getURL($segments);
		$segments = array('node' => $this->content->getUrlSegment(),
							'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'View'));
		$view->editbar = $this->content->getEditBar($view->submit_url, vBCms_Route_Content::getURL($segments), $view->formid);
		$view->publisher = $this->content->getPublishEditor($view->submit_url, $view->formid,
			true, true, $this->content->getPublicPreview(), $this->content->getComments_Enabled());
		$view->authorid = ($this->content->getUserId());
		$view->authorname = ($this->content->getUsername());
		$view->viewcount = ($this->content->getViewCount());
		$view->parentid = $this->content->getParentId();
		$view->post_started = ($this->content->getPostStarted());
		$view->post_posted = ($this->content->getPostPosted());

		$view->comment_count = ($this->content->getReplyCount());
		$view->contentid = $this->content->getContentId(true);

		$view->show_threaded = true;
		$view->per_page = 10;
		$view->indent_per_level = 5;
		$view->max_level = 4;
		// Add form check
		$this->addPostId($view);
		return $view;
	}

	/**
	 * Creates a content view.
	 * The default method fetches a view based on the required result, package
	 * identifier and content class identifier.  Child classes may want to override
	 * this.  Ths method is also voluntary if the getView methods are overriden.
	 *
	 * @param string $result					- The result identifier for the view
	 * @return vB_View
	 */
	protected function createView($result)
	{
		$result = strtolower($this->package . '_content_' . $this->class . '_' . $result);

		return new vBCms_View_Article($result);
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 28694 $
|| ####################################################################
\*======================================================================*/