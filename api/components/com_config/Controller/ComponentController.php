<?php
/**
 * @package     Joomla.API
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Config\Api\View\Component\JsonapiView;
use Joomla\Component\Config\Administrator\Model\ComponentModel;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The component controller
 *
 * @since  4.0.0
 */
class ComponentController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'component';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'component';

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$viewType = $this->app->getDocument()->getType();
		$viewLayout = $this->input->get('layout', 'default', 'string');

		try
		{
			/** @var JsonapiView $view */
			$view = $this->getView(
				$this->default_view,
				$viewType,
				'',
				['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
			);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		/** @var ComponentModel $model */
		$model = $this->getModel($this->contentType);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'), 500);
		}

		// Push the model into the view (as default)
		$view->setModel($model, true);
		$view->set('component_name', $this->input->get('component_name'));

		$view->document = $this->app->getDocument();
		$view->displayList();

		return $this;
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function edit()
	{
		/** @var ComponentModel $model */
		$model = $this->getModel($this->contentType);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'), 500);
		}

		// Access check.
		if (!$this->allowEdit())
		{
			throw new NotAllowed('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED', 403);
		}

		$option = $this->input->get('component_name');

		// TODO: Not the cleanest thing ever but it works...
		Form::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $option);

		// Must load after serving service-requests
		$form = $model->getForm();

		$data = json_decode($this->input->json->getRaw(), true);

		$component = ComponentHelper::getComponent($option);
		$oldData   = $component->getParams()->toArray();
		$data      = array_replace($oldData, $data);

		// Validate the posted data.
		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			$errors   = $model->getErrors();
			$messages = [];

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$messages[] = "{$errors[$i]->getMessage()}";
				}
				else
				{
					$messages[] = "{$errors[$i]}";
				}
			}

			throw new InvalidParameterException(implode("\n", $messages));
		}

		// Attempt to save the configuration.
		$data = [
			'params' => $validData,
			'id'     => ExtensionHelper::getExtensionRecord($option)->extension_id,
			'option' => $option
		];

		if (!$model->save($data))
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SERVER'), 500);
		}

		return $this;
	}
}
