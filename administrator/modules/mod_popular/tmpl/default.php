<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('bootstrap.framework');
?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
	<caption class="sr-only"><?php echo $module->title; ?></caption>
	<thead>
		<tr>
			<th scope="col" style="width:60%"><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
			<th scope="col" style="width:20%"><?php echo Text::_('JGLOBAL_HITS'); ?></th>
			<th scope="col" style="width:20%"><?php echo Text::_('JDATE'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php if (count($list)) : ?>
		<?php foreach ($list as $i => $item) : ?>
			<?php // Calculate popular items ?>
			<?php $hits = (int) $item->hits; ?>
			<?php $hits_class = ($hits >= 10000 ? 'danger' : ($hits >= 1000 ? 'warning' : ($hits >= 100 ? 'info' : 'secondary'))); ?>
			<tr>
				<th scope="row">
					<?php if ($item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time); ?>
					<?php endif; ?>
					<?php if ($item->link) : ?>
						<a href="<?php echo $item->link; ?>">
							<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
						</a>
					<?php else : ?>
						<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
					<?php endif; ?>
				</th>
				<td>
					<span class="badge badge-<?php echo $hits_class; ?>"><?php echo $item->hits; ?></span>
				</td>
				<td>
					<?php echo HTMLHelper::_('date', $item->publish_up, Text::_('DATE_FORMAT_LC4')); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php else : ?>
		<tr>
			<td colspan="3">
				<?php echo Text::_('MOD_POPULAR_NO_MATCHING_RESULTS'); ?>
			</td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>
