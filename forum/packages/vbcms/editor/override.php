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

require_once DIR . '/includes/class_editor_override.php' ;

class vBCms_Editor_Override extends vB_Editor_Override
{
	//public $template_toolbar_on = 'editor_toolbar_on';
	public $template_toolbar_on = 'vbcms_editor_toolbar_on';

	/*** type-specific parse function
	* @param string 	the text to be parsed
	* @param array		options
	* ***/
	public function parse_for_wysiwyg($text, array $options = null)
	{
		require_once DIR . '/packages/vbcms/bbcode/wysiwyg.php' ;
		require_once DIR . '/packages/vbcms/bbcode/html.php' ;
		$wysiwyg_parser = new vBCms_BBCode_Wysiwyg($this->registry, vBCms_BBCode_Wysiwyg::fetchCmsTags());

		// todo: options
		return $wysiwyg_parser->do_parse($text, false, true, true, true, true);
	}

	/**** returns the type of parse
	*
	* @return string 
	*
	****/
	public function get_parse_type()
	{
		return 'cms';
	}
}

/*======================================================================*\
|| ####################################################################
|| # CVS: $RCSfile$ - $Revision: 29998 $
|| ####################################################################
\*======================================================================*/