<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT.'/components/com_cjlib/framework/api.php';
require_once JPATH_ROOT.'/components/com_cjfit/router.php';
require_once JPATH_ROOT.'/components/com_cjfit/helpers/route.php';
require_once JPATH_ROOT . '/components/com_cjfit/helpers/rulesparser.php';

class PlgCjFitActivity extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->loadLanguage('com_cjfit', JPATH_ROOT);
	}
	
	public function onReachingDailyStepsGoal($context, $notification, $goal, $value)
	{
		if ($context != 'com_cjfit.notification')
		{
			return true;
		}
		
		$myparams = $this->params;
		if($myparams->get('activity_goal_steps', true))
		{
			$this->streamActivity($notification, array($goal, $value), 1);
		}
		
		if($myparams->get('points_goal_steps', true))
		{
			$this->awardPoints($notification, array($goal, $value), 1);
		}
		
		if($myparams->get('email_goal_steps', true))
		{
			$this->sendEmail($notification, array($goal, $value), 1);
		}
		
		return true;
	}
	
	public function onReachingDailyDistanceGoal($context, $notification, $goal, $value)
	{
		if ($context != 'com_cjfit.notification')
		{
			return true;
		}
		
		$myparams = $this->params;
		if($myparams->get('activity_goal_distance', true))
		{
			$this->streamActivity($notification, array($goal, $value), 2);
		}
		
		if($myparams->get('points_goal_distance', true))
		{
			$this->awardPoints($notification, array($goal, $value), 2);
		}
		
		if($myparams->get('email_goal_distance', true))
		{
			$this->sendEmail($notification, array($goal, $value), 2);
		}
		
		return true;
	}
	
	public function onReachingDailyCaloriesGoal($context, $notification, $goal, $value)
	{
		if ($context != 'com_cjfit.notification')
		{
			return true;
		}
		
		$myparams = $this->params;
		if($myparams->get('activity_goal_calories', true))
		{
			$this->streamActivity($notification, array($goal, $value), 3);
		}
		
		if($myparams->get('points_goal_calories', true))
		{
			$this->awardPoints($notification, array($goal, $value), 3);
		}
		
		if($myparams->get('email_goal_calories', true))
		{
			$this->sendEmail($notification, array($goal, $value), 3);
		}
		
		return true;
	}
	
	public function onReachingDailyActiveMinutesGoal($context, $notification, $goal, $value)
	{
		if ($context != 'com_cjfit.notification')
		{
			return true;
		}
		
		$myparams = $this->params;
		if($myparams->get('activity_goal_active_minutes', true))
		{
			$this->streamActivity($notification, array($goal, $value), 4);
		}
		
		if($myparams->get('points_goal_active_minutes', true))
		{
			$this->awardPoints($notification, array($goal, $value), 4);
		}
		
		if($myparams->get('email_goal_active_minutes', true))
		{
			$this->sendEmail($notification, array($goal, $value), 4);
		}
		
		return true;
	}
	
	public function onSuccessfulChallenge($context, $notification, $challenge)
	{
		if($challenge->params->get('stream_activity'))
		{
			$this->streamActivity($notification, $challenge, 5);
		}
		
		if($challenge->params->get('award_points'))
		{
			$this->awardPoints($notification, $challenge, 5);
		}
		
		if($challenge->params->get('send_notification'))
		{
			$this->sendEmail($notification, $challenge, 5);
		}
	}
	
	private function streamActivity($notification, $options, $type)
	{
		$params 		= JComponentHelper::getParams('com_cjfit');
		$streamApp 		= $params->get('stream_component', 'cjforum');
		$displayName	= $params->get('display_name', 'name');
		
		// Activity stream
		if(empty($streamApp) || $streamApp == 'none')
		{
			return true;
		}
		
		$api 			= new CjLibApi();
		$language 		= JFactory::getLanguage();
		$language->load('com_cjfit');
		
		$profileApp		= $params->get('profile_component', 'cjforum');
		$userName 		= $api->getUserProfileUrl($profileApp, $notification->user_id, false, $notification->$displayName);
		$dashboardUrl 	= JRoute::_(CjFitHelperRoute::getDashboardRoute($notification->user_id . ':' . $notification->handle));
		$title 			= '';
		$parentId		= str_replace('-', '', $notification->update_date);
		
		switch ($type)
		{
			case 1: // steps goal
				$title 			= JText::sprintf('COM_CJFIT_ACTIVITY_STEPS_GOAL_REACHED_TITLE', $userName);
				$description 	= JText::sprintf('COM_CJFIT_ACTIVITY_STEPS_GOAL_REACHED_DESC', $options[1], $options[0]);
				$function 		= 'com_cjfit.steps_goal';
				break;
		
			case 2: // distance goal
				$title 			= JText::sprintf('COM_CJFIT_ACTIVITY_DISTANCE_GOAL_REACHED_TITLE', $userName);
				$description 	= JText::sprintf('COM_CJFIT_ACTIVITY_DISTANCE_GOAL_REACHED_DESC', $options[1], $options[0]);
				$function 		= 'com_cjfit.distance_goal';
				break;
		
			case 3: // calories goal
				$title 			= JText::sprintf('COM_CJFIT_ACTIVITY_CALORIES_GOAL_REACHED_TITLE', $userName);
				$description 	= JText::sprintf('COM_CJFIT_ACTIVITY_CALORIES_GOAL_REACHED_DESC', $options[1], $options[0]);
				$function 		= 'com_cjfit.calories_goal';
				break;
		
			case 4: // active minutes goal
				$title 			= JText::sprintf('COM_CJFIT_ACTIVITY_ACTIVE_MINUTES_GOAL_REACHED_TITLE', $userName);
				$description 	= JText::sprintf('COM_CJFIT_ACTIVITY_ACTIVE_MINUTES_GOAL_REACHED_DESC', $options[1], $options[0]);
				$function 		= 'com_cjfit.active_minutes_goal';
				break;
				
			case 5: // win challenge
				$title			= JText::sprintf('COM_CJFIT_ACTIVITY_WON_THE_CHALLENGE_TITLE', $userName, $options->title);
				$description 	= $options->description;
				$function 		= 'com_cjfit.challenge';
				break;
		}
			
		$activity 				= new stdClass();
		$activity->type 		= $function;
		$activity->href 		= $dashboardUrl;
		$activity->title 		= $title;
		$activity->description 	= $description;
		$activity->userId 		= $notification->user_id;
		$activity->featured 	= 0;
		$activity->language 	= $language->getTag();
		$activity->itemId 		= $notification->user_id;
		$activity->parentId 	= $parentId;
		$activity->length 		= $params->get('max_description_length', 250);
		
		$api->pushActivity($streamApp, $activity);
		
		return true;
	}
	
	private function awardPoints($notification, $options, $type)
	{
		$user 				= JFactory::getUser();
		$api 				= new CjLibApi();
		$params 			= JComponentHelper::getParams('com_cjfit');
		$displayName		= $params->get('display_name', 'name');
		
		$pointsComponent 	= $params->get('points_component', 'cjforum');
		$profileComponent 	= $params->get('profile_component', 'cjforum');
		
		$info 				= '';
		$reference 			= $notification->update_date;
		$awardedTo 			= $notification->user_id;
		$dashboardUrl		= JRoute::_(CjFitHelperRoute::getDashboardRoute($notification->user_id . ':' . $notification->handle));
		$userName 			= JHtml::link($dashboardUrl, $notification->$displayName);
		$points				= 0;
		
		switch ($type)
		{
			case 1: // steps goal
				$title 		= JText::sprintf('COM_CJFIT_POINTS_STEPS_GOAL_REACHED_TITLE', $options[0]);
				$info 		= JText::sprintf('COM_CJFIT_ACTIVITY_STEPS_GOAL_REACHED_DESC', $options[0], $options[1]);
				$function 	= 'com_cjfit.steps_goal';
				break;
		
			case 2: // distance goal
				$title 		= JText::sprintf('COM_CJFIT_POINTS_DISTANCE_GOAL_REACHED_TITLE', $options[0]);
				$info 		= JText::sprintf('COM_CJFIT_ACTIVITY_DISTANCE_GOAL_REACHED_DESC', $options[0], $options[1]);
				$function 	= 'com_cjfit.distance_goal';
				break;
		
			case 3: // calories goal
				$title 		= JText::sprintf('COM_CJFIT_POINTS_CALORIES_GOAL_REACHED_TITLE', $options[0]);
				$info 		= JText::sprintf('COM_CJFIT_ACTIVITY_CALORIES_GOAL_REACHED_DESC', $options[0], $options[1]);
				$function 	= 'com_cjfit.calories_goal';
				break;
		
			case 4: // active minutes goal
				$title 		= JText::sprintf('COM_CJFIT_POINTS_ACTIVE_MINUTES_GOAL_REACHED_TITLE', $options[0]);
				$info 		= JText::sprintf('COM_CJFIT_ACTIVITY_ACTIVE_MINUTES_GOAL_REACHED_DESC', $options[0], $options[1]);
				$function 	= 'com_cjfit.active_minutes_goal';
				break;
				
			case 5:
				$title 		= JText::sprintf('COM_CJFIT_POINTS_WON_THE_CHALLENGE_TITLE', $options->title);
				$info 		= $options->description;
				$function 	= 'com_cjfit.challenge';
				$points		= $options->points;
				break;
		}
		
		$fields = array('function'=>$function, 'reference'=>$reference, 'info'=>$info, 'component'=>'com_cjfit', 'title'=>$title);
		if($points)
		{
			$fields['points'] = $points;
		}
		
		$api->awardPoints($pointsComponent, $awardedTo, $fields);
		
		return true;
	}
	
	private function sendEmail($notification, $options, $type)
	{
		$db 			= JFactory::getDbo();
		$params 		= JComponentHelper::getParams('com_cjfit');
		$displayName	= $params->get('display_name', 'name');
		$emailType 		= null;
	
		switch ($type)
		{
			case 1: // steps goal
				$emailType = 'com_cjfit.steps_goal';
				break;

			case 2: // distance goal
				$emailType = 'com_cjfit.distance_goal';
				break;

			case 3: // calories goal
				$emailType = 'com_cjfit.calories_goal';
				break;
					
			case 4: // active minutes goal
				$emailType = 'com_cjfit.active_minutes_goal';
				break;
				
			case 5: // challenge
				$emailType = 'com_cjfit.challenge';
				break;
		}

		$template = null;
		$tag = JFactory::getLanguage()->getTag();
			
		$query = $db->getQuery(true)
			->select('title, description, language')
			->from('#__cjfit_email_templates')
			->where('email_type = '.$db->q($emailType))
			->where('language in ('.$db->q($tag).','.$db->q('*').')')
			->where('published = 1');
			
		$db->setQuery($query);
		$templates = $db->loadObjectList('language');

		if(isset($templates[$tag]))
		{
			$template = $templates[$tag];
		}
		else if(isset($templates['*']))
		{
			$template = $templates['*'];
		}
		
		if(!empty($template))
		{
			JLoader::import('mail', JPATH_ROOT.'/components/com_cjfit/models');

			$config 			= JFactory::getConfig();
			$sitename 			= $config->get('sitename');
			$forceSSL			= $config->get('force_ssl') == 2 ? 1 : 2;
			$message 			= new stdClass();
			$mailModel			= JModelLegacy::getInstance( 'mail', 'CjFitModel' );
			$dashboardUrl		= JRoute::_(CjFitHelperRoute::getDashboardRoute($notification->user_id . ':' . $notification->handle), false, $forceSSL);
			$userName 			= JHtml::link($dashboardUrl, $notification->$displayName);
			$activityDate		= CjLibDateUtils::getLocalizedDate($notification->update_date, $params->get('date_format', 'Y-m-d'));

			$recipients			= array();
			$subject			= str_ireplace('{ACTIVITY_DATE}', $activityDate, $template->title);
			$description 		= str_ireplace('{ACTIVITY_DATE}', $activityDate, $template->description);
			$description 		= str_ireplace('{DASHBOARD_URL}', $dashboardUrl, $description);
			$description		= str_ireplace('{AUTHOR_NAME}', $notification->$displayName, $description);
			$description		= str_ireplace('{SITENAME}', $sitename, $description);
			
			if($type == 5)
			{
				$subject		= str_ireplace('{CHALLENGE_TITLE}', $options->title, $subject);
				$description 	= str_ireplace('{CHALLENGE_TITLE}', $options->title, $description);
				$description 	= str_ireplace('{CHALLENGE_DESCRIPTION}', $options->description, $description);
			}
			else
			{
				$description 	= str_ireplace('{ACTIVITY_GOAL}', $options[0], $description);
				$description 	= str_ireplace('{ACTIVITY_VALUE}', $options[1], $description);
			}

			$recipient			= new stdClass();
			$recipient->id 		= $notification->user_id;
			$recipient->name 	= $notification->$displayName;
			$recipient->email 	= $notification->email;
			$recipients[] 		= $recipient;
			
			$message->asset_id 	= $notification->user_id;
			
			if(!empty($recipients) && !empty($message))
			{
				$message->asset_name	= $emailType;
				$message->subject		= $subject;
				$message->description	= $description;
				
				$mailModel->enqueueMail($message, $recipients, 'none');
			}
		}
	
		return true;
	}
}