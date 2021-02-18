<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use djchen\OAuth2\Client\Provider\Fitbit;

class CjFitControllerSubscriber extends JControllerForm
{
	public function execute($task)
	{
		$app = JFactory::getApplication();
		$verify = $app->input->getCmd('verify');
		$params = JComponentHelper::getParams('com_cjfit');
		
		if(!empty($verify))
		{
			$code = $params->get('subscriber_verification_code');
			
			if($verify == $code)
			{
				http_response_code(204);
			}
			else
			{
				http_response_code(404);
			}
			
			jexit();
		}
		
		// new update from api
		$entityBody = file_get_contents('php://input');
		$expectedSignature = base64_encode(hash_hmac("sha1", $entityBody, $params->get('client_secret') . "&", true));
		$signature = $_SERVER['HTTP_X_FITBIT_SIGNATURE'];
		
		if ($signature != $expectedSignature) 
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}
		
		if(!empty($entityBody))
		{
			$json = json_decode($entityBody);
		
			if(!empty($json) && is_array($json))
			{
				$model = $this->getModel('notification', 'CjFitModel');
				$model->addNotifications($json);
			}
		}
		
		http_response_code(204);
		jexit();
	}
}