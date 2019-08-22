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
 * Plugin to add a container element to the pagebuilder
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderContainer extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add container element which can have every other element as child
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_CONTAINER_NAME');

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_CONTAINER_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_CONTAINER_DESC'),
			'id'          => 'container',
			'parent'      => array('root', 'column'),
			'children'    => true,
			'component'   => true,
			'message'     => true,
			'config'      => [
				'fluid' => [
					'type' => 'select',
					'value' => [
						Text::_('JYES') => '1',
						Text::_('JNO') => '0'
					],
					'label' => Text::_('PLG_PAGEBUILDER_CONTAINER_FLUID'),
					'default' => 0
				]
			]
		);
	}

	/**
	 * Get rendering options for frontend templates
	 *
	 * @param   array  $data  Options set in pagebuilder editor like classes, size etc.
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onRenderPagebuilderElement($context, $data)
	{
		if ($context !== 'com_template.pagebuilder.container')
		{
			return;
		}

		$html = '<div ';
		$classes = array('container');

		if (isset($data->options))
		{
			if (!empty($data->options->fluid))
			{
				$classes = ['container-fluid'];
			}

			if (!empty($data->options->class))
			{
				$classes[] = $data->options->class;
			}
		}

		$html .= ' class="' . implode(' ', $classes) . '"';
		$html .= '>';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_CONTAINER_NAME'),
			'id'    => 'container',
			'start' => $html,
			'end'   => '</div>'
		);
	}
}
