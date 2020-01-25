<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Components Category field.
 *
 * @since  4.0.0
 */
class TransitionField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $type = 'Transition';

	/**
	 * The component and section separated by ".".
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $extension;

	/**
	 * The workflow stage to use.
	 *
	 * @var   integer
	 */
	protected $workflowStage;

	/**
	 * Method to setup the extension
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result)
		{
			$input = Factory::getApplication()->input;

			if (\strlen($element['extension']))
			{
				$this->extension = (string) $element['extension'];
			}
			else
			{
				$this->extension = $input->getCmd('extension');
			}

			if (\strlen($element['workflow_stage']))
			{
				$this->workflowStage = (int) $element['workflow_stage'];
			}
			else
			{
				$this->workflowStage = $input->getInt('id');
			}
		}

		return $result;
	}

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array  An array of HTMLHelper options.
	 *
	 * @since  4.0.0
	 */
	protected function getOptions()
	{
		// Let's get the id for the current item, either category or content item.
		$jinput = Factory::getApplication()->input;

		// Initialise variable.
		$db = Factory::getDbo();
		$extension = $this->extension;
		$workflowStage = (int) $this->workflowStage;

		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('t.id', 'value'),
					$db->quoteName('t.title', 'text'),
					$db->quoteName('s.condition'),
				]
			)
			->from(
				[
					$db->quoteName('#__workflow_transitions', 't'),
					$db->quoteName('#__workflow_stages', 's'),
					$db->quoteName('#__workflow_stages', 's2'),
				]
			)
			->whereIn($db->quoteName('t.from_stage_id'), [-1, $workflowStage])
			->where(
				[
					$db->quoteName('t.to_stage_id') . ' = ' . $db->quoteName('s.id'),
					$db->quoteName('t.to_stage_id') . ' != :stage1',
					$db->quoteName('s.workflow_id') . ' = ' . $db->quoteName('s2.workflow_id'),
					$db->quoteName('s2.id') . ' = :stage2',
					$db->quoteName('t.published') . ' = 1',
					$db->quoteName('s.published') . ' = 1',
				]
			)
			->bind(':stage1', $workflowStage, ParameterType::INTEGER)
			->bind(':stage2', $workflowStage, ParameterType::INTEGER)
			->order($db->quoteName('t.ordering'));

		$items = $db->setQuery($query)->loadObjectList();

		if (\count($items))
		{
			$user = Factory::getUser();

			$items = array_filter(
				$items,
				function ($item) use ($user, $extension)
				{
					return $user->authorise('core.execute.transition', $extension . '.transition.' . $item->value);
				}
			);

			// Sort by transition name
			$items = ArrayHelper::sortObjects($items, 'value', 1, true, true);

			Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

			$workflow = new Workflow(['extension' => $this->extension]);

			foreach ($items as $item)
			{
				$conditionName = $workflow->getConditionName((int) $item->condition);

				$item->text .= ' [' . Text::_($conditionName) . ']';
			}
		}

		// Get workflow stage title
		$query = $db->getQuery(true)
			->select($db->quoteName('title'))
			->from($db->quoteName('#__workflow_stages'))
			->where($db->quoteName('id') . ' = :stage')
			->bind(':stage', $workflowStage, ParameterType::INTEGER);

		$workflowName = $db->setQuery($query)->loadResult();

		$default = [HTMLHelper::_('select.option', '', Text::_($workflowName))];

		$options = array_merge(parent::getOptions(), $items);

		if (\count($options))
		{
			$default[] = HTMLHelper::_('select.option', '-1', '--------', ['disable' => true]);
		}

		// Merge with defaults
		return array_merge($default, $options);
	}
}
