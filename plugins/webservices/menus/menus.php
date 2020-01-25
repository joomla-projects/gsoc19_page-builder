<?php
/**
 * @package     Joomla.Menus
 * @subpackage  Webservices.Menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_menus.
 *
 * @since  4.0.0
 */
class PlgWebservicesMenus extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_menus's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$router->createCRUDRoutes(
			'v1/menus/site',
			'menus',
			['component' => 'com_menus', 'client_id' => 0]
		);

		$router->createCRUDRoutes(
			'v1/menus/administrator',
			'menus',
			['component' => 'com_menus', 'client_id' => 1]
		);

		$router->createCRUDRoutes(
			'v1/menus/site/items',
			'items',
			['component' => 'com_menus', 'client_id' => 0]
		);

		$router->createCRUDRoutes(
			'v1/menus/administrator/items',
			'items',
			['component' => 'com_menus', 'client_id' => 1]
		);

		$routes = [
			new Route(
				['GET'], 'v1/menus/site/items/types', 'items.getTypes', [],
				['public' => false, 'component' => 'com_menus', 'client_id' => 0]
			),
			new Route(
				['GET'], 'v1/menus/administrator/items/types', 'items.getTypes', [],
				['public' => false, 'component' => 'com_menus', 'client_id' => 1]
			),
		];

		$router->addRoutes($routes);
	}
}
