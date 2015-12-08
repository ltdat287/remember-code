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

/**
 * CMS Layout View
 * View for rendering a page layout, it's grid html or customised template, and the
 * content and widget views that the layout contains.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: $
 * @since $Date: $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_View_Layout extends vB_View
{
	/**
	 * Widget locations
	 * 
	 * @var array
	 */
	public $widgetlocations = array();
	
	
	
	/*Render========================================================================*/

	/**
	 * Prepare the widget block locations and other info.
	 */
	protected function prepareProperties()
	{
		// Prepare widget and content blocks
		$this->prepareBlocks();
	}


	/**
	 * Arranges widgets and content into the layout block locations.
	 */
	protected function prepareBlocks()
	{
		// Get widget columns and sort into block locations
		$blocks = array();

		$add_content = true;

		foreach ($this->widgetlocations AS $column => &$indices)
		{
			ksort($indices);
			foreach ($indices AS $index => $widgetid)
			{
				if (!isset($this->widgets[$widgetid]))
				{
					continue;
				}

				if ($add_content AND ($this->contentcolumn == $column) AND ($this->contentindex <= $index))
				{
					$blocks[$column][] = $this->content;
					$add_content = false;
				}

				$blocks[$column][] = $this->widgets[$widgetid];
			}
		}

		// Ensure the content was added
		if ($add_content)
		{
			$blocks[$this->contentcolumn][$this->contentindex] = $this->content;
		}


		ksort($blocks);
		foreach ($blocks AS $column => &$indices)
		{
			ksort($indices);
		}

		// TODO: Remove once the template iterator is done
		foreach ($blocks AS $column => &$index)
		{
			$index = implode("\n", $index);
		}

		$this->column = $blocks;

		unset($this->_properties['widgets']);
		unset($this->_properties['widgetlocations']);
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision: 28709 $
|| ####################################################################
\*======================================================================*/