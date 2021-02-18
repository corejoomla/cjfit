<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjFitController extends JControllerLegacy
{
	protected $default_view = 'dashboard';

	public function display ($cachable = false, $urlparams = false)
	{
		parent::display();
		
		$custom_tag = true;
		$document = JFactory::getDocument();
		
		CjScript::_('querybuilder', array('custom' => $custom_tag));
		CJFunctions::add_css_to_document($document, JUri::root(true).'/media/com_cjfit/css/cjfit.min.css', $custom_tag);
		CJFunctions::add_script(JUri::root(true).'/media/com_cjfit/js/cjfit.min.js', $custom_tag);
		
		return $this;
	}
}
