<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitTableUser extends JTable
{

	public function __construct (&$db)
	{
		parent::__construct('#__cjfit_users', 'id', $db);
	}
}
