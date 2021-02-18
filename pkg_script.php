<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  plg_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class pkg_cjfitInstallerScript
{
	public function preflight( $type, $parent )
	{
		if(version_compare(PHP_VERSION, '5.6', '<'))
		{
			return false;
		}
		
		return true;
	}
	
	public function postflight($type, $parent)
	{
		$installCjLib = false;
		if(!file_exists(JPATH_ROOT.'/components/com_cjlib/framework.php'))
		{
			$installCjLib = true;
		}
		
		if(!$installCjLib)
		{
			require_once JPATH_ROOT . '/components/com_cjfit/helpers/constants.php';
			
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('manifest_cache')
				->from($db->quoteName('#__extensions'))
				->where('element = ' . $db->q('pkg_cjlib'));
			$db->setQuery($query);
			
			$manifest = json_decode($db->loadResult(), true);
			$installedCjLibVersion = $manifest['version'];
			if(!$installedCjLibVersion || version_compare(CJFIT_CJLIB_MIN_VERSION, $installedCjLibVersion, '>'))
			{
				$installCjLib = true;
			}
		}
		
		if($installCjLib)
		{
			$url 		= 'https://www.corejoomla.com/media/autoupdates/files/pkg_cjlib_v2.8.8.zip';
			$package 	= $this->downloadPackage($url);
			$return		= $this->installPackage($package);
		}
		
		echo '<div class="well">
		<p>CjFit Package:</p>
		<table class="table table-hover table-striped">
		<tr><td>CjFit Component</td><td>Successfully installed</td></tr>
		<tr><td>CjFit Challenges Module</td><td>Successfully installed</td></tr>
		<tr><td>CjFit Leaderboard Module</td><td>Successfully installed</td></tr>
		<tr><td>CjFit Steps List Module</td><td>Successfully installed</td></tr>
		<tr><td>CjFit Steps Summary Plugin</td><td>Successfully installed</td></tr>
		<tr><td>CjFit Activity Plugin</td><td>Successfully installed</td></tr>
		</table>
		<p>Thank you for using corejoomla&reg; software. Please add a rating and review at Joomla&reg; Extension Directory.</p>
		</div>';
	}
	
	private function installPackage($package)
	{
		// Get an installer instance.
		$app = JFactory::getApplication();
		$installer = JInstaller::getInstance();
		
		if (is_array($package) && isset($package['dir']) && is_dir($package['dir']))
		{
			$installer->setPath('source', $package['dir']);
			
			if (!$installer->findManifest())
			{
				// If a manifest isn't found at the source, this may be a Joomla package; check the package directory for the Joomla manifest
				if (file_exists($package['dir'] . '/administrator/manifests/files/joomla.xml'))
				{
					// We have a Joomla package
					JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
					
					$app->enqueueMessage(
							JText::sprintf('COM_INSTALLER_UNABLE_TO_INSTALL_JOOMLA_PACKAGE', JRoute::_('index.php?option=com_joomlaupdate')),
							'warning'
							);
					
					return false;
				}
			}
		}
		
		// Was the package unpacked?
		if (!$package || !$package['type'])
		{
			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			$app->enqueueMessage(JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');
			
			return false;
		}
		
		// Install the package.
		if (!$installer->install($package['dir']))
		{
			// There was an error installing the package.
			$app->enqueueMessage(JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type']))));
			
			return false;
		}
		
		return true;
	}
	
	private function downloadPackage($url)
	{
		// Download the package at the URL given.
		$p_file = JInstallerHelper::downloadPackage($url);
		
		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));
			
			return false;
		}
		
		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');
		
		// Unpack the downloaded package file.
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);
		return $package;
	}
}