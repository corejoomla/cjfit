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
$activity	= $data->activity;
$params		= $data->params;

$layout		= $params->get('ui_layout', 'default');
$theme		= $params->get('theme', 'default');
?>
<h3 class="page-header no-space-top"><?php echo $data->item->author;?></h3>
<div class="row equal">
	<div class="col-md-9">
		<div class="row">
			<div  class="col-md-8">
				<div class="panel panel-default">
					<div class="panel-body">
						<?php echo $this->sublayout('activity', array('data'=>$data));?>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<?php echo $this->sublayout('minutes', array('data'=>$data));?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<?php echo $this->sublayout('steps', array('data'=>$data));?>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<?php echo $this->sublayout('distance', array('data'=>$data));?>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-body">
						<?php echo $this->sublayout('calories', array('data'=>$data));?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-body">
				<?php echo $this->sublayout('leaderboard', array('data'=>$data));?>
			</div>
		</div>
	</div>
</div>