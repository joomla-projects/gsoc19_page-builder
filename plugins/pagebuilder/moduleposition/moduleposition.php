<?php
/**
 * @package    Joomla.Plugin
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin to add a moduleposition element to the pagebuilder
 *
 * @since  4.0.0
 */
class PlgPagebuilderModuleposition extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add moduleposition element which can have every other element as child
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_MODULEPOSITION_NAME');

		$chromeValues = array(
			'None' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_NONE'),
			'HTML5' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HTML5'),
			'Horz' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HORZ'),
			'Outline' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_OUTLINE'),
			'Rounded' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_ROUNDED'),
			'Table' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_TABLE'),
			'XHTML' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_XHTML'),
		);

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_DESC'),
			'id'          => 'moduleposition',
			'parent'      => array('root', 'grid', 'container', 'column'),
			'children'    => false,
			'config'  => array(
				'position_name' => array(
					'value' => '',
					'type' => 'text',
					'required' => true,
					'show' => true,
					'label' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_POSITION_NAME')
				),
				'module_chrome' => array(
					'value' => $chromeValues,
					'type' => 'select',
					'required' => false,
					'show' => false,
					'label' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME')
				)
			)
		);
	}

	/**
	 * Get rendering options for frontend templates
	 *
	 * @param   array  $data  Options set in pagebuilder editor like classes, size etc.
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onRender($data)
	{
		$html = '<jdoc:include';

		if (isset($data->options))
		{
			$html .= empty($data->options->type) ? ' type="modules"' : ' type="' . $data->options->type . '"';
			$html .= empty($data->options->position_name) ? '' : ' name="' . $data->options->position_name . '"';
			$html .= empty($data->options->module_chrome) ? '' : ' style="' . $data->options->module_chrome . '"';
		}

		$html .= ' />';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'id'    => 'moduleposition',
			'start' => $html,
			'end'   => null
		);
	}
}
