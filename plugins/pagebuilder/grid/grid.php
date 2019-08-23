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
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderGrid extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add grid element which can have every other element as child
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @param   string  $context  Indicates the type of the rendered element
	 * @param   array   $data     Options set in pagebuilder editor like classes, size etc.
	 *
	 * @return  array   tags and their attributes to render the element
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onRenderPagebuilderElement($context, $data)
	{
		if ($context !== 'com_template.pagebuilder.grid')
		{
			return array();
		}

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
			'config' => $data->options,
			'id'    => 'grid',
			'start' => $html,
			'end'   => '</div>'
		);
	}
}
