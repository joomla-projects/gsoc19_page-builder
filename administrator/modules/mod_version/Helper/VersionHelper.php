<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Version\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Version;

/**
 * Helper for mod_version
 *
 * @since  1.6
 */
abstract class VersionHelper
{
	/**
	 * Get the Joomla version number.
	 *
	 * @return  string  String containing the current Joomla version.
	 */
	public static function getVersion()
	{
		$version = new Version;

		return '&#x200E;' . $version->getShortVersion();
	}
}
