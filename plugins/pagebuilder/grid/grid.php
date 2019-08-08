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
 * Plugin to add a grid element to the pagebuilder
 *
 * @since  4.0.0
 */
class PlgPagebuilderGrid extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add grid element which can have every other element as child
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_GRID_NAME');

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_GRID_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_GRID_DESC'),
			'id'          => 'grid',
			'parent'      => array('root', 'container', 'column'),
			'children'    => true,
			'component'   => true,
			'message'     => true
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
		$html    = '<div';
		$classes = ['grid', 'row'];

		if (isset($data->options))
		{
			if (!empty($data->options->class))
			{
				$classes[] = $data->options->class;
			}
		}

		$html .= ' class="' . implode(' ', $classes) . '"';
		$html .= '>';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_GRID_NAME'),
			'id'    => 'grid',
			'start' => $html,
			'end'   => '</div>'
		);
	}
}
