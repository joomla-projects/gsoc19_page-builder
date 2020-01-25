<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Console;
use Joomla\CMS\Extension\ExtensionManagerTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Console\Application;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The Joomla! CMS Console Application
 *
 * @since  4.0.0
 */
class ConsoleApplication extends Application implements DispatcherAwareInterface, CMSApplicationInterface
{
	use DispatcherAwareTrait, EventAware, IdentityAware, ContainerAwareTrait, ExtensionManagerTrait, ExtensionNamespaceMapper;

	/**
	 * The name of the application.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $name = null;

	/**
	 * The application message queue.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $messages = [];

	/**
	 * The application session object.
	 *
	 * @var    SessionInterface
	 * @since  4.0.0
	 */
	private $session;

	/**
	 * Class constructor.
	 *
	 * @param   InputInterface       $input       An optional argument to provide dependency injection for the application's input object. If the
	 *                                            argument is an InputInterface object that object will become the application's input object,
	 *                                            otherwise a default input object is created.
	 * @param   OutputInterface      $output      An optional argument to provide dependency injection for the application's output object. If the
	 *                                            argument is an OutputInterface object that object will become the application's output object,
	 *                                            otherwise a default output object is created.
	 * @param   Registry             $config      An optional argument to provide dependency injection for the application's config object. If the
	 *                                            argument is a Registry object that object will become the application's config object,
	 *                                            otherwise a default config object is created.
	 * @param   DispatcherInterface  $dispatcher  An optional argument to provide dependency injection for the application's event dispatcher. If the
	 *                                            argument is a DispatcherInterface object that object will become the application's event dispatcher,
	 *                                            if it is null then the default event dispatcher will be created based on the application's
	 *                                            loadDispatcher() method.
	 * @param   Container            $container   Dependency injection container.
	 *
	 * @since   4.0.0
	 */
	public function __construct(
		?InputInterface $input = null,
		?OutputInterface $output = null,
		?Registry $config = null,
		?DispatcherInterface $dispatcher = null,
		?Container $container = null
	)
	{
		parent::__construct($input, $output, $config);

		$this->setName('Joomla!');
		$this->setVersion(JVERSION);

		// Register the client name as cli
		$this->name = 'cli';

		$container = $container ?: Factory::getContainer();
		$this->setContainer($container);

		if ($dispatcher)
		{
			$this->setDispatcher($dispatcher);
		}

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());
		$this->set('execution.microtimestamp', microtime(true));

		// Set the current directory.
		$this->set('cwd', getcwd());

		// Set up the environment
		$this->input->set('format', 'cli');
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  integer  The exit code for the application
	 *
	 * @since   4.0.0
	 * @throws  \Throwable
	 */
	protected function doExecute(): int
	{
		$exitCode = parent::doExecute();

		$style = new SymfonyStyle($this->getConsoleInput(), $this->getConsoleOutput());

		$methodMap = [
			self::MSG_ALERT     => 'error',
			self::MSG_CRITICAL  => 'caution',
			self::MSG_DEBUG     => 'comment',
			self::MSG_EMERGENCY => 'caution',
			self::MSG_ERROR     => 'error',
			self::MSG_INFO      => 'note',
			self::MSG_NOTICE    => 'note',
			self::MSG_WARNING   => 'warning',
		];

		// Output any enqueued messages before the app exits
		foreach ($this->getMessageQueue() as $type => $messages)
		{
			$method = $methodMap[$type] ?? 'comment';

			$style->$method($messages);
		}

		return $exitCode;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Throwable
	 */
	public function execute()
	{
		// Load extension namespaces
		$this->createExtensionNamespaceMap();

		// Import CMS plugin groups to be able to subscribe to events
		PluginHelper::importPlugin('system');
		PluginHelper::importPlugin('console');

		parent::execute();
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function enqueueMessage($msg, $type = self::MSG_INFO)
	{
		if (!key_exists($type, $this->messages))
		{
			$this->messages[$type] = [];
		}

		$this->messages[$type][] = $msg;
	}

	/**
	 * Gets the name of the current running application.
	 *
	 * @return  string  The name of the application.
	 *
	 * @since   4.0.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get the commands which should be registered by default to the application.
	 *
	 * @return  \Joomla\Console\CommandInterface[]
	 *
	 * @since   4.0.0
	 */
	protected function getDefaultCommands(): array
	{
		return array_merge(
			parent::getDefaultCommands(),
			[
				new Console\CleanCacheCommand,
				new Console\CheckUpdatesCommand,
				new Console\RemoveOldFilesCommand,
				new Console\AddUserCommand,
				new Console\AddUserToGroupCommand,
				new Console\RemoveUserFromGroupCommand,
				new Console\DeleteUserCommand,
				new Console\ChangeUserPasswordCommand,
				new Console\ListUserCommand,
			]
		);
	}

	/**
	 * Retrieve the application configuration object.
	 *
	 * @return  Registry
	 *
	 * @since   4.0.0
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   4.0.0
	 */
	public function getMessageQueue()
	{
		return $this->messages;
	}

	/**
	 * Method to get the application session object.
	 *
	 * @return  SessionInterface  The session object
	 *
	 * @since   4.0.0
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Check the client interface by name.
	 *
	 * @param   string  $identifier  String identifier for the application interface
	 *
	 * @return  boolean  True if this application is of the given type client interface.
	 *
	 * @since   4.0.0
	 */
	public function isClient($identifier)
	{
		return $this->getName() === $identifier;
	}

	/**
	 * Flag if the application instance is a CLI or web based application.
	 *
	 * Helper function, you should use the native PHP functions to detect if it is a CLI application.
	 *
	 * @return  boolean
	 *
	 * @since       4.0.0
	 * @deprecated  5.0  Will be removed without replacements
	 */
	public function isCli()
	{
		return true;
	}

	/**
	 * Sets the session for the application to use, if required.
	 *
	 * @param   SessionInterface  $session  A session object.
	 *
	 * @return  $this
	 *
	 * @since   4.0.0
	 */
	public function setSession(SessionInterface $session): self
	{
		$this->session = $session;

		return $this;
	}

	/**
	 * Returns the application \JMenu object.
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  null
	 *
	 * @since   4.0.0
	 */
	public function getMenu($name = null, $options = array())
	{
		return null;
	}
}
