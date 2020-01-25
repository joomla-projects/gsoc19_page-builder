<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;

/**
 * Component dispatcher factory interface
 *
 * @since  4.0.0
 */
interface ComponentDispatcherFactoryInterface
{
	/**
	 * Creates a dispatcher.
	 *
	 * @param   CMSApplicationInterface  $application  The application
	 * @param   Input                    $input        The input object, defaults to the one in the application
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   4.0.0
	 */
	public function createDispatcher(CMSApplicationInterface $application, Input $input = null): DispatcherInterface;
}
