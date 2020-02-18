<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'https://unpkg.com/grapesjs');
HTMLHelper::_('stylesheet', 'https://unpkg.com/grapesjs/dist/css/grapes.min.css');
$this->useCoreUI = true;

$user = Factory::getUser();
?>

<!--<form action="--><?php
//echo Route::_('index.php?option=com_templates&layout=edit&id=' . (int) $this->item->id); ?><!--" method="post"-->
<!--	  name="adminForm" id="style-form" class="form-validate">-->
<!---->
<!--	--><?php
//	echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
<!---->
<!--	<div>-->
<!--		--><?php
//		echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>
<!---->
<!--		--><?php
//		echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JDETAILS')); ?>
<!---->
<!--		<div class="row">-->
<!--			<div class="col-lg-9">-->
<!--				<div class="card">-->
<!--					<h2 class="card-header">-->
<!--						--><?php
//						echo Text::_($this->item->template); ?>
<!--					</h2>-->
<!--					<div class="card-body">-->
<!--						<div class="info-labels">-->
<!--							<span class="badge badge-secondary">-->
<!--								--><?php
//								echo $this->item->client_id == 0 ? Text::_('JSITE') : Text::_('JADMINISTRATOR'); ?>
<!--							</span>-->
<!--						</div>-->
<!--						<div>-->
<!--							<p>--><?php
//								echo Text::_($this->item->xml->description); ?><!--</p>-->
<!--							--><?php
//							$this->fieldset = 'description';
//							$description    = LayoutHelper::render('joomla.edit.fieldset', $this);
//							?>
<!--							--><?php
//							if ($description) : ?>
<!--								<p class="readmore">-->
<!--									<a href="#" onclick="document.querySelector('#tab-description').click();">-->
<!--										--><?php
//										echo Text::_('JGLOBAL_SHOW_FULL_DESCRIPTION'); ?>
<!--									</a>-->
<!--								</p>-->
<!--							--><?php
//							endif; ?>
<!--						</div>-->
<!--						--><?php
//						$this->fieldset = 'basic';
//						$html           = LayoutHelper::render('joomla.edit.fieldset', $this);
//						echo $html ? '<hr>' . $html : '';
//						?>
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
<!--			<div class="col-lg-3">-->
<!--				<div class="card">-->
<!--					<div class="card-body">-->
<!--						--><?php
//						// Set main fields.
//						$this->fields = array(
//								'home',
//								'client_id',
//								'template'
//						);
//						?>
<!--						--><?php
//						echo LayoutHelper::render('joomla.edit.global', $this); ?>
<!--					</div>-->
<!--				</div>-->
<!--			</div>-->
<!--		</div>-->
<!--		--><?php
//		echo HTMLHelper::_('uitab.endTab'); ?>
<!---->
<!--		--><?php
//		if ($description) : ?>
<!--			--><?php
//			echo HTMLHelper::_('uitab.addTab', 'myTab', 'description', Text::_('JGLOBAL_FIELDSET_DESCRIPTION')); ?>
<!--			<fieldset id="fieldset-description" class="options-form">-->
<!--				<legend>--><?php
//					echo Text::_('JGLOBAL_FIELDSET_DESCRIPTION'); ?><!--</legend>-->
<!--				<div>-->
<!--					--><?php
//					echo $description; ?>
<!--				</div>-->
<!--			</fieldset>-->
<!--			--><?php
//			echo HTMLHelper::_('uitab.endTab'); ?>
<!--		--><?php
//		endif; ?>
<!---->
<!--		--><?php
//		$this->fieldsets = array();
//		$this->ignore_fieldsets = array('basic', 'description');
//		echo LayoutHelper::render('joomla.edit.params', $this);
//		?>
<!---->
<!--		--><?php
//		if ($user->authorise('core.edit', 'com_menus') && $this->item->client_id == 0 && $this->canDo->get(
//						'core.edit.state'
//				)) : ?>
<!--			--><?php
//			echo HTMLHelper::_('uitab.addTab', 'myTab', 'assignment', Text::_('COM_TEMPLATES_MENUS_ASSIGNMENT')); ?>
<!--			<fieldset id="fieldset-assignment" class="options-form">-->
<!--				<legend>--><?php
//					echo Text::_('COM_TEMPLATES_MENUS_ASSIGNMENT'); ?><!--</legend>-->
<!--				<div>-->
<!--					--><?php
//					echo $this->loadTemplate('assignment'); ?>
<!--				</div>-->
<!--			</fieldset>-->
<!--			--><?php
//			echo HTMLHelper::_('uitab.endTab'); ?>
<!--		--><?php
//		endif; ?>
<!---->
<!--		--><?php
//		echo HTMLHelper::_('uitab.endTabSet'); ?>
<!---->
<!--		<input type="hidden" name="task" value="">-->
<!--		--><?php
//		echo HTMLHelper::_('form.token'); ?>
<!--	</div>-->
<!--</form>-->

<div class="panel__top">
	<div class="panel__basic-actions"></div>
	<div class="panel__devices"></div>
	<div class="panel__switcher"></div>
</div>
<div class="editor-row">
	<div class="editor-canvas">
		<div id="gjs">
			<h1>Hello World Component!</h1>
		</div>
	</div>
	<div class="panel__right">
		<div class="layers-container"></div>
		<div class="styles-container"></div>
		<div class="traits-container"></div>
	</div>
</div>
<div id="blocks"></div>

<script type="application/javascript">
	window.addEventListener('DOMContentLoaded', (event) => {
		const editor = grapesjs.init({
			// Indicate where to init the editor. You can also pass an HTMLElement
			container: '#gjs',
			// Get the content for the canvas directly from the element
			// As an alternative we could use: `components: '<h1>Hello World Component!</h1>'`,
			fromElement: true,
			// Size of the editor
			height: '300px',
			width: 'auto',
			blockManager: {
				appendTo: '#blocks',
				blocks: [
					{
						id: 'section', // id is mandatory
						label: '<b>Section</b>', // You can use HTML/SVG inside labels
						attributes: { class:'gjs-block-section' },
						content: `<section>
          <h1>This is a simple title</h1>
          <div>This is just a Lorem text: Lorem ipsum dolor sit amet</div>
        </section>`,
					}, {
						id: 'text',
						label: 'Text',
						content: '<div data-gjs-type="text">Insert your text here</div>',
					}, {
						id: 'image',
						label: 'Image',
						// Select the component once it's dropped
						select: true,
						// You can pass components as a JSON instead of a simple HTML string,
						// in this case we also use a defined component type `image`
						content: { type: 'image' },
						// This triggers `active` event on dropped components and the `image`
						// reacts by opening the AssetManager
						activate: true,
					}
				]
			},
			layerManager: {
				appendTo: '.layers-container'
			},
			// We define a default panel as a sidebar to contain layers
			panels: {
				defaults: [{
					id: 'layers',
					el: '.panel__right',
					// Make the panel resizable
					resizable: {
						maxDim: 350,
						minDim: 200,
						tc: 0, // Top handler
						cl: 1, // Left handler
						cr: 0, // Right handler
						bc: 0, // Bottom handler
						// Being a flex child we need to change `flex-basis` property
						// instead of the `width` (default)
						keyWidth: 'flex-basis',
					},
				},
				{
					id: 'panel-switcher',
					el: '.panel__switcher',
					buttons: [{
						id: 'show-layers',
						active: true,
						label: 'Layers',
						command: 'show-layers',
						// Once activated disable the possibility to turn it off
						togglable: false,
					}, {
						id: 'show-style',
						active: true,
						label: 'Styles',
						command: 'show-styles',
						togglable: false,
					},
						{
							id: 'show-traits',
							active: true,
							label: 'Traits',
							command: 'show-traits',
							togglable: false,
						}
					],
				},
					{
						id: 'panel-devices',
						el: '.panel__devices',
						buttons: [{
							id: 'device-desktop',
							label: 'D',
							command: 'set-device-desktop',
							active: true,
							togglable: false,
						}, {
							id: 'device-mobile',
							label: 'M',
							command: 'set-device-mobile',
							togglable: false,
						}],
					}
				]
			},
			// The Selector Manager allows to assign classes and
			// different states (eg. :hover) on components.
			// Generally, it's used in conjunction with Style Manager
			// but it's not mandatory
			selectorManager: {
				appendTo: '.styles-container'
			},
			styleManager: {
				appendTo: '.styles-container',
				sectors: [{
					name: 'Dimension',
					open: false,
					// Use built-in properties
					buildProps: ['width', 'min-height', 'padding'],
					// Use `properties` to define/override single property
					properties: [
						{
							// Type of the input,
							// options: integer | radio | select | color | slider | file | composite | stack
							type: 'integer',
							name: 'The width', // Label for the property
							property: 'width', // CSS property (if buildProps contains it will be extended)
							units: ['px', '%'], // Units, available only for 'integer' types
							defaults: 'auto', // Default value
							min: 0, // Min value, available only for 'integer' types
						}
					]
				},{
					name: 'Extra',
					open: false,
					buildProps: ['background-color', 'box-shadow', 'custom-prop'],
					properties: [
						{
							id: 'custom-prop',
							name: 'Custom Label',
							property: 'font-size',
							type: 'select',
							defaults: '32px',
							// List of options, available only for 'select' and 'radio'  types
							options: [
								{ value: '12px', name: 'Tiny' },
								{ value: '18px', name: 'Medium' },
								{ value: '32px', name: 'Big' },
							],
						}
					]
				}]
			},
			traitManager: {
				appendTo: '.traits-container',
			},
			mediaCondition: 'min-width', // default is `max-width`
			deviceManager: {
				devices: [{
					name: 'Mobile',
					width: '320',
					widthMedia: '',
				}, {
					name: 'Desktop',
					width: '',
					widthMedia:'1024',
				}]
			},
			storageManager: {
				id: 'gjs-',             // Prefix identifier that will be used inside storing and loading
				type: 'local',          // Type of the storage
				autosave: true,         // Store data automatically
				autoload: true,         // Autoload stored data on init
				stepsBeforeSave: 1,     // If autosave enabled, indicates how many changes are necessary before store method is triggered
				storeComponents: true,  // Enable/Disable storing of components in JSON format
				storeStyles: true,      // Enable/Disable storing of rules in JSON format
				storeHtml: true,        // Enable/Disable storing of components as HTML string
				storeCss: true,         // Enable/Disable storing of rules as CSS string
			}
		});

		// Set initial device as Mobile
		editor.setDevice('Mobile');

		editor.Panels.addPanel({
			id: 'panel-top',
			el: '.panel__top',
		});
		editor.Panels.addPanel({
			id: 'basic-actions',
			el: '.panel__basic-actions',
			buttons: [
				{
					id: 'visibility',
					active: true, // active by default
					className: 'btn-toggle-borders',
					label: '<u>B</u>',
					command: 'sw-visibility', // Built-in command
				}, {
					id: 'export',
					className: 'btn-open-export',
					label: 'Exp',
					command: 'export-template',
					context: 'export-template', // For grouping context of buttons from the same panel
				}, {
					id: 'show-json',
					className: 'btn-show-json',
					label: 'JSON',
					context: 'show-json',
					command(editor) {
						editor.Modal.setTitle('Components JSON')
								.setContent(`<textarea style="width:100%; height: 250px;">
            ${JSON.stringify(editor.getComponents())}
          </textarea>`)
								.open();
					},
				}
			],
		});

		editor.on('run:export-template:before', opts => {
			console.log('Before the command run');
			if (0 /* some condition */) {
				opts.abort = 1;
			}
		});
		editor.on('run:export-template', () => console.log('After the command run'));
		editor.on('abort:export-template', () => console.log('Command aborted'));

		// Define commands
		editor.Commands.add('show-layers', {
			getRowEl(editor) { return editor.getContainer().closest('.editor-row'); },
			getLayersEl(row) { return row.querySelector('.layers-container') },

			run(editor, sender) {
				const lmEl = this.getLayersEl(this.getRowEl(editor));
				lmEl.style.display = '';
			},
			stop(editor, sender) {
				const lmEl = this.getLayersEl(this.getRowEl(editor));
				lmEl.style.display = 'none';
			},
		});
		editor.Commands.add('show-styles', {
			getRowEl(editor) { return editor.getContainer().closest('.editor-row'); },
			getStyleEl(row) { return row.querySelector('.styles-container') },

			run(editor, sender) {
				const smEl = this.getStyleEl(this.getRowEl(editor));
				smEl.style.display = '';
			},
			stop(editor, sender) {
				const smEl = this.getStyleEl(this.getRowEl(editor));
				smEl.style.display = 'none';
			},
		});
		editor.Commands.add('show-traits', {
			getTraitsEl(editor) {
				const row = editor.getContainer().closest('.editor-row');
				return row.querySelector('.traits-container');
			},
			run(editor, sender) {
				this.getTraitsEl(editor).style.display = '';
			},
			stop(editor, sender) {
				this.getTraitsEl(editor).style.display = 'none';
			},
		});

		// Commands
		editor.Commands.add('set-device-desktop', {
			run: editor => editor.setDevice('Desktop')
		});
		editor.Commands.add('set-device-mobile', {
			run: editor => editor.setDevice('Mobile')
		});

	});

</script>
<style type="text/css">
	/* Let's highlight canvas boundaries */
	#gjs {
		border: 3px solid #444;
	}

	/* Reset some default styling */
	.gjs-cv-canvas {
		top: 0;
		width: 100%;
		height: 100%;
	}

	.gjs-block {
		width: auto;
		height: auto;
		min-height: auto;
	}
	.panel__top {
		padding: 0;
		width: 100%;
		display: flex;
		position: initial;
		justify-content: center;
		justify-content: space-between;
	}
	.panel__basic-actions {
		position: initial;
	}

	.editor-row {
		display: flex;
		justify-content: flex-start;
		align-items: stretch;
		flex-wrap: nowrap;
		height: 300px;
	}

	.editor-canvas {
		flex-grow: 1;
	}

	.panel__right {
		flex-basis: 230px;
		position: relative;
		overflow-y: auto;
	}
	.panel__switcher {
		position: initial;
	}

	.panel__devices {
		position: initial;
	}

</style>
