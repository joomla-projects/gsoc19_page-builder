<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\CacheControllerFactory;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the cache controller dependency
 *
 * @since  4.0
 */
class CacheController implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->alias('cache.controller.factory', CacheControllerFactoryInterface::class)
			->alias(CacheControllerFactory::class, CacheControllerFactoryInterface::class)
			->share(
				CacheControllerFactoryInterface::class,
				function (Container $container)
				{
					return new CacheControllerFactory;
				},
				true
			);
	}
}
