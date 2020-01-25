<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

/**
 * Versions helper class, provides methods to perform various tasks relevant
 * versioning of content.
 *
 * @since  3.2
 */
class ContentHistoryHelper extends CMSHelper
{
	/**
	 * Alias for storing type in versions table
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $typeAlias = null;

	/**
	 * Constructor
	 *
	 * @param   string  $typeAlias  The type of content to be versioned (for example, 'com_content.article').
	 *
	 * @since   3.2
	 */
	public function __construct($typeAlias = null)
	{
		$this->typeAlias = $typeAlias;
	}

	/**
	 * Method to delete the history for an item.
	 *
	 * @param   Table  $table  Table object being versioned
	 *
	 * @return  boolean  true on success, otherwise false.
	 *
	 * @since   3.2
	 */
	public function deleteHistory($table)
	{
		$id     = (int) $table->{$table->getKeyName()};
		$typeId = Table::getInstance('Contenttype', 'JTable')->getTypeId($this->typeAlias);
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true)
			->delete($db->quoteName('#__ucm_history'))
			->where(
				[
					$db->quoteName('ucm_item_id') . ' = :id',
					$db->quoteName('ucm_type_id') . ' = :typeId',
				]
			)
			->bind(':id', $id, ParameterType::INTEGER)
			->bind(':typeId', $typeId, ParameterType::INTEGER);
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Method to get a list of available versions of this item.
	 *
	 * @param   integer  $typeId  Type id for this component item.
	 * @param   mixed    $id      Primary key of row to get history for.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   3.2
	 */
	public function getHistory($typeId, $id)
	{
		// Cast as integer until method is typehinted.
		$typeId = (int) $typeId;
		$id     = (int) $id;

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(['h.version_note', 'h.save_date', 'u.name']))
			->from($db->quoteName('#__ucm_history', 'h'))
			->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('h.editor_user_id'))
			->where(
				[
					$db->quoteName('h.ucm_item_id') . ' = :id',
					$db->quoteName('h.ucm_type_id') . ' = :typeId',
				]
			)
			->bind(':id', $id, ParameterType::INTEGER)
			->bind(':typeId', $typeId, ParameterType::INTEGER)
			->order($db->quoteName('h.save_date') . ' DESC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to save a version snapshot to the content history table.
	 *
	 * @param   Table  $table  Table object being versioned
	 *
	 * @return  boolean  True on success, otherwise false.
	 *
	 * @since   3.2
	 */
	public function store($table)
	{
		$dataObject = $this->getDataObject($table);
		$historyTable = Table::getInstance('Contenthistory', 'JTable');
		$typeTable = Table::getInstance('Contenttype', 'JTable');
		$typeTable->load(array('type_alias' => $this->typeAlias));
		$historyTable->set('ucm_type_id', $typeTable->type_id);

		$key = $table->getKeyName();
		$historyTable->set('ucm_item_id', $table->$key);

		// Don't store unless we have a non-zero item id
		if (!$historyTable->ucm_item_id)
		{
			return true;
		}

		$historyTable->set('version_data', json_encode($dataObject));
		$input = Factory::getApplication()->input;
		$data = $input->get('jform', array(), 'array');
		$versionName = false;

		if (isset($data['version_note']))
		{
			$versionName = InputFilter::getInstance()->clean($data['version_note'], 'string');
			$historyTable->set('version_note', $versionName);
		}

		// Don't save if hash already exists and same version note
		$historyTable->set('sha1_hash', $historyTable->getSha1($dataObject, $typeTable));

		if ($historyRow = $historyTable->getHashMatch())
		{
			if (!$versionName || ($historyRow->version_note === $versionName))
			{
				return true;
			}
			else
			{
				// Update existing row to set version note
				$historyTable->set('version_id', $historyRow->version_id);
			}
		}

		$result = $historyTable->store();

		// Load history_limit config from extension.
		$aliasParts = explode('.', $this->typeAlias);

		$context = $aliasParts[1] ?? '';

		$maxVersionsContext = ComponentHelper::getParams($aliasParts[0])->get('history_limit_' . $context, 0);

		if ($maxVersionsContext)
		{
			$historyTable->deleteOldVersions($maxVersionsContext);
		}
		elseif ($maxVersions = ComponentHelper::getParams($aliasParts[0])->get('history_limit', 0))
		{
			$historyTable->deleteOldVersions($maxVersions);
		}

		return $result;
	}
}
