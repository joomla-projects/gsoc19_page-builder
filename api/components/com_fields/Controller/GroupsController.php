<?php
/**
 * @package     Joomla.API
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The groups controller
 *
 * @since  4.0.0
 */
class GroupsController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'groups';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'groups';

	/**
	 * Basic display of an item view
	 *
	 * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		$this->input->set('model_state', ['filter.context' => $this->getContextFromInput()]);

		return parent::displayItem($id);
	}

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$this->input->set('model_state', ['filter.context' => $this->getContextFromInput()]);

		return parent::displayList();
	}

	/**
	 * Get extension from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getContextFromInput()
	{
		return $this->input->exists('context') ?
			$this->input->get('context') : $this->input->post->get('context');
	}
}
