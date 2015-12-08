<?php
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
 * Bootstrap MVC to vBForum
 * Eventually this should be removed once refactoring of legacy code is complete.
 * All of these classes are context specific and need to be called after the
 * appropriate global.php or legacy bootstrap.
 * @see vB_Bootstrap
 *
 * @tutorial
 * 	require_once(DIR . '/includes/class_bootstrap_framework.php');
 *	vB_Bootstrap_Framework::init();
 *
 *	// Get Widgets
 *	$widgets = vBCms_Widget::getWidgetCollection(array(1), vBCms_Item_Widget::INFO_CONFIG);
 *	$widgets = vBCms_Widget::getWidgetControllers($widgets, true);
 *
 *	// Register the templater to be used for XHTML
 *	vB_View::registerTemplater(vB_View::OT_XHTML, new vB_Templater_vB());
 *
 *	foreach($widgets AS $widget)
 *	{
 *		echo($widget->getPageView());
 *	}
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 29424 $
 * @since $Date: 2009-02-02 14:07:13 +0000 (Mon, 02 Feb 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_Bootstrap_Framework
{
	/**
	 * Whether the bootstrap has been initialized.
	 *
	 * @var bool
	 */
	protected static $initialized;


	/**
	 * Initializes the bootstrap and framework.
	 */
	public static function init($relative_path = false)
	{
		require_once (DIR . '/includes/class_friendly_url.php');

		if (!self::$initialized)
		{
			// Ensure the application cache is loaded
			global $vbulletin;
			$vbulletin->datastore->fetch(array('routes'));

			// Notify includes they are ok to run
			if (!defined('VB_ENTRY'))
			{
				define('VB_ENTRY', 1);
			}

			// Mark the framework as loaded
			define('VB_FRAMEWORK', 1);

			// Get the entry time
			define('VB_ENTRY_TIME', microtime(true));

			// vB core path
			define('VB_PATH', realpath(dirname(__FILE__) . '/../vb') . '/');

			// The package path
			define('VB_PKG_PATH', realpath(VB_PATH . '../packages') . '/');

			// Bootstrap to the new system
			require_once(VB_PATH . 'vb.php');

			vB::init($relative_path);
		}

		self::$initialized = true;
	}
}

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 26995 $
|| ####################################################################
\*======================================================================*/
