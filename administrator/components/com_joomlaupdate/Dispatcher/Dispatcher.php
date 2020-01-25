<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_admin
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Joomla Update is checked for global core.admin rights - not the usual core.manage for the component
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
		// Check the user has permission to access this component if in the backend
		if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.admin'))
		{
			throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
