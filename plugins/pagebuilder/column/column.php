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
 * Plugin to add a column element to the pagebuilder
 *
 * @since  4.0.0
 */
class PlgPagebuilderColumn extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add column element which can be a child of grids
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_COLUMN_NAME');

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_COLUMN_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_COLUMN_DESC'),
			'id'          => 'column',
			'parent'      => array('grid'),
			'children'    => true
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
		$html    = '<div ';
		$classes = array('column');

		if (isset($data->options))
		{
			if (!empty($data->options->class))
			{
				$classes[] = $data->options->class;
			}

			if (!empty($data->options->size))
			{
				$classes[] = 'col-' . $data->options->size;
			}

			if (!empty($data->options->offsetClass))
			{
				$classes[] = $data->options->offsetClass;
			}
		}

		$html .= ' class="' . implode(' ', $classes) . '"';
		$html .= '>';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_COLUMN_NAME'),
			'id'    => 'column',
			'start' => $html,
			'end'   => '</div>'
		);
	}
}
