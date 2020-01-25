<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$input           = Factory::getApplication()->input;
$field           = $input->getCmd('field');
$listOrder       = $this->escape($this->state->get('list.ordering'));
$listDirn        = $this->escape($this->state->get('list.direction'));
$enabledStates   = array(0 => 'icon-publish', 1 => 'icon-unpublish');
$activatedStates = array(0 => 'icon-publish', 1 => 'icon-unpublish');
$userRequired    = (int) $input->get('required', 0, 'int');
$onClick         = "window.parent.jSelectUser(this);window.parent.Joomla.Modal.getCurrent().close()";

?>
<div class="container-popup">
	<form action="<?php echo Route::_('index.php?option=com_users&view=users&layout=modal&tmpl=component&groups=' . $input->get('groups', '', 'BASE64') . '&excluded=' . $input->get('excluded', '', 'BASE64')); ?>" method="post" name="adminForm" id="adminForm">
		<?php if (!$userRequired) : ?>
		<div>
			<button type="button" class="btn btn-primary button-select" data-user-value="0" data-user-name="<?php echo $this->escape(Text::_('JLIB_FORM_SELECT_USER')); ?>"
				data-user-field="<?php echo $this->escape($field); ?>"><?php echo Text::_('JOPTION_NO_USER'); ?></button>&nbsp;
		</div>
		<?php endif; ?>
		<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-info">
				<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
				<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
		<table class="table table-sm">
			<caption id="captionTable" class="sr-only">
				<?php echo Text::_('COM_USERS_USERS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
			</caption>
			<thead>
				<tr>
					<th scope="col">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" style="width:25%">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" style="width:1%" class="text-center">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" style="width:1%" class="text-center">
						<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
					</th>
					<th scope="col" style="width:25%">
						<?php echo Text::_('COM_USERS_HEADING_GROUPS'); ?>
					</th>
					<th scope="col" style="width:1%">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 0; ?>
				<?php foreach ($this->items as $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<th scope="row">
							<a class="pointer button-select" href="#" data-user-value="<?php echo $item->id; ?>" data-user-name="<?php echo $this->escape($item->name); ?>"
								data-user-field="<?php echo $this->escape($field); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
						</th>
						<td>
							<?php echo $this->escape($item->username); ?>
						</td>
						<td class="text-center tbody-icon">
							<span class="<?php echo $enabledStates[(int) $this->escape($item->block)]; ?>"></span>
						</td>
						<td class="text-center tbody-icon">
							<span class="<?php echo $activatedStates[(empty($item->activation) ? 0 : 1)]; ?>"></span>
						</td>
						<td>
							<?php echo nl2br($item->group_names); ?>
						</td>
						<td>
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
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>">
		<input type="hidden" name="boxchecked" value="0">
		<input type="hidden" name="required" value="<?php echo $userRequired; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
