<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

if(!empty($challenges))
{
	foreach ($challenges as $challenge)
	{
		?>
		<p><strong><?php echo $challenge->title;?></strong></p>
		<div><?php echo \Joomla\String\StringHelper::substr(strip_tags($challenge->description), 0, 250);?></div>
		<?php 
	}
}
else 
{
	echo JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');
}