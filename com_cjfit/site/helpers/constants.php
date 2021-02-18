<?php 
/**
 * @package     corejoomla.site
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

defined('CJFIT_CURR_VERSION') or define('CJFIT_CURR_VERSION',			'@version@');
defined('CJFIT_CJLIB_MIN_VERSION') or define('CJFIT_CJLIB_MIN_VERSION',	'2.7.0');
defined('CJFIT_MEDIA_DIR') or define('CJFIT_MEDIA_DIR',					JPATH_ROOT.'/media/com_cjfit/');
defined('CJFIT_MEDIA_URI') or define('CJFIT_MEDIA_URI',					JURI::root(true).'/media/com_cjfit/');
defined('CJFIT_ASSET_ID') or define('CJFIT_ASSET_ID', 					11);
defined('ACTIVITY_TYPE_STEPS') or define('ACTIVITY_TYPE_STEPS', 		1);
defined('ACTIVITY_TYPE_DISTANCE') or define('ACTIVITY_TYPE_DISTANCE',	2);
defined('ACTIVITY_TYPE_CALORIES') or define('ACTIVITY_TYPE_CALORIES',	3);
defined('ACTIVITY_TYPE_SEDENTARY') or define('ACTIVITY_TYPE_SEDENTARY',	4);
defined('ACTIVITY_TYPE_LACTIVE') or define('ACTIVITY_TYPE_LACTIVE',		5);
defined('ACTIVITY_TYPE_FACTIVE') or define('ACTIVITY_TYPE_FACTIVE',		6);
defined('ACTIVITY_TYPE_VACTIVE') or define('ACTIVITY_TYPE_VACTIVE',		7);