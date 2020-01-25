<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Service\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

/**
 * Utility class for creating HTML Grids.
 *
 * @since  1.5
 */
class AdministratorService
{
	/**
	 * Get the associated language flags
	 *
	 * @param   int  $newsfeedid  The item id to search associations
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  \Exception  Throws a 500 Exception on Database failure
	 */
	public function association($newsfeedid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = Associations::getAssociations('com_newsfeeds', '#__newsfeeds', 'com_newsfeeds.item', $newsfeedid))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated newsfeed items
			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query
				->select(
					[
						$db->quoteName('c.id'),
						$db->quoteName('c.name', 'title'),
						$db->quoteName('cat.title', 'category_title'),
						$db->quoteName('l.sef', 'lang_sef'),
						$db->quoteName('l.lang_code'),
						$db->quoteName('l.image'),
						$db->quoteName('l.title', 'language_title'),
					]
				)
				->from($db->quoteName('#__newsfeeds', 'c'))
				->join('LEFT', $db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
				->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('c.language') . ' = ' . $db->quoteName('l.lang_code'))
				->where(
					[
						$db->quoteName('c.id') . ' IN (' . implode(',', $query->bindArray(array_values($associations))) . ')',
						$db->quoteName('c.id') . ' != :id',
					]
				)
				->bind(':id', $newsfeedid, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500);
			}

			if ($items)
			{
				$languages = LanguageHelper::getContentLanguages(array(0, 1));
				$content_languages = array_column($languages, 'lang_code');

				foreach ($items as &$item)
				{
					if (in_array($item->lang_code, $content_languages))
					{
						$text    = strtoupper($item->lang_sef);
						$url     = Route::_('index.php?option=com_newsfeeds&task=newsfeed.edit&id=' . (int) $item->id);
						$tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
							. htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
						$classes = 'badge badge-secondary';

						$item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
							. '<div role="tooltip" id="tip-' . (int) $newsfeedid . '-' . (int) $item->id . '">' . $tooltip . '</div>';
					}
					else
					{
						// Display warning if Content Language is trashed or deleted
						Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item->lang_code), 'warning');
					}
				}
			}

			$html = LayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
}
