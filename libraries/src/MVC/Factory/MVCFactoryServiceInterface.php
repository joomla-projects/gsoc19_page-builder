<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

\defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a MVC factory.
 *
 * @since  4.0.0
 */
interface MVCFactoryServiceInterface
{
	/**
	 * Get the factory.
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since   4.0.0
	 * @throws  \UnexpectedValueException May be thrown if the factory has not been set.
	 */
	public function getMVCFactory(): MVCFactoryInterface;
}
