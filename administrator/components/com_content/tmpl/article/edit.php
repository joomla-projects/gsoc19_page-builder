<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** @var \Joomla\Component\Content\Administrator\View\Article\HtmlView $this */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::_('script', 'com_contenthistory/admin-history-versions.js', ['version' => 'auto', 'relative' => true]);

$this->configFieldsets  = array('editorConfig');
$this->hiddenFieldsets  = array('basic-limited');
$fieldsetsInImages = ['image-intro', 'image-full'];
$fieldsetsInLinks = ['linka', 'linkb', 'linkc'];
$this->ignore_fieldsets = array_merge(array('jmetadata', 'item_associations'), $fieldsetsInImages, $fieldsetsInLinks);
$this->useCoreUI = true;

// Create shortcut to parameters.
$params = clone $this->state->get('params');
$params->merge(new Registry($this->item->attribs));

$app = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();

if (!$assoc)
{
	$this->ignore_fieldsets[] = 'frontendassociations';
}

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_content&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_CONTENT_ARTICLE_CONTENT')); ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body">
						<fieldset class="adminform">
							<?php echo $this->form->getLabel('articletext'); ?>
							<?php echo $this->form->getInput('articletext'); ?>
						</fieldset>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="bg-white px-3">
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php // Do not show the images and links options if the edit form is configured not to. ?>
		<?php if ($params->get('show_urls_images_backend') == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'images', Text::_('COM_CONTENT_FIELDSET_URLS_AND_IMAGES')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
				<?php foreach ($fieldsetsInImages as $fieldset) : ?>
					<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
						<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
						<div>
						<?php echo $this->form->renderFieldset($fieldset); ?>
						</div>
					</fieldset>
				<?php endforeach; ?>
				</div>
				<div class="col-12 col-lg-6">
				<?php foreach ($fieldsetsInLinks as $fieldset) : ?>
					<fieldset id="fieldset-<?php echo $fieldset; ?>" class="options-form">
						<legend><?php echo Text::_($this->form->getFieldsets()[$fieldset]->label); ?></legend>
						<div>
						<?php echo $this->form->renderFieldset($fieldset); ?>
						</div>
					</fieldset>
				<?php endforeach; ?>
				</div>
			</div>

			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ($params->get('show_article_options', 1) == 1) : ?>
			<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
		<?php endif; ?>

		<?php // Do not show the publishing options if the edit form is configured not to. ?>
		<?php if ($params->get('show_publishing_options', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_CONTENT_FIELDSET_PUBLISHING')); ?>
			<div class="row">
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-publishingdata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
						<div>
						<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
						</div>
					</fieldset>
				</div>
				<div class="col-12 col-lg-6">
					<fieldset id="fieldset-metadata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
						<div>
						<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
						</div>
					</fieldset>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if (!$isModal && $assoc && $params->get('show_associations_edit', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
			<fieldset id="fieldset-associations" class="options-form">
			<legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
			<div>
			<?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
			</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php elseif ($isModal && $assoc) : ?>
			<div class="hidden"><?php echo LayoutHelper::render('joomla.edit.associations', $this); ?></div>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin') && $params->get('show_configure_edit_options', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'editor', Text::_('COM_CONTENT_SLIDER_EDITOR_CONFIG')); ?>
			<fieldset id="fieldset-editor" class="form-no-margin options-form">
				<legend><?php echo Text::_('COM_CONTENT_SLIDER_EDITOR_CONFIG'); ?></legend>
				<div class="column-count-md-2 column-count-lg-3">
				<?php echo $this->form->renderFieldset('editorConfig'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin') && $params->get('show_permissions', 1) == 1) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_CONTENT_FIELDSET_RULES')); ?>
			<fieldset id="fieldset-rules" class="options-form">
				<legend><?php echo Text::_('COM_CONTENT_FIELDSET_RULES'); ?></legend>
				<div>
				<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<?php // Creating 'id' hiddenField to cope with com_associations sidebyside loop ?>
		<?php if ($params->get('show_publishing_options', 1) == 0) : ?>
			<?php $hidden_fields = $this->form->getInput('id'); ?>
			<div class="hidden"><?php echo $hidden_fields; ?></div>
		<?php endif; ?>

		<input type="hidden" name="task" value="">
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>">
		<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
