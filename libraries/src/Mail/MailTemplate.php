<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use PHPMailer\PHPMailer\Exception as phpmailerException;

/**
 * Email Templating Class
 *
 * @since  4.0.0
 */
class MailTemplate
{
	/**
	 * Mailer object to send the actual mail.
	 *
	 * @var    \Joomla\CMS\Mail\Mail
	 * @since  4.0.0
	 */
	protected $mailer;

	/**
	 * Identifier of the mail template.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $template_id;

	/**
	 * Language of the mail template.
	 *
	 * @var    string
	 */
	protected $language;

	/**
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $data = array();

	/**
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $attachments = array();

	/**
	 * List of recipients of the email
	 *
	 * @var    \stdClass[]
	 * @since  4.0.0
	 */
	protected $recipients = array();

	/**
	 * Constructor for the mail templating class
	 *
	 * @param   string  $template_id  Id of the mail template.
	 * @param   string  $language     Language of the template to use.
	 * @param   Mail    $mailer       Mail object to send the mail with.
	 *
	 * @since   4.0.0
	 */
	public function __construct($template_id, $language, Mail $mailer = null)
	{
		$this->template_id = $template_id;
		$this->language = $language;

		if ($mailer)
		{
			$this->mailer = $mailer;
		}
		else
		{
			$this->mailer = Factory::getMailer();
		}
	}

	/**
	 * Add an attachment to the mail
	 *
	 * @param   string  $name  Filename of the attachment
	 * @param   string  $file  Either a filepath or filecontent
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function addAttachment($name, $file)
	{
		$attachment = new \stdClass;
		$attachment->name = $name;
		$attachment->file = $file;
		$this->attachments[] = $attachment;
	}

	/**
	 * Adds recipients for this mail
	 *
	 * @param   string  $mail  Mail address of the recipient
	 * @param   string  $name  Name of the recipient
	 * @param   string  $type  How should the recipient receive the mail? ('to', 'cc', 'bcc')
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function addRecipient($mail, $name, $type = 'to')
	{
		$recipient = new \stdClass;
		$recipient->mail = $mail;
		$recipient->name = $name;
		$recipient->type = $type;
		$this->recipients[] = $recipient;
	}

	/**
	 * Add data to replace in the template
	 *
	 * @param   array  $data  Associative array of strings to replace
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function addTemplateData($data)
	{
		$this->data = array_merge($this->data, $data);
	}

	/**
	 * Render and send the mail
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.0.0
	 * @throws  phpmailerException
	 */
	public function send()
	{
		$config = ComponentHelper::getParams('com_mails');

		$mail = self::getTemplate($this->template_id, $this->language);

		/** @var Registry $params */
		$params = $mail->params;
		$gconfig = Factory::getConfig();

		if ($config->get('alternative_mailconfig'))
		{
			if ($this->mailer->Mailer == 'smtp' || $params->get('mailer') == 'smtp')
			{
				$smtpauth = ($params->get('smtpauth', $gconfig->get('smtpauth')) == 0) ? null : 1;
				$smtpuser = $params->get('smtpuser', $gconfig->get('smtpuser'));
				$smtppass = $params->get('smtppass', $gconfig->get('smtppass'));
				$smtphost = $params->get('smtphost', $gconfig->get('smtphost'));
				$smtpsecure = $params->get('smtpsecure', $gconfig->get('smtpsecure'));
				$smtpport = $params->get('smtpport', $gconfig->get('smtpport'));
				$this->mailer->useSmtp($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
			}

			if ($params->get('mailer') == 'sendmail')
			{
				$this->mailer->isSendmail();
			}

			$mailfrom = $params->get('mailfrom', $gconfig->get('mailfrom'));
			$fromname = $params->get('fromname', $gconfig->get('fromname'));

			if (MailHelper::isEmailAddress($mailfrom))
			{
				$this->mailer->setFrom(MailHelper::cleanLine($mailfrom), MailHelper::cleanLine($fromname), false);
			}
		}

		Factory::getApplication()->triggerEvent('onMailBeforeRendering', array($this->template_id, &$this));

		$mail->subject = $this->replaceTags(Text::_($mail->subject), $this->data);
		$this->mailer->setSubject($mail->subject);

		if ($config->get('mail_style', 'plaintext') == 'plaintext')
		{
			$mail->body = $this->replaceTags(Text::_($mail->body), $this->data);
			$this->mailer->setBody($mail->body);
		}

		if ($config->get('mail_style', 'plaintext') == 'html')
		{
			$this->mailer->IsHTML(true);
			$mail->htmlbody = $this->replaceTags(Text::_($mail->htmlbody), $this->data);
			$this->mailer->setBody($mail->htmlbody);
		}

		if ($config->get('mail_style', 'plaintext') == 'both')
		{
			$this->mailer->IsHTML(true);
			$mail->htmlbody = $this->replaceTags(Text::_($mail->htmlbody), $this->data);
			$this->mailer->setBody($mail->htmlbody);
			$mail->body = $this->replaceTags(Text::_($mail->body), $this->data);
			$this->mailer->AltBody = $mail->body;
		}

		if ($config->get('copy_mails') && $params->get('copyto'))
		{
			$this->mailer->addBcc($params->get('copyto'));
		}

		foreach ($this->recipients as $recipient)
		{
			switch ($recipient->type)
			{
				case 'cc':
					$this->mailer->addcc($recipient->mail, $recipient->name);
					break;
				case 'bcc':
					$this->mailer->addBcc($recipient->mail, $recipient->name);
					break;
				case 'to':
				default:
					$this->mailer->addAddress($recipient->mail, $recipient->name);
			}
		}

		$path = JPATH_ROOT . '/' . $config->get('attachment_folder') . '/';

		foreach ((array) json_decode($mail->attachments)  as $attachment)
		{
			if (is_file($path . $attachment->file))
			{
				$this->mailer->addAttachment($path . $attachment->file, $attachment->name ?? $attachment->file);
			}
		}

		foreach ($this->attachments as $attachment)
		{
			if (is_file($attachment->file))
			{
				$this->mailer->addAttachment($attachment->file, $attachment->name);
			}
			else
			{
				$this->mailer->AddStringAttachment($attachment->file, $attachment->name);
			}
		}

		return $this->mailer->Send();
	}

	/**
	 * Replace tags with their values recursively
	 *
	 * @param   string  $text  The template to process
	 * @param   array   $tags  An associative array to replace in the template
	 *
	 * @return  string  Rendered mail template
	 *
	 * @since   4.0.0
	 */
	protected function replaceTags($text, $tags)
	{
		foreach ($tags as $key => $value)
		{
			if (is_array($value))
			{
				$matches = array();
				preg_match_all('/{' . strtoupper($key) . '}(.*?){/' . strtoupper($key) . '}/s', $text, $matches);

				foreach ($matches[0] as $i => $match)
				{
					$replacement = '';

					foreach ($value as $subvalue)
					{
						if (is_array($subvalue))
						{
							$replacement .= $this->replaceTags($matches[1][$i], $subvalue);
						}
					}

					$text = str_replace($match, $replacement, $text);
				}
			}
			else
			{
				$text = str_replace('{' . strtoupper($key) . '}', $value, $text);
			}
		}

		return $text;
	}

	/**
	 * Get a specific mail template
	 *
	 * @param   string  $key       Template identifier
	 * @param   string  $language  Language code of the template
	 *
	 * @return  object  An object with the data of the mail
	 *
	 * @since   4.0.0
	 */
	public static function getTemplate($key, $language)
	{
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($db->quoteName('#__mail_templates'))
			->where($db->quoteName('template_id') . ' = :key')
			->whereIn($db->quoteName('language'), ['', $language], ParameterType::STRING)
			->order($db->quoteName('language') . ' DESC')
			->bind(':key', $key);
		$db->setQuery($query);
		$mail = $db->loadObject();

		if ($mail)
		{
			$mail->params = new Registry($mail->params);
		}

		return $mail;
	}

	/**
	 * Insert a new mail template into the system
	 *
	 * @param   string  $key       Mail template key
	 * @param   string  $subject   A default subject (normally a translateable string)
	 * @param   string  $body      A default body (normally a translateable string)
	 * @param   array   $tags      Associative array of tags to replace
	 * @param   string  $htmlbody  A default htmlbody (normally a translateable string)
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public static function createTemplate($key, $subject, $body, $tags, $htmlbody = '')
	{
		$db = Factory::getDbo();

		$template = new \stdClass;
		$template->template_id = $key;
		$template->language = '';
		$template->subject = $subject;
		$template->body = $body;
		$template->htmlbody = $htmlbody;
		$params = new \stdClass;
		$params->tags = array($tags);
		$template->params = json_encode($params);

		return $db->insertObject('#__mail_templates', $template);
	}

	/**
	 * Update an existing mail template
	 *
	 * @param   string  $key       Mail template key
	 * @param   string  $subject   A default subject (normally a translateable string)
	 * @param   string  $body      A default body (normally a translateable string)
	 * @param   array   $tags      Associative array of tags to replace
	 * @param   string  $htmlbody  A default htmlbody (normally a translateable string)
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public static function updateTemplate($key, $subject, $body, $tags, $htmlbody = '')
	{
		$db = Factory::getDbo();

		$template = new \stdClass;
		$template->template_id = $key;
		$template->language = '';
		$template->subject = $subject;
		$template->body = $body;
		$template->htmlbody = $htmlbody;
		$params = new \stdClass;
		$params->tags = array($tags);
		$template->params = json_encode($params);

		return $db->updateObject('#__mail_templates', $template, ['template_id', 'language']);
	}

	/**
	 * Method to delete a mail template
	 *
	 * @param   string  $key  The key of the mail template
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   4.0.0
	 */
	public static function deleteTemplate($key)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__mail_templates'))
			->where($db->quoteName('template_id') . ' = :key')
			->bind(':key', $key);
		$db->setQuery($query);

		return $db->execute();
	}
}

