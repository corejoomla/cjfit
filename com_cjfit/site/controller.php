<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitController extends JControllerLegacy
{

	public function __construct ($config = array())
	{
		$this->input = JFactory::getApplication()->input;
		parent::__construct($config);
	}

	public function display ($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		
		// Set the default view name and format from the Request.
		$vName = $this->input->getCmd('view', 'dashboard');
		$this->input->set('view', $vName);
		
		$safeurlparams = array(
				'catid' => 'INT',
				'id' => 'INT',
				'cid' => 'ARRAY',
				'year' => 'INT',
				'month' => 'INT',
				'limit' => 'UINT',
				'limitstart' => 'UINT',
				'showall' => 'INT',
				'return' => 'BASE64',
				'filter' => 'STRING',
				'filter_order' => 'CMD',
				'filter_order_Dir' => 'CMD',
				'filter-search' => 'STRING',
				'print' => 'BOOLEAN',
				'lang' => 'CMD',
				'Itemid' => 'INT'
		);
		
		JHtml::_('bootstrap.framework');
		JHtml::_('script', 'system/core.js', false, true);
		
		$params = JComponentHelper::getParams('com_cjfit');
		$loadBsCss = $params->get('load_bootstrap_css', false);
		$custom_tag = $params->get('custom_tag', true);
		$cachable = true;
		
		if($loadBsCss)
		{
			CjLib::behavior('bootstrap', array('loadcss' => $loadBsCss, 'customtag'=>$custom_tag));
		}
		
		CJLib::behavior('bscore', array('customtag'=>$custom_tag));
		CjScript::_('fontawesome', array('custom'=>true));
		CjScript::_('chartjs', array('custom'=>true));
		
		CJFunctions::add_css_to_document($document, JUri::root(true).'/media/com_cjfit/css/cjfit.min.css', $custom_tag);
		CJFunctions::add_script(JUri::root(true) . '/media/com_cjfit/js/raphael.min.js', $custom_tag);
		CJFunctions::add_script(JUri::root(true) . '/media/com_cjfit/js/justgage.js', $custom_tag);
		CJFunctions::add_script(JUri::root(true) . '/media/com_cjfit/js/cjfit.min.js', $custom_tag);

		parent::display($cachable, $safeurlparams);
		return $this;
	}
}
