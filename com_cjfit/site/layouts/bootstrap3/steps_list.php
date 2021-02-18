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

if(is_object($item))
{
	?>
	<div class="cjfit-steps-list">
		<table class="table table-striped table-hover table-condensed">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_CJFIT_LABEL_TOTAL');?></th>
					<th><?php echo JText::_('JDATE');?></th>
					<th><?php echo JText::_('COM_CJFIT_LABEL_GOAL');?></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				foreach ($activity->steps as $i => $step)
				{
					$percent = $item->goals['steps'] > 0 ? round($step->activity_value * 100 / $item->goals['steps'], 2) : 0;
					?>
					<tr>
						<td><i class="fa fa-paw"></i> &nbsp;<?php echo $step->activity_value?></td>
						<td><?php echo CjFitHelper::getActivityDate($step->activity_date);?></td>
						<td><?php echo $percent;?>%</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<?php 
	
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
				<div class="media-left margin-right-10 hidden-xs">
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