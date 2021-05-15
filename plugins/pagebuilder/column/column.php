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
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderColumn extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add column element which can be a child of grids
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAddElement(): array
	{
		Text::script('PLG_PAGEBUILDER_COLUMN_NAME');

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_COLUMN_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_COLUMN_DESC'),
			'id'          => 'column',
			'parent'      => array('grid'),
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
		if ($context !== 'com_template.pagebuilder.column')
		{
			return array();
		}

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
				if (is_string($data->options->size))
				{
					$classes[] = 'col-' . $data->options->size;
				}
				else
				{
					foreach ($data->options->size as $device => $size)
					{
						if (!empty($size))
						{
							$classes[] = $device === 'xs' ? 'col-' . $size : 'col-' . $device . '-' . $size;
						}
					}
				}
			}

			if (!empty($data->options->offset))
			{
				foreach ($data->options->offset as $device => $offset)
				{
					if (!empty($offset))
					{
						$classes[] = 'offset-' . $device . '-' . $offset;
					}
				}
			}
		}

		$html .= ' class="' . implode(' ', $classes) . '"';
		$html .= '>';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_COLUMN_NAME'),
			'config' => $data->options,
			'id'    => 'column',
			'start' => $html,
			'end'   => '</div>'
		);
	}
}
