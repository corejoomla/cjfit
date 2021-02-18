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
$activity	= $data->activity;
$user		= $data->user;
$params		= $data->params;
$redirect	= base64_encode(JUri::getInstance()->toString());
$unitHelper	= new CjFitUnitsHelper($params->get('distance_unit'));

if(is_object($item))
{
	?>
	<div class="cjfit-summary">
		<?php 
		foreach ($activity->steps as $i => $step)
		{
			$percent 	= $item->goals['steps'] > 0 ? round($step->activity_value * 100 / $item->goals['steps']) : 0;
			$distance 	= $unitHelper->getFactoredValue(1, $activity->distance[$i]->activity_value);
			$unit 		= $unitHelper->getDistanceMeasure();
			?>
			<div class="steps-page steps-page-<?php echo $i;?> new"<?php echo $i > 0 ? ' style="display: none;"' : '';?>>
				<div class="muted text-muted activity-date"><?php echo CjFitHelper::getActivityDate($step->activity_date);?></div>
				<div class="steps-guage" id="steps-guage-<?php echo $i;?>" data-value="<?php echo $step->activity_value?>" data-goal="<?php echo $item->goals['steps'];?>"></div>
				<div class="text-center steps-guage-footer margin-bottom-20">
					<div class="footer-heading muted text-muted"><?php echo JText::sprintf('COM_CJFIT_GOAL_PERCENTAGE', $percent, $item->goals['steps'])?></div>
					<div class="muted text-muted"><?php echo JText::_('COM_CJFIT_LABEL_YOUR_DAILY_GOAL');?></div>
				</div>
			
				<div class="row-fluid summary-stats">
					<div class="span4">
						<div class="text-center muted text-muted stats-num"><?php echo isset($activity->calories[$i]) ? $activity->calories[$i]->activity_value: 0;?></div>
						<div class="text-center muted text-muted stat-label"><?php echo JText::_('COM_CJFIT_LABEL_CALORIES');?></div>
					</div>
					<div class="span4">
						<div class="text-center muted text-muted stats-num"><?php echo $distance . ' ' . $unit;?></div>
						<div class="text-center muted text-muted stat-label"><?php echo JText::_('COM_CJFIT_LABEL_DISTANCE');?></div>
					</div>
					<div class="span4">
					<div class="text-center muted text-muted stats-num"><?php echo $item->average_daily_steps;?></div>
						<div class="text-center muted text-muted stat-label"><?php echo JText::_('COM_CJFIT_LABEL_DAILY_AVERAGE');?></div>
					</div>
				</div>
				
				<div class="summary-page-navigation">
					<a class="cjfit-summary-prev" href="#" onclick="return false"><i class="fa fa-chevron-left"></i></a>
					<a class="cjfit-summary-next" href="#" onclick="return false"><i class="fa fa-chevron-right"></i></a>
					<?php echo JText::sprintf('COM_CJFIT_PAGE_N_OF_TOTAL_PAGES', $i + 1, count($activity->steps));?>
				</div>
			</div>
			<?php
		}
		
		if($params->get('show_user_info'))
		{
			$api		= new CjLibApi();
			$avatarApp	= $params->get('avatar_component', 'none');
			$userName	= $params->get('user_display_name', 'name');
			$avatarSize	= $params->get('avatar_size', 36);
			$author		= $user->$userName;
			$alias		= isset($user->handle) ? ($user->id . ':' . $user->handle) : $user->id;
			$profileUrl = CjFitHelperRoute::getDashboardRoute($alias);
			$userAvatar = $api->getUserAvatarImage($avatarApp, $user->id, $item->author_email, $avatarSize, true);
			?>
			<hr/>
			<div class="user-info">
				<div class="media">
					<?php if($avatarApp != 'none'):?>
					<div class="pull-left margin-right-10 hidden-phone">
						<a href="<?php echo JRoute::_($profileUrl);?>" title="<?php echo $author?>" class="thumbnail no-margin-bottom" data-toggle="tooltip">
							<img src="<?php echo $userAvatar;?>" alt="<?php echo $author;?>" class="media-object" 
								style="min-width: <?php echo $avatarSize;?>px; max-width: <?php echo $avatarSize;?>px;">
						</a>
					</div>
					<?php endif;?>
					
					<div class="media-body">
						<h4 class="media-heading no-space-top no-pad-bottom margin-bottom-5"><?php echo $author;?></h4>
						<small><?php echo JText::sprintf('COM_CJFIT_TOTAL_STEPS', CjLibUtils::formatNumber($item->lifetime['steps']));?></small>
					</div>
				</div>
			</div>
			<?php 
		}
		?>
	</div>
	<?php 
}
else
{
	?>
	<p><i class="fa fa-info-circle"></i> <?php echo JText::_('COM_CJFIT_USER_NOT_REGISTERED_WITH_FITBIT_1');?></p>
	
	<?php if($user->id == JFactory::getUser()->id):?>
	<p><?php echo JText::_('COM_CJFIT_USER_NOT_REGISTERED_WITH_FITBIT_2')?></p>
	<p>
		<a href="<?php echo JRoute::_(CjFitHelperRoute::getAuthorizationRoute().'&return='.$redirect);?>" class="btn btn-primary">
			<?php echo JText::_('COM_CJFIT_CONNECT_FITBIT');?>
		</a>
	</p>
	<?php endif;?>
	
	<?php 
}