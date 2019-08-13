<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Layout variables in
 * @var   array  $displayData
 *
 * -----------------
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   string   $name            Name of the input field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   string   $value           Value attribute of the field.
 */
extract($displayData);

// Add JavaScript
HTMLHelper::_('script', 'media/system/js/fields/pagebuilder/pagebuilder.js');

// Add stylesheet
HTMLHelper::_('stylesheet', 'media/system/css/fields/pagebuilder.css');

// Include plugins for pagebuilder elements like container or columns
PluginHelper::importPlugin('pagebuilder');
$pluginElements = Factory::getApplication()->triggerEvent(
	'onAddElement', array('param')
);

$options = array(
	'disabled' => $disabled,
	'elements' => $pluginElements,
	'id' => $id,
	// Images to select new elements
	'images' => array(
		'row12'   => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_12.png', 'row12'),
		'row84'   => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_8_4.png', 'row84'),
		'row66'   => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_6_6.png', 'row66'),
		'row48'   => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_4_8.png', 'row48'),
		'row444'  => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_4_4_4.png', 'row444'),
		'row363'  => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_3_6_3.png', 'row363'),
		'row3333' => HTMLHelper::_('image', 'media/system/images/pagebuilder/row_3_3_3_3.png', 'row3333'),
	),
	'readonly' => $readonly
);

Factory::getDocument()->addScriptOptions('system.pagebuilder', $options);

Text::script('COM_TEMPLATES_ADD', true);
Text::script('COM_TEMPLATES_ADD_CLASS', true);
Text::script('COM_TEMPLATES_ADD_COLUMN', true);
Text::script('COM_TEMPLATES_ADD_ELEMENT', true);
Text::script('COM_TEMPLATES_ADD_ELEMENT_DESC', true);
Text::script('COM_TEMPLATES_ADD_GRID', true);
Text::script('COM_TEMPLATES_ADD_MODULE', true);
Text::script('COM_TEMPLATES_ADD_OFFSET', true);
Text::script('COM_TEMPLATES_CLOSE', true);
Text::script('COM_TEMPLATES_COMPONENT', true);
Text::script('COM_TEMPLATES_CUSTOM', true);
Text::script('COM_TEMPLATES_DEVICE_XS', true);
Text::script('COM_TEMPLATES_DEVICE_SM', true);
Text::script('COM_TEMPLATES_DEVICE_MD', true);
Text::script('COM_TEMPLATES_DEVICE_LG', true);
Text::script('COM_TEMPLATES_DEVICE_XL', true);
Text::script('COM_TEMPLATES_DELETE_COLUMN', true);
Text::script('COM_TEMPLATES_DELETE_GRID', true);
Text::script('COM_TEMPLATES_EDIT_COLUMN', true);
Text::script('COM_TEMPLATES_EDIT', true);
Text::script('COM_TEMPLATES_EDIT_POSITION', true);
Text::script('COM_TEMPLATES_HORZ', true);
Text::script('COM_TEMPLATES_HTML5', true);
Text::script('COM_TEMPLATES_INSERT', true);
Text::script('COM_TEMPLATES_MAX_COLUMN_SIZE', true);
Text::script('COM_TEMPLATES_MESSAGE', true);
Text::script('COM_TEMPLATES_NO_CHILD_ALLOWED', true);
Text::script('COM_TEMPLATES_NONE', true);
Text::script('COM_TEMPLATES_OFFSET', true);
Text::script('COM_TEMPLATES_OUTLINE', true);
Text::script('COM_TEMPLATES_POSITION_NAME', true);
Text::script('COM_TEMPLATES_ROUNDED', true);
Text::script('COM_TEMPLATES_SELECT_DEVICE', true);
Text::script('COM_TEMPLATES_SELECT_ELEMENT', true);
Text::script('COM_TEMPLATES_SELECT_LAYOUT', true);
Text::script('COM_TEMPLATES_SELECT_MODULE_CHROME', true);
Text::script('COM_TEMPLATES_SETTINGS', true);
Text::script('COM_TEMPLATES_TABLE', true);
Text::script('COM_TEMPLATES_VIEW', true);
Text::script('COM_TEMPLATES_XHTML', true);
Text::script('JCANCEL', true);
Text::script('JSEARCH_RESET', true);
Text::script('JTOOLBAR_BACK', true);
Text::script('JTOOLBAR_CLOSE', true);
?>

<input type="text" id="<?php echo $id ?>" name="<?php echo $name ?>" class="hidden" value="<?php echo $value ?>">

<!-- Create the editor with Vue.js -->
<div id="pagebuilder-editor"></div>
