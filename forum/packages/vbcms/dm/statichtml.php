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
 * Test Content Data Manager.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 32878 $
 * @since $Date: 2009-10-28 13:38:49 -0500 (Wed, 28 Oct 2009) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_DM_StaticHtml extends vBCms_DM_Node
{
	/*Properties====================================================================*/

	/**
	* Field definitions for the type-specific info.
	* The field definitions are in the form:
	*	array(fieldname => array(VF_TYPE, VF_REQ, VF_METHOD, VF_VERIFY)).
	*
	* @var array string => array(int, int, mixed)
	*/
	protected $type_fields = array(
		'html' => 			array(vB_Input::TYPE_STR,		self::REQ_NO)
	);

	/**
	 * Map of table => field for fields that can automatically be updated with their
	 * set value.
	 *
	 * @var array (tablename => array(fieldnames))
	 */
	protected $type_table_fields = array(
		'cms_statichtml' =>		array('html')
	);

	/**
	 * Table name of the primary table.
	 * This is used to get the new item id on an insert save and should be defined
	 * in child classes where the default save() implementation is used.
	 *
	 * @var string
	 */
	protected $type_table = 'cms_statichtml';

	/**
	 * vB_Item Class.
	 * Class of the vB_Item that this DM is responsible for updating and/or
	 * creating.  This is used to instantiate the item when lazy loading based on an
	 * item id.  This should be defined in child classes where the default
	 * implementation is used.
	 *
	 * @var string
	 */
	protected $item_class = 'vBCms_Item_Content_StaticHtml';

//	protected $package = 'vBCms';
//	protected $class = 'StaticHtml';
	protected $index_search = true;


	/*Save==========================================================================*/

	/**
	* Resolves the condition SQL to be used in update queries.
	* This method is abstract and must be defined as there should always be a
	* condition for an existing item.
	*
	* @param string $table						- The table to get the condition for
	* @return string							- The resolved sql
	*/
	protected function getTypeConditionSQL($table)
	{

		$this->assertItem();
		
		return 'contentid = ' . intval($this->item->getId());
		
	}
}

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 32878 $
|| ####################################################################
\*======================================================================*/