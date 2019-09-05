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
 * Plugin to add a moduleposition element to the pagebuilder
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

	private function loadValidModulePositions(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('position')]) #
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('published') . ' = 1')
            ->order('position asc');

        $modelresults = $db->setQuery($query)->loadObjectList();
        $positions = [];
        foreach($modelresults as $res){
            $res = $res->position;
            if ($res == NULL) $res = "NONE";
            $positions[$res] = $res;
        }
        return $positions;
    }

	/**
	 * Add moduleposition element which can have every other element as child
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_MODULEPOSITION_NAME');

		$chromeValues = array(
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_NONE') => '',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HTML5') => 'html5',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_HORZ') => 'horz',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_OUTLINE') => 'outline',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_ROUNDED') => 'rounded',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_TABLE') => 'table',
			Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME_XHTML') => 'xhtml',
		);

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_DESC'),
			'id'          => 'moduleposition',
			'parent'      => array('root', 'grid', 'container', 'column'),
			'children'    => false,
			'component'   => false,
			'message'     => false,
			'config'  => array(
				'position_name' => array(
					'value' => $this->loadValidModulePositions(),
					'type' => 'select',
					'required' => true,
					'show' => true,
					'label' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_POSITION_NAME'),
					'placeholder' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_ENTER_NAME')
				),
				'module_chrome' => array(
					'value' => $chromeValues,
					'type' => 'select',
					'required' => false,
					'show' => true,
					'label' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_CONFIG_CHROME')
				)
			)
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
		if ($context !== 'com_template.pagebuilder.moduleposition')
		{
			return array();
		}

		$html = '<jdoc:include';

		if (isset($data->options))
		{
			$html .= ' type="modules"';
			$html .= empty($data->options->position_name) ? '' : ' name="' . $data->options->position_name . '"';
			$html .= empty($data->options->module_chrome) ? '' : ' style="' . $data->options->module_chrome . '"';
		}

		$html .= ' />';

		return array(
			'title' => Text::_('PLG_PAGEBUILDER_MODULEPOSITION_NAME'),
			'config' => $data->options,
			'id'    => 'moduleposition',
			'start' => $html,
			'end'   => null
		);
	}
}
