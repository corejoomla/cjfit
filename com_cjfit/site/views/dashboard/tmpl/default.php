<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
$layout 	= $this->params->get('layout', 'default');
$theme 		= $this->params->get('theme', 'default');
?>
<div class="cj-wrapper" id="cj-wrapper">
	<?php
	if(is_object($this->item))
	{
		?>
		<?php echo JLayoutHelper::render($layout.'.dashboard', array('data'=>$this));?>
		<div style="display: none;">
			<span class="cjfit_pageid_dashboard"></span>
		</div>
		<?php 
	}
	else
	{
		$app 		= JFactory::getApplication();
		$userId 	= $app->input->getInt('id');
		$redirect	= base64_encode(JUri::getInstance()->toString());
		?>
		<div class="panel panel-<?php echo $theme;?>">
			<div class="panel-body">
				<p><i class="fa fa-info-circle"></i> <?php echo JText::_('COM_CJFIT_USER_NOT_REGISTERED_WITH_FITBIT_1');?></p>
				
				<?php if(!$userId || $userId == JFactory::getUser()->id):?>
				<p><?php echo JText::_('COM_CJFIT_USER_NOT_REGISTERED_WITH_FITBIT_2')?></p>
				<p>
					<a href="<?php echo JRoute::_(CjFitHelperRoute::getAuthorizationRoute().'&return='.$redirect);?>" class="btn btn-primary">
						<?php echo JText::_('COM_CJFIT_CONNECT_FITBIT');?>
					</a>
				</p>
				<?php endif;?>
			</div>
		</div>
		<?php
		if(!empty($this->params->get('disonnected_profile_text')) && !empty($this->article))
		{
			echo $this->article->event->beforeDisplayContent;
			echo $this->article->text;
			echo $this->article->event->afterDisplayContent;
		}
	}
	?>
</div>