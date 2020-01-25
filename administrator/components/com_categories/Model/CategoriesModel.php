<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Categories Component Categories Model
 *
 * @since  1.6
 */
class CategoriesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'language', 'a.language', 'language_title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_time', 'a.created_time',
				'created_user_id', 'a.created_user_id',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
				'level', 'a.level',
				'path', 'a.path',
				'tag',
			);
		}

		if (Associations::isEnabled())
		{
			$config['filter_fields'][] = 'association';
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.lft', $direction = 'asc')
	{
		$app = Factory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Adjust the context to support forced languages.
		if ($forcedLanguage)
		{
			$this->context .= '.' . $forcedLanguage;
		}

		$extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', 'com_content', 'cmd');

		$this->setState('filter.extension', $extension);
		$parts = explode('.', $extension);

		// Extract the component name
		$this->setState('filter.component', $parts[0]);

		// Extract the optional section name
		$this->setState('filter.section', (count($parts) > 1) ? $parts[1] : null);

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language.
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.extension');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . $this->getState('filter.tag');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a database query to list categories.
	 *
	 * @return  \JDatabaseQuery object.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.note, a.published, a.access' .
				', a.checked_out, a.checked_out_time, a.created_user_id' .
				', a.path, a.parent_id, a.level, a.lft, a.rgt' .
				', a.language'
			)
		);
		$query->from($db->quoteName('#__categories', 'a'));

		// Join over the language
		$query->select(
			[
				$db->quoteName('l.title', 'language_title'),
				$db->quoteName('l.image', 'language_image'),
			]
		)
			->join(
				'LEFT',
				$db->quoteName('#__languages', 'l'),
				$db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language')
			);

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join(
				'LEFT',
				$db->quoteName('#__users', 'uc'),
				$db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out')
			);

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join(
				'LEFT',
				$db->quoteName('#__viewlevels', 'ag'),
				$db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access')
			);

		// Join over the users for the author.
		$query->select($db->quoteName('ua.name', 'author_name'))
			->join(
				'LEFT',
				$db->quoteName('#__users', 'ua'),
				$db->quoteName('ua.id') . ' = ' . $db->quoteName('a.created_user_id')
			);

		// Join over the associations.
		$assoc = $this->getAssoc();

		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->join(
					'LEFT',
					$db->quoteName('#__associations', 'asso'),
					$db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('asso.context') . ' = ' . $db->quote('com_categories.item')
				)
				->join(
					'LEFT',
					$db->quoteName('#__associations', 'asso2'),
					$db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key')
				)
				->group('a.id, l.title, uc.name, ag.title, ua.name');
		}

		// Filter by extension
		if ($extension = $this->getState('filter.extension'))
		{
			$query->where($db->quoteName('a.extension') . ' = :extension')
				->bind(':extension', $extension);
		}

		// Filter on the level.
		if ($level = (int) $this->getState('filter.level'))
		{
			$query->where($db->quoteName('a.level') . ' <= :level')
				->bind(':level', $level, ParameterType::INTEGER);
		}

		// Filter by access level.
		if ($access = (int) $this->getState('filter.access'))
		{
			$query->where($db->quoteName('a.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = $user->getAuthorisedViewLevels();
			$query->whereIn($db->quoteName('a.access'), $groups);
		}

		// Filter by published state
		$published = (string) $this->getState('filter.published');

		if (is_numeric($published))
		{
			$published = (int) $published;
			$query->where($db->quoteName('a.published') . ' = :published')
				->bind(':published', $published, ParameterType::INTEGER);
		}
		elseif ($published === '')
		{
			$query->whereIn($db->quoteName('a.published'), [0, 1]);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$search = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :search')
					->bind(':search', $search, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . str_replace(' ', '%', trim($search)) . '%';
				$query->extendWhere(
					'AND',
					[
						$db->quoteName('a.title') . ' LIKE :title',
						$db->quoteName('a.alias') . ' LIKE :alias',
						$db->quoteName('a.note') . ' LIKE :note',
					],
					'OR'
				)
					->bind(':title', $search)
					->bind(':alias', $search)
					->bind(':note', $search);
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('a.language') . ' = :language')
				->bind(':language', $language);
		}

		// Filter by a single tag.
		$tagId = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$tagId = (int) $tagId;
			$typeAlias = $extension . '.category';
			$query->where($db->quoteName('tagmap.tag_id') . ' = :tagid')
				->bind(':tagid', $tagId, ParameterType::INTEGER)
				->join(
					'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = :typealias'
				)
				->bind(':typealias', $typeAlias);
		}

		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'a.lft');
		$listDirn = $db->escape($this->getState('list.direction', 'ASC'));

		if ($listOrdering == 'a.access')
		{
			$query->order('a.access ' . $listDirn . ', a.lft ' . $listDirn);
		}
		else
		{
			$query->order($db->escape($listOrdering) . ' ' . $listDirn);
		}

		// Group by on Categories for \JOIN with component tables to count items
		$query->group('a.id,
				a.title,
				a.alias,
				a.note,
				a.published,
				a.access,
				a.checked_out,
				a.checked_out_time,
				a.created_user_id,
				a.path,
				a.parent_id,
				a.level,
				a.lft,
				a.rgt,
				a.language,
				l.title,
				l.image,
				uc.name,
				ag.title,
				ua.name'
		);

		return $query;
	}

	/**
	 * Method to determine if an association exists
	 *
	 * @return  boolean  True if the association exists
	 *
	 * @since   3.0
	 */
	public function getAssoc()
	{
		static $assoc = null;

		if (!is_null($assoc))
		{
			return $assoc;
		}

		$extension = $this->getState('filter.extension');

		$assoc = Associations::isEnabled();
		$extension = explode('.', $extension);
		$component = array_shift($extension);
		$cname = str_replace('com_', '', $component);

		if (!$assoc || !$component || !$cname)
		{
			$assoc = false;

			return $assoc;
		}

		$componentObject = $this->bootComponent($component);

		if ($componentObject instanceof AssociationServiceInterface && $componentObject instanceof CategoryServiceInterface)
		{
			$assoc = true;

			return $assoc;
		}

		$hname = $cname . 'HelperAssociation';
		\JLoader::register($hname, JPATH_SITE . '/components/' . $component . '/helpers/association.php');

		$assoc = class_exists($hname) && !empty($hname::$category_association);

		return $assoc;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   3.0.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items != false)
		{
			$extension = $this->getState('filter.extension');

			$this->countItems($items, $extension);
		}

		return $items;
	}

	/**
	 * Method to load the countItems method from the extensions
	 *
	 * @param   \stdClass[]  $items      The category items
	 * @param   string       $extension  The category extension
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function countItems(&$items, $extension)
	{
		$parts     = explode('.', $extension, 2);
		$section   = '';

		if (count($parts) > 1)
		{
			$section = $parts[1];
		}

		$component = Factory::getApplication()->bootComponent($parts[0]);

		if ($component instanceof CategoryServiceInterface)
		{
			$component->countItems($items, $section);
		}
	}
}
