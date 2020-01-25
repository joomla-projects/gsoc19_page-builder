<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Languages Controller.
 *
 * @since  1.5
 */
class InstalledController extends BaseController
{
	/**
	 * Task to set the default language.
	 *
	 * @return  void
	 */
	public function setDefault()
	{
		// Check for request forgeries.
		$this->checkToken();

		$cid = $this->input->get('cid', '');
		$model = $this->getModel('installed');

		if ($model->publish($cid))
		{
			// Switching to the new administrator language for the message
			if ($model->getState('client_id') == 1)
			{
				$language = Factory::getLanguage();
				$newLang = Language::getInstance($cid);
				Factory::$language = $newLang;
				Factory::getApplication()->loadLanguage($language = $newLang);
				$newLang->load('com_languages', JPATH_ADMINISTRATOR);
			}

			if (Multilanguage::isEnabled() && $model->getState('client_id') == 0)
			{
				$msg = Text::_('COM_LANGUAGES_MSG_DEFAULT_MULTILANG_SAVED');
				$type = 'message';
			}
			else
			{
				$msg = Text::_('COM_LANGUAGES_MSG_DEFAULT_LANGUAGE_SAVED');
				$type = 'message';
			}
		}
		else
		{
			$msg = $model->getError();
			$type = 'error';
		}

		$clientId = $model->getState('client_id');
		$this->setRedirect('index.php?option=com_languages&view=installed&client=' . $clientId, $msg, $type);
	}

	/**
	 * Task to switch the administrator language.
	 *
	 * @return  void
	 */
	public function switchAdminLanguage()
	{
		// Check for request forgeries.
		$this->checkToken();

		$cid   = $this->input->get('cid', '');
		$model = $this->getModel('installed');

		// Fetching the language name from the langmetadata.xml or xx-XX.xml respectively.
		$file = JPATH_ADMINISTRATOR . '/language/' . $cid . '/langmetadata.xml';

		if (!is_file($file))
		{
			$file = JPATH_ADMINISTRATOR . '/language/' . $cid . '/' . $cid . '.xml';
		}

		$info         = Installer::parseXMLInstallFile($file);
		$languageName = $info['name'];

		if ($model->switchAdminLanguage($cid))
		{
			// Switching to the new language for the message
			$language = Factory::getLanguage();
			$newLang = Language::getInstance($cid);
			Factory::$language = $newLang;
			Factory::getApplication()->loadLanguage($language = $newLang);
			$newLang->load('com_languages', JPATH_ADMINISTRATOR);

			$msg = Text::sprintf('COM_LANGUAGES_MSG_SWITCH_ADMIN_LANGUAGE_SUCCESS', $languageName);
			$type = 'message';
		}
		else
		{
			$msg = $model->getError();
			$type = 'error';
		}

		$this->setRedirect('index.php?option=com_languages&view=installed', $msg, $type);
	}
}
