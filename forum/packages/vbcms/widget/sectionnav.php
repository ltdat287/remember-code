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
 * vBCms_Widget_Nav
 *
 * @package
 * @author ebrown
 * @copyright Copyright (c) 2009
 * @version $Id: sectionnav.php 35056 2010-01-21 17:02:12Z ebrown $
 * @access public
 */
class vBCms_Widget_SectionNav extends vBCms_Widget
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
	protected $class = 'SectionNav';

	/*** cache lifetime, minutes ****/
	protected $cache_ttl = 1440;


	/*Render========================================================================*/

	/**
	 * Returns the config view for the widget.
	 *
	 * @return vBCms_View_Widget				- The view result
	 */
	public function getConfigView($widget = false)
	{
		global $vbphrase;
		$this->assertWidget();

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'do'      => vB_Input::TYPE_STR,
			'template_name'    => vB_Input::TYPE_STR
			));

		$view = new vB_View_AJAXHTML('cms_widget_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_widget_x', $this->widget->getTitle());
		$config = $this->widget->getConfig();
		$widgetdm = $this->widget->getDM();

		if ((vB::$vbulletin->GPC['do'] == 'config') AND $this->verifyPostId())
		{
			if (vB::$vbulletin->GPC_exists['template_name'])
			{
				$config['template_name'] = vB::$vbulletin->GPC['template_name'];
			}

			if ($this->content)
			{
				$widgetdm->setConfigNode($this->content->getNodeId());
			}

			$widgetdm->set('config', $config);
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
		if (!isset($config['template_name']) OR ($config['template_name'] == '') )
		{
			$config['template_name'] = 'vbcms_widget_sectionnav_page';
		}
		// add the config content
		$configview = $this->createView('config');

		$configview->template_name = $config['template_name'];


		// item id to ensure form is submitted to us
		$this->addPostId($configview);

		$view->setContent($configview);

		// send the view
		$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_widget'));

		return $view;	}


	/**
	 * Fetches the standard page view for a widget.
	 *
	 * @param bool $skip_errors					- If using a collection, omit widgets that throw errors
	 * @return vBCms_View_Widget				- The resolved view, or array of views
	 */
	public function getPageView()
	{

		$this->assertWidget();

		if (! isset($vbulletin->userinfo['permissions']['cms']))
		{
			require_once DIR . '/packages/vbcms/permissions.php';
			vBCMS_Permissions::getUserPerms();
		}
		// Create view
		$config = $this->widget->getConfig();
		if (!isset($config['template_name']) OR ($config['template_name'] == '') )
		{
			$config['template_name'] = 'vbcms_widget_sectionnav_page';
		}

		$canviewlist = implode(', ', vB::$vbulletin->userinfo['permissions']['cms']['viewonly']);
		$caneditlist = implode(', ', vB::$vbulletin->userinfo['permissions']['cms']['canedit']);
		$for_node = intval($this->content->getContentTypeId()) == intval(vb_Types::instance()->getContentTypeID("vBCms_Section")) ?
			$this->content->getNodeId() : $this->content->getParentId();
		// Create view
		$view = new vBCms_View_Widget($config['template_name']);
		if ( $link_nodes = vB_Cache::instance()->read($cache_key = $this->getHash($this->widget->getId(), $for_node), false, true))
		{
			$links_before = $link_nodes['links_before'];
			$links_above = $link_nodes['links_above'];
			$links_sibling = $link_nodes['links_sibling'];
			$links_children = $link_nodes['links_children'];
			$links_after = $link_nodes['links_after'];
			$myself = $link_nodes['myself'];
		}
		else
		{
			//If we're on a section, we show for this nodeid. If we're on
			// on a leaf-type node we show for the parent

			$section_possibles = vBCms_ContentManager::getSections();
			$my_left = $this->content->getNodeLeft();
			$my_right = $this->content->getNodeRight();
			$my_parent = $this->content->getParentId();
			$my_nodeid = $this->content->getNodeId();
			$my_title = '';

			$links_above = array();
			$links_before = array();
			$links_above = array();
			$links_sibling = array();
			$links_after = array();
			$links_children = array();
			$top_level = array();

			if (! isset(vB::$vbulletin->userinfo['permissions']['cms']) )
			{
				vBCMS_Permissions::getUserPerms();
			}
			$route = new vBCms_Route_Content();
			$route->setParameter('action', 'view');

			$homeid = $sections[0]['nodeid'];
			//Now let's scan the array;
			$indent = 0;
			$i = 1;
			$noderight = 0;
			//Let's remove items we're not supposed to see.
			$sections= array();
			foreach ($section_possibles as $key => $section)
			{
				if (/** This user has permissions to view this record **/
					( in_array($section['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canedit'])
					OR (in_array($section['permissionsfrom'],vB::$vbulletin->userinfo['permissions']['cms']['canview'] )
					AND $section['setpublish'] == '1' AND $section['publishdate'] < TIMENOW ))
					AND /** This user also has rights to the parents **/
					($section['noderight'] > $noderight))
				{
					$sections[] = $section;
				}
				else
				{
					//So the children will be skipped
					$noderight = $section['noderight'];
				}
			}

			//First the sections ahead of us
			while($i < count($sections) AND $my_left > $sections[$i]['nodeleft'])
			{
				$route->node = $sections[$i]['nodeid'] . (strlen($sections[$i]['url']) ? '-' . $sections[$i]['url'] : '' );

				//see if it's a top-level
				if ($sections[$i]['parentnode'] == $homeid)
				{
					$links_before[] =  array('title' => $sections[$i]['title'],
					'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => 0);
				}//is it a sibling?
				else if ($my_parent == $sections[$i]['parentnode'])
				{
					$links_sibling[] =  array('title' => $sections[$i]['title'],
						 'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => 0);
				}

				$i++;
			}

			//Now our parentage and children
			while($i < count($sections)  AND $my_right > $sections[$i]['nodeleft'])
			{
				$route->node = $sections[$i]['nodeid'] . (strlen($sections[$i]['url']) ? '-' . $sections[$i]['url'] : '' );
				if ($my_nodeid == $sections[$i]['parentnode'])
				{
					$links_children[] =  array('title' => $sections[$i]['title'],
						 'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => ($indent) * 10);
				}
				else if ($my_nodeid == $sections[$i]['nodeid'])
				{
					$myself =  array('title' => $sections[$i]['title'],
						 'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => $indent * 10);
			}
				else
			{
					$links_above[] =  array('title' => $sections[$i]['title'],
						 'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => $indent * 10);
					$my_title = $sections[$i]['title'];
					$indent++;

				}
				$i++;
			}

			//Now the afters
			while ($i < count($sections))
			{
				$route->node = $sections[$i]['nodeid'] . (strlen($sections[$i]['url']) ? '-' . $sections[$i]['url'] : '' );

				if ($sections[$i]['parentnode'] == $homeid)
				{
					$links_after[] =  array('title' => $sections[$i]['title'],
					'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => 0);
				}
				else if ($my_parent == $sections[$i]['parentnode'])
				{
					$links_sibling[] =  array('title' => $sections[$i]['title'],
						 'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => 0);
				}
				$i++;
			}

			foreach($links_sibling as $key => $value)
			{
				$links_sibling[$key]['indent'] = $indent * 10;
			}

			$route->node = $sections[1]['nodeid'] . (strlen($sections[1]['url']) ? '-' . $sections[1]['url'] : '' );
			//We have the pieces, now let's string them together;
			//Top level first

			$links_before = array_merge(array(array('title' => $sections[0]['title'],
					'sectionurl' => $route->getCurrentUrl(array('node' =>$route->node, 'action' => 'view')), 'indent' => 0)), $links_before);
			//Now write to the cache
			vB_Cache::instance()->write($cache_key,
				   array('links_before' => $links_before, 'links_above' => $links_above,
				   'links_sibling' => $links_sibling , 'links_after' => $links_after,
				   'links_children' => $links_children, 'myself' => $myself ), $this->cache_ttl,
					array('section_nav_' . $for_node, 'sections_updated'));
		}

		//The first record is the root

		$view->links_before = $links_before;
		$view->links_above = $links_above;
		$view->links_sibling = $links_sibling;
		$view->links_children = $links_children;
		$view->links_after = $links_after;
		$view->myself = $myself;
		$view->widget_title = $this->widget->getTitle();

		return $view;
	}

	//This function builds an array of all section nodes in the hierarchy that
	// have children
	protected function buildSectionList()
	{

	}

	/**
	 * This returns a hash for widget caching. We include nodeid and userid b
	 *
	 *
	 * @param integer $widgetid
	 * @return hash that will identify this widget content for this page
	 */
	protected function getHash($widgetid, $for_node)
	{
		$context = new vB_Context('sectionnav_widget' , array('widgetid' => $widgetid,
			'permissions' => vB::$vbulletin->userinfo['permissions']['cms'],
			'nodeid' => $for_node ));
		return strval($context);

	}

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 33229 $
|| ####################################################################
\*======================================================================*/