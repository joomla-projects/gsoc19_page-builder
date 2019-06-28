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
use Joomla\CMS\Plugin\PluginHelper;

// Add JavaScript
HTMLHelper::_('script', 'media/com_templates/js/pagebuilder.js');

// Add stylesheet
HTMLHelper::_('stylesheet', 'node_modules/vuetify/dist/vuetify.css');
HTMLHelper::_('stylesheet', 'media/com_templates/css/pagebuilder.css');

// Populate the language
$this->loadTemplate('texts');

// Include plugins for new pagebuilder elements like container or columns
PluginHelper::importPlugin('pagebuilder');
$pluginElements = Factory::getApplication()->triggerEvent(
	'onAddElement', array('param')
);

// Images to select new elements
Factory::getDocument()->addScriptOptions('com_templates', array(
		'images' => array(
			'row12'   => HTMLHelper::_('image', 'media/com_templates/images/row_12.png', 'row12'),
			'row84'   => HTMLHelper::_('image', 'media/com_templates/images/row_8_4.png', 'row84'),
			'row66'   => HTMLHelper::_('image', 'media/com_templates/images/row_6_6.png', 'row66'),
			'row48'   => HTMLHelper::_('image', 'media/com_templates/images/row_4_8.png', 'row48'),
			'row444'  => HTMLHelper::_('image', 'media/com_templates/images/row_4_4_4.png', 'row444'),
			'row363'  => HTMLHelper::_('image', 'media/com_templates/images/row_3_6_3.png', 'row363'),
			'row3333' => HTMLHelper::_('image', 'media/com_templates/images/row_3_3_3_3.png', 'row3333'),
		),
		'elements' => $pluginElements
	)
);

$fieldset = $this->form->getFieldset('pagebuilder');

foreach ($fieldset as $field)
{
	echo $field->renderField();
}
?>
<div id="com-templates"></div>
