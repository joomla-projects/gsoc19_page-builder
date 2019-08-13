<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;

/**
 * Form Field class for the Joomla Platform.
 *
 * Provides an editor to build up a HTML and CSS layout for frontend rendering.
 *
 * @since  4.0
 */
class PagebuilderField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type = 'Pagebuilder';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $layout = 'joomla.form.field.pagebuilder';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   4.0
	 */
	protected function getInput()
	{
		// Value is in json format, so decode double-quotes for html value="..."
		$this->value = htmlspecialchars($this->value);

		return parent::getInput();
	}
}
