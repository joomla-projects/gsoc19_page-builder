<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$lang = Factory::getLanguage(); ?>

<nav class="pagenavigation">
	<ul class="pagination ml-0">
	<?php if ($row->prev) :
		$direction = $lang->isRtl() ? 'right' : 'left'; ?>
		<li class="previous page-item">
			<a class="page-link" href="<?php echo Route::_($row->prev); ?>" rel="prev">
			<span class="sr-only">
				<?php echo Text::sprintf('JPREVIOUS_TITLE', htmlspecialchars($rows[$location-1]->title)); ?>
			</span>
			<?php echo '<span class="icon-chevron-' . $direction . '" aria-hidden="true"></span> <span aria-hidden="true">' . $row->prev_label . '</span>'; ?>
			</a>
		</li>
	<?php endif; ?>
	<?php if ($row->next) :
		$direction = $lang->isRtl() ? 'left' : 'right'; ?>
		<li class="next page-item">
			<a class="page-link" href="<?php echo Route::_($row->next); ?>" rel="next">
			<span class="sr-only">
				<?php echo Text::sprintf('JNEXT_TITLE', htmlspecialchars($rows[$location+1]->title)); ?>
			</span>
			<?php echo '<span aria-hidden="true">' . $row->next_label . '</span> <span class="icon-chevron-' . $direction . '" aria-hidden="true"></span>'; ?>
			</a>
		</li>
	<?php endif; ?>
	</ul>
</nav>
