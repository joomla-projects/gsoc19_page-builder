<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Page Builder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
			'name'     		=> Text::_('PLG_PAGEBUILDER_GRID_NAME'),
			'description' 	=> Text::_('PLG_PAGEBUILDER_GRID_DESC'),
			'id'       		=> 'plg_pagebuilder_grid',
			'parent'   		=> array('root','Container'),
			'children' 		=> true
		);
	}
}
