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
 * Plugin to add a paragraph element to the pagebuilder
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderParagraph extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add paragraph element which can have every other element as child
	 *
	 * @param   string  $context  Where the Page Builder is called
	 * @param   array   $params   Data for the element
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPageBuilderAddElement($context, $params)
	{
		$configContext = $this->params->get('context');

		if (strpos($context, $configContext) === false)
		{
			return array();
		}

		Text::script('PLG_PAGEBUILDER_PARAGRAPH_NAME');

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_PARAGRAPH_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_PARAGRAPH_DESC'),
			'id'          => 'paragraph',
			'parent'      => array('root', 'container', 'grid', 'column'),
			'children'    => false,
			'component'   => false,
			'message'     => false,
			'config'      => array(
				'text' => array(
					'type' => 'text',
					'label' => Text::_('PLG_PAGEBUILDER_PARAGRAPH_TEXT'),
					'default' => ''
				)
			)
		);
	}

	/**
	 * Get rendering options for content
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
		if ($context !== 'paragraph')
		{
			return array();
		}

		$html = '<p ';
		$classes = array('paragraph');

		if (isset($data->options))
		{
			if (!empty($data->options->class))
			{
				$classes[] = $data->options->class;
			}
		}

		$html .= ' class="' . implode(' ', $classes) . '"';
		$html .= '>';
		$html .= $data->options->text;

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_PARAGRAPH_NAME'),
			'config' => $data->options,
			'id'    => 'paragraph',
			'start' => $html,
			'end'   => '</p>'
		);
	}
}
