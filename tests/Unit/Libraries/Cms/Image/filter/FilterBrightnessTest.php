<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\CMS\Image\Filter\Brightness as FilterBrightness;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Image.
 *
 * @since  __DEPLOY_VERSION__
 */
class ImageFilterBrightnessTest extends UnitTestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setup(): void
	{
		parent::setUp();

		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			$this->markTestSkipped('No GD support so skipping Image tests.');
		}
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   __DEPLOY_VERSION__
	 */
	protected function tearDown(): void
	{
		parent::tearDown();
	}

	/**
	 * Tests the ImageFilterBrightness::execute method.
	 *
	 * This tests to make sure we can brighten the image.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExecute()
	{
		// Create a image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Define red.
		$red = imagecolorallocate($imageHandle, 127, 0, 0);

		// Draw a red rectangle to fill the image.
		imagefilledrectangle($imageHandle, 0, 0, 100, 100, $red);

		$filter = new FilterBrightness($imageHandle);

		$filter->execute(array(IMG_FILTER_BRIGHTNESS => 10));

		$this->assertEquals(
			137,
			imagecolorat($imageHandle, 50, 50) >> 16 & 0xFF
		);
	}

	/**
	 * Tests the ImageFilterBrightness::execute method - invalid argument.
	 *
	 * This tests to make sure an exception is properly thrown.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testExecuteInvalidArgument()
	{
		$this->expectException(\InvalidArgumentException::class);

		// Create a image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		// Define red.
		$red = imagecolorallocate($imageHandle, 127, 0, 0);

		// Draw a red rectangle to fill the image.
		imagefilledrectangle($imageHandle, 0, 0, 100, 100, $red);

		$filter = new FilterBrightness($imageHandle);

		$filter->execute(array());
	}
}
