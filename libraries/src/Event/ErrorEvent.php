<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

\defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;

/**
 * Event class for representing the application's `onError` event
 *
 * @since  4.0.0
 */
class ErrorEvent extends AbstractEvent
{
	/**
	 * Get the event's application object
	 *
	 * @return  AbstractApplication
	 *
	 * @since   4.0.0
	 */
	public function getApplication(): AbstractApplication
	{
		return $this->arguments['application'];
	}

	/**
	 * Get the event's error object
	 *
	 * @return  \Throwable
	 *
	 * @since   4.0.0
	 */
	public function getError(): \Throwable
	{
		return $this->getArgument('subject');
	}

	/**
	 * Set the event's error object
	 *
	 * @param   \Throwable  $error  The new error to process
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setError(\Throwable $error)
	{
		$this->setArgument('subject', $error);
	}
}
