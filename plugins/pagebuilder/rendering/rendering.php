<?php
/**
 * @package    Joomla.Plugin
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin to render elements from the Page Builder
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderRendering extends CMSPlugin
{
	/**
	 * Render elements created with the PageBuilder editor
	 *
	 * @return  string
	 *
	 * @throws Exception
	 * @since  4.0
	 */
	public function onAjaxRendering()
	{
		$input = Factory::getApplication()->input;
		$json  = $input->json->getRaw();

		try
		{
			$elements = json_decode($json);
		}
		catch (Exception $e)
		{
			return 'Elements are not in JSON format!';
		}

		$elementComment = '<!--' . $json . '-->';
		$result         = empty($elements) ? '' : self::render($elements);

		return $elementComment . $result;
	}

	/**
	 * Retrieve plugin rendering data
	 * Returns false when no matching plugin was found.
	 *
	 * @param   array  $data    element data for the renderer
	 * @param   string $context page context to get matching elements
	 *
	 * @return  array|boolean
	 *
	 * @throws Exception
	 * @since  4.0
	 */
	private static function getPluginRenderer($data, $context = 'com_templates.style')
	{
		$pluginRenderer = Factory::getApplication()->triggerEvent(
			'onRenderPagebuilderElement',
			array($context, $data)
		);

		foreach ($pluginRenderer as $plugin)
		{
			if (empty($plugin))
			{
				continue;
			}

			return $plugin;
		}

		return false;
	}

	/**
	 * Render pagebuilder grid
	 *
	 * @param   array $elements blocks that build the website
	 *
	 * @return  string
	 *
	 * @throws Exception
	 * @since  4.0
	 */
	private static function render($elements)
	{
		$html = '';

		foreach ($elements as $element)
		{
			$renderData = self::getPluginRenderer($element);

			// Create default element to fill space
			if (!$renderData)
			{
				$renderData['start'] = '<div>';
				$renderData['end']   = '</div>';
			}

			$html .= $renderData['start'];

			if (!empty($element->options->component))
			{
				$html .= '<jdoc:include type="component" />';
			}
			elseif (!empty($element->options->message))
			{
				$html .= '<jdoc:include type="message" />';
			}

			if (!empty($element->children))
			{
				$html .= self::render($element->children);
			}

			$html .= $renderData['end'];
		}

		return $html;
	}
}
