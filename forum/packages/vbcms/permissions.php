<?php
 if (!defined('VB_ENTRY')) die('Access denied.');
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
 * CMS Content Controller
 * Base content controller for CMS specific content types.
 *
 * @package vBulletin
 * @author Ed Brown, vBulletin Development Team
 * @version $Revision: 35056 $
 * @since $Date: 2010-01-21 11:02:12 -0600 (Thu, 21 Jan 2010) $
 * @copyright vBulletin Solutions Inc.
 */

 /**
  * Class to return permissions
  *
  */

 class vBCMS_Permissions
{
	/**** Caching time in minutes for permissions info ********/
 	protected static $cache_ttl = 5;

 	//Permissions are:
 	//1: canview
 	//2: cancreate
 	//4: canedit
 	//8: canpublish
 	//16: canUseHtml
 	protected static $permissionsfrom = array();

 	/*** returns a string suitable for use in a "where" clause to limit results
 	* to those visible to this user.
 	* ***/
 	protected static $permission_string = false;

 	//We may have to check "canUseHtml" for several articles.
 	//Might as well cache the usergroups info.

 	private static $known_users = array();



 	 /**** This queries for a user's permissions. It is normally called once early
 	 * on a CMS page to read this user's permissions.
 	 *
 	 * @param int
 	 *
 	 ****/
 	public static function getUserPerms($userid = false)
	{
		require_once DIR . '/includes/class_bootstrap_framework.php' ;
		vB_Bootstrap_Framework::init();
		$cache_ttl = 1440;

		if ($userid > 0)
		{
			$record = vB::$vbulletin->db->query_first("SELECT usergroupid, membergroupids FROM "
				. TABLE_PREFIX . "user WHERE userid = $userid");
			$usergroups = $record['usergroupid']
			. (strlen($record['membergroupids']) ? ',' . $record['membergroupids'] : '');
		}
		else
		{
			$userid = vB::$vbulletin->userinfo['userid'];
			$usergroups = vB::$vbulletin->userinfo['usergroupid']
			. (strlen(vB::$vbulletin->userinfo['membergroupids']) ? ',' . vB::$vbulletin->userinfo['membergroupids'] : '');
		}
		vB::$vbulletin->userinfo['permissions']['cms'] = self::getPerms($userid, $usergroups);
	}



 	/**** This function generates and if appropriate caches CMS permissions for a user
 	 *
 	 * @param int
 	 * @param string
 	 *
 	 * @return array
 	 ****/
 	public static function getPerms($userid, $usergroups = false)
 	{

		if ($userid == vB::$vbulletin->userinfo['userid'] AND isset(vB::$vbulletin->userinfo['permissions']['cms']) )
 		{
 			return vB::$vbulletin->userinfo['permissions']['cms'];
 		}

 		//See if we have a cached version
 		$hash = self::getHash($userid);

 		//See if we already have this user;
 		if (array_key_exists($userid, self::$known_users))
 		{
 			return self::$known_users[$userid];
 		}

 		if ($cmsperms = vB_Cache::instance()->read($hash, true, true))
 		{
 			if ($userid == vB::$vbulletin->userinfo['userid'] )
 			{
 				vB::$vbulletin->userinfo['permissions']['cms'] = $cmsperms;
 			}
 			else
 			{
 				$known_users[$userid] = $cmsperms;
 			}
 			return $cmsperms;
 		}

 		if (! $usergroups)
 		{
 			if ($userid == 0)
 			{
 				$usergroups = '1';
 			}
 			else
 			{
 				$record = vB::$vbulletin->db->query_first("SELECT usergroupid, membergroupids FROM "
 					. TABLE_PREFIX . "user WHERE userid = $userid");
 				$usergroups = $record['usergroupid']
 				. (strlen($record['membergroupids']) ? ',' . $record['membergroupids'] : '');
 			}
 		}

 		$cmsperms = array();
 		//We need to create four arrays
 		$cmsperms['canview'] = array();
 		$cmsperms['cancreate'] = array();
 		$cmsperms['canedit'] = array();
 		$cmsperms['canpublish'] = array();
 		$cmsperms['canusehtml'] = array();

		//The admin settings are all done by hooks, so we need to parse out the information
 		//ourselves manually.
 		$sql ="SELECT vbcmspermissions FROM " . TABLE_PREFIX .
 			"administrator WHERE userid = $userid";
			$record = vB::$vbulletin->db->query_first($sql);
 		if ($record AND $record['vbcmspermissions'])
 		{
 			$cmsperms['admin'] = $record['vbcmspermissions'];

 		}
 		else
 		{
 			$cmsperms['admin'] = 0;
 		}


 		if ($usergroups != '')
 		{
			$rst = vB::$vbulletin->db->query_read($sql = "SELECT nodeid,
			MAX(permissions & 1) AS canview, MAX(permissions & 2) AS cancreate , MAX(permissions & 4) AS canedit,
			MAX(permissions & 8) AS canpublish, MAX(permissions & 16) AS canusehtml
			FROM " . TABLE_PREFIX . "cms_permissions p
			WHERE usergroupid IN (" . $usergroups . ")
			GROUP BY nodeid; ");

			while($rst AND $result = vB::$vbulletin->db->fetch_array($rst))
	 		{

	 			if ($result['canview'] OR $result['canedit'] OR $result['canpublish'] OR $result['canusehtml'])
	 			{
	 				$cmsperms['canview'][] = $result['nodeid'];
	 			}

	 			if ($result['cancreate'] OR $result['canusehtml'] OR $result['canedit'])
	 			{
	 				$cmsperms['cancreate'][] = $result['nodeid'];
	 			}

	 			if ($result['canedit'] OR $result['canpublish'] OR $result['canusehtml'])
	 			{
	 				$cmsperms['canedit'][] = $result['nodeid'];
	 			}

	 			if ($result['canpublish'] OR $result['canusehtml'])
	 			{
	 				$cmsperms['canpublish'][] = $result['nodeid'];
	 			}

	 			if ($result['canusehtml'])
	 			{
	 				$cmsperms['canusehtml'][] = $result['nodeid'];
	 			}
	 		}
 		}
 		//when we use these in "where" clauses we'd better have at least one value.

 		if (!count($cmsperms['canview']))
 		{
 			$cmsperms['canview'][] = -1;
 		}

 		if (!count($cmsperms['cancreate']))
 		{
 			$cmsperms['cancreate'][] = -1;
 		}

 		if (!count($cmsperms['canedit']))
 		{
 			$cmsperms['canedit'][] = -1;
 		}

 		if (!count($cmsperms['canpublish']))
 		{
 			$cmsperms['canpublish'][] = -1;
 		}
 		if (!count($cmsperms['canusehtml']))
 		{
 			$cmsperms['canusehtml'][] = -1;
 		}


 		$cmsperms['alledit'] = $cmsperms['canedit'];

	   $cmsperms['viewonly'] =
 		   array_diff($cmsperms['canview'],
 		   $cmsperms['alledit']);

 		$cmsperms['allview'] = $cmsperms['canview'];

 		if ($userid == vB::$vbulletin->userinfo['userid'] )
 		{
 			vB::$vbulletin->userinfo['permissions']['cms'] = $cmsperms;
 		}
		else
		{
			self::$known_users[$userid] = $cmsperms;
		}

  		vB_Cache::instance()->write($hash, $cmsperms, self::$cache_ttl,
			array('cms_permissions_change', "permissions_$userid"));

 		return $cmsperms;
 	}


 	/** This pulls the permission data from the database for a node,
 	*  if we don't already have it.
 	***/
 	private static function getPermissionsFrom($nodeid)
 	{
 		if (! $record = vB::$vbulletin->db->query_first("SELECT permissionsfrom, hidden, setpublish, publishdate, userid FROM " .
 			TABLE_PREFIX . "cms_node WHERE nodeid = $nodeid"))
 		{
 			return false;
 		}
 		if (intval($record['permissionsfrom']))
 		{
 			self::$permissionsfrom[$nodeid] = $record;
 			return $record;
 		}
 		return false;
 	}
	/****
	 * This resets permissions for a node, and it's surrounding nodes, should they be unassigned.
	 *
	 ****/
	private static function repairPermissions($nodeid)
	{
		//we start by generating a list of this node's parents. We go up the tree until
		// either we find a node with assigned permissions, or we hit the top.
		//If we hit the top, we use that node.
		require_once DIR . '/includes/class_bootstrap_framework.php' ;
		vB_Bootstrap_Framework::init();

		$parents = array();
		$rst = vB::$vbulletin->db->query_read("SELECT parent.nodeid,
		parent.permissionsfrom FROM " . TABLE_PREFIX . "cms_node AS parent INNER JOIN
		" . TABLE_PREFIX . "cms_node AS node ON node.nodeleft BETWEEN parent.nodeleft AND parent.noderight
			AND parent.nodeid <> node.nodeid
		WHERE node.nodeid = $nodeid ORDER BY node.nodeleft DESC");
		$permissionsfrom = 1;

		while($record = vB::$vbulletin->db->fetch_array($rst))
		{
			$parents[] = $record;

			if (intval($record['permissionsfrom']))
			{
				$permissionsfrom = $record['permissionsfrom'];
				break;
			}
		}
		//Now we go back down the list. Assign the node to the children at each level;
		foreach ($parents as $parent)
		{
			vB::$vbulletin->db->query_write("UPDATE ". TABLE_PREFIX . "cms_node SET permissionsfrom = $permissionsfrom
				WHERE permissionsfrom IS NULL AND parentnode = " . $parent['nodeid']);
		}

	}


	/****
	* This determines if the user can view a node
	*
	* @param int
	*
	* @return boolean
	* ****/
 	public static function canView($nodeid)
	{

 		require_once DIR . '/includes/class_bootstrap_framework.php' ;
 		vB_Bootstrap_Framework::init();

 		if (! isset(vB::$vbulletin->userinfo['permissions']['cms']) )
 		{
 			self::getUserPerms();
 		}

 		if (! isset(vB::$vbulletin->userinfo['permissions']['cms']) )
 		{
 			self::getUserPerms();
 		}

 		if (array_key_exists ($nodeid, self::$permissionsfrom))
 		{
 			$permfrom = self::$permissionsfrom[$nodeid];
 		}
 		else
 		{
 			if (!$permfrom = self::getPermissionsFrom($nodeid))
 			{
 				return false;
 			}
 		}

		if (intval($permfrom['hidden']))
		{
			return (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canpublish']));
		}

 		if ($permfrom['userid'] == vB::$vbulletin->userinfo['userid'])
 		{
 			return true;
 		}

 		if (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canedit']))
 		{
 			return true;
 		}

 		return (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canview'])
 			AND intval($permfrom['setpublish']) AND ($permfrom['publishdate'] < TIMENOW));
	}

 	/*** In some cases we have a node, then in a few lines need to get the permissions from. If we have
 	* a value, let's keep it***/
 	public function setPermissionsfrom($nodeid, $permissionsfrom, $hidden = 0,
 		$setpublish = false, $publishdate = false, $userid = false)
 	{
 		if (intval($permissionsfrom) AND intval($nodeid) AND intval($userid) )
 		{
 			self::$permissionsfrom[$nodeid] = array('permissionsfrom' => intval($permissionsfrom),
 				'hidden' => intval($hidden), '$setpublish' =>$setpublish,
 				'publishdate' => $publishdate, 'userid' => $userid);
 		}
 	}


 	/** This function tells whether we can create a content node.
 	 * The rules are: if we have publish rights we can create any type of content
 	 * If we have create or edit we can create non-section types.
 	 * @param int
 	 *
 	 * @return boolean
 	 ***/
 	public static function canEdit($nodeid)
	{
		if (! isset(vB::$vbulletin->userinfo['permissions']['cms']) )
		{
			self::getUserPerms();
		}
			if (array_key_exists ($nodeid, self::$permissionsfrom))
		{
			$permfrom = self::$permissionsfrom[$nodeid];
		}
		else
		{
			if (!$permfrom = self::getPermissionsFrom($nodeid))
			{
				return false;
			}
		}

		if ($permfrom['userid'] == vB::$vbulletin->userinfo['userid'])
		{
			return true;
		}

		if (intval($permfrom['hidden']))
		{
			return (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canpublish']));
		}

		return (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canedit']));
	}

 	/**** This gives us a string suitable for using in a "where" clause that
 	* limits results from the node table to those records this user can see
 	* That means either: They have canedit, or it's theirs,
 	* or they have canview and it's published
 	 * @param string
 	 *
 	 * @return string
 	 * ***/
 	public static function getPermissionString($tablename = 'node')
 	{
 		if (self::$permission_string)
 		{
 			return self::$permission_string;
 		}

 		require_once DIR . '/includes/class_bootstrap_framework.php' ;
 		vB_Bootstrap_Framework::init();

 		if (!isset(vB::$vbulletin->userinfo['permissions']['cms']) )
 		{
 			self::getUserPerms();
 		}

 		$result = "( ($tablename.permissionsfrom IN (" . implode(',', vB::$vbulletin->userinfo['permissions']['cms']['canedit']) .
			")) OR ($tablename.userid =" . vB::$vbulletin->userinfo['userid'] . ") OR ($tablename.permissionsfrom IN (" .
			implode(',', vB::$vbulletin->userinfo['permissions']['cms']['canview']) . ") AND $tablename.setpublish = 1 AND $tablename.publishdate <" .
			 TIMENOW . "))";

 		return $result;
 	}

 	/** This function tells whether we can create a content node.
 	* The rules are: if we have publish rights we can create any type of content
 	* If we have create or edit we can create non-section types.
 	*
 	* @param int
 	* @param int
 	*
 	* @return boolean
 	***/
 	public static function canCreate($nodeid, $contenttype)
 	{

 		if (array_key_exists ($nodeid, self::$permissionsfrom))
 		{
 			$permfrom = self::$permissionsfrom[$nodeid];
 		}
 		else
 		{
 			if (!$permfrom = self::getPermissionsFrom($nodeid))
 			{
 				return false;
 			}
 		}

 		if (! isset(vB::$vbulletin->userinfo['permissions']['cms']) )
 		{
 			self::getUserPerms();
 		}

		if (intval($permfrom['hidden']))
		{
			return (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['canpublish']));
		}

		return (in_array($permfrom['permissionsfrom'], vB::$vbulletin->userinfo['permissions']['cms']['cancreate']));


 		return false;
 	}

 	/** This function tells whether this user can create a content node.
 	 * The rules are: if we have publish rights we can create any type of content
 	 * If we have create or edit we can create non-section types.
 	 * @param int
 	 * @param int
 	 * @param int
 	 *
 	 * @return boolean
 	 ***/
 	public static function canUseHtml($nodeid, $contenttype, $userid)
 	{
 		if (! intval($userid))
 		{
 			return false;
 		}

		if (array_key_exists ($nodeid, self::$permissionsfrom))
 		{
 			$permfrom = self::$permissionsfrom[$nodeid];
 		}
 		else
 		{
 			if (!$permfrom = self::getPermissionsFrom($nodeid))
 			{
 				return false;
 			}
 		}

 		$perms = self::getPerms($userid);
 		$result = in_array($permfrom['permissionsfrom'], $perms['canusehtml']) ?  1 : 0;

 		return $result;
 	}

	/********* Get a hash so we can cache the data
	 * @param int
	 *
	 *@return string
	 ********/
	protected static function getHash($userid = null)
	{
		if ($userid == null)
		{
			$userid = vB::$vbulletin->userinfo['userid'];
		}
		$context = new vB_Context('cms_priv' , array('userid' => $userid));
		return strval($context);

	}
 }