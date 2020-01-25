<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset\AssetItem;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\CMS\WebAsset\WebAssetItem;

/**
 * Web Asset Item class for form.validate asset
 *
 * @since  __DEPLOY_VERSION__
 */
class FormValidateAssetItem extends WebAssetItem implements WebAssetAttachBehaviorInterface
{
	/**
	 * Method called when asset attached to the Document.
	 * Useful for Asset to add a Script options.
	 *
	 * @param   Document  $doc  Active document
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onAttachCallback(Document $doc)
	{
		// Add validate.js language strings
		Text::script('JLIB_FORM_CONTAINS_INVALID_FIELDS');
		Text::script('JLIB_FORM_FIELD_REQUIRED_VALUE');
		Text::script('JLIB_FORM_FIELD_REQUIRED_CHECK');
		Text::script('JLIB_FORM_FIELD_INVALID_VALUE');
	}
}
