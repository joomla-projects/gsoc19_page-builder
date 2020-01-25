<?php
/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Image;

use Joomla\CMS\Image\Filter\Brightness as FilterBrightness;
use Joomla\CMS\Image\Filter\Inspector as FilterInspector;
use Joomla\Test\TestHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Image.
 *
 * @since  __DEPLOY_VERSION__
 */
class ImageFilterTest extends UnitTestCase
{
	/**
	 * @var  FilterInspector  The object to test.
	 */
	protected $instance;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setUp(): void
	{
		parent::setUp();

		// Verify that GD support for PHP is available.
		if (!extension_loaded('gd'))
		{
			$this->markTestSkipped('No GD support so skipping Image tests.');
		}

		$this->instance = new FilterInspector(imagecreate(10, 10));
	}

	/**
	 * Tests the Image::__construct method - with an invalid argument.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testConstructorInvalidArgument()
	{
		$this->expectException(\InvalidArgumentException::class);

		$filter = new FilterBrightness('test');
	}

	/**
	 * Tests the Image::__construct method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testConstructor()
	{
		// Create an image handle of the correct size.
		$imageHandle = imagecreatetruecolor(100, 100);

		$filter = new FilterBrightness($imageHandle);

		$this->assertEquals(
			$imageHandle,
			TestHelper::getValue($filter, 'handle')
		);
	}
}
