<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Newsfeeds\Site\Helper\RouteHelper;

HTMLHelper::_('behavior.core');

$app = Factory::getApplication();

$function  = $app->input->getCmd('function', 'jSelectNewsfeed');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div class="container-popup">

	<form action="<?php echo Route::_('index.php?option=com_newsfeeds&view=newsfeeds&layout=modal&tmpl=component&function=' . $function); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-sm">
				<caption id="captionTable" class="sr-only">
					<?php echo Text::_('COM_NEWSFEEDS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
				</caption>
				<thead>
					<tr>
						<th scope="col" style="width:1%" class="text-center">
							<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" class="title">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:15%" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:15%" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
						</th>
						<th scope="col" style="width:1%" class="d-none d-md-table-cell">
							<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
				$iconStates = array(
					-2 => 'icon-trash',
					0  => 'icon-unpublish',
					1  => 'icon-publish',
					2  => 'icon-archive',
				);
				?>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php if ($item->language && Multilanguage::isEnabled())
					{
						$tag = strlen($item->language);
						if ($tag == 5)
						{
							$lang = substr($item->language, 0, 2);
						}
						elseif ($tag == 6)
						{
							$lang = substr($item->language, 0, 3);
						}
						else {
							$lang = '';
						}
					}
					elseif (!Multilanguage::isEnabled())
					{
						$lang = '';
					}
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center tbody-icon">
							<span class="<?php echo $iconStates[$this->escape($item->published)]; ?>" aria-hidden="true"></span>
						</td>
						<th scope="row">
							<a href="javascript:void(0)" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>', '<?php echo $this->escape($item->catid); ?>', null, '<?php echo $this->escape(RouteHelper::getNewsfeedRoute($item->id, $item->catid, $item->language)); ?>', '<?php echo $this->escape($lang); ?>', null);">
							<?php echo $this->escape($item->name); ?></a>
							<div class="small">
								<?php echo Text::_('JCATEGORY') . ': ' . $this->escape($item->category_title); ?>
							</div>
						</th>
						<td class="small d-none d-md-table-cell">
							<?php echo $this->escape($item->access_level); ?>
						</td>
						<td class="small d-none d-md-table-cell">
							<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
						</td>
						<td class="d-none d-md-table-cell">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php // load the pagination. ?>
			<?php echo $this->pagination->getListFooter(); ?>

		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
