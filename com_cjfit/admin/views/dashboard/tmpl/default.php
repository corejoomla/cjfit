<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->id;
$span		= !empty( $this->sidebar) ? 'span10' : '';
$params		= $this->params;
?>
<div id="cj-wrapper">
	<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php endif;?>
	<div id="j-main-container" class="<?php echo $span;?>">
		<div class="span8">
			<?php if($user->authorise('core.admin')):?>
			<table class="table table-hover">
				<tr>
					<th>Hourly Cron URL</th>
					<td><?php echo JUri::root(false) .'index.php?option=com_cjfit&task=cron.execute&secret='.$params->get('cron_secret')?></td>
				</tr>
				<tr>
					<th>Daily Cron URL</th>
					<td><?php echo JUri::root(false) .'index.php?option=com_cjfit&task=cron.daily&secret='.$params->get('cron_secret')?></td>
				</tr>
				<tr>
					<th>Callback URL:</th>
					<td><?php echo JUri::root(false) . 'index.php?option=com_cjfit&task=user.validate';?></td>
				</tr>
				<tr>
					<th>Subscriber Endpoint URL</th>
					<td><?php echo JUri::root(false) . 'index.php?option=com_cjfit&task=subscriber.execute';?></td>
				</tr>
				<tr>
					<th>Subscriber Type</th>
					<td>JSON Body</td>
				</tr>
				<tr>
					<th>OAuth 2.0 Application Type</th>
					<td>Server</td>
				</tr>
			</table>
			<?php else:?>
				<?php echo JText::_('COM_CJFIT_DASHBOARD_CONFIG_VALUES_HIDDEN');?>
			<?php endif;?>
		</div>
		<div class="span4">
			<div class="panel panel-default">
				<div class="panel-heading">
					<strong><i class="fa fa-bullhorn"></i> <?php echo JText::_('COM_CJFIT_TITLE_VERSION');?></strong>
				</div>
				<table class="table table-striped">
					<tbody>
						<tr>
							<th><?php echo JText::_('COM_CJFIT_INSTALLED_VERSION');?>:</th>
							<td><?php echo CJFIT_CURR_VERSION;?></td>
						<tr>
						<?php if(!empty($this->version)):?>
						<tr>
							<th>Latest Version:</th>
							<td><?php echo $this->version['version'];?></td>
						</tr>
						<tr>
							<th>Latest Version Released On:</th>
							<td><?php echo $this->version['released'];?></td>
						</tr>
						<tr>
							<th>CjLib Version</th>
							<td><?php echo CJLIB_VER;?></td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center;">
								<?php if($this->version['status'] == 1):?>
								<a href="http://www.corejoomla.com/downloads.html" target="_blank" class="btn btn-danger">
									<i class="icon-download icon-white"></i> <span style="color: white">Please Update</span>
								</a>
								<?php else:?>
								<a href="#" class="btn btn-success"><i class="icon-ok icon-white"></i> <span style="color: white">Up-to date</span></a>
								<?php endif;?>
							</td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading"><strong>Credits: </strong></div>
				<div class="panel-body">
					<div>CjFit is a free software released under Gnu/GPL license. Copyright &copy; 2009-17 corejoomla.com</div>
					<div>Core Components: Bootstrap, jQuery, FontAwesome and ofcourse Joomla<sup>&reg;</sup>.</div>
				</div>
			</div>
		</div>
	</div>
</div>