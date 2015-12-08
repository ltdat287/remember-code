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
 * CMS Content Collection
 * Fetches CMS specific content items, including node related info.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 32878 $
 * @since $Date: 2009-10-28 13:38:49 -0500 (Wed, 28 Oct 2009) $
 * @copyright vBulletin Solutions Inc.
 */
 
class vBCms_Collection_Content_StaticHtml extends vBCms_Collection_Content
{
	/*Item==========================================================================*/

	/**
	 * The package identifier of the child items.
	 *
	 * @var string
	 */
	protected $item_package = 'vBCms';

	/**
	 * The class identifier of the child items.
	 *
	 * @var string
	 */
	protected $item_class = 'StaticHtml';



	/*Constants=====================================================================*/

	/**
	 * Map of query => info.
	 * INFO_CONTENT is queried with QUERY_BASIC.
	 *
	 * @var array int => int
	 */
	protected $query_info = array(
		self::QUERY_BASIC => /* vB_Item::INFO_BASIC | vB_Item::INFO_NODE | vBCms_Item_Content::INFO_CONTENT */ 19,
		self::QUERY_PARENTS => vBCms_Item_Content::INFO_PARENTS,
		self::QUERY_CONFIG => vBCms_Item_Content::INFO_CONFIG
	);



	/*LoadInfo======================================================================*/

	/**
	 * Fetches additional fields for querying INFO_CONTENT in QUERY_BASIC.
	 * Note: Child classes may provide a seperate query for INFO_CONTENT.  In that
	 * case, this does not need to be redefined.
	 *
	 * @return string
	 */
	protected function getContentQueryFields()
	{
		return ", statichtml.html";
	}


	/**
	 * Fetches additional join for querying INFO_CONTENT in QUERY_BASIC.
	 * Note: Child classes may provide a seperate query for INFO_CONTENT.  In that
	 * case, this does not need to be redefined.
	 *
	 * @return string
	 */
	protected function getContentQueryJoins()
	{
		return "INNER JOIN " . TABLE_PREFIX . "cms_statichtml AS statichtml ON statichtml.contentid = node.contentid";
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 32878 $
|| ####################################################################
\*======================================================================*/