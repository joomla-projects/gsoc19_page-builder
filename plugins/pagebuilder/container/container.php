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
 * Plugin to add a container element to the pagebuilder
 *
 * @since  4.0.0
 */
class PlgPagebuilderContainer extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add container element which can have every other element as child
	 *
	 * @param   array  $params  Data for the element
	 *
	 * @return  array   A list of icon definition associative arrays
	 *
	 * @since   4.0.0
	 */
	public function onAddElement($params)
	{
		Text::script('PLG_PAGEBUILDER_CONTAINER_NAME');

		return array(
			'name'     => Text::_('PLG_PAGEBUILDER_CONTAINER_NAME'),
			'id'       => 'plg_pagebuilder_container',
			'children' => '*',
			'root'     => true
		);
	}
}