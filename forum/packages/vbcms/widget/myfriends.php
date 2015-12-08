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
 * vBCms_Widget_myFriends
 *
 * @package
 * @author ebrown
 * @copyright Copyright (c) 2009
 * @version $Id: myfriends.php 35028 2010-01-19 23:17:11Z ebrown $
 * @access public
 */
class vBCms_Widget_myFriends extends vBCms_Widget
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
	protected $class = 'myFriends';

	/*** cache lifetime, minutes ****/
	protected $cache_ttl = 5;

	/*Render========================================================================*/

	/**
	 * Returns the config view for the widget.
	 *
	 * @return vBCms_View_Widget				- The view result
	 */
	public function getConfigView()
	{
		$this->assertWidget();

		global $vbphrase;
		require_once DIR . '/includes/functions_databuild.php';
		fetch_phrase_group('vbcms');

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'do'      => vB_Input::TYPE_STR,
			'days'    => vB_Input::TYPE_UINT,
			'item_id'    => vB_Input::TYPE_UINT,
			'count'    => vB_Input::TYPE_UINT,
			'rb_type'  => vB_Input::TYPE_UINT,
			'template_name'  => vB_Input::TYPE_STR,
			'contenttypeid'   => vB_Input::TYPE_ARRAY
		));

		$view = new vB_View_AJAXHTML('cms_widget_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_widget_x', $this->widget->getTitle());

		$config = $this->widget->getConfig();

		if ((vB::$vbulletin->GPC['do'] == 'config') AND $this->verifyPostId())
		{
			if (vB::$vbulletin->GPC_exists['days'])
			{
				$config['days'] = vB::$vbulletin->GPC['days'];
			}

			if (vB::$vbulletin->GPC_exists['count'])
			{
				$config['count'] =  vB::$vbulletin->GPC['count'];
			}

			if (vB::$vbulletin->GPC_exists['template_name'])
			{
				$config['template_name'] =  vB::$vbulletin->GPC['template_name'];
			}

			if ( vB::$vbulletin->GPC_exists['rb_type'] AND intval(vB::$vbulletin->GPC['rb_type']))
			{
				$config['contenttypeid'] = vB::$vbulletin->GPC['rb_type'];
				vB::$vbulletin->input->clean_array_gpc('p', array(
					'template_' .  vB::$vbulletin->GPC['rb_type'] => vB_Input::TYPE_STR));

				$config['template'] =
				(vB::$vbulletin->GPC_exists['template_' . vB::$vbulletin->GPC['rb_type']] ?
				vB::$vbulletin->GPC['template_' . vB::$vbulletin->GPC['rb_type']] :
				'vbcms_searchresult_' . vB_Types::instance()->getPackageClass(vB::$vbulletin->GPC['rb_type']) );
			}
			else
			{
				$config['contenttypeid'] = vB_Types::instance()->getContentTypeID('vBForum_Post');
				$config[ 'template'] =	'vbcms_searchresult_post';
			}

			$widgetdm = $this->widget->getDM();
			$widgetdm->set('config', $config);

			if ($this->content)
			{
				$widgetdm->setConfigNode($this->content->getNodeId());
			}

			$widgetdm->save();

			//clear the cache
			vB_Cache::instance()->event('widget_config_' . $this->widget->getId());
			vB_Cache::instance()->cleanNow();

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
			$contenttypes = array() ;
			require_once DIR . '/includes/functions_databuild.php';
			fetch_phrase_group('search');

			foreach (vB_Search_Core::get_instance()->get_indexed_types() as $type)
			{
				$contenttypes[$type['contenttypeid']] = array('name' => $type['class'],
					'contenttypeid' => $type['contenttypeid'],
					'template' => ((intval($type['contenttypeid']) == intval($config['contenttypeid'])) and
								isset($config['template'])) ?
							$config['template'] : 'vbcms_searchresult_' . strtolower($type['class']),
					'checked' => intval($type['contenttypeid']) == intval($config['contenttypeid']) ? 'checked="checked"' : '')  ;
			}

			$configview->contenttypes = $contenttypes;
			$show_checked = array();

			// Contenttype select
			$select_types = '';
			foreach (vB_Search_Core::get_instance()->get_indexed_types() as $type)
			{
				$contenttypes[$type['contenttypeid']] = array('name' => $type['class'],
					'contenttypeid' => $type['contenttypeid'],
					'template' => ((intval($type['contenttypeid']) == intval($config['contenttypeid'])) and
								isset($config['template'])) ?
							$config['template'] : 'vbcms_searchresult_' . strtolower($type['class']),
					'checked' => intval($type['contenttypeid']) == intval($config['contenttypeid']) ? 'checked="checked"' : '')  ;
			}
			$configview->contenttypes = $contenttypes;

			$configview->count = $config['count'];
			$configview->days = $config['days'];
			$configview->template_name = ($config['template_name'] ? $config['template_name'] : 'vbcms_widget_searchwidget_page');

			// add id to form
			$this->addPostId($configview);

			$view->setContent($configview);
			// send the view
			$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_widget'));
		}

		return $view;
	}


	/**
	 * Fetches the standard page view for a widget.
	 *
	 * @param bool $skip_errors					- If using a collection, omit widgets that throw errors
	 * @return vBCms_View_Widget				- The resolved view, or array of views
	 */
	public function getPageView()
	{
		$this->assertWidget();
		$config = $this->widget->getConfig();

		if (!intval(vB::$vbulletin->userinfo['userid']))
		{
			return '';
		}

		// Create view
		$view = new vB_View($config['template_name'] ? $config['template_name'] : 'vbcms_widget_searchwidget_page');
		$view->class = $this->widget->getClass();
		$view->title = $this->widget->getTitle();
		$view->description = $this->widget->getDescription();

		if (!$view->friends_html = vB_Cache::instance()->read($this->getHash($this->widget->getId()), true, false))
		{

			if ($config['contenttypeid'] == null)
			{
				$config['contenttypeid']= array();
			}
			else if (!is_array($config['contenttypeid']))
			{
				$config['contenttypeid'] = array($config['contenttypeid']);
			}

			$view->friends_html = $this->makeFriends($config);
			vB_Cache::instance()->write($this->getHash($this->widget->getId()),
				   $view->friends_html, $this->cache_ttl, 'widget_config_' . $this->widget->getId());
		}

		$view->widget_title = $this->widget->getTitle();

		return $view;
	}

	/**
	 * This does the actual work of creating the navigation elements. This needs some
	 * styling, but we'll do that later.
	 * We use the existing search functionality. It's already all there, we just need
	 * to
	 *
	 * @return string;
	 */
	private function makeFriends($config)
	{
		include_once DIR . '/includes/functions_misc.php';

		if ($rst = vB::$vbulletin->db->query_read("SELECT relationid FROM "
			. TABLE_PREFIX . "userlist WHERE friend='yes' AND userid = "
			. vB::$vbulletin->userinfo['userid']
			))
		{
			$userids = array();

			while($row = vB::$vbulletin->db->fetch_row($rst))
			{
				$userids[] = $row[0];
			}

			//If there are no friends there's no friend information.
			if (! count($userids))
			{
				return '';
			}

			$criteria = vB_Search_Core::get_instance()->create_criteria(vB_Search_Core::SEARCH_ADVANCED);
			$criteria->add_contenttype_filter($config['contenttypeid']);
			$criteria->set_advanced_typeid($contenttypeid);

			if (!count($userids))
			{
				new vB_Phrase('global', 'your_friends_list_is_empty');
			}

			$criteria->add_userid_filter($userids, false);
			$criteria->set_grouped(vB_Search_Core::GROUP_NO);
			$timelimit = TIMENOW - (86400 * $config['days']);
			$criteria->add_date_filter(vB_Search_Core::OP_GT, $timelimit);
			$criteria->set_sort('dateline', 'desc');
			$current_user = new vB_Legacy_CurrentUser();
			$results = vB_Search_Results::create_from_cache($current_user, $criteria);

			if (!$results)
			{
				$results = vB_Search_Results::create_from_criteria($current_user, $criteria);
			}

			return $this->renderResult($config, $results);

		}
		return '';
	}

	/***** This function actually renders the results
	 *
	 * @param array  holds the configuration values
	 *
	 * @param array  result of sql query
	 *
	 * @return string
	 *****/
	private function renderResult($config, $results)
	{
		//until we manage to figure out how to handle this
		global $show;
		$show['inlinemod'] = false;
		$page_results = $results->get_page(1, $config['count'], 1);
		//prepare types for render
		$items_by_type = array();
		foreach ($page_results as $item)
		{
			$typeid = $item->get_contenttype();

			if ($typeid)
			{
				$items_by_type[$typeid][] =  $item;
			}
		}

		foreach ($items_by_type as $contenttype => $items)
		{
			$type = vB_Search_Core::get_instance()->get_search_type_from_id($contenttype);
			$type->prepare_render($results->get_user(), $items);

		}

		//perform render
		$searchbits = '';
		foreach ($page_results as $item)
		{
			$searchbits .= $item->render($results->get_user(), $results->get_criteria(), $config['template']);
		}

		return $searchbits;
	}

	/**
	 * Returns a hash function for caching. Obviously each user must have a unique
	 * widget view.
	 *
	 * @param integer $widgetid
	 * @return hash that will identify this widget content for this user
	 */
	protected function getHash($widgetid)
	{
		$context = new vB_Context('widget' , array('widgetid' => $widgetid,
			'userid' => vB::$vbulletin->userinfo['userid']));
		return strval($context);
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 32878 $
|| ####################################################################
\*======================================================================*/