<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Notes;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

/**
 * User notes list view
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * A list of user note objects.
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $items;

	/**
	 * The pagination object.
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 * @since  2.5
	 */
	protected $pagination;

	/**
	 * The model state.
	 *
	 * @var    CMSObject
	 * @since  2.5
	 */
	protected $state;

	/**
	 * The model state.
	 *
	 * @var    User
	 * @since  2.5
	 */
	protected $user;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Override the display method for the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Initialise view variables.
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->user          = $this->get('User');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Turn parameters into registry objects
		foreach ($this->items as $item)
		{
			$item->cparams = new Registry($item->category_params);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_users', 'category', $this->state->get('filter.category_id'));

		ToolbarHelper::title(Text::_('COM_USERS_VIEW_NOTES_TITLE'), 'users user');

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('note.add');
		}

		if ($canDo->get('core.edit.state') || $canDo->get('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state'))
			{
				$childBar->publish('notes.publish')->listCheck(true);
				$childBar->unpublish('notes.unpublish')->listCheck(true);
				$childBar->archive('notes.archive')->listCheck(true);
				$childBar->checkin('notes.checkin')->listCheck(true);
			}

			if (!$this->state->get('filter.published') == -2 && $canDo->get('core.edit.state'))
			{
				$childBar->trash('notes.trash');
			}
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('notes.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_users');
		}

		$toolbar->help('JHELP_USERS_USER_NOTES');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'u.name'        => Text::_('COM_USERS_USER_HEADING'),
			'a.subject'     => Text::_('COM_USERS_SUBJECT_HEADING'),
			'c.title'       => Text::_('COM_USERS_CATEGORY_HEADING'),
			'a.state'       => Text::_('JSTATUS'),
			'a.review_time' => Text::_('COM_USERS_REVIEW_HEADING'),
			'a.id'          => Text::_('JGRID_HEADING_ID')
		);
	}
}
