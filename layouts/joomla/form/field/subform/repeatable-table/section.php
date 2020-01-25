<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);

?>

<tr class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php foreach ($form->getGroup('') as $field) : ?>
		<td data-column="<?php echo strip_tags($field->label); ?>">
			<?php echo $field->renderField(array('hiddenLabel' => true)); ?>
		</td>
	<?php endforeach; ?>
	<?php if (!empty($buttons)) : ?>
	<td>
		<div class="btn-group">
			<?php if (!empty($buttons['add'])) : ?>
				<button type="button" class="group-add btn btn-sm btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
					<span class="fa fa-plus" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
			<?php if (!empty($buttons['remove'])) : ?>
				<button type="button" class="group-remove btn btn-sm btn-danger" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>">
					<span class="fa fa-minus" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?>
				<button type="button" class="group-move btn btn-sm btn-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>">
					<span class="fa fa-arrows-alt" aria-hidden="true"></span>
				</button>
			<?php endif; ?>
		</div>
	</td>
	<?php endif; ?>
</tr>
