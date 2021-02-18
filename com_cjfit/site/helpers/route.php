<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

abstract class CjFitHelperRoute
{
	protected static $lookup = array();

	protected static $lang_lookup = array();
	
	public static function getAuthorizationRoute ($language = 0)
	{
		$params = JComponentHelper::getParams('com_cjfit');
		$link = 'index.php?option=com_cjfit&task=user.authorize';
		
		if ($params->get('sef_advanced', 0))
		{
			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}
		else
		{
			$needles = array('authorize' => array(0));
			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();
				
				if (isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}
			
			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}
		
		return $link;
	}
	
	public static function getCallbackRoute ($language = 0)
	{
		$params = JComponentHelper::getParams('com_cjfit');
		$link = 'index.php?option=com_cjfit&task=user.validate';
		
// 		if ($params->get('sef_advanced', 0))
// 		{
// 			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
// 			{
// 				$link .= '&lang=' . $language;
// 			}
// 		}
// 		else
// 		{
// 			$needles = array('callback' => array(0));
// 			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
// 			{
// 				self::buildLanguageLookup();
				
// 				if (isset(self::$lang_lookup[$language]))
// 				{
// 					$link .= '&lang=' . self::$lang_lookup[$language];
// 					$needles['language'] = $language;
// 				}
// 			}
			
// 			if ($item = self::_findItem($needles))
// 			{
// 				$link .= '&Itemid=' . $item;
// 			}
// 		}
		
		$link = JUri::root(false) . $link;
//  		$link = 'http://www.corejoomla.com/j3/index.php?option=com_cjfit&task=user.validate';
		
		return $link;
	}
	
	public static function getDashboardRoute ($id = 0, $language = 0)
	{
		// Create the link
		$params = JComponentHelper::getParams('com_cjfit');
		$link = 'index.php?option=com_cjfit&view=dashboard&id=' . $id;
		
		if ($params->get('sef_advanced', 0))
		{
			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}
		else
		{
			$needles = array('dashboard' => array(0));
			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();
				
				if (isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
					$needles['language'] = $language;
				}
			}
			
			if ($item = self::_findItem($needles))
			{
				$link .= '&Itemid=' . $item;
			}
		}
		
		return $link;
	}
	
	protected static function buildLanguageLookup ()
	{
		if (count(self::$lang_lookup) == 0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');
			
			$db->setQuery($query);
			$langs = $db->loadObjectList();
			
			foreach ($langs as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	protected static function _findItem ($needles = null)
	{
		$app      = JFactory::getApplication();
		$menus    = $app->getMenu('site');
		$language = isset($needles['language']) ? $needles['language'] : '*';

		// Prepare the reverse lookup array.
		if (!isset(self::$lookup[$language]))
		{
			self::$lookup[$language] = array();
			$component  = JComponentHelper::getComponent('com_cjfit');
			
			$attributes = array('component_id');
			$values = array($component->id);

			if ($language != '*')
			{
				$attributes[] = 'language';
				$values[]     = array($needles['language'], '*');
			}

			$items = $menus->getItems($attributes, $values);

			foreach ($items as $item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];

					if (!isset(self::$lookup[$language][$view]))
					{
						self::$lookup[$language][$view] = array();
					}

					if (isset($item->query['id']))
					{
						/**
						 * Here it will become a bit tricky
						 * language != * can override existing entries
						 * language == * cannot override existing entries
						 */
						if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
						{
							self::$lookup[$language][$view][$item->query['id']] = $item->id;
						}
					}
					elseif (in_array($view, array('register', 'callback')))
					{
						if (!isset(self::$lookup[$language][$view][0]) || $item->language != '*')
						{
							self::$lookup[$language][$view][0] = $item->id;
						}
					}
				}
			}
		}
		
		if ($needles)
		{
			foreach ($needles as $view => $ids)
			{
				if (isset(self::$lookup[$language][$view]))
				{
					foreach ($ids as $id)
					{
						if (isset(self::$lookup[$language][$view][(int) $id]))
						{
							return self::$lookup[$language][$view][(int) $id];
						}
					}
				}
			}
		}

		// Check if the active menuitem matches the requested language
		$active = $menus->getActive();

		if ($active && $active->component == 'com_cjit' && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
		{
			return $active->id;
		}

		// If not found, return language specific home link
		$default = $menus->getDefault($language);

		return !empty($default->id) ? $default->id : null;
	}
}
