<?php
/**
 * @package     Joomla.Plugin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
	 * @param   array $params Data for the element
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_MODULEPOSITION_NAME');

		return array(
			'name'     => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'id'       => 'plg_pagebuilder_moduleposition',
			'parent'   => array('root', 'grid', 'container'),
			'children' => false
		);
	}

	/**
	 * Get rendering options for frontend templates
	 *
	 * @param   array $data options set in pagebuilder editor like classes, size etc.
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onRender($data)
	{
		// TODO: include modules when they are integrated into the pagebuilder with type and name
		// $include = '<jdoc:include type="' . $type . '" name="' . $name . '" style="' . $style . '" />';
		$html    = '<span ';
		$classes = array('grid');

		if (isset($data->options))
		{
			if (!empty($data->options->class))
			{
				$classes[] = $data->options->class;
			}

			$html .= empty($data->options->style) ? '' : ' style="' . $data->options->style . '"';
			$html .= empty($data->options->type) ? '' : ' type="' . $data->options->type . '"';
		}

		$html .= ' class="' . implode(' ', $classes) . '"';
		$html .= '> POSITION ';

		return array(
			'name'  => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'id'    => 'plg_pagebuilder_grid',
			'start' => $html,
			'end'   => '</span>'
		);
	}
}
