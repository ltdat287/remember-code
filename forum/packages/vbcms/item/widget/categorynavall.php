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
 * @version $Revision: 35190 $
 * @since $Date: 2010-01-28 13:14:49 -0600 (Thu, 28 Jan 2010) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Widget_CategoryNavAll extends vBCms_Item_Widget
{
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
	protected $class = 'CategoryNavAll';

	/** The default configuration **/
	protected $config = array(
		'template_name' => 'vbcms_widget_categorynavall_page',
	);

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 35190 $
|| ####################################################################
\*======================================================================*/