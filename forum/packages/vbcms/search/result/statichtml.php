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
 * @package vBulletin
 * @subpackage Search
 * @author Ed Brown, vBulletin Development Team
 * @version $Id: statichtml.php 30550 2009-04-28 23:55:20Z ebrown $
 * @since $Date: 2009-04-28 16:55:20 -0700 (Tue, 28 Apr 2009) $
 * @copyright vBulletin Solutions Inc.
 */

require_once (DIR . '/vb/search/result.php');
include_once DIR . '/packages/vbcms/item/content/statichtml.php';
require_once (DIR . '/vb/search/indexcontroller/null.php');
/**
 * Result Implementation for CMS StaticHtml
 *
 * @see vB_Search_Result
 * @package vBulletin
 * @subpackage Search
 */
class vBCms_Search_Result_StaticHtml extends vB_Search_Result
{

	/** record contenttypeid  **/
	private $contenttypeid;
	
	/** record node id   **/
	private $itemid;

	/** database record  **/
	private $record;

// ###################### Start create ######################
	/**
	 * factory method to create a result object
	 *
	 * @param integer $id
	 * @return result object
	 */
	public function create($id)
	{
		$contenttypeid =  vB_Types::instance()->getContentTypeID(
			'vBCms_StaticHtml');

		if ($rst = vB::$vbulletin->db->query_read("SELECT s.contentid as itemid,
		u.username, s.contentid, n.nodeid, u.userid, i.html_title,
		s.html AS pagetext, i.title, i.description, n.publishdate, n.parentnode,
		parent.title AS parenttitle, parent.html_title AS parent_html_title,
		FROM " . TABLE_PREFIX . "cms_statichtml s
		LEFT JOIN " . TABLE_PREFIX . "cms_node n ON n.contentid = s.contentid
  		LEFT JOIN " . TABLE_PREFIX . "cms_nodeinfo i ON i.nodeid = n.nodeid
  		LEFT JOIN " . TABLE_PREFIX . "cms_nodeinfo AS parent ON parent.nodeid = n.parentnode
  		LEFT JOIN " . TABLE_PREFIX . "user u ON u.userid = n.userid
		WHERE s.contentid = $id AND n.contenttypeid = " . $contenttypeid))
		{
			if ($search_result = vB::$vbulletin->db->fetch_array($rst))
			{
				//If unpublished we hide this.
				if (!($search_result['publishdate'] < TIMENOW))
				{
					continue;
				}
				$item = new vBCms_Search_Result_StaticHtml();
				$item->itemid = $search_result['itemid'];
				$item->contenttypeid = $contenttypeid;
				$categories = array();

				if ($rst1 = vB::$vbulletin->db->query_read("SELECT cat.categoryid, cat.category FROM " .
					TABLE_PREFIX . "cms_nodecategory nc INNER JOIN " .	TABLE_PREFIX .
					"cms_category cat ON nc.categoryid = cat.categoryid WHERE nc.nodeid = " .
					$search_result['nodeid']))
				{
					while($record = vB::$vbulletin->db->fetch_array($rst1))
					{
						$categories[$record['categoryid']] = $record;
					}
				}
				$search_result['categories'] = $categories;
				$item->record = $search_result;
				return $item;
			}
			return false;
		}
	}

	/**
	 * this will create an array of result objects from an array of ids()
	 *
	 * @param array of integer $ids
	 * @return array of objects
	 */
	public function create_array($ids)
	{
		$contenttypeid = vB_Types::instance()->getContentTypeID(
			'vBCms_StaticHtml');

		if ($rst = vB::$vbulletin->db->query_read("SELECT s.contentid as itemid,
		u.username, s.contentid, n.nodeid, u.userid, i.html_title,
		s.html AS pagetext, i.title, i.description, n.publishdate
		FROM " . TABLE_PREFIX . "cms_statichtml s
		LEFT JOIN " . TABLE_PREFIX . "cms_node n ON n.contentid = s.contentid
  		LEFT JOIN " . TABLE_PREFIX . "cms_nodeinfo i ON i.nodeid = n.nodeid
  		LEFT JOIN " . TABLE_PREFIX . "user u ON u.userid = n.userid
		WHERE s.contentid IN (" . implode(', ', $ids) .
			") AND n.contenttypeid = " . $contenttypeid))
		{
			while ($search_result = vB::$vbulletin->db->fetch_array($rst))
			{

				//If unpublished we hide this.
				if (!($search_result['publishdate'] < TIMENOW))
				{
					continue;
				}
				$item = new vBCms_Search_Result_StaticHtml();
				$item->itemid = $search_result['itemid'];
				$item->contenttypeid = $contenttypeid;
				if ($rst1 = vB::$vbulletin->db->query_read("SELECT cat.categoryid, cat.category FROM " .
					TABLE_PREFIX . "cms_nodecategory nc INNER JOIN " .	TABLE_PREFIX .
					"cms_category cat ON nc.categoryid = cat.categoryid WHERE nc.nodeid = " .
					$search_result['nodeid']))
				{
					while($record = vB::$vbulletin->db->fetch_array($rst1))
					{
						$categories[$record['categoryid']] = $record;
					}
				}
				$search_result['categories'] = $categories;
				$item->record = $search_result;
				$items[$search_result['itemid']] = $item;
			}
			return $items;
		}
		return false;
	}

	/**
	 * protected constructor, to ensure use of create()
	 *
	 */
	protected function __construct()
	{}

	/**
	 * all result objects must tell their contenttypeid
	 *
	 * @return integer contenttypeid
	 */
	public function get_contenttype()
	{
		return vB_Types::instance()->getContentTypeID('vBCms_StaticHtml');
	}

	/**
	 * all result objects must tell whether they are searchable
	 *
	 * @param mixed $user: the id of the user requesting access
	 * @return bool true
	 */

	public function can_search($user)
	//By definition, StaticHtml is always searchable, even
	// for a guest.
	{
		return true;
	}

	
	/**** returns the database record
	 *
	 * @return array
	 ****/

	public function get_record()
	{
		return $this->record;
	}
	/**
	 * function to return the rendered html for this result
	 *
	 * @param string $current_user
	 * @param object $criteria
	 * @return
	 */

	public function render($current_user, $criteria, $template_name = '')
	{
		global $vbulletin;
		global $show;
		include_once DIR . '/vb/search/searchtools.php';

		if (!strlen($template_name))
		{
			$template_name = 'vbcms_searchresult_statichtml';
		}
		$template = vB_Template::create($template_name);

		$template->register('title', $this->record['title'] );
		$template->register('html_title', $this->record['html_title'] );
		$template->register('page_url', vB_Route::create('vBCms_Route_Content', $this->record['nodeid'])->getCurrentURL());
		$template->register('username', $this->record['username']);
		$template->register('user', array(
			'username' => $this->record['username'],
			'userid' => $this->record['userid'],
		));
		$template->register('parentnode', $this->record['parentnode']);
		$template->register('parenttitle', $this->record['parenttitle']);
		$template->register('parent_html_title', $this->record['parent_html_title']);
		$template->register('pagetext',
			vB_Search_Searchtools::getSummary(vB_Search_Searchtools::stripHtmlTags($this->record['pagetext']), 100));
		$template->register('publishdate', $this->record['publishdate']);
		$template->register('published', $this->record['publishdate'] >= TIMENOW ?
		 true : false );
		$template->register('dateline', date($vbulletin->options['dateformat']. ' '
			. $vbulletin->options['default_timeformat'], $this->record['dateline']));
		$template->register('dateformat', $vbulletin->options['dateformat']);
		$template->register('timeformat', $vbulletin->options['default_timeformat']);
		$template->register('categories', $this->record['categories']);
		$result = $template->render();
		return $result;

	}

}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 30550 $
|| ####################################################################
\*======================================================================*/