<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\ApiMVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service MVC factory.
 *
 * @since  4.0.0
 */
class MVCFactory implements ServiceProviderInterface
{
	/**
	 * The extension namespace
	 *
	 * @var  string
	 *
	 * @since   4.0.0
	 */
	private $namespace;

	/**
	 * MVCFactory constructor.
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   4.0.0
	 */
	public function __construct(string $namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->set(
			MVCFactoryInterface::class,
			function (Container $container)
			{
				if (\Joomla\CMS\Factory::getApplication()->isClient('api'))
				{
					$factory = new ApiMVCFactory($this->namespace);
				}
				else
				{
					$factory = new \Joomla\CMS\MVC\Factory\MVCFactory($this->namespace);
				}

				$factory->setFormFactory($container->get(FormFactoryInterface::class));

				return $factory;
			}
		);
	}
}
