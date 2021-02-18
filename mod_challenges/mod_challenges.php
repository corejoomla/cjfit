<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$db 			= JFactory::getDbo();
$nullDate 		= $db->quote($db->getNullDate());
$nowDate 		= $db->quote(JFactory::getDate()->toSql());

$query = $db->getQuery(true)
	->select('a.id, a.title, a.description, a.rules, a.points, a.checked_out, a.checked_out_time, a.published, a.access, a.created, a.created_by, a.language')
	->from('#__cjfit_challenges AS a')
	->where('a.published = 1')
	->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
	->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');

$db->setQuery($query);
$challenges = $db->loadObjectList();

require(JModuleHelper::getLayoutPath("mod_challenges"));