<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

/**
 * Utility class working with content language select lists
 *
 * @since  1.6
 */
abstract class JHtmlContentLanguage
{
	/**
	 * Cached array of the content language items.
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected static $items = null;

	/**
	 * Get a list of the available content language items.
	 *
	 * @param   boolean  $all        True to include All (*)
	 * @param   boolean  $translate  True to translate All
	 *
	 * @return  string
	 *
	 * @see     \Joomla\CMS\Form\Field\ContentlanguageField
	 * @since   1.6
	 */
	public static function existing($all = false, $translate = false)
	{
		if (empty(static::$items))
		{
			// Get the database object and a new query object.
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);

			// Build the query.
			$query->select(
				[
					$db->quoteName('a.lang_code', 'value'),
					$db->quoteName('a.title', 'text'),
					$db->quoteName('a.title_native'),
				]
			)
				->from($db->quoteName('#__languages', 'a'))
				->where($db->quoteName('a.published') . ' >= 0')
				->order($db->quoteName('a.title'));

			// Set the query and load the options.
			$db->setQuery($query);
			static::$items = $db->loadObjectList();
		}

		if ($all)
		{
			$all_option = array(new CMSObject(array('value' => '*', 'text' => $translate ? Text::alt('JALL', 'language') : 'JALL_LANGUAGE')));

			return array_merge($all_option, static::$items);
		}
		else
		{
			return static::$items;
		}
	}
}
