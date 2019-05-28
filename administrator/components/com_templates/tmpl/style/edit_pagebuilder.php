<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

// Add JavaScript
HTMLHelper::_('script', 'media/com_templates/js/pagebuilder.js');

// Add stylesheet
HTMLHelper::_('stylesheet', 'media/com_templates/css/pagebuilder.css');

$fieldset = $this->form->getFieldset('pagebuilder');
foreach ($fieldset as $field) {
    echo $field->renderField();
}
?>
<div id="com-templates"></div>
