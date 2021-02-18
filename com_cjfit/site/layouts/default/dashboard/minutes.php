<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$data 		= $displayData['data'];
$item		= $data->item;
$params		= $data->params;
$sendetary	= isset($item->activity[ACTIVITY_TYPE_SEDENTARY]['activity_value']) ? $item->activity[ACTIVITY_TYPE_SEDENTARY]['activity_value'] : 0;
$lactive	= isset($item->activity[ACTIVITY_TYPE_LACTIVE]['activity_value']) ? $item->activity[ACTIVITY_TYPE_LACTIVE]['activity_value'] : 0;
$factive	= isset($item->activity[ACTIVITY_TYPE_FACTIVE]['activity_value']) ? $item->activity[ACTIVITY_TYPE_FACTIVE]['activity_value'] : 0;
$vactive	= isset($item->activity[ACTIVITY_TYPE_VACTIVE]['activity_value']) ? $item->activity[ACTIVITY_TYPE_VACTIVE]['activity_value'] : 0;
$value		= $lactive + $factive + $vactive;
$percent	= $item->goals['steps'] > 0 ? $value * 100 / $item->goals['steps'] : 0;
?>
<div class="dashboard-item">
	<div class="dashboard-item-title"><?php echo JText::_('COM_CJFIT_LABEL_ACTIVE_MINUTES')?></div>
	<div class="dashboard-guage" id="dashabord-guage-active-minutes"
		data-value="<?php echo $value?>" 
		data-goal="<?php echo $item->goals['activeMinutes'];?>"
		data-label="<?php echo JText::sprintf('COM_CJFIT_VALUE_OF_GOAL', $item->goals['activeMinutes'])?>"></div>
</div>