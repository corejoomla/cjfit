<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjfit
 *
 * @copyright   Copyright (C) 2009 - 2017 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
?>
<div id="cj-wrapper" class="cj-wrapper <?php echo $moduleclass_sfx; ?>">

	<?php echo JLayoutHelper::render($layout.'.dashboard.leaderboard', array('data'=>$data), JPATH_ROOT . '/components/com_cjfit/layouts', array('debug' => false) );?>

</div>