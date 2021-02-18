<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjlib
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjFitUnitsHelper 
{
	private $_lbl_distance 		= 'km';
	private $_lbl_speed			= 'mps';
	private $_lbl_elevation		= 'm';
	
	private $_fact_distance		= 1;
	private $_fact_speed 		= 1;
	private $_fact_elevation 	= 1;

	function __construct($distance = 'km', $speed = 'mps', $elevation = 'm') 
	{
		switch ($distance)
		{
			case 'mi':
				$this->_lbl_distance 	= JText::_('COM_CJFIT_UNIT_MILES');
				$this->_fact_distance 	= 0.621371;
				break;
				
			case 'm':
				$this->_lbl_distance 	= JText::_('COM_CJFIT_UNIT_METRES');
				$this->_fact_distance 	= 1000;
				break;
				
			case 'ft':
				$this->_lbl_distance 	= JText::_('COM_CJFIT_UNIT_FOOT');
				$this->_fact_distance 	= 3280.84;
				break;
				
			case 'km':
			default:
				$this->_lbl_distance	= JText::_('COM_CJFIT_UNIT_KILOMETRE');
				$this->_fact_distance 	= 1;
				break;
		}
		
		switch ($elevation)
		{
			case 'ft':
				$this->_fact_elevation = 3.28084;
				$this->_lbl_elevation = JText::_('COM_CJFIT_UNIT_FOOT');
				break;
				
			default:
				$this->_lbl_elevation = JText::_('COM_CJFIT_UNIT_METRE');
				$this->_fact_elevation = 1;
				break;
		}
		
		switch($speed)
		{
			case 'mph':
				$this->_lbl_speed = JText::_('COM_CJFIT_UNIT_MPH');
				$this->_fact_speed = 2.23694;
				break;
				
			case 'fps':
				$this->_lbl_speed = JText::_('COM_CJFIT_UNIT_FPS');
				$this->_fact_speed = 3.28084;
				break;
				
			case 'kmph':
				$this->_lbl_speed = JText::_('COM_CJFIT_UNIT_KMPH');
				$this->_fact_speed = 3.6;
				break;
				
			case 'mps':
			default:
				$this->_lbl_speed = JText::_('COM_CJFIT_UNIT_MPS');
				$this->_fact_speed = 1;
				break;
		}
	}
	
	function getFactoredValue($type, $value, $round=2)
	{
		switch ($type)
		{
			case 1: //distance
				return round( $value * $this->_fact_distance, 2 );
				break;
				
			case 2: //elevation
				return round( $value * $this->_fact_elevation, 0 );
				break;
				
			case 3: //speed
				return round( $value * $this->_fact_speed, 2 );
				break;
				
			case 4: //heart rate
				return $value;
				break;
		}
	}
	
	function getLabel($type)
	{
		switch($type)
		{
			case 1: return $this->_lbl_distance;
			case 2: return $this->_lbl_elevation;
			case 3: return $this->_lbl_speed;
			case 4: return '';
		}
	}
	
	public function getDistanceMeasure()
	{
		return $this->_lbl_distance;
	}
	
	public function getSpeedMeasure()
	{
		return $this->_lbl_speed;
	}
	
	public function getElevationMeasure()
	{
		return $this->_lbl_elevation;
	}
}