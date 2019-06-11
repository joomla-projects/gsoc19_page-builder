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

/**
 * Template Helper class.
 *
 * @since  4.0
 */
abstract class RenderHelper
{
	/**
	 * Decode pagebuilder param and render the elements
	 *
	 * @param   string $param JSON with grids, columns and modules, build by the pagebuilder
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	public static function renderElements($param)
	{
		$grid = json_decode($param);
		$html = '<div class="container">' . self::render($grid) . '</div>';

		return $html;
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
	public static function render($elements)
	{
		$html = '';

		foreach ($elements as $element)
		{
			$classes = array();

			if (!empty($element->class))
			{
				$classes[] = $element->class;
			}

			$include = '';
			$name    = '';
			$style   = 'none';
			$size    = '';
			$type    = 'modules';

			foreach ($element->options as $key => $option)
			{
				switch ($key)
				{
					case 'name':
						$name      = $option;
						$classes[] = 'container-' . $option;
						break;
					case 'size':
						$size      = $option;
						$classes[] = $option;
						break;
					case 'style':
						$style = $option;
						break;
					case 'type':
						$type = $option;
						break;
					default:
						$classes[] = $option;
				}
			}

			if ($element->type === 'grid')
			{
				$classes[] = 'row';
			}
			elseif ($element->type === 'column')
			{
				$classes[] = 'col';
			}
			else // Position for modules, messages or components
			{
				// TODO: include modules when they are integrated into the pagebuilder with type and name
				// $include = '<jdoc:include type="' . $type . '" name="' . $name . '" style="' . $style . '" />';
				$include = 'POSITION ';
			}

			$html .= '<div class="' . implode(' ', $classes) . '">';
			$html .= $include . $size;

			if (!empty($element->children))
			{
				$html .= self::render($element->children);
			}

			$html .= '</div>';
		}

		return $html;
	}
}
