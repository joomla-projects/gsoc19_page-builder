<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\Event;
use Joomla\Registry\Registry;

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @since  2.5
 */
class Captcha implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * Captcha Plugin object
	 *
	 * @var	   CMSPlugin
	 * @since  2.5
	 */
	private $_captcha;

	/**
	 * Editor Plugin name
	 *
	 * @var    string
	 * @since  2.5
	 */
	private $_name;

	/**
	 * Array of instances of this class.
	 *
	 * @var	   Captcha[]
	 * @since  2.5
	 */
	private static $_instances = array();

	/**
	 * Class constructor.
	 *
	 * @param   string  $captcha  The plugin to use.
	 * @param   array   $options  Associative array of options.
	 *
	 * @since   2.5
	 * @throws  \RuntimeException
	 */
	public function __construct($captcha, $options)
	{
		$this->_name = $captcha;
		$this->setDispatcher(Factory::getApplication()->getDispatcher());
		$this->_load($options);
	}

	/**
	 * Returns the global Captcha object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $captcha  The plugin to use.
	 * @param   array   $options  Associative array of options.
	 *
	 * @return  Captcha|null  Instance of this class.
	 *
	 * @since   2.5
	 * @throws  \RuntimeException
	 */
	public static function getInstance($captcha, array $options = array())
	{
		$signature = md5(serialize(array($captcha, $options)));

		if (empty(self::$_instances[$signature]))
		{
			self::$_instances[$signature] = new Captcha($captcha, $options);
		}

		return self::$_instances[$signature];
	}

	/**
	 * Fire the onInit event to initialise the captcha plugin.
	 *
	 * @param   string  $id  The id of the field.
	 *
	 * @return  boolean  True on success
	 *
	 * @since	2.5
	 * @throws  \RuntimeException
	 */
	public function initialise($id)
	{
		$event = new Event(
			'onInit',
			['id' => $id]
		);

		$this->getDispatcher()->dispatch('onInit', $event);

		return true;
	}

	/**
	 * Get the HTML for the captcha.
	 *
	 * @param   string  $name   The control name.
	 * @param   string  $id     The id for the control.
	 * @param   string  $class  Value for the HTML class attribute
	 *
	 * @return  string  The return value of the function "onDisplay" of the selected Plugin.
	 *
	 * @since   2.5
	 * @throws  \RuntimeException
	 */
	public function display($name, $id, $class = '')
	{
		// Check if captcha is already loaded.
		if ($this->_captcha === null)
		{
			return '';
		}

		// Initialise the Captcha.
		if (!$this->initialise($id))
		{
			return '';
		}

		$event = new Event(
			'onDisplay',
			[
				'name'  => $name,
				'id'    => $id ?: $name,
				'class' => $class,
			]
		);

		$result = $this->getDispatcher()->dispatch('onInit', $event);

		// TODO REFACTOR ME! This is Ye Olde Way of returning plugin results
		return $result['result'][0];
	}

	/**
	 * Checks if the answer is correct.
	 *
	 * @param   string  $code  The answer.
	 *
	 * @return  bool    Whether the provided answer was correct
	 *
	 * @since	2.5
	 * @throws  \RuntimeException
	 */
	public function checkAnswer($code)
	{
		// Check if captcha is already loaded
		if ($this->_captcha === null)
		{
			return false;
		}

		$event = new Event(
			'onCheckAnswer',
			['code'	=> $code]
		);

		$result = $this->getDispatcher()->dispatch('onCheckAnswer', $event);

		// TODO REFACTOR ME! This is Ye Olde Way of returning plugin results
		return $result['result'][0];
	}

	/**
	 * Method to react on the setup of a captcha field. Gives the possibility
	 * to change the field and/or the XML element for the field.
	 *
	 * @param   \Joomla\CMS\Form\Field\CaptchaField  $field    Captcha field instance
	 * @param   \SimpleXMLElement                    $element  XML form definition
	 *
	 * @return void
	 */
	public function setupField(\Joomla\CMS\Form\Field\CaptchaField $field, \SimpleXMLElement $element)
	{
		if ($this->_captcha === null)
		{
			return;
		}

		$event = new Event(
			'onSetupField',
			[
				'field' => $field,
				'element' => $element,
			]
		);

		$result = $this->getDispatcher()->dispatch('onCheckAnswer', $event);

		// TODO REFACTOR ME! This is Ye Olde Way of returning plugin results
		return $result['result'][0];
	}

	/**
	 * Load the Captcha plugin.
	 *
	 * @param   array  $options  Associative array of options.
	 *
	 * @return  void
	 *
	 * @since	2.5
	 * @throws  \RuntimeException
	 */
	private function _load(array $options = array())
	{
		// Build the path to the needed captcha plugin
		$name = InputFilter::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/captcha/' . $name . '/' . $name . '.php';

		if (!is_file($path))
		{
			throw new \RuntimeException(Text::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin = PluginHelper::getPlugin('captcha', $this->_name);

		if (!$plugin)
		{
			throw new \RuntimeException(Text::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
		}

		// Check for already loaded params
		if (!($plugin->params instanceof Registry))
		{
			$params = new Registry($plugin->params);
			$plugin->params = $params;
		}

		// Build captcha plugin classname
		$name = 'PlgCaptcha' . $this->_name;
		$dispatcher     = $this->getDispatcher();
		$this->_captcha = new $name($dispatcher, (array) $plugin, $options);
	}
}
