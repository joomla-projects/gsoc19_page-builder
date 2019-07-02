<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Template Helper class.
 *
 * @since  4.0
 */
abstract class RenderHelper
{
	/**
	 * Defines how much columns the grid should have
	 * @var number
	 *
	 * @since   4.0
	 */
	private static $gridSize = 12;

	/**
	 * Decode pagebuilder param and render the elements
	 *
	 * @param   string $param JSON with grids, columns and modules, build by the pagebuilder
	 *
	 * @return  string
	 *
	 * @since   4.0
	 * @throws \Exception
	 */
	public static function renderElements($param)
	{
		$elements = json_decode($param);

		if (empty($elements))
		{
			throw new \Exception('No elements available');
		}

		self::$gridSize = isset($elements->gridSize) ? $elements->gridSize : 12;

		PluginHelper::importPlugin('pagebuilder');

		$html = '<div class="root">' . self::render($elements) . '</div>';

		return $html;
	}

	/**
	 * Retrieve plugin rendering data
	 * Returns false when no matching plugin was found.
	 *
	 * @param   string $name name of element and matching plugin
	 * @param   array  $data element data for the renderer
	 *
	 * @return  array|boolean
	 *
	 * @since   4.0
	 */
	private static function getPluginRenderer($name, $data)
	{
		$pluginRenderer = Factory::getApplication()->triggerEvent('onRender', array($data));

		foreach ($pluginRenderer as $plugin)
		{
			if (strtolower($plugin['name']) === strtolower($name))
			{
				return $plugin;
			}
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
	 * @since   4.0
	 */
	private static function render($elements)
	{
		$html = '';

		foreach ($elements as $element)
		{
			$name = $element->type;

			$renderData = self::getPluginRenderer($name, $element);

			// Create default element to fill space
			if (!$renderData)
			{
				$renderData['start'] = '<div>';
				$renderData['end']   = '</div>';
			}

			$html .= $renderData['start'];

			if (!empty($element->children))
			{
				$html .= self::render($element->children);
			}

			$html .= $renderData['end'];
		}

		return $html;
	}
}
