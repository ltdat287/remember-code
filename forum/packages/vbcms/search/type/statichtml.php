<?php if (!defined('VB_ENTRY')) die('Access denied.');

/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/


require_once (DIR . '/vb/search/type.php');

/**
 * vBCms_Search_Type_StaticHtml
 *
 * @package
 * @author Ed Brown
 * @copyright Copyright (c) 2009
 * @version $Id: statichtml.php 30550 2009-04-28 23:55:20Z ebrown $
 * @access public
 */
class vBCms_Search_Type_StaticHtml extends vB_Search_Type
{

	/** package name ***/
	protected $package = "vBCms";

	/** class name ***/
	protected $class = "StaticHtml";

	/** package name for the group item ***/
	protected $group_package = "vBCms";

	/** class name for the group item***/
	protected $group_class = "StaticHtml";

	/**
	 * vBCms_Search_Type_StaticHtml::fetch_validated_list()
	 *
	 * @param mixed $user
	 * @param mixed $ids
	 * @param mixed $gids
	 * @return
	 */
	public function fetch_validated_list($user, $ids, $gids)
	{

		include_once DIR . '/packages/vbcms/item/content/statichtml.php';
		$list = array();
		//anyone can see anything that is published
		$html = vBCms_Search_Result_StaticHtml::create_array($ids);

		return array('list' => $html, 'groups_rejected' => array());
	}

	// ###################### Start prepare_render ######################
	/**
	 * called before the render to do necessary phrase loading, etc.
	 *
	 * @param object $user
	 * @param object $results
	 * @return
	 */
	public function prepare_render($user, $results)
	{
		$phrase = new vB_Legacy_Phrase();
		$phrase->add_phrase_groups(array('user', 'search'));

		foreach ($results AS $result)
		{
			$privs = array();
			//if we have a right for any item in the result set we have that right

			foreach ($privs AS $key => $priv)
			{
				$this->mod_rights[$key] = ($this->mod_rights[$key] OR (bool) $priv);
			}
		}
	}

	/**
	 * called to set any additional header text. We don't have any
	 *
	 * @return string
	 */
	public function additional_header_text()
	{
		return '';
	}

	/**
	 * return the name displayed for this type
	 *
	 * @return string
	 */
	public function get_display_name()
	{
		return vB_Types::instance()->getContentTypeTitle($this->get_contenttypeid());
	}

	/**
	 * This function composes the html to display the user interface for this
	 * search type
	 *
	 * @param mixed $prefs : the array of user preferences
	 * @param mixed $contenttypeid : the content type for which we are going to
	 *    search
	 * @param array registers : any additional elements to be registered. These are
	 * 	just passed to the template
	 * @param string $template_name : name of the template to use for display. We have
	 *		a default template.
	 * @param boolean $groupable : a flag to tell whether the interface should display
	 * 	grouping option(s).
	 * @return $html: complete html for the search elements
	 */
	public function listUi($prefs = null, $contenttypeid = null, $registers = null,
		$template_name = null)
	{
		global $vbulletin, $vbphrase;


		if (! isset($template_name))
		{
			$template_name = 'search_input_default';
		}

		if (! isset($contenttypeid))
		{
			$contenttypeid = vB_Search_Core::get_instance()->get_contenttypeid('vBCms', 'StaticHtml');
		}

		$template = vB_Template::create($template_name);
		$template->register('securitytoken', $vbulletin->userinfo['securitytoken']);
		$template->register('class', $this->get_display_name());
		$template->register('contenttypeid',$contenttypeid);

		$prefsettings = array(
			'select'=> array('searchdate', 'beforeafter', 'titleonly', 'sortby', 'sortorder'),
			'cb' => array('exactname'),
			'value' => array('query', 'searchuser'));
		$this->setPrefs($template, $prefs, $prefsettings);
		vB_Search_Searchtools::searchIntroRegisterHumanVerify($template);

		if (isset($registers) AND is_array($registers) )
		{
			foreach($registers as $key => $value)
			{
				$template->register($key, htmlspecialchars_uni($value));
			}
		}
		return $template->render();
	}

	/**
	 * standard factory method
	 *
	 * @param integer $id
	 * @return object
	 */
	public function create_item($id)
	{
		return vBCms_Search_Result_StaticHtml::create($id);
	}

	/**
	 * can this type be grouped?
	 *
	 * @return boolean
	 */
	public function can_group()
	{
		return false;
	}

	/**
	 * is this type grouped by default?
	 *
	 * @return boolean
	 */
	public function group_by_default()
	{
		return false;
	}

	/**
	 * can this type be searched?
	 *
	 * @return boolean
	 */
	public function cansearch()
	{
		return true;
	}

	/**
	 * return any inline moderation options
	 *
	 * @return options array
	 *
	 * In general this doesn't get moderated.
	 */
	public function get_inlinemod_options()
	{
		return array();
	}


	/**
	 * what type of inline moderation is available?
	 *
	 * @return
	 */
	public function get_inlinemod_type()
	{
		return '';
	}

	/**
	 * what inline moderation actions are available?
	 *
	 * @return
	 */
	public function get_inlinemod_action()
	{
		return '';
	}

/**
 * Each search type has some responsibilities, one of which is to tell
 * what are its default preferences
 *
 * @return array
 */
	public function additional_pref_defaults()
	{
		return array(
			'query'         => '',
			'exactname'     => 0,
			'searchuser'     => '',
			'searchdate'    => 0,
			'beforeafter'   => 'after',
			'sortby'   => 'title',
			'sortorder'   => 'descending');
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 30550 $
|| ####################################################################
\*======================================================================*/