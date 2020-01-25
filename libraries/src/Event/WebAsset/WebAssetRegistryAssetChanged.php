<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\WebAsset;

\defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\WebAsset\WebAssetItemInterface;
use Joomla\CMS\WebAsset\WebAssetRegistryInterface;

/**
 * Event class for WebAssetRegistry "asset changed" events
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetRegistryAssetChanged extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = array())
	{
		parent::__construct($name, $arguments);

		// Check for required arguments
		if (!\array_key_exists('asset', $arguments) || !($arguments['asset'] instanceof WebAssetItemInterface))
		{
			throw new BadMethodCallException("Argument 'asset' of event $name is not of the expected type");
		}

		if (!\array_key_exists('assetType', $arguments) || !is_string($arguments['assetType']))
		{
			throw new BadMethodCallException("Argument 'assetType' of event $name is not of the expected type");
		}

		if (!\array_key_exists('change', $arguments) || !is_string($arguments['change']))
		{
			throw new BadMethodCallException("Argument 'change' of event $name is not of the expected type");
		}
	}

	/**
	 * Setter for the subject argument
	 *
	 * @param   WebAssetRegistryInterface  $value  The value to set
	 *
	 * @return  WebAssetRegistryInterface
	 *
	 * @throws  BadMethodCallException  if the argument is not of the expected type
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function setSubject($value)
	{
		if (!$value || !($value instanceof WebAssetRegistryInterface))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
		}

		return $value;
	}

	/**
	 * Return modified asset
	 *
	 * @return  WebAssetItemInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAsset(): WebAssetItemInterface
	{
		return $this->arguments['asset'];
	}

	/**
	 * Return a type of modified asset
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssetType(): string
	{
		return $this->arguments['assetType'];
	}

	/**
	 * Return a type of changes: new, remove, override
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getChange(): string
	{
		return $this->arguments['change'];
	}
}
