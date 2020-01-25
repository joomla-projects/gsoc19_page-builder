<?php
/**
 * Bootstrap file for the Joomla! CMS [with legacy libraries].
 * Including this file into your application will make Joomla libraries available for use.
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
defined('JPATH_PLATFORM') or define('JPATH_PLATFORM', __DIR__);

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

defined('IS_WIN') or define('IS_WIN', ($os === 'WIN') ? true : false);
defined('IS_UNIX') or define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')) ? true : false);

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';

	// If JLoader still does not exist panic.
	if (!class_exists('JLoader'))
	{
		throw new RuntimeException('Joomla Platform not loaded.');
	}
}

// Setup the autoloaders.
JLoader::setup();

// Register the library base path for CMS libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms', false, true);

// Create the Composer autoloader
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';

// We need to pull our decorated class loader into memory before unregistering Composer's loader
class_exists('\\Joomla\\CMS\\Autoload\\ClassLoader');

$loader->unregister();

// Decorate Composer autoloader
spl_autoload_register([new \Joomla\CMS\Autoload\ClassLoader($loader), 'loadClass'], true, true);

// Register the class aliases for Framework classes that have replaced their Platform equivalents
require_once JPATH_LIBRARIES . '/classmap.php';

/**
 * Register the global exception handler. We will try and set a better Joomla Application Handler
 * once we know the request type (JSON, HTML, Cli etc) as we execute the application so it's
 * fine for this to be HTML Specific.
 */
\Symfony\Component\ErrorHandler\ErrorHandler::register(
	(new \Symfony\Component\ErrorHandler\ErrorHandler)
		->setExceptionHandler([new \Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer(false), 'render'])
);

// Register the error handler which processes E_USER_DEPRECATED errors
set_error_handler(['JErrorPage', 'handleUserDeprecatedErrors'], E_USER_DEPRECATED);

// Suppress phar stream wrapper for non .phar files
$behavior = new \TYPO3\PharStreamWrapper\Behavior;
\TYPO3\PharStreamWrapper\Manager::initialize(
	$behavior->withAssertion(new \TYPO3\PharStreamWrapper\Interceptor\PharExtensionInterceptor)
);

if (in_array('phar', stream_get_wrappers()))
{
	stream_wrapper_unregister('phar');
	stream_wrapper_register('phar', 'TYPO3\\PharStreamWrapper\\PharStreamWrapper');
}

// Define the Joomla version if not already defined.
defined('JVERSION') or define('JVERSION', (new JVersion)->getShortVersion());

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	JLog::addLogger(['logger' => 'messagequeue'], JLog::ALL, ['jerror']);
}

// Register the Crypto lib
JLoader::register('Crypto', JPATH_PLATFORM . '/php-encryption/Crypto.php');

// Register the PasswordHash library.
JLoader::register('PasswordHash', JPATH_PLATFORM . '/phpass/PasswordHash.php');
