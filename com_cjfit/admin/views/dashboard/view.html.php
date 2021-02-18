<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitViewDashboard extends JViewLegacy
{
	protected $state;
	
	public function display ($tpl = null)
	{
		CjFitHelper::addSubmenu('dashboard');
		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		$this->params = JComponentHelper::getParams('com_cjfit');
		
		$version = CJFunctions::get_component_update_check('com_cjfit', CJFIT_CURR_VERSION);
		$v = array();
		
		if(!empty($version) && !empty($version['connect']))
		{
			$v['connect'] = (int)$version['connect'];
			$v['version'] = (string)$version['version'];
			$v['released'] = (string)$version['released'];
			$v['changelog'] = (string)$version['changelog'];
			$v['status'] = (string)$version['status'];
		}
		
		$this->version = $v;
		parent::display($tpl);
	}

	protected function addToolbar ()
	{
		$canDo = JHelperContent::getActions('com_cjfit');
		$user = JFactory::getUser();
		
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		
		JToolbarHelper::title(JText::_('COM_CJFIT_DASHBOARD_TITLE'), 'stack dashboard');
		
		if ($user->authorise('core.admin', 'com_cjfit'))
		{
			JToolbarHelper::preferences('com_cjfit');
		}
	}
}
