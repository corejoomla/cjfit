<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$data 			= $displayData['data'];
$params			= $data->params;
$item			= $data->item;
$pagination		= $data->pagination;
$achievements	= $data->achievements;

if(!empty($achievements))
{
	?>
	<table class="table table-striped table-hover">
		<?php 
		foreach ($achievements as $achievement)
		{
			$icon 		= '';
			$text 		= '';
			$goal 		= '';
			$value		= $achievement->goal_value;
			$unitHelper	= new CjFitUnitsHelper($params->get('distance_unit'));
			$unit 		= $unitHelper->getDistanceMeasure();
			
			switch ($achievement->goal_type)
			{
				case 1: // steps
					$icon = 'steps.png';
					$text = 'COM_CJFIT_ACHIEVEMENTS_STEPS_GOAL';
					$goal = $item->goals['steps'];
					break;
					
				case 2: // distance
					$icon = 'distance.png';
					$text = 'COM_CJFIT_ACHIEVEMENTS_DISTANCE_GOAL';
					$goal = $unitHelper->getFactoredValue(1, $item->goals['distance']) . ' ' . $unit;
					$value = $value . ' ' . $unit;
					break;
					
				case 3: // calories
					$icon = 'calories.png';
					$text = 'COM_CJFIT_ACHIEVEMENTS_CALORIES_GOAL';
					$goal = $item->goals['calories'];
					break;
					
				case 4: // active minutes
					$icon = 'clock.png';
					$text = 'COM_CJFIT_ACHIEVEMENTS_ACTIVE_MINUTES_GOAL';
					$goal = $item->goals['activeMinutes'];
					break;
					
				case 5: // challenge
					$icon = 'trophy.png';
					$text = 'COM_CJFIT_ACHIEVEMENTS_WON_CHALLENGE';
					$goal = $achievement->challenge_title;
					break;
			}
			?>
			<tr>
				<td style="width: 30px;"><img src="<?php echo JUri::root(true) . '/media/com_cjfit/images/' . $icon;?>" alt="" style="width: 24px; max-height: 24px;"/></td>
				<td><?php echo CjFitHelper::getActivityDate($achievement->goal_date);?></td> 
				<td><?php echo JText::sprintf($text, $goal, $value, array(array('jsSafe'=>true)));?></td>
			</tr>
			<?php 
		}
		?>
	</table>
	<?php
	if (($params->def('show_pagination', 2) == 1  || ($params->get('show_pagination') == 2)) && ($pagination->pagesTotal > 1))
	{
		?>
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
			<div class="pagination">
				<?php if ($params->def('show_pagination_results', 1)) : ?>
					<p class="counter pull-right">
						<?php echo $pagination->getPagesCounter(); ?>
					</p>
				<?php endif; ?>
		
				<?php echo $pagination->getPagesLinks(); ?>
			</div>
		</form>
		<?php 
    }
}
else 
{
	?>
	<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');?></div>
	<?php 
}