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
$value		= isset($item->activity[ACTIVITY_TYPE_CALORIES]['activity_value']) ? $item->activity[ACTIVITY_TYPE_CALORIES]['activity_value'] : 0;
$percent	= $item->goals['caloriesOut'] > 0 ? $value * 100 / $item->goals['caloriesOut'] : 0;
?>
<div class="dashboard-item">
	<div class="dashboard-item-title"><?php echo JText::_('COM_CJFIT_LABEL_CALORIES')?></div>
	<div class="dashboard-guage new" id="dashabord-guage-catlories"
		data-value="<?php echo $value?>" 
		data-goal="<?php echo $item->goals['caloriesOut'];?>"
		data-label="<?php echo JText::sprintf('COM_CJFIT_VALUE_OF_GOAL', $item->goals['caloriesOut'])?>"></div>
</div>