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
$leaders	= $data->leaderboard;
$params		= $data->params;
$api		= new CjLibApi();
$profileApp	= $params->get('profile_component', 'none');
$avatarApp	= $params->get('avatar_component', 'none');
$avatarSize	= 24;
$unitHelper	= new CjFitUnitsHelper($params->get('distance_unit'));
$unit 		= $unitHelper->getDistanceMeasure();
?>
<div class="leaderboard">
	<?php if($params->get('show_leaderboard_title', true)):?>
	<div class="dashboard-item-title"><?php echo JText::_('COM_CJFIT_LABEL_LEADERBOARD')?></div>
	<?php endif;?>
	
	<?php 
	foreach ($leaders as $i => $leader)
	{
		$alias			= $leader->handle ? ($leader->user_id. ':' . $leader->handle) : $leader->user_id;
		$profileUrl 	= CjFitHelperRoute::getDashboardRoute($alias);
		$userAvatar 	= $api->getUserAvatarImage($avatarApp, $leader->user_id, $leader->author_email, $avatarSize, true);
		$author			= $leader->author;
		$activity_value = 0;
		
		switch ($data->state->get('leaderboard.type'))
		{
			case 'steps': $activity_value = CjLibUtils::formatNumber($leader->activity_value); break;
			case 'distance': $activity_value = $unitHelper->getFactoredValue(1, $leader->activity_value) . $unit; break;
			case 'calories': $activity_value = $leader->activity_value; break;
		}
		?>
		<div class="media margin-bottom-20">
			<div class="pull-left leaderboard-rank-num">
				<span class="fa-stack fa-1x small text-small">
					<i class="fa fa-circle-thin fa-stack-2x text-primary"></i>
					<strong class="fa-stack-1x leader-icon-text"><?php echo $i + 1;?></strong>
				</span>
			</div>
			<?php if($avatarApp != 'none'):?>
			<div class="pull-left leaderboard-avatar">
				<a href="<?php echo JRoute::_($profileUrl);?>" title="<?php echo $author?>" class="thumbnail no-margin-bottom" data-toggle="tooltip">
					<img src="<?php echo $userAvatar;?>" alt="<?php echo $author;?>" class="media-object" 
						style="min-width: <?php echo $avatarSize;?>px; max-width: <?php echo $avatarSize;?>px; width: <?php echo $avatarSize;?>px;">
				</a>
			</div>
			<?php endif;?>
			
			<div class="media-body">
				<div class="media-heading no-space-bottom"><?php echo $author;?></div>
				<small><?php echo JText::sprintf($data->activity_label, $activity_value);?></small>
			</div>
		</div>
		<?php 
	}
	?>
</div>