<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
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
require_once JPATH_ROOT . '/components/com_cjfit/helpers/units.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/route.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/rulesparser.php';

$user 					= JFactory::getUser();
$document 				= JFactory::getDocument();
$language 				= JFactory::getLanguage();
$appParams				= JComponentHelper::getParams('com_cjfit');
$layout					= $appParams->get('ui_layout', 'default');
$custom_tag				= $appParams->get('custom_tag', true);
$moduleclass_sfx		= $params->get('moduleclass_sfx'); 

$language->load('com_cjfit');
JLoader::import('user', JPATH_ROOT.'/components/com_cjfit/models');

$model 					= JModelLegacy::getInstance( 'user', 'CjFitModel' );
$state 					= $model->getState(); // access the state first so that it can be modified

$data 					= new stdClass();
$api					= new CjLibApi();

$profileApp				= $appParams->get('profile_component');
$pk						= $api->getProfileIdFromRequest($profileApp, $user->id);
$data->item 			= $model->getItem($pk);
$data->params 			= $appParams->merge($params);
$data->user				= JFactory::getUser($pk);
$data->activity			= $model->getDailyActivity($pk);

CjScript::_('fontawesome', array('custom'=>$custom_tag));

CJFunctions::add_css_to_document($document, JUri::root(true).'/media/com_cjfit/css/cjfit.min.css', $custom_tag);
CJFunctions::add_script(JUri::root(true) . '/media/com_cjfit/js/raphael.min.js', $custom_tag);
CJFunctions::add_script(JUri::root(true) . '/media/com_cjfit/js/justgage.js', $custom_tag);
CJFunctions::add_script(JUri::root(true).'/media/com_cjfit/js/cjfit.min.js', $custom_tag);

require(JModuleHelper::getLayoutPath("mod_stepsummary"));