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
 * Test Content Controller
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 34955 $
 * @since $Date: 2010-01-13 17:30:49 -0600 (Wed, 13 Jan 2010) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Content_StaticHtml extends vBCms_Content
{
	/*Properties====================================================================*/

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'StaticHtml';

	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * Whether the content is configurable with getConfigView().
	 *
	 * @var bool
	 */
	protected $is_configurable = true;

	/**
	 * Controller Parameters.
	 *
	 * @var mixed
	 */
	protected $parameters = array();
	
	/*** parent node id ***/
	protected $parent_node = false;


	/*Creation======================================================================*/

	/**
	 * Creates a new, empty content item to add to a node.
	 *
	 * @param vBCms_DM_Node $nodeid				- The DM of the node that the content is being created for
	 * @return int								- The id of the new content
	 */
	public function createDefaultContent(vBCms_DM_Node $nodedm)
	{
		global $vbphrase;

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'nodeid' => vB_Input::TYPE_UINT,
			'parentnode' => vB_Input::TYPE_UINT
		));

		//We should have a nodeid, but a parentnode is even better.

		if ($this->parent_node)
		{
			$parentnode = $this->parent_node;
		}
		else if (vB::$vbulletin->GPC_exists['parentnode'] AND intval(vB::$vbulletin->GPC['parentnode'] ))
		{
			$parentnode = vB::$vbulletin->GPC['parentnode'];
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

		$contentdm = new vBCms_DM_StaticHtml();
		$contentdm->set('contenttypeid', vB_Types::instance()->getContentTypeID("vBCms_StaticHtml"));
		$contentdm->set('html', $vbphrase['contenttype_statichtml_default_content']);
		$contentdm->set('html_title',$vbphrase['new_page']);
		$contentdm->set('title',$vbphrase['new_page']);
		$contentdm->set('parentnode', $parentnode);

		if (!($nodeid = $contentdm->save()))
		{
			throw (new vB_Exception_Content('Failed to create default content for contenttype ' . get_class($this)));
		}
		//at this point we have saved the data. We need to get the content id, which isn't easily available.
		if ($record = vB::$vbulletin->db->query_first("SELECT contentid FROM " . TABLE_PREFIX .
			"cms_node WHERE nodeid = $nodeid"))
		{
			$nodedm->set('contentid', $record['contentid']);
			$nodedm->set('item_id', $record['contentid']);
		}

		return $nodeid;
	}

	/*** This function sets the parent node for creating a new article
	 ****/
	public function setParentNode($parentnode)
	{

		$this->parent_node = $parentnode;
	}


	/*Render========================================================================*/

	/**
	 * Fetches the view for configuring a content item.
	 *
	 * @param mixed $parameters					- Request parameters
	 * @return vB_View | bool					- Returns a view or false
	 */
	public function getConfigView($parameters = false)
	{
		$view = new vB_View_AJAXHTML('cms_content_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_content_x', $this->content->getTitle());

		vB::$vbulletin->input->clean_array_gpc('p', array(
			'do' => vB_Input::TYPE_STR,
			'font' => vB_Input::TYPE_STR,
			'size' => vB_Input::TYPE_UINT
		));

		if (vB::$vbulletin->GPC['do'] == 'config' AND $this->verifyPostId())
		{
			$nodedm = new vBCms_DM_Node($this->content);
			$nodedm->set('config', array('font' => vB::$vbulletin->GPC['font'], 'size' => vB::$vbulletin->GPC['size']));
			$nodedm->save();

			if (!$nodedm->hasErrors())
			{
				$segments = array(	'node' => $this->content->getUrlSegment(),
									'action' => vB_Router::getUserAction('vBCms_Controller_Content', 'EditPage'));
				$view->setUrl(vB_View_AJAXHTML::URL_FINISHED, vBCms_Route_Content::getURL($segments));

				$view->setStatus(vB_View_AJAXHTML::STATUS_FINISHED, new vB_Phrase('vbcms', 'configuration_saved'));
			}
			else
			{
				if (vB::$vbulletin->debug)
				{
					$view->addErrors($nodedm->getErrors());
				}

				// only send a message
				$view->setStatus(vB_View_AJAXHTML::STATUS_MESSAGE, new vB_Phrase('vbcms', 'configuration_failed'));
			}
		}
		else
		{
			// add the config content
			$configview = $this->createView('config');
			$config = $this->content->getConfig();
			$configview->font = $config['font'];
			$configview->size = $config['size'];
			$configview->title = $vbphrase['new_page'];
			$configview->html_title = $vbphrase['new_page'];
			$configview->addArray(array('description' => $this->content->getDescription()));
			$configview->package = $this->package;
			$configview->class = $this->class;

			$this->addPostId($configview);

			$view->setContent($configview);

			//Set the class and package
			$view->package = $this->package;
			$view->class = $this->class;

			// send the view
			$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_content'));
		}

		return $view;
	}

	/****
	 * Saves data from a form submit
	 * takes no parameters and returns nothing
	 *
	 ****/
	protected function saveData()
	{
		// collect error messages
		$errors = array();
		vB::$vbulletin->input->clean_array_gpc('p', array(
			'do' => vB_Input::TYPE_STR,
			'html' => vB_Input::TYPE_STR,
			'title' => vB_Input::TYPE_STR,
			'new_parentid' => TYPE_INT,
			'html_title' => vB_Input::TYPE_STR,
			'publicpreview' => TYPE_INT,
			'item_id' => vB_Input::TYPE_INT

		));

		if (vB::$vbulletin->GPC['do'] == 'movenode'
			and vB::$vbulletin->GPC_exists['new_parentid'] AND intval(vB::$vbulletin->GPC['new_parentid']))
		{
			vBCms_ContentManager::moveSection(array($this->content->getNodeId()), vB::$vbulletin->GPC['new_parentid']);
			$new_sectionid = vB::$vbulletin->GPC['new_parentid'];
		}

		$new_values = array();
		// create DM and save
		$dm = $this->content->getDM();
		$dm->set('contentid', $this->content->getId());
		$dm->set('item_id', $this->content->getId());

		if (vB::$vbulletin->GPC_exists['html_title'])
		{
			$new_values['html_title'] = vB::$vbulletin->GPC['html_title'];
			$dm->set('html_title', vB::$vbulletin->GPC['html_title']);
		}

		if (vB::$vbulletin->GPC_exists['html'])
		{
			$new_values['html'] = vB::$vbulletin->GPC['html'];
			$dm->set('html', vB::$vbulletin->GPC['html']);
		}

		if (vB::$vbulletin->GPC_exists['comments_enabled'])
		{
			$new_values['comments_enabled'] = vB::$vbulletin->GPC['comments_enabled'];
			$dm->set('comments_enabled', vB::$vbulletin->GPC['comments_enabled']);
		}

		if (vB::$vbulletin->GPC_exists['title'])
		{
			$new_values['title'] = vB::$vbulletin->GPC['title'];
			$dm->set('title', vB::$vbulletin->GPC['title']);
		}

		if (vB::$vbulletin->GPC_exists['publicpreview'])
		{
			$new_values['publicpreview'] = vB::$vbulletin->GPC['publicpreview'];
			$dm->set('publicpreview', vB::$vbulletin->GPC['publicpreview']);
		}

		// add node info
		$dm->setNodeTitle($title);

		// set the node segment if it's empty
		if (!$this->content->getUrlTitle())
		{
			$dm->setNodeURLSegment($title);
		}

		$success = $dm->saveFromForm($this->content->getNodeId());

		//invalidate the navigation cache.

		vB_Cache::instance()->event(array('sections_updated' ));
		vBCms_Content::cleanContentCache();

		if ($dm->hasErrors())
		{
			$fieldnames = array(
				'html' => new vB_Phrase('vbcms', 'html')
			);

			$view->errors = $dm->getErrors(array_keys($fieldnames));
			$view->error_summary = self::getErrorSummary($dm->getErrors(array_keys($fieldnames)), $fieldnames);
			$view->status = $view->error_view->title;
		}
		else
		{
			$view->status = new vB_Phrase('vbcms', 'content_saved');
			$this->cleanContentCache();
		}

		// postback content
		$view->html_title = $new_values['html_title'];
		$view->title = $new_values['title'];
	}


	/**
	 * Populates a view with the expected info from a content item.
	 * Note: The view type should be based on the VIEW constants defined by the
	 * content handler class.
	 *
	 * Child classes will need to extend or override this for custom content.
	 *
	 * @param vB_View $view
	 * @param int $viewtype
	 */
	protected function populateViewContent(vB_View $view, $viewtype = self::VIEW_PAGE, $increment_count = true)
	{
		if ($_REQUEST['do'] == 'apply' OR $_REQUEST['do'] == 'update' OR $_REQUEST['do'] == 'movenode')
		{
			$this->SaveData();
			$this->content->reloadContent();
		}

		if ($_REQUEST['do'] == 'delete'  AND $this->content->canEdit())
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
		if ($increment_count)
		{
				//update the view count
			vB::$vbulletin->db->query_write("UPDATE " . TABLE_PREFIX .
					"cms_nodeinfo set viewcount = viewcount + 1 where nodeid = " . $this->content->getNodeId());
		}
		parent::populateViewContent($view, $viewtype);

		$view->url = $this->content->getUrl();
		$view->html = $this->content->getHtml();
		$view->html_title = $this->content->getHtmlTitle();
		$view->title = $this->content->getTitle();
		$view->font = $this->content->getConfig('font');
		$view->fontsize = $this->content->getConfig('size');
		$view->update_url = vB_Router::getURL();
		$view->parenttitle = $this->content->getParentTitle();
		$view->setpublish = $this->content->getSetPublish();
		$view->dateformat = vB::$vbulletin->options['dateformat'];

		//tagging code
		require_once DIR . '/includes/class_taggablecontent.php';
		$taggable = vB_Taggable_Content_Item::create(vB::$vbulletin, $this->content->getContentTypeId(),
			$this->content->getContentId(), $this->content);
		$view->tags = $taggable->fetch_rendered_tag_list();
		$view->tag_count = $taggable->fetch_existing_tag_count();
		$view->showtags = vB::$vbulletin->options['threadtagging'];
		$view->categories = $this->content->getCategories();
		$view->contenttypeid = vB_Types::instance()->getContentTypeID("vBCms_StaticHtml");

	}


	/**
	 * Fetches a rich page view of the specified content item.
	 * This method can accept parameters from the client code which are usually
	 * derived from user input.  Parameters are passed as an array in the order that
	 * they were received.  Parameters do not normally have assoc keys.
	 *
	 * Note: Parameters are always passed raw, so ensure that validation and
	 * escaping is performed where required.
	 *
	 * Skip permissions should allow content to be rendered regardless of the
	 * current user's permissions.
	 *
	 * Child classes will inevitably override this with wildly different
	 * implementations.
	 *
	 * @param array mixed $parameters			- Request parameters
	 * @param bool $skip_permissions			- Whether to skip can view permission checking
	 * @return vB_View | bool					- Returns a view or false
	 */
	public function getInlineEditBodyView($parameters = false)
	{
		global $vbphrase;
		require_once DIR . '/includes/functions_databuild.php' ;
		fetch_phrase_group('cpcms');

		//confirm that the user has edit rights
		if (!$this->content->canEdit())
		{
			return $vb_phrase['no_edit_permissions'];
		}

		if ($_REQUEST['do'] == 'delete')
		{
			$dm = $this->content->getDM();
			$dm->delete();
			$this->cleanContentCache();
			return $vbphrase['item_deleted'];
		}

		// Load the content item
		if (!$this->loadContent($this->getViewInfoFlags(self::VIEW_PAGE )))
		{
			throw (new vB_Exception_404());
		}
		if ($_REQUEST['do'] == 'apply' OR $_REQUEST['do'] == 'update' OR $_REQUEST['do'] == 'movenode')
		{
			$this->SaveData();
		}

		// Create view
		$view = $this->createView('inline', self::VIEW_PAGE);

		// Add the content to the view
		$this->populateViewContent($view, self::VIEW_PAGE, false);

		// Check if inline form was submitted
		// postback content

		// TODO: Don't need to escape this with new template syntax
		$view->formid = 'cms_statichtml_data';

		$view->type = new vB_Phrase('vbcms', 'statichtml');
		$view->adding = 	new vB_Phrase('cpcms', 'adding_x', $vbphrase['article']);
		$view->typetitle = $this->content->getTypeTitle();
		$view->metadata = $this->content->getMetadataEditor();
		$view->comments_enabled = ($this->content->getComments_Enabled());
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
		$view->publisher = $this->content->getPublishEditor($view->submit_url, $view->formid);
		$view->show_threaded = true;
		$view->per_page = 10;

		$this->addPostId($view);

		return $view;
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 32974 $
|| ####################################################################
\*======================================================================*/