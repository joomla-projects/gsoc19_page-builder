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
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderModuleposition extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  4.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add moduleposition element which can have every other element as child
	 *
	 * @param   string  $context  Where the Page Builder is called
	 * @param   array   $params   Data for the element
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   4.0
	 */
	public function onPageBuilderAddElement($context, $params)
	{
		if (!$this->checkContext($context))
		{
			return array();
		}

		Text::script('PLG_PAGEBUILDER_MODULEPOSITION_NAME');

		$chromeValues = array(
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_NONE') => '',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HTML5') => 'html5',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HORZ') => 'horz',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_OUTLINE') => 'outline',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_ROUNDED') => 'rounded',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_TABLE') => 'table',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_XHTML') => 'xhtml',
		);

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_DESC'),
			'id'          => 'moduleposition',
			'parent'      => array('root', 'grid', 'container', 'column'),
			'children'    => false,
			'component'   => false,
			'message'     => false,
			'config'  => array(
				'position_name' => array(
					'value' => '',
					'type' => 'text',
					'required' => true,
					'show' => true,
					'label' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_POSITION_NAME'),
					'placeholder' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_ENTER_NAME')
				),
				'module_chrome' => array(
					'value' => $chromeValues,
					'type' => 'select',
					'required' => false,
					'show' => true,
					'label' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME')
				)
			)
		);
	}

	/**
	 * Checks if the element is allowed for the current context
	 *
	 * @param   string  $context where the current Page Builder is used
	 *
	 * @return boolean
	 * @since 4.0
	 */
	private function checkContext($context)
	{
		$contexts = $this->params->get('context');

		if (strpos($context, $contexts) !== false)
		{
			return true;
		}

		$extensions = $this->params->get('extensions', []);

		foreach ($extensions as $extension)
		{
			if (strpos($context, $extension) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get rendering options for frontend templates
	 *
	 * @param   string  $context  Indicates the type of the rendered element
	 * @param   array   $data     Options set in pagebuilder editor like classes, size etc.
	 *
	 * @return  array   tags and their attributes to render the element
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPageBuilderRenderElement($context, $data)
	{
		if ($context !== 'moduleposition')
		{
			return array();
		}

		$html = '<jdoc:include';

		if (isset($data->options))
		{
			$html .= ' type="modules"';
			$html .= empty($data->options->position_name) ? '' : ' name="' . $data->options->position_name . '"';
			$html .= empty($data->options->module_chrome) ? '' : ' style="' . $data->options->module_chrome . '"';
		}

		$html .= ' />';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'config' => $data->options,
			'id'    => 'moduleposition',
			'start' => $html,
			'end'   => null
		);
	}
}
