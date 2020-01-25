<?php
/**
 * @package     Joomla.API
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Api\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\Exception\SendEmail;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Registry\Registry;
use Joomla\String\Inflector;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The contact controller
 *
 * @since  4.0.0
 */
class ContactController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'contacts';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'contacts';

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		$data = (array) json_decode($this->input->json->getRaw(), true);

		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		$this->input->set('data', $data);

		return parent::save($recordKey);
	}

	/**
	 * Submit contact form
	 *
	 * @param   integer  $id Leave empty if you want to retrieve data from the request
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function submitForm($id = null)
	{
		if ($id === null)
		{
			$id = $this->input->post->get('id', 0, 'int');
		}

		$modelName = Inflector::singularize($this->contentType);

		/** @var  \Joomla\Component\Contact\Site\Model\ContactModel $model */
		$model = $this->getModel($modelName, 'Site');

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$model->setState('filter.published', 1);

		$data    = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');
		$contact = $model->getItem($id);

		if ($contact->id === null)
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		$contactParams = new Registry($contact->params);

		if (!$contactParams->get('show_email_form'))
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_DISPLAY_EMAIL_FORM'));
		}

		// Contact plugins
		PluginHelper::importPlugin('contact');

		Form::addFormPath(JPATH_COMPONENT_SITE . '/forms');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \RuntimeException($model->getError(), 500);
		}

		if (!$model->validate($form, $data))
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

		// Validation succeeded, continue with custom handlers
		$results = $this->app->triggerEvent('onValidateContact', [&$contact, &$data]);

		foreach ($results as $result)
		{
			if ($result instanceof \Exception)
			{
				throw new InvalidParameterException($result->getMessage());
			}
		}

		// Passed Validation: Process the contact plugins to integrate with other applications
		$this->app->triggerEvent('onSubmitContact', [&$contact, &$data]);

		// Send the email
		$sent = false;

		$params = ComponentHelper::getParams('com_contact');

		if (!$params->get('custom_reply'))
		{
			$sent = $this->_sendEmail($data, $contact, $params->get('show_email_copy', 0));
		}

		if (!$sent)
		{
			throw new SendEmail('Error sending message');
		}

		return $this;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   array      $data                  The data to send in the email.
	 * @param   \stdClass  $contact               The user information to send the email to
	 * @param   boolean    $copy_email_activated  True to send a copy of the email to the user.
	 *
	 * @return  boolean  True on success sending the email, false on failure.
	 *
	 * @since   1.6.4
	 */
	private function _sendEmail($data, $contact, $copy_email_activated)
	{
		$app = $this->app;

		if ($contact->email_to == '' && $contact->user_id != 0)
		{
			$contact_user      = User::getInstance($contact->user_id);
			$contact->email_to = $contact_user->get('email');
		}

		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');

		$name    = $data['contact_name'];
		$email   = PunycodeHelper::emailToPunycode($data['contact_email']);
		$subject = $data['contact_subject'];
		$body    = $data['contact_message'];

		// Prepare email body
		$prefix = Text::sprintf('COM_CONTACT_ENQUIRY_TEXT', Uri::base());
		$body   = $prefix . "\n" . $name . ' <' . $email . '>' . "\r\n\r\n" . stripslashes($body);

		// Load the custom fields
		if (!empty($data['com_fields']) && $fields = FieldsHelper::getFields('com_contact.mail', $contact, true, $data['com_fields']))
		{
			$output = FieldsHelper::render(
				'com_contact.mail',
				'fields.render', [
					'context' => 'com_contact.mail',
					'item'    => $contact,
					'fields'  => $fields,
				]
			);

			if ($output)
			{
				$body .= "\r\n\r\n" . $output;
			}
		}

		try
		{
			$mail = Factory::getMailer();
			$mail->addRecipient($contact->email_to);
			$mail->addReplyTo($email, $name);
			$mail->setSender([$mailfrom, $fromname]);
			$mail->setSubject($sitename . ': ' . $subject);
			$mail->setBody($body);
			$sent = $mail->Send();

			// If we are supposed to copy the sender, do so.

			// Check whether email copy function activated
			if ($copy_email_activated == true && !empty($data['contact_email_copy']))
			{
				$copytext = Text::sprintf('COM_CONTACT_COPYTEXT_OF', $contact->name, $sitename);
				$copytext .= "\r\n\r\n" . $body;
				$copysubject = Text::sprintf('COM_CONTACT_COPYSUBJECT_OF', $subject);

				$mail = Factory::getMailer();
				$mail->addRecipient($email);
				$mail->addReplyTo($email, $name);
				$mail->setSender([$mailfrom, $fromname]);
				$mail->setSubject($copysubject);
				$mail->setBody($copytext);
				$sent = $mail->Send();
			}
		}
		catch (\Exception $exception)
		{
			try
			{
				Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

				$sent = false;
			}
			catch (\RuntimeException $exception)
			{
				Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

				$sent = false;
			}
		}

		return $sent;
	}
}
