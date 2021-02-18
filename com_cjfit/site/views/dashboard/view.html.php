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
	/**
	 * Execute and display a template script.
	 *
	 * @param string $tpl
	 *        	The name of the template file to parse; automatically searches
	 *        	through the template paths.
	 *
	 * @return mixed A string if successful, otherwise a Error object.
	 */
	public function display ($tpl = null)
	{
		$app    				= JFactory::getApplication();
		$user   				= JFactory::getUser();
		$this->params			= JComponentHelper::getParams('com_cjfit');
		$this->state			= $this->get('State');
		$this->item				= $this->get('Item');
		$this->activity			= $this->get('Activity');
		$this->leaderboard		= $this->get('Leaderboard');
		$this->achievements		= $this->get('Achievements');
		$this->pagination		= $this->get('Pagination');
		$this->activity_label 	= $this->get('ActivityLabel');
		$this->leaderboard_type	= $this->state->get('leaderboard.type');
		
		if(!empty($this->params->get('disonnected_profile_text')))
		{
			$item				= $this->get('Article');
			if(!empty($item))
			{
				if ($this->params->get('show_intro', '1') == '1')
				{
					$item->text = $item->introtext . ' ' . $item->fulltext;
				}
				elseif ($item->fulltext)
				{
					$item->text = $item->fulltext;
				}
				else
				{
					$item->text = $item->introtext;
				}
				
				// Process the content plugins for topic description
				$dispatcher 	= JEventDispatcher::getInstance();
				
				JPluginHelper::importPlugin('content');
				$dispatcher->trigger('onContentPrepare', array('com_cjfit.dashboard', &$item, &$this->params, 0));
				
				$item->event = new stdClass();
				$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_cjfit.dashboard', &$item, &$this->params, 0));
				$item->event->beforeDisplayContent = trim(implode("\n", $results));
				
				$results = $dispatcher->trigger('onContentAfterDisplay', array('com_cjfit.dashboard', &$item, &$this->params, 0));
				$item->event->afterDisplayContent = trim(implode("\n", $results));
				
				$this->article = $item;
			}
		}
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();
		
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		
		$title = $this->params->get('page_title', '');
		
		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		
		$this->document->setTitle($title);
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		return parent::display($tpl);
	}
	
	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function prepareDocument ()
	{
		$app			= JFactory::getApplication();
		$menus			= $app->getMenu();
		$menu 			= $app->getMenu()->getActive();
		$this->pathway 	= $app->getPathway();
		$title         	= null;
		
		// Because the application sets a default page title, we need to get it from the menu item itself
		$this->menu = $menus->getActive();
		
		if ($this->menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $this->menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_($this->defaultPageTitle));
		}
		
		$title = $this->params->get('page_title', '');
		
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		
		$this->document->setTitle($title);
		
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		
		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}