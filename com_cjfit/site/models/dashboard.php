<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjFitModelDashboard extends JModelList
{
	protected $_context = 'com_cjfit.dashboard';
	protected $_item = null;
	protected $_activity = null;
	protected $_achievements = null;
	
	public function __construct($config = array())
	{
		parent::__construct($config = array());
		$this->populateState();
	}
	
	protected function populateState ($ordering = NULL, $direction = NULL)
	{
		$app = JFactory::getApplication('site');
		
		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('user.id', $pk);
		
		$leaderboardType = $app->input->getCmd('leaderboard_type', 'steps');
		$this->setState('leaderboard.type', $leaderboardType);
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
		
		parent::populateState($ordering, $direction);
	}
	
	public function getItem ($pk = null)
	{
		$user = $this->getUser();
		$app = JFactory::getApplication();
		
		$pk = (int) ((! empty($pk)) ? $pk :  $this->getState('user.id'));
		$pk = $pk ? $pk : $user->id;
		
		if ($this->_item === null)
		{
			$this->_item = array();
		}
		
		if (! isset($this->_item[$pk]))
		{
			JLoader::import('user', JPATH_ROOT.'/components/com_cjfit/models');
			$userModel	= JModelItem::getInstance( 'user', 'CjFitModel' );
			$userModel->getState();
			
			$data = $userModel->getItem($pk);
			if(is_object($data))
			{
				// get today's activity
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('activity_type, activity_value')
					->from('#__cjfit_daily_activity')
					->where('user_id = ' . $pk)
					->where('activity_date = CURDATE()');
				$db->setQuery($query);
				
				$data->activity = $db->loadAssocList('activity_type');
			}
			
			$this->_item[$pk] = $data;
		}
		
		return $this->_item[$pk];
	}
	
	public function getActivity ($pk = null)
	{
		$user = $this->getUser();
		$app = JFactory::getApplication();
		
		$pk = (! empty($pk)) ? $pk : (int) $this->getState('user.id');
		$pk = $pk > 0 ? $pk : $user->id;
		
		if ($this->_activity === null)
		{
			$this->_activity= array();
		}
		
		if (! isset($this->_activity[$pk]))
		{
			JLoader::import('user', JPATH_ROOT.'/components/com_cjfit/models');
			$userModel	= JModelItem::getInstance( 'user', 'CjFitModel' );
			$userModel->getState();
			
			$userModel->setState('activity.duration', 30);
			$this->_activity[$pk] = $userModel->getDailyActivity($pk);
		}
		
		return $this->_activity[$pk];
	}
	
	public function getListQuery()
	{
		$user = $this->getUser();
		$db = $this->getDbo();
		$pk = $this->getState('user.id') > 0 ? $this->getState('user.id') : $user->id;
		
		$query = $db->getQuery(true)
			->select('a.goal_date, a.goal_type, a.goal_value')
			->from('#__cjfit_goals_achieved AS a')
			->where('a.user_id = ' . (int) $pk)
			->order('a.goal_date DESC');
		
		$query
			->select('c.title as challenge_title')
			->join('LEFT', '#__cjfit_challenges AS c ON a.goal_type = 5 AND a.goal_value = c.id');
		
		return $query;
	}
	
	public function getAchievements ($pk = null)
	{
		$items = parent::getItems();
		
		return $items;
	}
	
	public function getLeaderboard()
	{
		$db 			= $this->getDbo();
		$params 		= JComponentHelper::getParams('com_cjfit');
		$displayName 	= $params->get('display_name', 'name');
		$limit			= $params->get('leaderboard_num_rows', 5);
		
		$query = $db->getQuery(true)
			->select('a.user_id, a.activity_type, sum(a.activity_value) as activity_value')
			->from('#__cjfit_leaderboard AS a');
		
		// join over cjfit users
		$query
			->select('ju.handle')
			->join('inner', '#__cjfit_users AS ju ON ju.id = a.user_id');
		
		// join over users
		$query
			->select('u.'.$db->qn($displayName).' AS author, u.email AS author_email')
			->join('inner', '#__users AS u ON a.user_id = u.id');
		
		$leaderboardType = ACTIVITY_TYPE_STEPS;
		switch ($this->getState('leaderboard.type'))
		{
			case 'steps':
				$leaderboardType = ACTIVITY_TYPE_STEPS;
				break;
				
			case 'distance':
				$leaderboardType = ACTIVITY_TYPE_DISTANCE;
				break;
				
			case 'calories':
				$leaderboardType = ACTIVITY_TYPE_CALORIES;
				break;
		}
		
		// get today's leaderboard
		$query->where('activity_type = ' . $leaderboardType);
		
		switch ($this->getState('leaderboard.duration', 'daily'))
		{
			case 'weekly':
				$query->where('activity_date >= SUBDATE(CURDATE(), INTERVAL WEEKDAY(NOW()) DAY)');
				break;
				
			case 'monthly':
				$query->where('activity_date >= DATE_FORMAT(NOW(), \'%Y-%m-01\')');
				break;
				
			default:
				$query->where('activity_date = CURDATE()');;
				break;
		}
		
		// order the result
		$query->where('activity_value > 0');
		$query->order('activity_value DESC');
		$query->group('user_id, activity_type');

		$db->setQuery($query, 0, $limit);
		$leaderboard = $db->loadObjectList();
		
		return $leaderboard;
	}
	
	public function getActivityLabel()
	{
		switch ($this->getState('leaderboard.type'))
		{
			case 'steps':
				return 'COM_CJFIT_NUM_STEPS';
				
			case 'distance':
				return 'COM_CJFIT_NUM_DISTANCE';
				
			case 'calories':
				return 'COM_CJFIT_NUM_CALORIES';
		}
		
		return 'COM_CJFIT_NUM_STEPS';
	}
	
	public function getArticle()
	{
		$params = JComponentHelper::getParams('com_cjfit');
		$articleId = (int) $params->get('disonnected_profile_text');
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.introtext, a.fulltext')
			->from('#__content AS a')
			->where('a.id = ' . $articleId);
		$db->setQuery($query);
		
		$item = $db->loadObject();
		
		return $item;
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