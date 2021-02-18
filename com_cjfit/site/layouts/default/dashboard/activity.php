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
$activities	= $data->activity;
$params		= $data->params;

if(empty($activities))
{
	echo JText::_('COM_CJFIT_NO_DATA_FOUND');
	return 0;
}

$steps = $distance = $calories = array();
foreach ($activities->steps as $activity)
{
	$steps[$activity->activity_date] = $activity->activity_value;
}
foreach ($activities->distance as $activity)
{
	$distance[$activity->activity_date] = $activity->activity_value;
}
foreach ($activities->calories as $activity)
{
	$calories[$activity->activity_date] = $activity->activity_value;
}
?>
<div class="activity-chart">
	<div class="dashboard-item-title margin-bottom-5"><?php echo JText::_('COM_CJFIT_LABEL_ACTIVITY')?></div>
	<div class="activity-chart" style="max-height: 150px;">
		<canvas id="dashboard-activity-chart-canvas"></canvas>
	</div>
</div>
<div style="display: none;">
	<div class="dashboard-steps-labels"><?php echo implode(',', array_keys($steps))?></div>
	<div class="dashboard-steps-values"><?php echo implode(',', array_values($steps))?></div>
	<div class="dashboard-distance-labels"><?php echo implode(',', array_keys($distance))?></div>
	<div class="dashboard-distance-values"><?php echo implode(',', array_values($distance))?></div>
	<div class="dashboard-calories-labels"><?php echo implode(',', array_keys($calories))?></div>
	<div class="dashboard-calories-values"><?php echo implode(',', array_values($calories))?></div>
</div>