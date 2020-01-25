<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\View\Updatesite;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use Joomla\Component\Installer\Administrator\Model\UpdatesiteModel;
use Joomla\Component\Installer\Administrator\View\Installer\HtmlView as InstallerViewDefault;

/**
 * View to edit an update site.
 *
 * @since  4.0.0
 */
class HtmlView extends InstallerViewDefault
{
	/**
	 * The Form object
	 *
	 * @var  Form
	 *
	 * @since   4.0.0
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 *
	 * @since   4.0.0
	 */
	protected $item;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 *
	 * @throws \Exception
	 */
	public function display($tpl = null): void
	{
		/** @var UpdatesiteModel $model */
		$model      = $this->getModel();
		$this->form = $model->getForm();
		$this->item = $model->getItem();

		// Remove the extra_query field if it's a free download extension
		$dlidSupportingSites = InstallerHelper::getDownloadKeySupportedSites(false);
		$update_site_id = $this->item->get('update_site_id');

		if (!in_array($update_site_id, $dlidSupportingSites))
		{
			$this->form->removeField('extra_query');
		}

		// Check for errors.
		if (count($errors = $model->getErrors()))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 *
	 * @throws  \Exception
	 */
	protected function addToolbar(): void
	{
		$app = Factory::getApplication();
		$app->input->set('hidemainmenu', true);

		$user       = $app->getIdentity();
		$userId     = $user->id;
		$checkedOut = !($this->item->checked_out === 0 || $this->item->checked_out === $userId);

		// Since we don't track these assets at the item level, use the category id.
		$canDo = ContentHelper::getActions('com_installer', 'updatesite');

		ToolbarHelper::title(Text::_('COM_INSTALLER_UPDATESITE_EDIT_TITLE'), 'address contact');

		// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
		$itemEditable   = $canDo->get('core.edit');
		$toolbarButtons = [];

		// Can't save the record if it's checked out and editable
		if (!$checkedOut && $itemEditable)
		{
			$toolbarButtons[] = ['apply', 'updatesite.apply'];
			$toolbarButtons[] = ['save', 'updatesite.save'];
		}

		ToolbarHelper::saveGroup($toolbarButtons);

		ToolbarHelper::cancel('updatesite.cancel', 'JTOOLBAR_CLOSE');

		ToolbarHelper::help('JHELP_COMPONENTS_INSTALLER_UPDATESITE_EDIT');
	}
}
