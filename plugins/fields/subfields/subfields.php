<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subfields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Fields subfields Plugin
 *
 * @since  4.0.0
 */
class PlgFieldsSubfields extends FieldsPlugin
{
	/**
	 * Two-dimensional array to hold to do a fast in-memory caching of rendered
	 * subfield values.
	 *
	 * @var array
	 *
	 * @since 4.0.0
	 */
	protected $renderCache = array();

	/**
	 * Array to do a fast in-memory caching of all custom field items.
	 *
	 * @var array
	 *
	 * @since 4.0.0
	 */
	protected static $customFieldsCache = null;

	/**
	 * Handles the onContentPrepareForm event. Adds form definitions to relevant forms.
	 *
	 * @param   JForm         $form  The form to manipulate
	 * @param   array|object  $data  The data of the form
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Get the path to our own form definition (basically ./params/subfields.xml)
		$path = $this->getFormPath($form, $data);

		if ($path === null)
		{
			return;
		}

		// Ensure it is an object
		$formData = (object) $data;

		// Now load our own form definition into a DOMDocument, because we want to manipulate it
		$xml = new DOMDocument;
		$xml->load($path);

		// Prepare a DOMXPath object
		$xmlxpath = new DOMXPath($xml);

		/**
		 * Get all fields of type "subfieldstype" in our own XML
		 *
		 * @var $valuefields \DOMNodeList
		 */
		$valuefields = $xmlxpath->evaluate('//field[@type="subfieldstype"]');

		// If we haven't found it, something is wrong
		if (!$valuefields || $valuefields->length != 1)
		{
			return;
		}

		// Now iterate over those fields and manipulate them, set its parameter `context` to our context
		foreach ($valuefields as $valuefield)
		{
			$valuefield->setAttribute('context', $formData->context);
		}

		// When this is not a new instance (editing an existing instance)
		if (isset($formData->id) && $formData->id > 0)
		{
			// Don't allow the 'repeat' attribute to be edited
			foreach ($xmlxpath->evaluate('//field[@name="repeat"]') as $field)
			{
				$field->setAttribute('readonly', '1');
			}
		}

		// And now load our manipulated form definition into the JForm
		$form->load($xml->saveXML(), true, '/form/*');
	}

	/**
	 * Manipulates the $field->value before the field is being passed to
	 * onCustomFieldsPrepareField.
	 *
	 * @param   string     $context  The context
	 * @param   object     $item     The item
	 * @param   \stdClass  $field    The field
	 *
	 * @return  void
	 *
	 * @since 4.0.0
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, $field)
	{
		// Check if the field should be processed by us
		if (!$this->isTypeSupported($field->type))
		{
			return;
		}

		$decoded_value = json_decode($field->value, true);

		if (!$decoded_value || !is_array($decoded_value))
		{
			return;
		}

		$field->value = $decoded_value;
	}

	/**
	 * Renders this fields value by rendering all sub fields and joining all those rendered sub fields together.
	 *
	 * @param   string     $context  The context
	 * @param   object     $item     The item
	 * @param   \stdClass  $field    The field
	 *
	 * @return  string
	 *
	 * @since 4.0.0
	 */
	public function onCustomFieldsPrepareField($context, $item, $field)
	{
		// Check if the field should be processed by us
		if (!$this->isTypeSupported($field->type))
		{
			return;
		}

		// If we don't have any subfields (or values for them), nothing to do.
		if (!is_array($field->value) || count($field->value) < 1)
		{
			return;
		}

		// Get the field params
		$field_params = $this->getParamsFromField($field);

		/**
		 * Placeholder to hold all rows (if this field is repeatable).
		 * Each array entry is another array representing a row, containing all of the sub fields that
		 * are valid for this row and their raw and rendered values.
		 */
		$subfields_rows = array();

		// Create an array with entries being subfields forms, and if not repeatable, containing only one element.
		$rows = $field->value;

		if ($field_params->get('repeat', '1') == '0')
		{
			$rows = array($field->value);
		}

		// Iterate over each row of the data
		foreach ($rows as $row)
		{
			// Holds all sub fields of this row, incl. their raw and rendered value
			$row_subfields = array();

			// For each row, iterate over all the subfields
			foreach ($this->getSubfieldsFromField($field) as $subfield)
			{
				// Just to be sure, unset this subfields value (and rawvalue)
				$subfield->rawvalue = $subfield->value = '';

				// If we have data for this field in the current row
				if (isset($row[$subfield->name]) && $row[$subfield->name])
				{
					// Take over the data into our virtual subfield
					$subfield->rawvalue = $subfield->value = $row[$subfield->name];
				}

				// Do we want to render the value of this field, and is the value non-empty?
				if ($subfield->value !== '' && $subfield->render_values == '1')
				{
					/**
					 * Construct the cache-key for our renderCache. It is important that the cache key
					 * is as unique as possible to avoid false duplicates (e.g. type and rawvalue is not
					 * enough for the cache key, because type 'list' and value '1' can have different
					 * rendered values, depending on the list items), but it also must be as general as possible
					 * to not cause too many unneeded rendering processes (e.g. the type 'text' will always be
					 * rendered the same when it has the same rawvalue).
					 */
					$renderCache_key = serialize(
						array(
							$subfield->type,
							$subfield->id,
							$subfield->rawvalue,
						)
					);

					// Let's see if we have a fast in-memory result for this
					if (isset($this->renderCache[$renderCache_key]))
					{
						$subfield->value = $this->renderCache[$renderCache_key];
					}
					else
					{
						// Render this virtual subfield
						$subfield->value = Factory::getApplication()->triggerEvent(
							'onCustomFieldsPrepareField',
							array($context, $item, $subfield)
						);
						$this->renderCache[$renderCache_key] = $subfield->value;
					}
				}

				// Flatten the value if it is an array (list, checkboxes, etc.) [independent of render_values]
				if (is_array($subfield->value))
				{
					$subfield->value = implode(' ', $subfield->value);
				}

				// Store the subfield (incl. its raw and rendered value) into this rows sub fields
				$row_subfields[$subfield->fieldname] = $subfield;
			}

			// Store all the sub fields of this row
			$subfields_rows[] = $row_subfields;
		}

		// Store all the rows and their corresponding sub fields in $field->subfields_rows
		$field->subfields_rows = $subfields_rows;

		// Call our parent to combine all those together for the final $field->value
		return parent::onCustomFieldsPrepareField($context, $item, $field);
	}

	/**
	 * Returns a DOMElement which is the child of $orig_parent and represents
	 * the form XML definition for this field.
	 *
	 * @param   \stdClass   $field        The field
	 * @param   DOMElement  $orig_parent  The original parent element
	 * @param   JForm       $form         The form
	 *
	 * @return  \DOMElement
	 *
	 * @since 4.0.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $orig_parent, Form $form)
	{
		// Call the onCustomFieldsPrepareDom method on FieldsPlugin
		$parent_field = parent::onCustomFieldsPrepareDom($field, $orig_parent, $form);

		if (!$parent_field)
		{
			return $parent_field;
		}

		// Override the fieldname attribute of the subform - this is being used to index the rows
		$parent_field->setAttribute('fieldname', 'row');

		// Make sure this `field` DOMElement has an attribute type=subform - our parent set this to
		// subfields, because that is our name. But we want the XML to be a subform.
		$parent_field->setAttribute('type', 'subform');

		// If the user configured this subfields instance as required
		if ($field->required)
		{
			// Then we need to have at least one row
			$parent_field->setAttribute('min', '1');
		}

		// Get the configured parameters for this field
		$field_params = $this->getParamsFromField($field);

		// If this fields should be repeatable, set some attributes on the subform element
		if ($field_params->get('repeat', '1') == '1')
		{
			$parent_field->setAttribute('multiple', 'true');
			$parent_field->setAttribute('layout', 'joomla.form.field.subform.repeatable-table');
		}

		// Create a child 'form' DOMElement under the field[type=subform] element.
		$parent_fieldset = $parent_field->appendChild(new DOMElement('form'));
		$parent_fieldset->setAttribute('hidden', 'true');
		$parent_fieldset->setAttribute('name', ($field->name . '_modal'));

		// If this field should be repeatable, set some attributes on the modal
		if ($field_params->get('repeat', '1') == '1')
		{
			$parent_fieldset->setAttribute('repeat', 'true');
		}

		// Get the configured sub fields for this field
		$subfields = $this->getSubfieldsFromField($field);

		// If we have 5 or more of them, use the `repeatable` layout instead of the `repeatable-table`
		if (count($subfields) >= 5)
		{
			$parent_field->setAttribute('layout', 'joomla.form.field.subform.repeatable');
		}

		// Iterate over the sub fields to call prepareDom on each of those sub-fields
		foreach ($subfields as $subfield)
		{
			// Let the relevant plugins do their work and insert the correct
			// DOMElement's into our $parent_fieldset.
			Factory::getApplication()->triggerEvent(
				'onCustomFieldsPrepareDom',
				array($subfield, $parent_fieldset, $form)
			);
		}

		return $parent_field;
	}

	/**
	 * Returns an array of all options configured for this field.
	 *
	 * @param   \stdClass  $field  The field
	 *
	 * @return  \stdClass[]
	 *
	 * @since 4.0.0
	 */
	protected function getOptionsFromField(\stdClass $field)
	{
		$result = array();

		// Fetch the options from the plugin
		$params = $this->getParamsFromField($field);

		foreach ($params->get('options', array()) as $option)
		{
			$result[] = (object) $option;
		}

		return $result;
	}

	/**
	 * Returns the configured params for a given field.
	 *
	 * @param   \stdClass  $field  The field
	 *
	 * @return  \Joomla\Registry\Registry
	 *
	 * @since 4.0.0
	 */
	protected function getParamsFromField(\stdClass $field)
	{
		$params = (clone $this->params);

		if (isset($field->fieldparams) && is_object($field->fieldparams))
		{
			$params->merge($field->fieldparams);
		}

		return $params;
	}

	/**
	 * Returns an array of all subfields for a given field. This will always return a bare clone
	 * of a sub field, so manipulating it is safe.
	 *
	 * @param   \stdClass  $field  The field
	 *
	 * @return  \stdClass[]
	 *
	 * @since 4.0.0
	 */
	protected function getSubfieldsFromField(\stdClass $field)
	{
		if (static::$customFieldsCache === null)
		{
			// Prepare our cache
			static::$customFieldsCache = array();

			// Get all custom field instances
			$customFields = FieldsHelper::getFields('');

			foreach ($customFields as $customField)
			{
				// Store each custom field instance in our cache with its id as key
				static::$customFieldsCache[$customField->id] = $customField;
			}
		}

		$result = array();

		// Iterate over all configured options for this field
		foreach ($this->getOptionsFromField($field) as $option)
		{
			// Check whether the wanted sub field really is an existing custom field
			if (!isset(static::$customFieldsCache[$option->customfield]))
			{
				continue;
			}

			// Get a clone of the sub field, so we and the caller can do some manipulation with it.
			$cur_field = (clone static::$customFieldsCache[$option->customfield]);

			// Manipulate it and add our custom configuration to it
			$cur_field->render_values = $option->render_values;

			/**
			 * Set the name of the sub field to its id so that the values in the database are being saved
			 * based on the id of the sub fields, not on their name. Actually we do not need the name of
			 * the sub fields to render them, but just to make sure we have the name when we need it, we
			 * store it as `fieldname`.
			 */
			$cur_field->fieldname = $cur_field->name;
			$cur_field->name = 'field' . $cur_field->id;

			// And add it to our result
			$result[] = $cur_field;
		}

		return $result;
	}
}
