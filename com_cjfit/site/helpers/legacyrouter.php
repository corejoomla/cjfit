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
 * Legacy routing rules class from com_cjfit
 *
 * @since       3.6
 * @deprecated  4.0
 */
class CjFitRouterRulesLegacy implements JComponentRouterRulesInterface
{
	/**
	 * Constructor for this legacy router
	 *
	 * @param   JComponentRouterView  $router  The router this rule belongs to
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function __construct($router)
	{
		$this->router = $router;
	}
	
	/**
	 * Preprocess the route for the com_cjfit component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function preprocess(&$query)
	{
	}
	
	/**
	 * Build the route for the com_cjfit component
	 *
	 * @param   array  &$query     An array of URL arguments
	 * @param   array  &$segments  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function build(&$query, &$segments)
	{
		// Get a menu item based on Itemid or currently active
		$params = JComponentHelper::getParams('com_cjfit');
		$advanced = $params->get('sef_advanced_link', 0);
		
		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->router->menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $this->router->menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}
		
		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_cjfit')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}
		
		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return;
		}
		
		// Are we dealing with an article or category that is attached to a menu item?
		if ($menuItem !== null
				&& $menuItem->query['view'] == $query['view']
				&& isset($menuItem->query['id'], $query['id'])
				&& $menuItem->query['id'] == (int) $query['id'])
		{
			unset($query['view']);
			
			if (isset($query['catid']))
			{
				unset($query['catid']);
			}
			
			if (isset($query['layout']))
			{
				unset($query['layout']);
			}
			
			unset($query['id']);
			
			return;
		}
		
		if ($view == 'dashboard')
		{
			if (!$menuItemGiven)
			{
				$segments[] = $view;
			}
			
			unset($query['view']);
			
			if (isset($query['id']))
			{
				// Make sure we have the id and the alias
				if (strpos($query['id'], ':') === false)
				{
					$db = JFactory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('handle')
						->from('#__cjfit_users')
						->where('id=' . (int) $query['id']);
					$db->setQuery($dbQuery);
					$alias = $db->loadResult();
					$query['id'] = $query['id'] . ':' . $alias;
				}
			}
			else
			{
				// We should have these two set for this view.  If we don't, it is an error
				return;
			}
			
			if ($advanced)
			{
				list($tmp, $id) = explode(':', $query['id'], 2);
			}
			else
			{
				$id = $query['id'];
			}
			
			$segments[] = $id;
			
			unset($query['id'], $query['catid']);
		}
		
		/*
		 * If the layout is specified and it is the same as the layout in the menu item, we
		 * unset it so it doesn't go into the query string.
		 */
		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}
		
		$total = count($segments);
		
		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}
	}
	
	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 * @param   array  &$vars      The URL attributes to be used by the application.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function parse(&$segments, &$vars)
	{
		$total = count($segments);
		
		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}
		
		// Get the active menu item.
		$item = $this->router->menu->getActive();
		$params = JComponentHelper::getParams('com_cjfit');
		$advanced = $params->get('sef_advanced_link', 0);
		$db = JFactory::getDbo();
		
		// Count route segments
		$count = count($segments);
		
		/*
		 * Standard routing for dashboard.  If we don't pick up an Itemid then we get the view from the segments
		 * the first segment is the view and the last segment is the id of the dashboard.
		 */
		if (!isset($item))
		{
			$vars['view'] = $segments[0];
			$vars['id'] = $segments[$count - 1];
			
			return;
		}
		
		if ($count == 1)
		{
			// We check to see if an alias is given.  If not, we assume it is an dashboard
			$vars['view'] = 'dashboard';
			$vars['id'] = (int) $segments[0];
			
			return;
			
		}
	}
}