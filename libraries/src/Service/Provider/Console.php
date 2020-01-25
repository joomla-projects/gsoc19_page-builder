<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Console\SessionGcCommand;
use Joomla\CMS\Console\SessionMetadataGcCommand;
use Joomla\CMS\Session\MetadataManager;
use Joomla\Database\Command\ExportCommand;
use Joomla\Database\Command\ImportCommand;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's console services
 *
 * @since  4.0
 */
class Console implements ServiceProviderInterface
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
		$container->share(
			SessionGcCommand::class,
			function (Container $container)
			{
				/*
				 * The command will need the same session handler that web apps use to run correctly,
				 * since this is based on an option we need to inject the container
				 */
				$command = new SessionGcCommand;
				$command->setContainer($container);

				return $command;
			},
			true
		);

		$container->share(
			SessionMetadataGcCommand::class,
			function (Container $container)
			{
				return new SessionMetadataGcCommand($container->get('session'), $container->get(MetadataManager::class));
			},
			true
		);

		$container->share(
			ExportCommand::class,
			function (Container $container)
			{
				return new ExportCommand($container->get('db'));
			},
			true
		);

		$container->share(
			ImportCommand::class,
			function (Container $container)
			{
				return new ImportCommand($container->get('db'));
			},
			true
		);
	}
}
