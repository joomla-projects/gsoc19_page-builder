<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Tracks;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Banners\Administrator\Model\TracksModel;

/**
 * View class for a list of tracks.
 *
 * @since  1.6
 */
class RawView extends BaseHtmlView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{
		/** @var TracksModel $model */
		$model    = $this->getModel();
		$basename = $model->getBaseName();
		$fileType = $model->getFileType();
		$mimeType = $model->getMimeType();
		$content  = $model->getContent();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->document->setMimeEncoding($mimeType);

		/** @var CMSApplication $app */
		$app = Factory::getApplication();
		$app->setHeader(
			'Content-disposition',
			'attachment; filename="' . $basename . '.' . $fileType . '"; creation-date="' . Factory::getDate()->toRFC822() . '"',
			true
		);
		echo $content;
	}
}
