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
 * Test Widget Controller
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 30298 $
 * @since $Date: 2009-04-14 13:42:28 -0700 (Tue, 14 Apr 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Widget_StaticBB extends vBCms_Widget
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
	protected $class = 'StaticBB';

	/**
	 * Whether the content is configurable with getConfigView().
	 * @see vBCms_Widget::getConfigView()
	 *
	 * @var bool
	 */
	protected $canconfig = false;



	/*Render========================================================================*/

	/**
	 * Returns the config view for the widget.
	 *
	 * @return vBCms_View_Widget				- The view result
	 */
	public function getConfigView($widget = false)
	{
		global $vbulletin, $messagearea, $vbphrase;

		$this->assertWidget();

		require_once DIR . '/includes/functions_editor.php';
		require_once DIR . '/packages/vbcms/wysiwyghtmlparser.php';
		require_once DIR . '/packages/vbcms/editor/override.php';
		require_once DIR . '/packages/vbcms/bbcode/html.php';
		require_once DIR . '/packages/vbcms/bbcode/wysiwyg.php';
		require_once DIR . '/includes/functions_databuild.php';
		fetch_phrase_group('posting');

		vB::$vbulletin->input->clean_array_gpc('r', array(
			'do'      => vB_Input::TYPE_STR,
			'message' => vB_Input::TYPE_STR,
			'wysiwyg' => vB_Input::TYPE_BOOL,
			'template_name'    => vB_Input::TYPE_STR
		));

		$view = new vB_View_AJAXHTML('cms_widget_config');
		$view->title = new vB_Phrase('vbcms', 'configuring_widget_x', $this->widget->getTitle());

		$config = $this->widget->getConfig();

		if ((vB::$vbulletin->GPC['do'] == 'config') AND $this->verifyPostId())
		{
			if (vB::$vbulletin->GPC['wysiwyg'])
			{
				$html_parser = new vBCms_WysiwygHtmlParser(vB::$vbulletin);
				$message = $html_parser->parse(vB::$vbulletin->GPC['message']);
			}
			else
			{
				$message = convert_urlencoded_unicode(vB::$vbulletin->GPC['message']);
			}

			$widgetdm = new vBCms_DM_Widget($this->widget);
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

			//make the editor
			$configview->editorid = construct_edit_toolbar(
				$pagetext,
				false,
				new vBCms_Editor_Override(vB::$vbulletin),
				true,
				true,
				true,
				'cms_article',
				'',
				array()
			);

			$templater = vB_Template::create('vbcms_widgetcontent_editor');

			$templater->register('values', $values);
			$templater->register('widgetid', $this->widget->getId());

			$templater->register('disablesmiliesoption', true);
			$templater->register('editorid', $configview->editorid);
			$templater->register('messagearea', $messagearea);
			$configview->editor = $templater->render();
//			$configview->editor = $this->getConfigEditorView();
			// item id to ensure form is submitted to us
			$this->addPostId($configview);

			$view->setContent($configview);

			// send the view
			$view->setStatus(vB_View_AJAXHTML::STATUS_VIEW, new vB_Phrase('vbcms', 'configuring_widget'));
		}

		return $view;
	}


	public function getConfigEditorView()
	{
		require_once DIR . '/includes/functions_databuild.php';
		fetch_phrase_group('posting');

		$config = $this->widget->getConfig();

		require_once DIR . '/includes/functions_editor.php';
		construct_edit_toolbar($config['html'], false, new vBCms_Editor_Override(vB::$vbulletin), true, true, false, 'cms_article');

		return $GLOBALS['messagearea'];
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

		// Create view
		$config = $this->widget->getConfig();
		if (!isset($config['template_name']) OR ($config['template_name'] == '') )
		{
			$config['template_name'] = 'vbcms_widget_staticbb_page';
		}

		// Create view
		$view = new vBCms_View_Widget($config['template_name']);
		$view->class = $this->widget->getClass();
		$view->title = $this->widget->getTitle();
		$view->description = $this->widget->getDescription();

		$bbcode_parser = new vBCms_BBCode_HTML(vB::$vbulletin, vBCms_BBCode_HTML::fetchCmsTags());
		$view->static_html = $bbcode_parser->do_parse($config['html'], false, true, true, true, true);
		$view->widget_title = $this->widget->getTitle();

		return $view;
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 30298 $
|| ####################################################################
\*======================================================================*/