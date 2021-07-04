<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.pagebuilder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Form\Field\PredefinedlistField;

/**
 * PageBuilder Context List
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.pagebuilder
 * @since  4.0
 */
class JFormFieldContext extends PredefinedlistField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $type = 'Context';

	/**
	 * Available contexts
	 *
	 * @var  array
	 * @since  4.0
	 */
	protected $predefinedOptions = array(
		'*'  => 'JALL',
		'layout'  => 'PLG_PAGEBUILDER_CONTEXT_LAYOUT',
		'content'  => 'PLG_PAGEBUILDER_CONTEXT_CONTENT',
	);
}
