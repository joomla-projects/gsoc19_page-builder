<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.7.0
 */
class ComponentsField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since  3.7.0
	 */
	protected $type = 'Components';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of JHtml options.
	 *
	 * @since   2.5.0
	 */
	protected function getOptions()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('name', 'text'),
					$db->quoteName('element', 'value'),
				]
			)
			->from($db->quoteName('#__extensions'))
			->where(
				[
					$db->quoteName('enabled') . ' >= 1',
					$db->quoteName('type') . ' = ' . $db->quote('component'),
				]
			);

		$items = $db->setQuery($query)->loadObjectList();

		if ($items)
		{
			$lang = Factory::getLanguage();

			foreach ($items as &$item)
			{
				// Load language
				$extension = $item->value;

				$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, true)
					|| $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension, null, false, true);

				// Translate component name
				$item->text = Text::_($item->text);
			}

			// Sort by component name
			$items = ArrayHelper::sortObjects($items, 'text', 1, true, true);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $items);

		return $options;
	}
}
