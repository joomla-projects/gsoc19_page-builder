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
 * Interface for a base model.
 *
 * @since  4.0.0
 */
interface ModelInterface
{
	/**
	 * Method to get the model name.
	 *
	 * @return  string  The name of the model
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getName();
}
