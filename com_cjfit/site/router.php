<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class of com_cjfit
 *
 * @since  3.7
 */
class CjFitRouter extends JComponentRouterView
{
	protected $noIDs = false;
	
	/**
	 * CjFit Component router constructor
	 *
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$params = JComponentHelper::getParams('com_cjfit');
		$this->noIDs = (bool) $params->get('sef_ids');
		$dashboard = new JComponentRouterViewconfiguration('dashboard');
		$dashboard->setKey('id');
		$this->registerView($dashboard);
		$form = new JComponentRouterViewconfiguration('form');
		$form->setKey('a_id');
		$this->registerView($form);
		
		parent::__construct($app, $menu);
		
		$this->attachRule(new JComponentRouterRulesMenu($this));
		
		if ($params->get('sef_advanced', 0))
		{
			$this->attachRule(new JComponentRouterRulesStandard($this));
			$this->attachRule(new JComponentRouterRulesNomenu($this));
		}
		else
		{
			JLoader::register('CjFitRouterRulesLegacy', __DIR__ . '/helpers/legacyrouter.php');
			$this->attachRule(new CjFitRouterRulesLegacy($this));
		}
	}
	
	/**
	 * Method to get the segment(s) for an dashboard
	 *
	 * @param   string  $id     ID of the dashboard to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getDashboardSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$db = JFactory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('handle'))
				->from($dbquery->qn('#__cjfit_users'))
				->where('id = ' . (int) $dbquery->q($id));
			$db->setQuery($dbquery);
			
			$id .= ':' . $db->loadResult();
		}
		
		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);
			
			return array($void => $segment);
		}
		
		return array((int) $id => $id);
	}
	
	/**
	 * Method to get the segment(s) for a form
	 *
	 * @param   string  $id     ID of the dashboard form to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 *
	 * @since   3.7.3
	 */
	public function getFormSegment($id, $query)
	{
		return $this->getDashboardSegment($id, $query);
	}
	
	/**
	 * Method to get the segment(s) for an dashboard
	 *
	 * @param   string  $segment  Segment of the dashboard to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getDashboardId($segment, $query)
	{
		if ($this->noIDs)
		{
			$db = JFactory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('id'))
				->from($dbquery->qn('#__cjfit_users'))
				->where('handle = ' . $dbquery->q($segment));
			$db->setQuery($dbquery);
			
			return (int) $db->loadResult();
		}
		
		return (int) $segment;
	}
}

/**
 * Content router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function cjfitBuildRoute(&$query)
{
	$app = JFactory::getApplication();
	$router = new CjFitRouter($app, $app->getMenu());
	
	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function cjfitParseRoute($segments)
{
	$app = JFactory::getApplication();
	$router = new CjFitRouter($app, $app->getMenu());
	
	return $router->parse($segments);
}