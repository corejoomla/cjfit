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
$value		= isset($item->activity[ACTIVITY_TYPE_DISTANCE]['activity_value']) ? $item->activity[ACTIVITY_TYPE_DISTANCE]['activity_value'] : 0;
$percent	= $item->goals['distance'] > 0 ? $value * 100 / $item->goals['distance'] : 0;
$unitHelper	= new CjFitUnitsHelper($params->get('distance_unit'));
$distance 	= $unitHelper->getFactoredValue(1, $value);
$unit 		= $unitHelper->getDistanceMeasure();
?>
<div class="dashboard-item">
	<div class="dashboard-item-title"><?php echo JText::_('COM_CJFIT_LABEL_DISTANCE')?></div>
	<div class="dashboard-guage" id="dashabord-guage-distance"
		data-value="<?php echo $distance?>" 
		data-goal="<?php echo $item->goals['distance'];?>"
		data-label="<?php echo JText::sprintf('COM_CJFIT_VALUE_OF_GOAL', $item->goals['distance'] . $unit)?>"
		data-decimals="1"></div>
</div>