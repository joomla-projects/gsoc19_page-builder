<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

/**
 * Interface for a list model.
 *
 * @since  4.0.0
 */
interface ListModelInterface
{
	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items
	 *
	 * @since   4.0.0
	 *
	 * @throws \Exception
	 */
	public function getItems();
}
