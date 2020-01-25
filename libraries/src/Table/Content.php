<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Content table
 *
 * @since  1.5
 */
class Content extends Table
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  A database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_content.article';
		parent::__construct('#__content', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_content.article.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Method to get the parent asset id for the record
	 *
	 * @param   Table    $table  A Table object (optional) for the asset parent
	 * @param   integer  $id     The id (optional) of the content.
	 *
	 * @return  integer
	 *
	 * @since   1.6
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$assetId = null;

		// This is an article under a category.
		if ($this->catid)
		{
			// Build the query to get the asset id for the parent category.
			$query = $this->_db->getQuery(true)
				->select($this->_db->quoteName('asset_id'))
				->from($this->_db->quoteName('#__categories'))
				->where($this->_db->quoteName('id') . ' = ' . (int) $this->catid);

			// Get the asset id from the database.
			$this->_db->setQuery($query);

			if ($result = $this->_db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error string
	 *
	 * @see     Table::bind()
	 * @since   1.6
	 */
	public function bind($array, $ignore = '')
	{
		// Search for the {readmore} tag and split the text up accordingly.
		if (isset($array['articletext']))
		{
			$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
			$tagPos = preg_match($pattern, $array['articletext']);

			if ($tagPos == 0)
			{
				$this->introtext = $array['articletext'];
				$this->fulltext = '';
			}
			else
			{
				list ($this->introtext, $this->fulltext) = preg_split($pattern, $array['articletext'], 2);
			}
		}

		if (isset($array['attribs']) && \is_array($array['attribs']))
		{
			$registry = new Registry($array['attribs']);
			$array['attribs'] = (string) $registry;
		}

		if (isset($array['metadata']) && \is_array($array['metadata']))
		{
			$registry = new Registry($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		// Bind the rules.
		if (isset($array['rules']) && \is_array($array['rules']))
		{
			$rules = new Rules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     Table::check()
	 * @since   1.5
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (trim($this->title) == '')
		{
			$this->setError(Text::_('COM_CONTENT_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (trim($this->alias) == '')
		{
			$this->alias = $this->title;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '')
		{
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '')
		{
			$this->fulltext = '';
		}

		/**
		 * Ensure any new items have compulsory fields set. This is needed for things like
		 * frontend editing where we don't show all the fields or using some kind of API
		 */
		if (!$this->id)
		{
			// Images can be an empty json string
			if (!isset($this->images))
			{
				$this->images = '{}';
			}

			// URLs can be an empty json string
			if (!isset($this->urls))
			{
				$this->urls = '{}';
			}

			// Attributes (article params) can be an empty json string
			if (!isset($this->attribs))
			{
				$this->attribs = '{}';
			}

			// Metadata can be an empty json string
			if (!isset($this->metadata))
			{
				$this->metadata = '{}';
			}

			// Hits must be zero on a new item
			$this->hits = 0;
		}

		// Set publish_up to null if not set
		if (!$this->publish_up)
		{
			$this->publish_up = null;
		}

		// Set publish_down to null if not set
		if (!$this->publish_down)
		{
			$this->publish_down = null;
		}

		// Check the publish down date is not earlier than publish up.
		if (!is_null($this->publish_up) && !is_null($this->publish_down) && $this->publish_down < $this->publish_up)
		{
			// Swap the dates.
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey))
		{
			// Only process if not empty

			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", '<', '>');

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, '', $this->metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);

			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->metakey = implode(', ', $clean_keys);
		}
		else
		{
			$this->metakey = '';
		}

		if ($this->metadesc === null)
		{
			$this->metadesc = '';
		}

		return true;
	}

	/**
	 * Overrides Table::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = true)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified_by = $user->get('id');
			$this->modified    = $date->toSql();
		}
		else
		{
			// New article. An article created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}

			// Set modified to created date if not set
			if (!(int) $this->modified)
			{
				$this->modified = $this->created;
			}

			// Set modified_by to created_by user if not set
			if (empty($this->modified_by))
			{
				$this->modified_by = $this->created_by;
			}
		}

		// Verify that the alias is unique
		$table = Table::getInstance('Content', 'JTable', array('dbo' => $this->getDbo()));

		if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(Text::_('JLIB_DATABASE_ERROR_ARTICLE_UNIQUE_ALIAS'));

			return false;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Overrides Table::store to load a row from the database.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
	 *                           If not set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @since   1.7.0
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		if ($ret = parent::load($keys, $reset, false))
		{
			// Load featuerd dates
			$query = $this->_db->getQuery(true)
				->select('featured_up, featured_down')
				->from('#__content_frontpage')
				->where('content_id = ' . (int) $this->id);
			$this->_db->setQuery($query);

			$row = $this->_db->loadAssoc();

			// Check that we have a result.
			if (!empty($row))
			{
				$this->featured_up = $row['featured_up'];
				$this->featured_down = $row['featured_down'];
			}
		}

		return $ret;
	}
}
