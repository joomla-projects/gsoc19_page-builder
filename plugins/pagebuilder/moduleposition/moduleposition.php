<?php
/**
 * @package    Joomla.Plugin
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin to add a module position element to the layout builder
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderModuleposition extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Load a list of used module positions.
	 *
	 * @return  array  List of used module positions.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function loadValidModulePositions(): array
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('modules.position'))
			->from($db->quoteName('#__modules', 'modules'))
			->innerJoin(
				$db->quoteName('#__extensions', 'extensions'),
				$db->quoteName('modules.module') . ' = ' . $db->quoteName('extensions.name') .
				' AND ' . $db->quoteName('extensions.client_id') . ' = 0'
			)
			->order($db->quoteName('position') . ' ASC');

		$results   = $db->setQuery($query)
			->loadObjectList();
		$positions = [];

		array_walk($results,
			static function ($result) use (&$positions) {
				$position = $result->position;

				$positions[$position] = $position ?? 'NONE';
			}
		);

		return $positions;
	}

	/**
	 * Add moduleposition element which can have every other element as child
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAddElement(): array
	{
		Text::script('PLG_PAGEBUILDER_MODULEPOSITION_NAME');

		$chromeValues = [
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_NONE')    => '',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HTML5')   => 'html5',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HORZ')    => 'horz',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_OUTLINE') => 'outline',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_ROUNDED') => 'rounded',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_TABLE')   => 'table',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_XHTML')   => 'xhtml',
		];

		return [
			'title'       => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_DESC'),
			'id'          => 'moduleposition',
			'parent'      => ['root', 'grid', 'container', 'column'],
			'children'    => false,
			'component'   => false,
			'message'     => false,
			'config'      => [
				'position_name' => [
					'value'       => $this->loadValidModulePositions(),
					'type'        => 'inputselect',
					'required'    => true,
					'show'        => true,
					'label'       => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_POSITION_NAME'),
					'placeholder' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_ENTER_NAME')
				],
				'module_chrome' => [
					'value'    => $chromeValues,
					'type'     => 'select',
					'required' => false,
					'show'     => true,
					'label'    => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME')
				]
			]
		];
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
	public function onRenderPagebuilderElement(string $context, array $data): array
	{
		if ($context !== 'com_template.pagebuilder.moduleposition')
		{
			return [];
		}

		$html = '<jdoc:include';

		if (isset($data->options))
		{
			$html .= ' type="modules"';
			$html .= empty($data->options->position_name) ? '' : ' name="' . $data->options->position_name . '"';
			$html .= empty($data->options->module_chrome) ? '' : ' style="' . $data->options->module_chrome . '"';
		}

		$html .= ' />';

		return [
			'title'  => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'config' => $data->options,
			'id'     => 'moduleposition',
			'start'  => $html,
			'end'    => null
		];
	}
}
