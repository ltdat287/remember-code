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
 * @version $Revision: 35057 $
 * @since $Date: 2010-01-21 11:19:37 -0600 (Thu, 21 Jan 2010) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Widget_RecentPosts extends vBCms_Item_Widget
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
	protected $class = 'RecentPosts';

	/** The default configuration **/
	protected $config = array(
		'forumchoice'    => '',
		'template_name' => 'vbcms_widget_recentposts_page',
		'days' => 7,
		'count' => 6,
	);

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 35057 $
|| ####################################################################
\*======================================================================*/