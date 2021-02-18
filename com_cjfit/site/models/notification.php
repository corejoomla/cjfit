<?php
use djchen\OAuth2\Client\Provider\Fitbit;

/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjFitModelNotification extends JModelItem
{
	private $_provider = null;
	
	public function addNotifications($notifications)
	{
		$db = $this->getDbo();
		foreach ($notifications as $notification)
		{
			$record 					= new stdClass();
			$record->owner_id			= $notification->ownerId;
			$record->owner_type			= $notification->ownerType;
			$record->subscription_id 	= $notification->subscriptionId;
			$record->update_date 		= $notification->date;
			$record->collection_type 	= $notification->collectionType;
			$record->state 				= 0;
			
			try 
			{
				$db->insertObject('#__cjfit_notifications', $record);
			}
			catch (Exception $e)
			{
				$query = $db->getQuery(true)
					->update('#__cjfit_notifications')
					->set('state = 0')
					->where('owner_id = ' . $db->q($notification->ownerId))
					->where('update_date = ' . $db->q($notification->date))
					->where('collection_type = ' . $db->q($notification->collectionType));
				$db->setQuery($query);
				try{$db->execute();}catch(Exception $e){}
			}
		}
	}
	
	public function processNotifications()
	{
		$db 			= $this->getDbo();
		$params 		= JComponentHelper::getParams('com_cjfit');
		$rulesParser	= new CjFitRulesParser();
		$nullDate 		= $db->quote($db->getNullDate());
		$nowDate 		= $db->quote(JFactory::getDate()->toSql());
		$today			= JFactory::getDate()->format('Y-m-d');
		$provider 		= $this->getProvider();
		$processed 		= 0;
		
		$query = $db->getQuery(true)
			->select('a.owner_id, a.subscription_id, a.update_date, a.collection_type, curdate() as goal_date')
			->select('u.id as user_id, u.handle, u.daily_goals, ju.name, ju.username, ju.email')
			->from('#__cjfit_notifications AS a')
			->join('inner', '#__cjfit_users AS u ON u.fitbit_owner_id = a.owner_id')
			->join('inner', '#__users AS ju ON ju.id = u.id')
			->where('state = 0')
			->where('collection_type = ' . $db->q('activities'))
			->where('owner_type = ' . $db->q('user'))
			->order('a.update_date ASC');
		$db->setQuery($query, 0, 100);
		$notifications = $db->loadObjectList();
		
		if(empty($notifications))
		{
			echo 'No records to process.';
			return true;
		}
		
		// Prefetch challenges
		$query = $db->getQuery(true)
			->select('a.id, a.title, a.description, a.points, a.rules, a.attribs')
			->from('#__cjfit_challenges AS a')
			->where('published = 1')
			->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
			->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		
		$db->setQuery($query);
		$challenges = $db->loadObjectList();
		
		if(!empty($challenges))
		{
			foreach ($challenges as &$challenge)
			{
				$challenge->rules = json_decode($challenge->rules, true);
				
				$challengeParams = new JRegistry();
				$challengeParams->loadString($challenge->attribs);
				
				$challenge->params = clone $params;
				$challenge->params->merge($challengeParams);
			}
		}
		
		// Prefetch user goals for today
		$userIds = array();
		foreach ($notifications as &$notification)
		{
			$notification->stepsGoalReached = false;
			$notification->distanceGoalReached = false;
			$notification->caloriesGoalReached = false;
			$notification->minutesGoalReached = false;
			$notification->chalengesWon = array();
			
			$userIds[] = (int) $notification->user_id;
		}
		
		$query = $db->getQuery(true)
			->select('user_id, goal_type, goal_value')
			->from('#__cjfit_goals_achieved')
			->where('goal_date = CURDATE()')
			->where('user_id IN (' . implode(',', $userIds).')');
		$db->setQuery($query);
		$userGoals = $db->loadObjectList();
		
		foreach ($userGoals as $goal)
		{
			foreach ($notifications as &$notification)
			{
				if($notification->user_id == $goal->user_id)
				{
					switch ($goal->goal_type)
					{
						case 1: // steps
							$notification->stepsGoalReached = true;
							break;
							
						case 2:
							$notification->distanceGoalReached = true;
							break;
							
						case 3:
							$notification->caloriesGoalReached = true;
							break;
							
						case 4:
							$notification->minutesGoalReached = true;
							break;
							
						case 5:
							$notification->challengesWon[] = $goal->goal_value;
							break;
					}
				}
			}
		}
		
		// Get user model
		JLoader::import('user', JPATH_ROOT.'/components/com_cjfit/models');
		$userModel = JModelItem::getInstance( 'user', 'CjFitModel' );
		$userModel->getState();
		
		JPluginHelper::importPlugin('cjfit');
		$dispatcher = JEventDispatcher::getInstance();
		
		foreach ($notifications as $notification)
		{
			$accessToken = $userModel->getToken($notification->user_id);
			if(empty($accessToken))
			{
				continue;
			}
			
			try 
			{
				$request = $provider->getAuthenticatedRequest(
					Fitbit::METHOD_GET,
					Fitbit::BASE_FITBIT_API_URL . '/1/user/'.$notification->owner_id.'/activities/date/'.$notification->update_date.'.json',
					$accessToken,
					['headers' => [Fitbit::HEADER_ACCEPT_LANG => ''], [Fitbit::HEADER_ACCEPT_LOCALE => 'en_US']]
					);
				
				$response = $provider->getParsedResponse($request);
				if(!empty($response['summary']))
				{
					$summary = $response['summary'];
					
					$query = $db->getQuery(true)
						->insert('#__cjfit_daily_activity')
						->columns('user_id, activity_date, activity_type, activity_value');
					
					$summary['distance'] = 0;
					foreach ($summary['distances'] as $t)
					{
						if($t['activity'] == 'total')
						{
							$summary['distance'] = $t['distance'];
							break;
						}
					}
					
					$value = $notification->user_id . ',' . $db->q($notification->update_date) . ',';
					$query->values($value . ACTIVITY_TYPE_STEPS . ',' . (int) $summary['steps']);
					$query->values($value . ACTIVITY_TYPE_DISTANCE . ',' . (float) $summary['distance']);
					$query->values($value . ACTIVITY_TYPE_CALORIES . ',' . (int) $summary['caloriesOut']);
					$query->values($value . ACTIVITY_TYPE_SEDENTARY . ',' . (int) $summary['sedentaryMinutes']);
					$query->values($value . ACTIVITY_TYPE_LACTIVE . ',' . (int) $summary['lightlyActiveMinutes']);
					$query->values($value . ACTIVITY_TYPE_FACTIVE . ',' . (int) $summary['fairlyActiveMinutes']);
					$query->values($value . ACTIVITY_TYPE_VACTIVE . ',' . (int) $summary['veryActiveMinutes']);
					
					$query = $query->__toString() . ' ON DUPLICATE KEY UPDATE activity_value = VALUES(activity_value);';
					
					$db->setQuery($query);
					$db->execute();
					
					$created = JFactory::getDate()->toSql();
					$query = $db->getQuery(true)
						->update('#__cjfit_users')
						->set('last_fetched = ' . $db->q($created))
						->where('id = ' . $notification->user_id);
					$db->setQuery($query);
					$db->execute();
					
					$query = $db->getQuery(true)
						->update('#__cjfit_notifications')
						->set('state = 1')
						->where('owner_id = ' . $db->q($notification->owner_id))
						->where('update_date = ' . $db->q($notification->update_date))
						->where('collection_type = ' . $db->q('activities'))
						->where('owner_type = ' . $db->q('user'));
					$db->setQuery($query);
					$db->execute();
					
					// check if daily goals reached and trigger events
					$dailyGoals = json_decode($notification->daily_goals, true);
					$summary['minutes'] = $summary['lightlyActiveMinutes'] + $summary['fairlyActiveMinutes'] + $summary['veryActiveMinutes'];
					
					if($notification->update_date == $today)
					{
						$this->triggerRules($db, $dispatcher, $rulesParser, $notification, $challenges, $dailyGoals, $summary);
					}
					
					$processed++;
				}
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
				// continue with next
			}
		}
		
		echo $processed . ' notifications processed.';
	}
	
	private function triggerRules($db, $dispatcher, $rulesParser, $notification, $challenges, $dailyGoals, $summary)
	{
		$values = array();
		
		if(!$notification->stepsGoalReached && $dailyGoals['steps'] > 0 && $summary['steps'] >= $dailyGoals['steps'])
		{
			$dispatcher->trigger('onReachingDailyStepsGoal',
					array('com_cjfit.notification', $notification, $dailyGoals['steps'], $summary['steps']));
			
			$values[] = $notification->user_id . ',' . $db->q($notification->goal_date) . ', 1, ' . $summary['steps'];
		}
		
		if(!$notification->distanceGoalReached && $dailyGoals['distance']> 0 && $summary['distance'] >= $dailyGoals['distance'])
		{
			$dispatcher->trigger('onReachingDailyDistanceGoal',
					array('com_cjfit.notification', $notification, $dailyGoals['steps'], $summary['distance']));
			
			$values[] = $notification->user_id . ',' . $db->q($notification->goal_date) . ', 2, ' . $summary['distance'];
		}
		
		if(!$notification->caloriesGoalReached && $dailyGoals['caloriesOut']> 0 && $summary['caloriesOut'] >= $dailyGoals['caloriesOut'])
		{
			$dispatcher->trigger('onReachingDailyCaloriesGoal',
					array('com_cjfit.notification', $notification, $dailyGoals['caloriesOut'], $summary['caloriesOut']));
			
			$values[] = $notification->user_id . ',' . $db->q($notification->goal_date) . ', 3, ' . $summary['caloriesOut'];
		}
		
		if(!$notification->minutesGoalReached && $dailyGoals['activeMinutes'] > 0 && $summary['minutes'] >= $dailyGoals['activeMinutes'])
		{
			$dispatcher->trigger('onReachingDailyActiveMinutesGoal',
					array('com_cjfit.notification', $notification, $dailyGoals['activeMinutes'], $summary['minutes']));
			
			$values[] = $notification->user_id . ',' . $db->q($notification->goal_date) . ', 4, ' . $summary['minutes'];
		}
		
		// challenges
		if(!empty($challenges))
		{
			$rulesParser->set($summary['steps'], $summary['distance'], $summary['caloriesOut'], $summary['minutes']);
			foreach ($challenges as $challenge)
			{
				if( (!$notification->challengesWon || !in_array($challenge->id, $notification->challengesWon)) && $rulesParser->getResult($challenge->rules))
				{
					$dispatcher->trigger('onSuccessfulChallenge', array('com_cjfit.notification', $notification, $challenge));
					$values[] = $notification->user_id . ',' . $db->q($notification->goal_date) . ', 5, ' . $challenge->id;
				}
			}
		}
		
		if(!empty($values))
		{
			$columns = array('user_id', 'goal_date', 'goal_type', 'goal_value');
			$query = $db->getQuery(true)->insert('#__cjfit_goals_achieved')->columns($columns)->values($values);
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	public function getProvider()
	{
		if(!$this->_provider)
		{
			$params 		= JComponentHelper::getParams('com_cjfit');
			$this->_provider = new Fitbit([
					'clientId'          => $params->get('client_id'),
					'clientSecret'      => $params->get('client_secret'),
					'redirectUri'       => CjFitHelperRoute::getCallbackRoute()
			]);
		}
		return $this->_provider;
	}
}