<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Feed\Parser;

use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedLink;
use Joomla\CMS\Feed\Parser\AtomParser;
use Joomla\Tests\Unit\UnitTestCase;
use ReflectionClass;
use SimpleXMLElement;
use XMLReader;

/**
 * Test class for AtomParser.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class AtomParserTest extends UnitTestCase
{
	/**
	 * Tests AtomParser::handleAuthor()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleAuthor()
	{
		$author = [
			'name' => 'John Doe',
			'email' => 'john@doe.name',
			'uri' => 'http://doe.name',
		];

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<author/>');
		$xmlElement->name = $author['name'];
		$xmlElement->email = $author['email'];
		$xmlElement->uri = $author['uri'];

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('setAuthor')
			->with($author['name'], $author['email'], $author['uri']);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleAuthor');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleContributor()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleContributor()
	{
		$contributor = [
			'name' => 'John Doe',
			'email' => 'john@doe.name',
			'uri' => 'http://doe.name',
		];

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<contributor />');
		$xmlElement->name = $contributor['name'];
		$xmlElement->email = $contributor['email'];
		$xmlElement->uri = $contributor['uri'];

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('addContributor')
			->with($contributor['name'], $contributor['email'], $contributor['uri']);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleContributor');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleGenerator()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleGenerator()
	{
		$generator = 'Joomla';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<generator>' . $generator . '</generator>');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('generator', $generator);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleGenerator');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleId()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleId()
	{
		$id = 'http://domain.com/path/to/resource';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<id>' . $id . '</id>');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('uri', $id);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleId');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleLink()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleLink()
	{
		$href = 'http://domain.com/path/to/resource';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<link href="' . $href . '" />');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('link',
				$this->callback(
					function ($param) use ($href)
					{
						return $param instanceof FeedLink && $param->uri === $href;
					}
				)
			);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleLink');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleRights()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleRights()
	{
		$copyright = 'All Rights Reserved.';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<rights>' . $copyright . '</rights>');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('copyright', $copyright);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleRights');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleSubtitle()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleSubtitle()
	{
		$subtitle = 'Lorem Ipsum ...';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<subtitle>' . $subtitle . '</subtitle>');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('description', $subtitle);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleSubtitle');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleTitle()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleTitle()
	{
		$title = 'My Title.';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<title>' . $title . '</title>');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('title', $title);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleTitle');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::handleUpdated()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testHandleUpdated()
	{
		$date = '2019-01-01T00:00:00Z';

		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<updated>' . $date . '</updated>');

		$feedMock = $this->createMock(Feed::class);
		$feedMock
			->expects($this->once())
			->method('__set')
			->with('updatedDate', $date);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('handleUpdated');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedMock, $xmlElement);
	}

	/**
	 * Tests AtomParser::initialise()
	 *
	 * @return void
	 * @since         3.1.4
	 * @throws \ReflectionException
	 */
	public function testInitialiseSetsDefaultVersion()
	{
		$readerMock = $this->createMock(XMLReader::class);
		$readerMock
			->expects($this->once())
			->method('getAttribute')
			->with('version')
			->willReturn('Some Version');

		// Use reflection to test protected method
		$atomParser = new AtomParser($readerMock);
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('initialise');
		$method->setAccessible(true);
		$method->invoke($atomParser);
		$attribute = $reflectionClass->getProperty('version');
		$attribute->setAccessible(true);

		$this->assertEquals('1.0', $attribute->getValue($atomParser));
	}

	/**
	 * Tests AtomParser::initialise()
	 *
	 * @return void
	 * @since         3.1.4
	 * @throws \ReflectionException
	 */
	public function testInitialiseSetsOldVersion()
	{
		$readerMock = $this->createMock(XMLReader::class);
		$readerMock
			->expects($this->once())
			->method('getAttribute')
			->with('version')
			->willReturn('0.3');

		// Use reflection to test protected method
		$atomParser = new AtomParser($readerMock);
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('initialise');
		$method->setAccessible(true);
		$method->invoke($atomParser);
		$attribute = $reflectionClass->getProperty('version');
		$attribute->setAccessible(true);

		$this->assertEquals('0.3', $attribute->getValue($atomParser));
	}

	/**
	 * Tests AtomParser::processFeedEntry()
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws \ReflectionException
	 */
	public function testProcessFeedEntry()
	{
		// It's currently not possible to mock simple xml element
		// @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/417
		$xmlElement = new SimpleXMLElement('<entry><id>http://example.com/id</id>
			<title>title</title><updated>August 25, 1991</updated><summary>summary</summary></entry>'
		);

		$feedEntryMock = $this->createMock(FeedEntry::class);
		$feedEntryMock
			->expects($this->any())
			->method('__set')
			->withConsecutive(
				['uri', 'http://example.com/id'],
				['title', 'title'],
				['updatedDate', 'August 25, 1991'],
				['content', 'summary']
			);

		// Use reflection to test protected method
		$atomParser = new AtomParser($this->createMock(XMLReader::class));
		$reflectionClass = new ReflectionClass($atomParser);
		$method = $reflectionClass->getMethod('processFeedEntry');
		$method->setAccessible(true);
		$method->invoke($atomParser, $feedEntryMock, $xmlElement);
	}
}
