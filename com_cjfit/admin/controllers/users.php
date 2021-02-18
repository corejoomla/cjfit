<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitControllerUsers extends JControllerAdmin
{
	protected $text_prefix = 'COM_CJFIT';
	
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}
	
	public function getModel ($name = 'Users', $prefix = 'CjFitModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		
		return $model;
	}

	protected function postDeleteHook (JModelLegacy $model, $ids = null)
	{
	}
	
	public function disconnect()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$user = JFactory::getUser();
		
		if (! $user->authorise('core.edit.state', 'com_cjfit'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 403);
		}
		
		$ids = $this->input->get('cid', array(), 'array');
		if (empty($ids))
		{
			throw new Exception(JText::_('JERROR_NO_ITEMS_SELECTED'), 500);
		}
		
		$model = $this->getModel();
		$model->disconnect($ids);
		
		$this->setRedirect(JRoute::_('index.php?option=com_cjfit&view=users', false));
	}
}