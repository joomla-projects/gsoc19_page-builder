<?php
/**
 * @package     Joomla.API
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Contenthistory\Administrator\Model\HistoryModel;
use Joomla\CMS\MVC\Controller\Exception;
use Joomla\CMS\Language\Text;

/**
 * The history controller
 *
 * @since  4.0.0
 */
class HistoryController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'history';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'history';

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$this->input->set('model_state', [
				'type_alias'     => $this->getTypeAliasFromInput(),
				'type_id'        => $this->getTypeIdFromInput(),
				'item_id'        => $this->getItemIdFromInput(),
				'list.ordering'  => 'h.save_date',
				'list.direction' => 'DESC',
			]
		);

		return parent::displayList();
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function keep()
	{
		/** @var HistoryModel $model */
		$model = $this->getModel($this->contentType);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$recordId = $this->input->getInt('id');

		if (!$recordId)
		{
			throw new Exception\ResourceNotFound(Text::_('JLIB_APPLICATION_ERROR_RECORD'), 404);
		}

		$cid = [$recordId];

		if (!$model->keep($cid))
		{
			throw new Exception\Save(Text::plural('COM_CONTENTHISTORY_N_ITEMS_KEEP_TOGGLE', count($cid)));
		}

		return $this;
	}

	/**
	 * Get item id from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getItemIdFromInput()
	{
		return $this->input->exists('id') ?
			$this->input->get('id') : $this->input->post->get('id');
	}

	/**
	 * Get type id from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getTypeIdFromInput()
	{
		return $this->input->exists('type_id') ?
			$this->input->get('type_id') : $this->input->post->get('type_id');
	}

	/**
	 * Get type alias from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getTypeAliasFromInput()
	{
		return $this->input->exists('type_alias') ?
			$this->input->get('type_alias') : $this->input->post->get('type_alias');
	}
}
