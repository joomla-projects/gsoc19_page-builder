<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Sql
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

/**
 * Fields Sql Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsSql extends \Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin
{
	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   Form        $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		$fieldNode->setAttribute('value_field', 'text');
		$fieldNode->setAttribute('key_field', 'value');

		return $fieldNode;
	}

	/**
	 * The save event.
	 *
	 * @param   string   $context  The context
	 * @param   JTable   $item     The table
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function onContentBeforeSave($context, $item, $isNew, $data = array())
	{
		// Only work on new SQL fields
		if ($context != 'com_fields.field' || !isset($item->type) || $item->type != 'sql' || !$isNew)
		{
			return true;
		}

		// If we are not a super admin, don't let the user create a SQL field
		if (!Access::getAssetRules(1)->allow('core.admin', $this->app->getIdentity()->getAuthorisedGroups()))
		{
			$item->setError(Text::_('PLG_FIELDS_SQL_CREATE_NOT_POSSIBLE'));

			return false;
		}

		$rules = $item->getRules()->getData();

		// Only change the edit rule and when it is empty
		if (array_key_exists('core.edit', $rules) && !$rules['core.edit']->getData())
		{
			// Set the denied flag on the root group
			$rules['core.edit']->mergeIdentity(1, false);
			$this->app->enqueueMessage(Text::_('PLG_FIELDS_SQL_RULES_ADAPTED'), 'warning');
		}

		return true;
	}
}
