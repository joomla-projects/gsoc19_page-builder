<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.pagebuilder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * PageBuilder Editor Plugin
 *
 * @since  4.0
 */
class PlgEditorPagebuilder extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1.4
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the editor.
	 *
	 * @param   string   $name     The control name.
	 * @param   string   $content  The contents of the editor.
	 * @param   string   $width    Not used.
	 * @param   string   $height   Not used.
	 * @param   int      $col      Not used.
	 * @param   int      $row      Not used.
	 * @param   boolean  $buttons  Not used.
	 * @param   string   $id       An optional ID for the textarea (note: since 1.6). If not supplied the name is used.
	 * @param   string   $asset    Not used.
	 * @param   object   $author   Not used.
	 * @param   array    $params   Associative array of editor parameters.
	 *
	 * @return  string  HTML
	 * @throws Exception
	 * @since 4.0
	 */
	public function onDisplay(
		$name, $content, $width, $height, $col, $row, $buttons = true, $id = null, $asset = null, $author = null, $params = array()
	)
	{
		if (empty($id))
		{
			$id = $name;
		}

		$readonly = !empty($params['readonly']) ? ' readonly disabled' : '';
		$input    = JFactory::getApplication()->input;
		$option   = $input->get('option', '');
		$view     = $input->get('view', '');
		$context  = $option . '.' . $view;

		HTMLHelper::_('script', 'media/plg_editors_pagebuilder/js/pagebuilder.js');
		HTMLHelper::_('stylesheet', 'media/plg_editors_pagebuilder/css/pagebuilder.css');

		// Activate elements to use them in the editor
		PluginHelper::importPlugin('pagebuilder');
		$pluginElements = Factory::getApplication()->triggerEvent(
			'onPageBuilderAddElement', array($context, 'params')
		);

		$options = array(
			'elements'  => $pluginElements,
			'id'        => $id,
			'renderUrl' => Uri::base(true) . '/index.php?option=com_ajax&plugin=pagebuilder&group=editors&format=raw&action=renderPage',
			'images'    => array(
				// Images to select new elements
				'row12'   => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_12.png', 'row12'),
				'row84'   => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_8_4.png', 'row84'),
				'row66'   => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_6_6.png', 'row66'),
				'row48'   => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_4_8.png', 'row48'),
				'row444'  => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_4_4_4.png', 'row444'),
				'row363'  => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_3_6_3.png', 'row363'),
				'row3333' => HTMLHelper::_('image', 'media/plg_editors_pagebuilder/images/row_3_3_3_3.png', 'row3333'),
			)
		);

		$this->includeLanguageStrings();
		Factory::getDocument()->addScriptOptions('editor_pagebuilder', $options);

		return '<div id="pagebuilder"></div>'
			. '<input type="text" class="hidden" name="' . $name . '" id="' . $id . '" value="' . $content . '" ' .
			$readonly . '/>';
	}

	/**
	 * Set language strings into script for the JS editor
	 *
	 * @return void
	 * @since 4.0
	 */
	private function includeLanguageStrings()
	{
		Text::script('JCANCEL', true);
		Text::script('JSEARCH_RESET', true);
		Text::script('JTOOLBAR_BACK', true);
		Text::script('JTOOLBAR_CLOSE', true);
		Text::script('PLG_PAGEBUILDER_ADD', true);
		Text::script('PLG_PAGEBUILDER_ADD_CLASS', true);
		Text::script('PLG_PAGEBUILDER_ADD_COLUMN', true);
		Text::script('PLG_PAGEBUILDER_ADD_ELEMENT', true);
		Text::script('PLG_PAGEBUILDER_ADD_ELEMENT_DESC', true);
		Text::script('PLG_PAGEBUILDER_ADD_GRID', true);
		Text::script('PLG_PAGEBUILDER_ADD_MODULE', true);
		Text::script('PLG_PAGEBUILDER_ADD_OFFSET', true);
		Text::script('PLG_PAGEBUILDER_CLOSE', true);
		Text::script('PLG_PAGEBUILDER_COMPONENT', true);
		Text::script('PLG_PAGEBUILDER_CUSTOM', true);
		Text::script('PLG_PAGEBUILDER_DELETE_COLUMN', true);
		Text::script('PLG_PAGEBUILDER_DELETE_GRID', true);
		Text::script('PLG_PAGEBUILDER_DEVICE_LG', true);
		Text::script('PLG_PAGEBUILDER_DEVICE_MD', true);
		Text::script('PLG_PAGEBUILDER_DEVICE_SM', true);
		Text::script('PLG_PAGEBUILDER_DEVICE_XL', true);
		Text::script('PLG_PAGEBUILDER_DEVICE_XS', true);
		Text::script('PLG_PAGEBUILDER_EDIT_COLUMN', true);
		Text::script('PLG_PAGEBUILDER_EDIT', true);
		Text::script('PLG_PAGEBUILDER_EDIT_POSITION', true);
		Text::script('PLG_PAGEBUILDER_INSERT', true);
		Text::script('PLG_PAGEBUILDER_MAX_COLUMN_SIZE', true);
		Text::script('PLG_PAGEBUILDER_MESSAGE', true);
		Text::script('PLG_PAGEBUILDER_NO_CHILD_ALLOWED', true);
		Text::script('PLG_PAGEBUILDER_NONE', true);
		Text::script('PLG_PAGEBUILDER_OFFSET', true);
		Text::script('PLG_PAGEBUILDER_POSITION_NAME', true);
		Text::script('PLG_PAGEBUILDER_SELECT_DEVICE', true);
		Text::script('PLG_PAGEBUILDER_SELECT_ELEMENT', true);
		Text::script('PLG_PAGEBUILDER_SELECT_LAYOUT', true);
		Text::script('PLG_PAGEBUILDER_SELECT_MODULE_CHROME', true);
		Text::script('PLG_PAGEBUILDER_SETTINGS', true);
		Text::script('PLG_PAGEBUILDER_VIEW', true);
	}

	/**
	 * API interface to get HTML rendered elements
	 *
	 * @throws Exception
	 * @since  4.0
	 * @return void
	 */
	public static function renderPage()
	{
		$app = JFactory::getApplication();

		if (!Session::checkToken('post'))
		{
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			echo Text::_('JINVALID_TOKEN_NOTICE');
			$app->close();
		}

		$input    = Factory::getApplication()->input;
		$json     = $input->json->getRaw();
		$elements = json_decode($json);

		if (empty($elements))
		{
			echo '';
			$app->close();
		}

		PluginHelper::importPlugin('pagebuilder');

		$elementComment = '<!--' . $json . '-->';
		$result         = self::render($elements);

		echo $elementComment . $result;

		$app->close();
	}

	/**
	 * Retrieve plugin rendering data
	 * Returns false when no matching plugin was found.
	 *
	 * @param   array  $data    element data for the renderer
	 * @param   string $context page context to get matching elements
	 *
	 * @return  array|boolean
	 *
	 * @throws Exception
	 * @since  4.0
	 */
	private static function getPluginRenderer($data, $context = 'com_templates.style')
	{
		$pluginRenderer = Factory::getApplication()->triggerEvent(
			'onRenderPagebuilderElement',
			array($context, $data)
		);

		foreach ($pluginRenderer as $plugin)
		{
			if (empty($plugin))
			{
				continue;
			}

			return $plugin;
		}

		return false;
	}

	/**
	 * Render pagebuilder grid
	 *
	 * @param   array $elements blocks that build the website
	 *
	 * @return  string
	 *
	 * @throws Exception
	 * @since  4.0
	 */
	private static function render($elements)
	{
		$html = '';

		foreach ($elements as $element)
		{
			$renderData = self::getPluginRenderer($element);

			// Create default element to fill space
			if (!$renderData)
			{
				$renderData['start'] = '<div>';
				$renderData['end']   = '</div>';
			}

			$html .= $renderData['start'];

			if (!empty($element->options->component))
			{
				$html .= '<jdoc:include type="component" />';
			}
			elseif (!empty($element->options->message))
			{
				$html .= '<jdoc:include type="message" />';
			}

			if (!empty($element->children))
			{
				$html .= self::render($element->children);
			}

			$html .= $renderData['end'];
		}

		return $html;
	}
}
