<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\WebAsset\Exception\UnknownAssetException;

/**
 * Web Asset Registry interface
 *
 * @since  4.0.0
 */
interface WebAssetRegistryInterface
{
	/**
	 * Get an existing Asset from a registry, by asset name and asset type.
	 *
	 * @param   string  $type  Asset type, script or style etc
	 * @param   string  $name  Asset name
	 *
	 * @return  WebAssetItem
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since   4.0.0
	 */
	public function get(string $type, string $name): WebAssetItemInterface;

	/**
	 * Add Asset to registry of known assets
	 *
	 * @param   string                 $type   Asset type, script or style etc
	 * @param   WebAssetItemInterface  $asset  Asset instance
	 *
	 * @return  self
	 *
	 * @since   4.0.0
	 */
	public function add(string $type, WebAssetItemInterface $asset): self;

	/**
	 * Remove Asset from registry.
	 *
	 * @param   string  $type  Asset type, script or style etc
	 * @param   string  $name  Asset name
	 *
	 * @return  self
	 *
	 * @since   4.0.0
	 */
	public function remove(string $type, string $name): self;

	/**
	 * Check whether the asset exists in the registry.
	 *
	 * @param   string  $type  Asset type, script or style etc
	 * @param   string  $name  Asset name
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function exists(string $type, string $name): bool;
}

