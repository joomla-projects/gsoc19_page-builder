<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Factory as CmsFactory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Document\HtmlDocument;

/**
 * The menu controller for ajax requests
 *
 * @since  3.9.0
 */
class AjaxController extends BaseController
{
	/**
	 * Method to fetch associations
	 *
	 * The method assumes that the following http parameters are passed in an Ajax Get request:
	 * handles request for the page builder
	 *
	 * @return string JSON answer
	 * @since  3.9.0
	 * @author Allan Karlson
	 */

	public function fetchAssociations()
	{
		switch ($this->input->get("action"))
		{
			case "pagebuilder_liveview":

				$jsonLayout = base64_decode($this->input->get("data"));
				//$view = RenderHelper::renderElements($jsonLayout); // layout JSON is encoded

				// Apodis index.php need a "grid" parameter which I encoded because of "parameter sanitizing",
				// therefore set it again to the $_GET parameter now
				$_GET['grid'] = $jsonLayout;

				$config = Factory::getConfig();

				$fulltempPath = $config->get('tmp_path') . "/../templates/";

				$htmlDoc = new HtmlDocument();
				$params  = ["directory" => $fulltempPath, "template" => "apodis", "file" => "index.php"];
				$htmlDoc->parse($params);
				//$outrender = $htmlDoc->render(true);


				// Try now with iframe
				// Save first the DB Entry
				$templateID = $this->input->get("template_id");
				$db         = CmsFactory::getDbo();


				$query = $db->getQuery(true);
				$query
					->select("params")
					->from($db->quoteName('#__template_styles'))
					->where($db->quoteName('id') . ' = ' . (int) $templateID);
				$db->setQuery($query)->execute();


				$db->setQuery($query);
				$orgParams = $db->loadObject();


				$paramParsed         = json_decode($orgParams->params, true);
				$paramParsed["grid"] = $jsonLayout;
				$paramParsed         = json_encode($paramParsed);


				$query = $db->getQuery(true);
				$query
					->update($db->quoteName('#__template_styles'))
					->set($db->quoteName('home') . ' = ' . 0)
					->where($db->quoteName('client_id') . ' = ' . 0);
				$db->setQuery($query)->execute();


				$query = $db->getQuery(true);
				$query
					->update($db->quoteName('#__template_styles'))
					->set($db->quoteName('params') . ' = ' . $db->quote($paramParsed))
					->set($db->quoteName('home') . ' = ' . 1)
					->where($db->quoteName('id') . ' = ' . (int) $templateID);
				$db->setQuery($query)->execute();


				echo new JsonResponse(true, "pagebuilder preview rendering");


		}
	}
}
