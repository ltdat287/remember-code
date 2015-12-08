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
 * CMS Content Route
 * Routing for displaying and managing CMS pages, nodes and content.
 *
 * @author vBulletin Development Team
 * @version $Revision$
 * @since $Date$
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_Route_Content extends vB_Route
{
	/*Properties====================================================================*/

	/**
	 * The segment scheme
	 *
	 * @see vB_Route::$_segment_scheme
	 *
	 * @var array mixed
	 */
	protected $_segment_scheme = array(
		'node'			=>	array (
			'default'	=>	'1'
			),
		'action'		=> array (
			'values'	=> array(),
			'default'	=>	'view'
		)
	);

	/**
	 * Action map.
	*/
	protected static $actions = array();



	/*Initialization================================================================*/

	/**
	 * The constructor.
	 *
	 * A base URL is required to prepend to compiled URL.  If a route path is not
	 * specified then the default route path is assumed.
	 *
	 * @param string $route_path				- The route path to compile
	 */
	public function __construct($route_path = false)
	{
		if (vB::$vbulletin->options['default_page'])
		{
			$this->_default_path = vB::$vbulletin->options['default_page'];
		}

		parent::__construct($route_path);

	}



	/*Response======================================================================*/

	/**
	 * Returns the response for the route.
	 *
	 * @return string							- The response
	 */
	public function getResponse()
	{
		if (!$this->isValid())
		{
			throw (new vB_Exception_404('Invalid route'));
		}
		
		if (!($controller = vB_Router::getActionController(get_class($this), $this->action, $this->_parameters)))
		{
			throw (new vB_Exception_404('Invalid action requested'));
		}

		return $controller->getResponse();
	}

	

	/*URL===========================================================================*/

	/**
	 * Validates a segment.
	 * The segment scheme is checked for constraints and boolean false is returned
	 * if the segment is not valid.
	 *
	 * Child classes can extend this if they use other validation methods.
	 *
	 * @throws vB_Exception_Router
	 *
	 * @param string $name						- The key name of the segment
	 * @param mixed $value						- The value to validate
	 * @return bool								- Success
	 */
	protected function validateSegment($name, $value)
	{
		if (!parent::validateSegment($name, $value))
		{
			return false;
		}
		
		if ('node' != $name)
		{
			return true;
		}
		
		return (bool)intval($value);
	}
	
	
	/**
	 * Returns a representative URL of a route.
	 * Optional segments and parameters may be passed to set the route state.
	 *
	 * @param array mixed $segments				- Assoc array of segment => value
	 * @param array mixed $parameters			- Array of parameter values, in order
	 * @return string							- The URL representing the route
	 */
	public static function getURL(array $segments = null, array $parameters = null)
	{
		$route = new vBCms_Route_Content();
		return $route->getCurrentURL($segments, $parameters);
	}
	
	
	/**
	 * Inflate dynamic segments to canonical values.
	 */
	public function inflateSegments()
	{
		$node = new vBCms_Item_Content($this->node);

		if (!$node->isValid())
		{
			return;
		}

		if ($this->node != ($segment = $node->getUrlSegment()))
		{
			$this->setSegment('node', $segment, true);
		}
	}
	

	/*Segments======================================================================*/

	/**
	 * Builds dynamic segment schemes.
	 */
	protected function buildSegmentScheme()
	{
		$actions = vB_Router::getRouteActions(get_class($this));

		$this->_segment_scheme['node']['default'] = vB::$vbulletin->options['default_page'];
		$this->_segment_scheme['action']['values'] = $actions;
	}
}

/*======================================================================*\
|| ####################################################################
|| # SVN: $Revision$
|| ####################################################################
\*======================================================================*/