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
 * Test Widget Item
 *
 * @package vBulletin
 * @author Edwin Brown, vBulletin Development Team
 * @version $Revision: 30298 $
 * @since $Date: 2009-04-14 13:42:28 -0700 (Tue, 14 Apr 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Widget_StaticBB extends vBCms_Item_Widget
{
	/*Properties====================================================================*/

	/**
	 * A package identifier.
	 *
	 * @var string
	 */
	protected $package = 'vBCms';

	/**
	 * A class identifier.
	 *
	 * @var string
	 */
	protected $class = 'StaticBB';

	/** The default configuration **/
	protected $config = array(
		'html'          => 'Testing',
		'template_name' => 'vbcms_widget_staticbb_page',
	);

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 30298 $
|| ####################################################################
\*======================================================================*/