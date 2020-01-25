<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Input\Input;

/**
 * Workflow controller
 *
 * @since  4.0.0
 */
class WorkflowController extends FormController
{
	/**
	 * The extension for which the workflows apply.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since   4.0.0
	 * @throws  \InvalidArgumentException when no extension is set
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// If extension is not set try to get it from input or throw an exception
		if (empty($this->extension))
		{
			$this->extension = $this->input->getCmd('extension');

			if (empty($this->extension))
			{
				throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_ERROR_EXTENSION_NOT_SET'));
			}
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowAdd($data = array())
	{
		return $this->app->getIdentity()->authorise('core.create', $this->extension);
	}

	/**
	 * Method to check if you can edit a record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = isset($data[$key]) ? (int) $data[$key] : 0;
		$user = $this->app->getIdentity();

		$record = $this->getModel()->getItem($recordId);

		if (!empty($record->id) && $record->core)
		{
			return false;
		}

		// Check "edit" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->extension . '.workflow.' . $recordId))
		{
			return true;
		}

		// Check "edit own" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->extension . '.workflow.' . $recordId))
		{
			return !empty($record) && $record->created_by == $user->id;
		}

		return false;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since  4.0.0
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since  4.0.0
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   BaseDatabaseModel  $model      The data model object.
	 * @param   array              $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		$task = $this->getTask();

		// The save2copy task needs to be handled slightly differently.
		if ($task === 'save2copy')
		{
			$table = $model->getTable();

			$key = $table->getKeyName();

			$recordId = $this->input->getInt($key);

			$db = $model->getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
				->from($db->quoteName('#__workflow_stages'))
				->where($db->quoteName('workflow_id') . ' = ' . (int) $recordId);

			$statuses = $db->setQuery($query)->loadAssocList();

			$smodel = $this->getModel('State');

			$workflowID = (int) $model->getState($model->getName() . '.id');

			$mapping = [];

			foreach ($statuses as $status)
			{
				$table = $smodel->getTable();

				$oldID = $status['id'];

				$status['workflow_id'] = $workflowID;
				$status['id'] = 0;
				unset($status['asset_id']);

				$table->save($status);

				$mapping[$oldID] = (int) $table->id;
			}

			$query->clear();

			$query->select('*')
				->from($db->quoteName('#__workflow_transitions'))
				->where($db->quoteName('workflow_id') . ' = ' . (int) $recordId);

			$transitions = $db->setQuery($query)->loadAssocList();

			$tmodel = $this->getModel('Transition');

			foreach ($transitions as $transition)
			{
				$table = $tmodel->getTable();

				$transition['from_stage_id'] = $mapping[$transition['from_stage_id']];
				$transition['to_stage_id'] = $mapping[$transition['to_stage_id']];

				$transition['workflow_id'] = $workflowID;
				$transition['id'] = 0;
				unset($transition['asset_id']);

				$table->save($transition);
			}
		}
	}

	/**
	 * Method to save a workflow.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  4.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();

		// The save2copy task needs to be handled slightly differently.
		if ($task === 'save2copy')
		{
			$data  = $this->input->post->get('jform', array(), 'array');

			// Prevent default
			$data['default'] = 0;

			$this->input->post->set('jform', $data);
		}

		return parent::save($key, $urlVar);
	}
}
