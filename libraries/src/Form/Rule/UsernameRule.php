<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  1.7.0
 */
class UsernameRule extends FormRule
{
	/**
	 * Method to test the username for uniqueness.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   1.7.0
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		// Get the database object and a new query object.
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Get the extra field check attribute.
		$userId = ($form instanceof Form) ? (int) $form->getValue('id') : 0;

		// Build the query.
		$query->select('COUNT(*)')
			->from($db->quoteName('#__users'))
			->where(
				[
					$db->quoteName('username') . ' = :username',
					$db->quoteName('id') . ' <> :userId',
				]
			)
			->bind(':username', $value)
			->bind(':userId', $userId, ParameterType::INTEGER);

		// Set and query the database.
		$db->setQuery($query);
		$duplicate = (bool) $db->loadResult();

		if ($duplicate)
		{
			return false;
		}

		return true;
	}
}
