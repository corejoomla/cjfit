<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::register('CjFitHelper', __DIR__ . '/helpers/cjfit.php');

////////////////////////////////////////// CjLib Includes ////////////////////
if(!file_exists(JPATH_ROOT.'/components/com_cjlib/framework.php') && !CjFitHelper::installCjLib())
{
	return;
}
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';
CJLib::import('corejoomla.framework.core');
////////////////////////////////////////// CjLib Includes ////////////////////

require_once JPATH_COMPONENT_SITE.'/helpers/constants.php';
require_once JPATH_COMPONENT_SITE.'/helpers/rulesparser.php';
JFactory::getLanguage()->load('com_cjfit', JPATH_ROOT);

$controller = JControllerLegacy::getInstance('CjFit');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
