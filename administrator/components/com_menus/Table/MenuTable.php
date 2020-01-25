<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Menu table
 *
 * @since  1.6
 */
class MenuTable extends \JTableMenu
{
	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function delete($pk = null, $children = false)
	{
		$return = parent::delete($pk, $children);

		if ($return)
		{
			// Delete key from the #__modules_menu table
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__modules_menu'))
				->where($db->quoteName('menuid') . ' = :pk')
				->bind(':pk', $pk, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();
		}

		return $return;
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     JTable::check
	 * @since   4.0.0
	 */
	public function check()
	{
		$return = parent::check();

		if ($return)
		{
			// Set publish_up to null date if not set
			if (!$this->publish_up)
			{
				$this->publish_up = null;
			}

			// Set publish_down to null date if not set
			if (!$this->publish_down)
			{
				$this->publish_down = null;
			}

			// Check the publish down date is not earlier than publish up.
			if (!is_null($this->publish_down) && !is_null($this->publish_up) && $this->publish_down < $this->publish_up)
			{
				$this->setError(Text::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));

				return false;
			}

			if ((int) $this->home)
			{
				// Set the publish down/up always for home.
				$this->publish_up   = null;
				$this->publish_down = null;
			}
		}

		return $return;
	}
}
