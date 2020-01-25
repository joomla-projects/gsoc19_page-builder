<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;

/**
 * The Categories List Controller
 *
 * @since  1.6
 */
class CategoriesController extends AdminController
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Category', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to get the number of content categories
	 *
	 * @return  string  The JSON-encoded amount of published content categories
	 *
	 * @since   4.0
	 */
	public function getQuickiconContent()
	{
		$model = $this->getModel('Categories');
		$model->setState('filter.published', 1);
		$model->setState('filter.extension', 'com_content');

		$amount = (int) $model->getTotal();

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_CATEGORIES_N_QUICKICON_SRONLY', $amount);
		$result['name'] = Text::plural('COM_CATEGORIES_N_QUICKICON', $amount);

		echo new JsonResponse($result);
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  boolean  False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		$this->checkToken();

		$extension = $this->input->get('extension');
		$this->setRedirect(Route::_('index.php?option=com_categories&view=categories&extension=' . $extension, false));

		/** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Rebuild succeeded.
			$this->setMessage(Text::_('COM_CATEGORIES_REBUILD_SUCCESS'));

			return true;
		}

		// Rebuild failed.
		$this->setMessage(Text::_('COM_CATEGORIES_REBUILD_FAILURE'));

		return false;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   4.0.0
	 */
	protected function getRedirectToListAppend()
	{
		$extension = $this->input->getCmd('extension', null);

		return '&extension=' . $extension;
	}
}
