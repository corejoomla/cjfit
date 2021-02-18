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
use League\OAuth2\Client\Token\AccessToken;

class CjFitControllerUser extends JControllerForm
{
	public function authorize()
	{
		$app 			= JFactory::getApplication();
		$user			= JFactory::getUser();
		$session 		= JFactory::getSession();
		$params 		= JComponentHelper::getParams('com_cjfit');
		
		if($user->guest)
		{
			$return = base64_encode(CjFitHelperRoute::getAuthorizationRoute());
			$loginUri = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId.'&return='.$return);
			$this->setRedirect($loginUri);
			return;
		}
		
		$accessToken = $this->getToken();
		if(empty($accessToken))
		{
			$provider = new Fitbit([
					'clientId'          => $params->get('client_id'),
					'clientSecret'      => $params->get('client_secret'),
					'redirectUri'       => $this->getRedirectUri()
			]);
			
			$authorizationUrl = $provider->getAuthorizationUrl();

			session_start();
			$_SESSION['oauth2state'] = $provider->getState();
			
			$return = $this->input->get('return', null, 'base64');
			$_SESSION['return'] = $return;
			
			$app->redirect($authorizationUrl);
		}
		else
		{
			echo JText::_('COM_CJFIT_USER_REGISTRATION_SUCCESSFULLY_COMPLETED');
		}
	}
	
	public function validate()
	{
		$app 		= JFactory::getApplication();
		$session 	= JFactory::getSession();
		$user		= JFactory::getUser();
		$params 	= JComponentHelper::getParams('com_cjfit');
		$state 		= $app->input->getString('state');
		$code 		= $app->input->getString('code');
		$model 		= $this->getModel('user', 'CjFitModel');
		
		if(empty($state) || $state != $_SESSION['oauth2state'])
		{
			unset($_SESSION['oauth2state']);
			throw new Exception(JText::_('COM_CJFIT_ERROR_INVALID_STATE'));
		}
		
		$provider = new Fitbit([
				'clientId'          => $params->get('client_id'),
				'clientSecret'      => $params->get('client_secret'),
				'redirectUri'       => $this->getRedirectUri()
		]);
		
		$accessToken 				= $provider->getAccessToken('authorization_code', ['code' => $code]);
		if(empty($accessToken))
		{
			throw new Exception(JText::_('JALERT_ERRORNOAUTHOR'), 500);
		}
		
		$resourceOwner 				= $provider->getResourceOwner($accessToken);
		$userDetails 				= $resourceOwner->toArray();
		
		// add user to the subscription
		$request = $provider->getAuthenticatedRequest(
				Fitbit::METHOD_POST,
				Fitbit::BASE_FITBIT_API_URL . '/1/user/-/apiSubscriptions/'.$user->id.'.json',
				$accessToken,
				['headers' => [Fitbit::HEADER_ACCEPT_LANG => ''], [Fitbit::HEADER_ACCEPT_LOCALE => 'en_US']]
				);
		$response = $provider->getParsedResponse($request);
		
		if(empty($response))
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		}
		else
		{
			$userObj 						= new stdClass();
			$userObj->id 					= $user->id;
			$userObj->handle				= $user->username;
			$userObj->token 				= CjFitHelper::getEncryptedToken($accessToken, $params->get('client_secret'));
			$userObj->fitbit_owner_id 		= $userDetails['encodedId'];
			$userObj->height 				= $userDetails['height'];
			$userObj->weight 				= $userDetails['weight'];
			$userObj->average_daily_steps 	= $userDetails['averageDailySteps'];
			$userObj->stride_length_running = $userDetails['strideLengthRunning'];
			$userObj->stride_length_walking = $userDetails['strideLengthWalking'];
			$userObj->attribs				= '{}';
			
			$model->save($userObj);
			
			$app->enqueueMessage(JText::_('COM_CJFIT_USER_REGISTRATION_SUCCESSFULLY_COMPLETED'));
		}
		
		$return = $_SESSION['return'];
		if(empty($return))
		{
			$return = JRoute::_(CjFitHelperRoute::getDashboardRoute(), false);
		}
		else 
		{
			$return = base64_decode($return);
		}
		
		$this->setRedirect($return);
	}
	
	private function getToken()
	{
		$user			= JFactory::getUser();
		$model 			= $this->getModel('user', 'CjFitModel');
		$accessToken 	= $model->getToken($user->id);
		
		return $accessToken;
	}
	
	private function getRedirectUri()
	{
// 		return JRoute::_(CjFitHelperRoute::getCallbackRoute(), false, -1);
		return CjFitHelperRoute::getCallbackRoute();
	}
}