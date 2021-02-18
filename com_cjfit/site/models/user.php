<?php
use League\OAuth2\Client\Token\AccessToken;
use djchen\OAuth2\Client\Provider\Fitbit;

/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjFitModelUser extends JModelItem
{
	private $_accessToken = null;
	private $_provider = null;
	protected $_activity = null;
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id'
			);
		}

		parent::__construct($config);
	}
	
	protected function populateState ()
	{
		$app = JFactory::getApplication('site');
		
		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('user.id', $pk);
		
		$duration = $app->input->getInt('activity_duration', 7);
		$this->setState('activity.duration', $duration > 365 ? 365 : $duration);
		
		$offset = $app->input->getUint('limitstart');
		$this->setState('list.offset', $offset);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
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
	
	public function getToken($userId)
	{
		if(!$this->_accessToken)
		{
			$db 		= $this->getDbo();
			$params 	= JComponentHelper::getParams('com_cjfit');
			$app 		= JFactory::getApplication();
			
			$query = $db->getQuery(true)
				->select('token')
				->from('#__cjfit_users')
				->where('id = '. $userId);
			$db->setQuery($query);
			$ciphertext = $db->loadResult();
			
			if(empty($ciphertext))
			{
				if($params->get('debug'))
				{
					$app->enqueueMessage('Empty token, user is not connected.');
				}
				
				return false;
			}
			
			$key 				= $params->get('client_secret');
			$this->_accessToken	= CjFitHelper::getDecryptedToken($ciphertext, $key);
			
			if(empty($this->_accessToken))
			{
				if($params->get('debug'))
				{
					$app->enqueueMessage('Unable to decypher token, user is not connected.');
				}
				
				return false;
			}
			
			if($this->_accessToken && $this->_accessToken->hasExpired())
			{
				$provider = $this->getProvider();
				
				try
				{
					$this->_accessToken = $provider->getAccessToken('refresh_token', ['refresh_token' => $this->_accessToken->getRefreshToken()]);
					$ciphertext = CjFitHelper::getEncryptedToken($this->_accessToken, $key);
					
					$query = $db->getQuery(true)
						->update('#__cjfit_users')
						->set('token = ' . $db->q($ciphertext))
						->where('id = ' . $userId);
					$db->setQuery($query);
					$db->execute();
				}
				catch (Exception $e)
				{
					if($params->get('debug'))
					{
						$app->enqueueMessage('Error: ' . $e->getMessage());
					}
					
					return false;
				}
			}
		}
		
		return $this->_accessToken;
	}
	
	public function save($user)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('count(*)')
			->from('#__cjfit_users')
			->where('id = ' . $user->id);
		$db->setQuery($query);
		$exists = (int) $db->loadResult();
		
		if($exists)
		{
			$db->updateObject('#__cjfit_users', $user, 'id');
		}
		else
		{
			$db->insertObject('#__cjfit_users', $user, 'id');
		}
		
		return true;
	}
	
	public function getItem ($pk = null)
	{
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('user.id');
		if ($this->_item === null)
		{
			$this->_item = array(0=>false);
		}
		
		if ($pk && ! isset($this->_item[$pk]))
		{
			try
			{
				$db 			= $this->getDbo();
				$params 		= JComponentHelper::getParams('com_cjfit');
				$displayName 	= $params->get('display_name', 'name');
				$currDate 		= JFactory::getDate()->format('Y-m-d');
				$provider 		= $this->getProvider();
				
				$query = $db->getQuery(true)
					->select('a.id, a.handle, a.fitbit_owner_id, a.token, a.height, a.weight, a.average_daily_steps, a.stride_length_running,' .
						'a.stride_length_walking, a.attribs, a.goals_date, a.last_fetched, a.daily_goals, a.lifetime_stats')
					->from('#__cjfit_users AS a');
				
				// join over users
				$query
					->select('u.'.$db->qn($displayName).' AS author, u.email AS author_email')
					->join('inner', '#__users AS u ON a.id = u.id');
				
				$query->where('a.id = ' . $pk);
				$db->setQuery($query);
				$data = $db->loadObject();
				
				if (empty($data))
				{
					// user fitbit account is not attached to the site or user does not exist
					return -1;
				}
				
				if($data->goals_date != $currDate || empty($data->daily_goals))
				{
					$accessToken = $this->getToken($pk);
					if(!$accessToken)
					{
						// user fitbit account is not attached to the site
						return -2;
					}
					
					$request = $provider->getAuthenticatedRequest(
							Fitbit::METHOD_GET,
							Fitbit::BASE_FITBIT_API_URL . '/1/user/'.$data->fitbit_owner_id.'/activities/goals/daily.json',
							$accessToken,
							['headers' => [Fitbit::HEADER_ACCEPT_LANG => ''], [Fitbit::HEADER_ACCEPT_LOCALE => 'en_US']]
							);
					
					$response = $provider->getParsedResponse($request);
					$data->daily_goals = !empty($response['goals']) 
						? json_encode($response['goals']) 
						: '{"activeMinutes":0, "caloriesOut":0, "distance":0, "steps":0}';
					
					$request = $provider->getAuthenticatedRequest(
							Fitbit::METHOD_GET,
							Fitbit::BASE_FITBIT_API_URL . '/1/user/'.$data->fitbit_owner_id.'/activities.json',
							$accessToken,
							['headers' => [Fitbit::HEADER_ACCEPT_LANG => ''], [Fitbit::HEADER_ACCEPT_LOCALE => 'en_US']]
							);
					
					$response = $provider->getParsedResponse($request);
					$data->lifetime_stats = json_encode($response);
					
					// update new goals text
					$query = $db->getQuery(true)
						->update('#__cjfit_users')
						->set('daily_goals = ' . $db->q($data->daily_goals))
						->set('lifetime_stats = ' . $db->q($data->lifetime_stats))
						->set('goals_date = ' . $db->q($currDate))
						->where('id = ' . $pk);
					$db->setQuery($query);
					$db->execute();
				}
				
				$lifetimeStats 		= json_decode($data->lifetime_stats, true);
				$data->goals	 	= json_decode($data->daily_goals, true);
				$data->lifetime 	= $lifetimeStats['lifetime']['total'];
				$data->best 		= $lifetimeStats['best']['total'];
				
				// update daily activity
				$lastFetched 		= JFactory::getDate($data->last_fetched);
				$now 				= JFactory::getDate();
				$mins 				= round(abs($now->toUnix() - $lastFetched->toUnix()) / 60, 2);
				
				if($mins > 60)
				{
					$types = array(
							ACTIVITY_TYPE_STEPS => 'steps', 
							ACTIVITY_TYPE_DISTANCE => 'distance', 
							ACTIVITY_TYPE_CALORIES => 'calories',
							ACTIVITY_TYPE_SEDENTARY => 'minutesSedentary',
							ACTIVITY_TYPE_LACTIVE => 'minutesLightlyActive',
							ACTIVITY_TYPE_FACTIVE => 'minutesFairlyActive',
							ACTIVITY_TYPE_VACTIVE => 'minutesVeryActive'
					);
					
					foreach ($types as $t => $type)
					{
						$status = $this->updateDailyActivity($pk, $data->fitbit_owner_id, $type, $t);
						if($status < 0)
						{
							return $status;
						}
					}
					
					$query = $db->getQuery(true)
						->update('#__cjfit_users')
						->set('last_fetched = ' .$db->q($now->toSql()))
						->where('id = ' .$pk);
					$db->setQuery($query);
					$db->execute();
					
					// check and trigger events on user goals
					$this->checkUserGoals($pk);
					
					// update leaderboard data
					$this->updateDailyLeaderboard();
				} 
				// end updating user daily activity
				
				// Get average daily steps
				$query = $db->getQuery(true)
					->select('avg(activity_value)')
					->from('#__cjfit_daily_activity')
					->where('user_id = ' . $pk)
					->where('activity_type = ' . ACTIVITY_TYPE_STEPS)
					->where('activity_value > 0');
				$db->setQuery($query);
				$data->average_daily_steps = round((int)$db->loadResult());
				
				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}
		
		return $this->_item[$pk];
	}
	
	private function updateDailyActivity($pk, $fitbitOwnerId, $type, $t)
	{
		// get the daily activity again
		$accessToken = $this->getToken($pk);
		if(!$accessToken)
		{
			// user fitbit account is not attached to the site
			return -2;
		}
		
		$provider = $this->getProvider();
		$request = $provider->getAuthenticatedRequest(
				Fitbit::METHOD_GET,
				Fitbit::BASE_FITBIT_API_URL . '/1/user/'.$fitbitOwnerId.'/activities/'.$type.'/date/today/30d.json',
				$accessToken,
				['headers' => [Fitbit::HEADER_ACCEPT_LANG => ''], [Fitbit::HEADER_ACCEPT_LOCALE => 'en_US']]
				);
		
		$response = $provider->getParsedResponse($request);
		if(!empty($response['activities-'.$type]))
		{
			$db = $this->getDbo();
			$dates = array();
			
			foreach ($response['activities-'.$type] as $activity)
			{
				$dates[] = $db->q($activity['dateTime']);
			}
			
			if(!empty($dates))
			{
				$query = $db->getQuery(true)
					->delete('#__cjfit_daily_activity')
					->where('user_id = ' . $pk)
					->where('activity_type = ' . (int) $t)
					->where('activity_date IN (' . implode(',', $dates) . ')');
				$db->setQuery($query);
				$db->execute();
				
				$query = $db->getQuery(true)
					->insert('#__cjfit_daily_activity')
					->columns('user_id, activity_type, activity_date, activity_value');

				foreach ($response['activities-'.$type] as $activity)
				{
					$query->values($pk . ',' . $db->q($t) . ',' . $db->q($activity['dateTime']) . ',' . $activity['value']);
				}
				
				$db->setQuery($query);
				$db->execute();
				
				return true;
			}
		}
		
		return false;
	}
	
	private function checkUserGoals($pk)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('activity_type, activity_value')
			->from('#__cjfit_daily_activity')
			->where('user_id = ' . $pk)
			->where('activity_date = CURDATE()');
		$db->setQuery($query);
		$activities = $db->loadObjectList();
		
		if(empty($activities))
		{
			return;
		}
		
		// Get user goals
		$query = $db->getQuery(true)
			->select('user_id, goal_type, goal_value')
			->from('#__cjfit_goals_achieved')
			->where('goal_date = CURDATE()')
			->where('user_id = ' . $pk);
		$db->setQuery($query);
		$userGoals = $db->loadObjectList();

		$stepsGoalReached 		= false;
		$distanceGoalReached 	= false;
		$caloriesGoalReached 	= false;
		$minutesGoalReached 	= false;
		$challengesWon 			= array();
		
		foreach ($userGoals as $goal)
		{
			switch ($goal->goal_type)
			{
				case 1: // steps
					$stepsGoalReached = true;
					break;
					
				case 2:
					$distanceGoalReached = true;
					break;
					
				case 3:
					$caloriesGoalReached = true;
					break;
					
				case 4:
					$minutesGoalReached = true;
					break;
					
				case 5:
					$challengesWon[] = $goal->goal_value;
					break;
			}
		}
		
		$query = $db->getQuery(true)
			->select('u.id as user_id, u.handle, u.daily_goals, ju.name, ju.username, ju.email')
			->from('#__cjfit_users AS u')
			->join('inner', '#__users AS ju ON ju.id = u.id')
			->where('u.id = ' . $pk);
		$db->setQuery($query);
		$userObj = $db->loadObject();

		JPluginHelper::importPlugin('cjfit');
		$dispatcher 			= JEventDispatcher::getInstance();
		$today 					= JFactory::getDate()->format('y-m-d');
		$values 				= array();
		$userObj->update_date 	= $today;
		$nullDate 				= $db->quote($db->getNullDate());
		$nowDate 				= $db->quote(JFactory::getDate()->toSql());
		$minutes = $steps = $distance = $calories = 0;
		
		if(!empty($userObj->daily_goals))
		{
			$dailyGoals = json_decode($userObj->daily_goals, true);
			foreach ($activities as $activity)
			{
				switch ($activity->activity_type)
				{
					case ACTIVITY_TYPE_STEPS:
						$steps = $activity->activity_value;
						if(!$stepsGoalReached && $dailyGoals['steps'] > 0 && $steps>= $dailyGoals['steps'])
						{
							$dispatcher->trigger('onReachingDailyStepsGoal',
									array('com_cjfit.notification', $userObj, $dailyGoals['steps'], $steps));
							
							$values[] = $userObj->user_id . ',' . $db->q($today) . ', 1, ' . $steps . ',' . $db->q($nowDate);
						}
						break;
					
					case ACTIVITY_TYPE_DISTANCE:
						$distance = $activity->activity_value;
						if(!$distanceGoalReached && $dailyGoals['distance'] > 0 && $distance >= $dailyGoals['distance'])
						{
							$dispatcher->trigger('onReachingDailyDistanceGoal',
									array('com_cjfit.notification', $userObj, $dailyGoals['steps'], $distance));
							
							$values[] = $userObj->user_id . ',' . $db->q($today) . ', 2, ' . $distance . ',' . $db->q($nowDate);
						}
						break;
						
					case ACTIVITY_TYPE_CALORIES:
						$calories = $activity->activity_value;
						if(!$caloriesGoalReached && $dailyGoals['caloriesOut']> 0 && $calories >= $dailyGoals['caloriesOut'])
						{
							$dispatcher->trigger('onReachingDailyCaloriesGoal',
									array('com_cjfit.notification', $userObj, $dailyGoals['caloriesOut'], $calories));
							
							$values[] = $userObj->user_id . ',' . $db->q($today) . ', 3, ' . $calories . ',' . $db->q($nowDate);
						}
						break;
						
					case ACTIVITY_TYPE_LACTIVE:
					case ACTIVITY_TYPE_FACTIVE:
					case ACTIVITY_TYPE_VACTIVE:
						$minutes = $minutes + $activity->activity_value;
						break;
				}
			}
			
			if($minutes > 0 && !$minutesGoalReached && $dailyGoals['activeMinutes'] > 0 && $minutes >= $dailyGoals['activeMinutes'])
			{
				$dispatcher->trigger('onReachingDailyActiveMinutesGoal',
						array('com_cjfit.notification', $userObj, $dailyGoals['activeMinutes'], $minutes));
				
				$values[] = $userObj->user_id . ',' . $db->q($today) . ', 4, ' . $minutes . ',' . $db->q($nowDate);
			}
		}
		
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
			$rulesParser = new CjFitRulesParser();
			$rulesParser->set($steps, $distance, $calories, $minutes);
			$params = JComponentHelper::getParams('com_cjfit');
			
			foreach ($challenges as $challenge)
			{
				$challenge->rules = json_decode($challenge->rules, true);
				
				$challengeParams = new JRegistry();
				$challengeParams->loadString($challenge->attribs);
				
				$challenge->params = clone $params;
				$challenge->params->merge($challengeParams);
				
				if(!in_array($challenge->id, $challengesWon) && $rulesParser->getResult($challenge->rules))
				{
					$dispatcher->trigger('onSuccessfulChallenge', array('com_cjfit.notification', $userObj, $challenge));
					$values[] = $userObj->user_id . ',' . $db->q($today) . ', 5, ' . $challenge->id . ',' . $db->q($nowDate);
				}
			}
		}
		
		if(!empty($values))
		{
			$columns = array('user_id', 'goal_date', 'goal_type', 'goal_value', 'created');
			$query = $db->getQuery(true)->insert('#__cjfit_goals_achieved')->columns($columns)->values($values);
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	public function getDailyActivity($pk = 0)
	{
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('user.id');
		if ($this->_activity === null)
		{
			$this->_item = array(0=>false);
		}
		
		$duration = (int) $this->getState('activity.duration');
		
		if ($pk && ! isset($this->_activity[$pk][$duration]))
		{
			$data			= new stdClass();
			$data->steps 	= array();
			$data->distance = array();
			$data->calories = array();
			
			try 
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('activity_type, activity_date, activity_value')
					->from('#__cjfit_daily_activity')
					->where('user_id = ' . (int) $pk)
					->where('activity_date > DATE(NOW()) - INTERVAL '.$duration.' DAY')
					->order('activity_date DESC');
				$db->setQuery($query);
				$activities = $db->loadObjectList();

				if(!empty($activities))
				{
					foreach ($activities as $activity)
					{
						switch ($activity->activity_type)
						{
							case ACTIVITY_TYPE_STEPS: //steps
								$data->steps[] = $activity;
								break;
								
							case ACTIVITY_TYPE_DISTANCE: //distance
								$data->distance[] = $activity;
								break;
								
							case ACTIVITY_TYPE_CALORIES: //calories
								$data->calories[] = $activity;
								break;
						}
					}
				}
				
				$this->_activity[$pk][$duration] = $data;
			}
			catch (Exception $e)
			{
				$this->_activity[$pk][$duration] = $data;
				// nothing
			}
		}
		
		return $this->_activity[$pk][$duration];
	}
	
	public function updateDailyLeaderboard()
	{
		$db = $this->getDbo();
		$query1 = $db->getQuery(true)
			->select('a.user_id, a.activity_date, a.activity_type, a.activity_value')
			->from('#__cjfit_daily_activity AS a')
			->join('left', '#__cjfit_daily_activity AS b ON a.activity_type = b.activity_type AND a.activity_value <= b.activity_value')
			->where('a.activity_date = CURDATE() AND b.activity_date = CURDATE()')
			->group('a.id')
			->having('count(*) <= 20')
			->order('a.activity_type, a.activity_value DESC');
		
		$query = $db->getQuery(true)
			->insert('#__cjfit_leaderboard')
			->columns($db->qn(array('user_id', 'activity_date', 'activity_type', 'activity_value')))
			->values($query1);
		
		$db->setQuery($query->__toString() . ' ON DUPLICATE KEY UPDATE activity_value = VALUES(activity_value)');
		$db->execute();
	}
	
	private function getUser()
	{
		$user = $this->getState('user');
		if(empty($user))
		{
			$user = JFactory::getUser();
		}
		
		return $user;
	}
}