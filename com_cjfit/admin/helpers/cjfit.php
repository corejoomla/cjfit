<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitHelper extends JHelperContent
{

	public static $extension = 'com_cjfit';

	public static function addSubmenu ($vName)
	{
		JHtmlSidebar::addEntry(JText::_('COM_CJFIT_MENU_DASHBOARD'), 'index.php?option=com_cjfit&view=dashboard', $vName == 'dashboard');
		JHtmlSidebar::addEntry(JText::_('COM_CJFIT_MENU_USERS'), 'index.php?option=com_cjfit&view=users', $vName == 'users');
		JHtmlSidebar::addEntry(JText::_('COM_CJFIT_MENU_CHALLENGES'), 'index.php?option=com_cjfit&view=challenges', $vName == 'challenges');
		JHtmlSidebar::addEntry(JText::_('COM_CJFIT_MENU_EMAIL_TEMPLATES'), 'index.php?option=com_cjfit&view=emails', $vName == 'emails');
	}
	
	public static function installCjLib()
	{
		// install cjlib if not already installed.
		if(file_exists(JPATH_ROOT . '/components/com_cjlib/framework.php'))
		{
			return;
		}
		
		$app = JFactory::getApplication();
		$language = JFactory::getLanguage();
		$version = self::getCurrentCjLibVersion();
		$installUrl = 'https://www.corejoomla.com/media/autoupdates/files/pkg_cjlib_v'.$version.'.zip';
		
		$app->input->set('installtype', 'url');
		$app->input->set('install_url', $installUrl);
		$language->load('com_installer', JPATH_ADMINISTRATOR);
		set_time_limit(120);
		
		JLoader::import('install', JPATH_ADMINISTRATOR.'/components/com_installer/models');
		$model	= JModelItem::getInstance( 'install', 'InstallerModel' );
		$model->getState();
		
		if(!$model->install())
		{
			echo 'Please install CjLib API Library.<br><a href="' . $installUrl . '">Download it here</a>';
			return false;
		}
		
		return true;
	}
	
	private function getCurrentCjLibVersion()
	{
		$url = 'http://www.corejoomla.com/extensions.xml';
		$data = '';
		$version = '2.7.0';
		
		// do not download latest version if PHP 5.6 is not available
		// 		if(version_compare(PHP_VERSION, '5.6') < 0)
			// 		{
		// 			return '2.6.6';
		// 		}
		
		//try to connect via cURL
		if(function_exists('curl_init') && function_exists('curl_exec'))
		{
			$ch = @curl_init();
			
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			
			//http code is greater than or equal to 300 ->fail
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			//timeout of 5s just in case
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$data = @curl_exec($ch);
			
			@curl_close($ch);
		}
		
		//try to connect via fsockopen
		if($data == '' && function_exists('fsockopen'))
		{
			$errno = 0;
			$errstr = '';
			
			//timeout handling: 5s for the socket and 5s for the stream = 10s
			$fsock = @fsockopen("www.corejoomla.com", 80, $errno, $errstr, 5);
			
			if ($fsock)
			{
				@fputs($fsock, "GET /extensions.xml HTTP/1.1\r\n");
				@fputs($fsock, "HOST: www.corejoomla.com\r\n");
				@fputs($fsock, "Connection: close\r\n\r\n");
				
				//force stream timeout...
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);
				
				$get_info = false;
				
				while (!@feof($fsock))
				{
					if ($get_info)
					{
						$data .= @fread($fsock, 8192);
					}
					else
					{
						if (@fgets($fsock, 8192) == "\r\n")
						{
							$get_info = true;
						}
					}
				}
				
				@fclose($fsock);
				
				//need to check data cause http error codes aren't supported here
				if(!strstr($data, '<corejoomla>'))
				{
					$data = '';
				}
			}
		}
		
		//try to connect via fopen
		if ($data == '' && function_exists('fopen') && ini_get('allow_url_fopen'))
		{
			//set socket timeout
			ini_set('default_socket_timeout', 5);
			
			$handle = @fopen ($url, 'r');
			
			//set stream timeout
			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);
			
			$data	= @fread($handle, 8192);
			
			@fclose($handle);
		}
		
		if( !empty($data) && strstr($data, '<corejoomla>') )
		{
			$xml = new SimpleXMLElement($data);
			foreach($xml->extension as $extension)
			{
				if($extension['name'] == 'com_cjlib')
				{
					$version = $extension->version;
					break;
				}
			}
		}
		
		return $version;
	}
}
