<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjFitRulesParser
{
	private $_steps = 0;
	private $_distance = 0;
	private $_calories = 0;
	private $_minutes = 0;
	
	public function set($steps, $distance, $calories, $minutes)
	{
		$this->_steps = $steps;
		$this->_distance = $distance;
		$this->_calories = $calories;
		$this->_minutes = $minutes;
	}
	
	public function getResult($rules)
	{
		return $this->parseGroup($rules);
	}
	
	private function parseGroup($rules)
	{
		if(empty($rules['rules']) || !in_array($rules['condition'], array('AND', 'OR')))
		{
			return false;
		}
		
		$matchAll = ($rules['condition'] == 'AND') ? true : false;
		return $this->parseRules($rules['rules'], $matchAll);
	}
	
	private function parseRules($rules, $matchAll)
	{
		$success = $matchAll ? true : false;
		foreach ($rules as $rule)
		{
			$result = false;
			if(array_key_exists('condition', $rules))
			{
				$result = $this->parseGroup($rules);
			}
			else 
			{
				switch ($rule['field'])
				{
					case 'steps':
						$value = $this->_steps;
						break;
						
					case 'distance':
						$value = $this->_distance;
						break;
						
					case 'calories':
						$value = $this->_calories;
						break;
						
					case 'minutes':
						$value = $this->_minutes;
						break;
				}
				
				$result = $this->compareValues($value, $rule['value'], $rule['operator']);
			}
			
			if( $matchAll && !$result )
			{
				$success = false;
				break;
			}
			else if(!$matchAll && $result)
			{
				$success = true;
				break;
			}
		}
		
		return $success;
	}
	
	private function compareValues($value, $ruleValue, $operator)
	{
		switch ($operator)
		{
			case 'equal':
				return $value == $ruleValue;
				
			case 'not_equal':
				return $value != $ruleValue;
				
			case 'less':
				return $value < $ruleValue;
				
			case 'less_or_equal':
				return $value <= $ruleValue;
				
			case 'greater':
				return $value > $ruleValue;
				
			case 'greater_or_equal':
				return $value >= $ruleValue;
				
			case 'between':
				return $value >= $ruleValue[0] && $value <= $ruleValue[1];
				
			case 'not_between':
				return $value < $ruleValue[0] || $value > $ruleValue[1];
		}
		
		return false;
	}
}