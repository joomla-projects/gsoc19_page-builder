<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Base class for a Joomla Module Dispatcher.
 *
 * @since  4.0.0
 */
abstract class AbstractModuleDispatcher extends Dispatcher
{
	/**
	 * The module instance
	 *
	 * @var    \stdClass
	 * @since  4.0.0
	 */
	protected $module;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   \stdClass                $module  The module
	 * @param   CMSApplicationInterface  $app     The application instance
	 * @param   Input                    $input   The input instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(\stdClass $module, CMSApplicationInterface $app, Input $input)
	{
		parent::__construct($app, $input);

		$this->module = $module;
	}

	/**
	 * Runs the dispatcher.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		$this->loadLanguage();

		$displayData = $this->getLayoutData();

		// Abort when display data is false
		if ($displayData === false)
		{
			return;
		}

		// Execute the layout without the module context
		$loader = static function (array $displayData)
		{
			extract($displayData);
			require ModuleHelper::getLayoutPath($displayData['module']->module, $displayData['params']->get('layout', 'default'));
		};

		$loader($displayData);
	}

	/**
	 * Returns the layout data. This function can be overridden by subclasses to add more
	 * attributes for the layout.
	 *
	 * If false is returned, then it means that the dispatch process should be aborted.
	 *
	 * @return  array|false
	 *
	 * @since   4.0.0
	 */
	protected function getLayoutData()
	{
		return [
			'module'   => $this->module,
			'app'      => $this->app,
			'input'    => $this->input,
			'params'   => new Registry($this->module->params),
			'template' => $this->app->getTemplate()
		];
	}

	/**
	 * Load the language.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function loadLanguage()
	{
		$language = $this->app->getLanguage();

		$coreLanguageDirectory      = JPATH_BASE;
		$extensionLanguageDirectory = JPATH_BASE . '/modules/' . $this->module->module;

		$langPaths = $language->getPaths();

		// Only load the module's language file if it hasn't been already
		if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory])))
		{
			// 1.5 or Core then 1.6 3PD
			$language->load($this->module->module, $coreLanguageDirectory, null, false, true) ||
			$language->load($this->module->module, $extensionLanguageDirectory, null, false, true);
		}
	}
}
