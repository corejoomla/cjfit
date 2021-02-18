<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitControllerCron extends JControllerForm
{
	public function execute($task)
	{
		$params = JComponentHelper::getParams('com_cjfit');
		$cronSecret = $params->get('cron_secret');
		
		if(empty($cronSecret))
		{
			jexit(JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		$app = JFactory::getApplication();
		$secret = $app->input->getCmd('secret');
		
		if(strcmp($cronSecret, $secret) !== 0)
		{
			jexit(JText::_('JERROR_ALERTNOAUTHOR'));
		}
		
		ini_set('max_execution_time', 120);
		
		$notificationModel = $this->getModel('notification');
		$notificationModel->processNotifications();
		
		$userModel = $this->getModel('user');
		$userModel->updateDailyLeaderboard();
		
		jexit('Success');
	}
	
	public function daily()
	{
		
	}
}