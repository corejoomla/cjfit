<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die();

class com_cjfitInstallerScript
{

	function install ($parent)
	{
		$this->update($parent);
		
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_cjfit');
	}

	function uninstall ($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_CJFIT_UNINSTALL_TEXT') . '</p>';
	}

	function update ($parent)
	{
		$db = JFactory::getDBO();
		if (method_exists($parent, 'extension_root'))
		{
			$sqlfile = $parent->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
		}
		else
		{
			$sqlfile = $parent->getParent()->getPath('extension_root') . '/sql/install.mysql.utf8.sql';
		}
		// Don't modify below this line
		$buffer = file_get_contents($sqlfile);
		if ($buffer !== false)
		{
			jimport('joomla.installer.helper');
			$queries = $db->splitSql($buffer);
			if (count($queries) != 0)
			{
				foreach ($queries as $query)
				{
					$query = trim($query);
					if ($query != '' && $query{0} != '#')
					{
						$db->setQuery($query);
						if (! $db->execute())
						{
// 							JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
// 							return false;
						}
					}
				}
			}
		}
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_CJFIT_UPDATE_TEXT') . '</p>';
		$parent->getParent()->setRedirectURL('index.php?option=com_cjfit');
	}

	function preflight ($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_CJFIT_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight ($type, $parent)
	{
		$db = JFactory::getDbo();
		$update_queries = array();
		
		// Perform all queries - we don't care if it fails
		foreach ($update_queries as $query)
		{
			$db->setQuery($query);
			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
			}
		}
		
		echo "<b><font color=\"red\">Database tables successfully migrated to the latest version. Please check the configuration options once again.</font></b>";
	}
}