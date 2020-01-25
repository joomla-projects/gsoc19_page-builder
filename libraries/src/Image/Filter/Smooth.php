<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Image
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Image\Filter;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Image\ImageFilter;

/**
 * Image Filter class adjust the smoothness of an image.
 *
 * @since  2.5.0
 */
class Smooth extends ImageFilter
{
	/**
	 * Method to apply a filter to an image resource.
	 *
	 * @param   array  $options  An array of options for the filter.
	 *
	 * @return  void
	 *
	 * @since   2.5.0
	 * @throws  \InvalidArgumentException
	 */
	public function execute(array $options = [])
	{
		// Validate that the smoothing value exists and is an integer.
		if (!isset($options[IMG_FILTER_SMOOTH]) || !\is_int($options[IMG_FILTER_SMOOTH]))
		{
			throw new \InvalidArgumentException('No valid smoothing value was given.  Expected integer.');
		}

		// Perform the smoothing filter.
		imagefilter($this->handle, IMG_FILTER_SMOOTH, $options[IMG_FILTER_SMOOTH]);
	}
}
