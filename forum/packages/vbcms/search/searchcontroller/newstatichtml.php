<?php if (!defined('VB_ENTRY')) die('Access denied.');

require_once DIR . '/vb/search/searchcontroller.php' ;

class vBCms_Search_SearchController_NewStaticHtml extends vBCms_Search_SearchController_NewContentNode
{

	/** standard constructor **/
	public function __construct()
	{
		$self->contenttypeid = vB_Types::instance()->getContentTypeID('vBCms_StaticHtml');
	}

}
/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 31978 $
|| ####################################################################
\*======================================================================*/