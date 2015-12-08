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
 * Test StaticHtml Content Type
 *
 * @author vBulletin Development Team
 * @version $Revision: 33619 $
 * @since $Date: 2009-11-17 15:26:09 -0600 (Tue, 17 Nov 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Item_Content_StaticHtml extends vBCms_Item_Content
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
	 * The DM for handling CMS StaticHtml data.
	 *
	 * @var string
	 */
	protected $dm_class = 'vBCms_DM_StaticHtml';

	/**
	 * Map of query => info.
	 * Include INFO_BASIC in QUERY_BASIC.
	 *
	 * @var array int => int
	 */
	protected $query_info = array(
		self::QUERY_BASIC => /* self::INFO_BASIC | self::INFO_NODE | self::INFO_CONTENT */ 39,
		self::QUERY_PARENTS => self::INFO_PARENTS,
		self::QUERY_CONFIG => self::INFO_CONFIG
	);



	/*ModelProperties===============================================================*/

	/**
	 * Node model properties.
	 *
	 * @var array string
	 */
	protected $content_properties = array(
		/*INFO_BASIC==================*/
		'html'
	);

	/*INFO_BASIC==================*/

	/**
	 * The html content.
	 *
	 * @var string
	 */
	protected $html;

	/*LoadInfo======================================================================*/

	/**
	 * Fetches the SQL for loading.
	 *
	 * @param int $required_query				- The required query
	 * @return string
	 */
	protected function getLoadQuery($required_query)
	{
		// Hooks should check the required query before populating the hook vars
		$hook_query_fields = $hook_query_joins = $hook_query_where = '';
		($hook = vBulletinHook::fetch_hook($this->query_hook)) ? eval($hook) : false;

		if (self::QUERY_BASIC == $required_query)
		{
			$sql = "SELECT node.nodeid " .
				($this->requireLoad(self::INFO_BASIC) ?
					",	node.contenttypeid, node.contentid, node.url, node.parentnode, node.styleid, node.userid, node.permissionsfrom,
						node.layoutid, node.publishdate, node.publicpreview, node.nodeleft, node.noderight, node.issection, node.showtitle, node.showuser, node.showpreview,
					node.showupdated, node.showviewcount, node.showcreation, node.settingsforboth, " : '') .
				($this->requireLoad(self::INFO_NODE) ?
					 ", info.description, info.title, info.html_title, info.viewcount, info.creationdate, info.workflowdate, info.associatedthreadid,
					 	info.workflowstatus, info.workflowcheckedout, info.viewcount, info.workflowlevelid, user.username" : '') .
				($this->requireLoad(self::INFO_CONTENT) ?
					", statichtml.html " : '') . "
					 $hook_query_fields
				FROM " . TABLE_PREFIX . "cms_node AS node" .
				($this->requireLoad(self::INFO_CONTENT) ? "
				INNER JOIN " . TABLE_PREFIX . "cms_statichtml AS statichtml ON statichtml.contentid = node.contentid" : '') .
				($this->requireLoad(self::INFO_NODE) ? "
				INNER JOIN " . TABLE_PREFIX . "cms_nodeinfo AS info ON info.nodeid = node.nodeid
				LEFT JOIN " . TABLE_PREFIX . "user AS user ON user.userid = node.userid" : '') . "
				$hook_query_joins
				WHERE " .
				(is_numeric($this->itemid) ? 'node.nodeid = ' . intval($this->nodeid)
										: 'node.contenttypeid = ' . intval($this->contenttypeid) . ' AND node.contentid = ' . intval($this->contentid)) . "
				$hook_query_where
			";
			return $sql;
		}

		return parent::getLoadQuery($required_query);
	}



	/*Accessors=====================================================================*/

	/**
	 * Fetches the static html content.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$this->Load(self::INFO_CONTENT);

		return $this->html;
	}

	/**
	 * when editing, sets the html so we can render the page
	 *
	 * @param string $html
	 * @return nothing
	 */
	public function setHtml($html)
	{
		$this->html = $html;
	}

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 32878 $
|| ####################################################################
\*======================================================================*/