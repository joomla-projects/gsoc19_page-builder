<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;

/**
 * Base class for a Joomla Json View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  4.0.0
 */
class JsonView extends AbstractView
{
	/**
	 * The base path of the view
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $_basePath = null;

	/**
	 * Charset to use in escaping mechanisms; defaults to urf8 (UTF-8)
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $_charset = 'UTF-8';

	/**
	 * The output of the view.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $_output = array();

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).
	 *                          charset: the character set to use for display
	 *                          escape: the name (optional) of the function to use for escaping strings
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
	 *                          layout: the layout (optional) to use to display the view
	 *
	 * @since   4.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Set the charset (used by the variable escaping functions)
		if (\array_key_exists('charset', $config))
		{
			Log::add('Setting a custom charset for escaping is deprecated. Override \JViewLegacy::escape() instead.', Log::WARNING, 'deprecated');
			$this->_charset = $config['charset'];
		}

		// Set a base path for use by the view
		if (\array_key_exists('base_path', $config))
		{
			$this->_basePath = $config['base_path'];
		}
		else
		{
			$this->_basePath = JPATH_COMPONENT;
		}
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		// Serializing the output
		$result = json_encode($this->_output);

		// Pushing output to the document
		$this->document->setBuffer($result);
	}
}
