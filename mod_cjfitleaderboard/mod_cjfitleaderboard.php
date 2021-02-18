<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  pkg_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';
CJLib::import('corejoomla.framework.core');
////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////

require_once JPATH_ROOT . '/components/com_cjfit/helpers/constants.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/helper.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/route.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/rulesparser.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/units.php';

$user 					= JFactory::getUser();
$document 				= JFactory::getDocument();
$language 				= JFactory::getLanguage();
$appParams				= JComponentHelper::getParams('com_cjfit');
$layout					= $appParams->get('ui_layout', 'default');
$moduleclass_sfx		= $appParams->get('moduleclass_sfx');
$custom_tag 			= $appParams->get('custom_tag', true);

$language->load('com_cjfit');
JLoader::import('user', JPATH_ROOT.'/components/com_cjfit/models');
JLoader::import('dashboard', JPATH_ROOT.'/components/com_cjfit/models');

$userModel 				= JModelLegacy::getInstance( 'user', 'CjFitModel' );
$state 					= $userModel->getState(); // access the state first so that it can be modified

$dashboardModel			= JModelLegacy::getInstance( 'dashboard', 'CjFitModel' );
$state 					= $dashboardModel->getState(); // access the state first so that it can be modified

$dashboardModel->setState('leaderboard.type', $params->get('leaderboard_type', 'steps'));
$dashboardModel->setState('leaderboard.duration', $params->get('leaderboard_duration', 'daily'));
$appParams->set('show_leaderboard_title', false);

$data 					= new stdClass();
$api					= new CjLibApi();

$profileApp				= $appParams->get('profile_component');
$pk						= $api->getProfileIdFromRequest($profileApp, $user->id);
$data->item 			= $userModel->getItem($pk);
$data->params 			= $appParams->merge($params);
$data->user				= JFactory::getUser($pk);
$data->leaderboard		= $dashboardModel->getLeaderboard();
$data->activity_label	= $dashboardModel->getActivityLabel();
$data->state			= $state;

CJFunctions::load_jquery(array('libs'=>array('fontawesome'), 'custom_tag'=>false));
CJFunctions::add_css_to_document($document, JUri::root(true).'/media/com_cjfit/css/cjfit.min.css', $custom_tag);
CJFunctions::add_script(JUri::root(true).'/media/com_cjfit/js/cjfit.min.js', $custom_tag);

require(JModuleHelper::getLayoutPath("mod_cjfitleaderboard"));