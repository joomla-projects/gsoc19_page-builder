<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Checkin\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Checkin Controller
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'checkin';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 */
	public function display($cachable = false, $urlparams = array())
	{
		return parent::display();
	}

	/**
	 * Check in a list of items.
	 *
	 * @return  void
	 */
	public function checkin()
	{
		// Check for request forgeries
		$this->checkToken();

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			$this->app->enqueueMessage(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'warning');
		}
		else
		{
			// Get the model.
			/** @var \Joomla\Component\Checkin\Administrator\Model\CheckinModel $model */
			$model = $this->getModel('Checkin');

			// Checked in the items.
			$this->setMessage(Text::plural('COM_CHECKIN_N_ITEMS_CHECKED_IN', $model->checkin($ids)));
		}

		$this->setRedirect('index.php?option=com_checkin');
	}

	/**
	 * Provide the data for a badge in a menu item via JSON
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function getMenuBadgeData()
	{
		if (!Factory::getUser()->authorise('core.manage', 'com_checkin'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$model = $this->getModel('Checkin');

		$amount = (int) count($model->getItems());

		echo new JsonResponse($amount);
	}

	/**
	 * Method to get the number of locked icons
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function getQuickiconContent()
	{
		if (!Factory::getUser()->authorise('core.manage', 'com_checkin'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$model = $this->getModel('Checkin');

		$amount = (int) count($model->getItems());

		$result = [];

		$result['amount'] = $amount;
		$result['sronly'] = Text::plural('COM_CHECKIN_N_QUICKICON_SRONLY', $amount);

		echo new JsonResponse($result);
	}
}
