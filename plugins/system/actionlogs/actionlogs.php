<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
class PlgSystemActionLogs extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   object  $subject  The object to observe.
	 * @param   array   $config   An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Import actionlog plugin group so that these plugins will be triggered for events
		PluginHelper::importPlugin('actionlog');
	}

	/**
	 * Listener for the `onAfterInitialise` event
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function onAfterInitialise()
	{
		// Load plugin language files.
		$this->loadLanguage();
	}

	/**
	 * Adds additional fields to the user editing form for logs e-mail notifications
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 *
	 * @throws  Exception
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		$formName = $form->getName();

		$allowedFormNames = [
			'com_users.profile',
			'com_users.user',
		];

		if (!in_array($formName, $allowedFormNames, true))
		{
			return true;
		}

		/**
		 * We only allow users who has Super User permission change this setting for himself or for other users
		 * who has same Super User permission
		 */

		$user = Factory::getUser();

		if (!$user->authorise('core.admin'))
		{
			return true;
		}

		// If we are on the save command, no data is passed to $data variable, we need to get it directly from request
		$jformData = $this->app->input->get('jform', [], 'array');

		if ($jformData && !$data)
		{
			$data = $jformData;
		}

		if (is_array($data))
		{
			$data = (object) $data;
		}

		if (empty($data->id) || !User::getInstance($data->id)->authorise('core.admin'))
		{
			return true;
		}

		Form::addFormPath(__DIR__ . '/forms');

		if ((!PluginHelper::isEnabled('actionlog', 'joomla')) && Factory::getApplication()->isClient('administrator'))
		{
			$form->loadFile('information', false);

			return true;
		}

		if (!PluginHelper::isEnabled('actionlog', 'joomla'))
		{
			return true;
		}

		$form->loadFile('actionlogs', false);
	}

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onContentPrepareData($context, $data)
	{
		if (!in_array($context, ['com_users.profile', 'com_admin.profile', 'com_users.user']))
		{
			return true;
		}

		if (is_array($data))
		{
			$data = (object) $data;
		}

		if (!User::getInstance($data->id)->authorise('core.admin'))
		{
			return true;
		}

		$db = $this->db;
		$id = (int) $data->id;

		$query = $db->getQuery(true)
			->select($db->quoteName(['notify', 'extensions']))
			->from($db->quoteName('#__action_logs_users'))
			->where($db->quoteName('user_id') . ' = :userid')
			->bind(':userid', $id, ParameterType::INTEGER);

		try
		{
			$values = $db->setQuery($query)->loadObject();
		}
		catch (ExecutionFailureException $e)
		{
			return false;
		}

		if (!$values)
		{
			return true;
		}

		$data->actionlogs                       = new StdClass;
		$data->actionlogs->actionlogsNotify     = $values->notify;
		$data->actionlogs->actionlogsExtensions = $values->extensions;

		return true;
	}

	/**
	 * Runs after the HTTP response has been sent to the client and delete log records older than certain days
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	public function onAfterRespond()
	{
		$daysToDeleteAfter = (int) $this->params->get('logDeletePeriod', 0);

		if ($daysToDeleteAfter <= 0)
		{
			return;
		}

		// The delete frequency will be once per day
		$deleteFrequency = 3600 * 24;

		// Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
		// timestamp. If the difference is greater than the cache timeout we shall not execute again.
		$now  = time();
		$last = (int) $this->params->get('lastrun', 0);

		if (abs($now - $last) < $deleteFrequency)
		{
			return;
		}

		// Update last run status
		$this->params->set('lastrun', $now);

		$db     = $this->db;
		$params = $this->params->toString('JSON');
		$query  = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = :params')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('actionlogs'))
			->bind(':params', $params);

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return;
		}

		try
		{
			// Update the plugin parameters
			$result = $db->setQuery($query)->execute();

			$this->clearCacheGroups(['com_plugins'], [0, 1]);
		}
		catch (Exception $exc)
		{
			// If we failed to execite
			$db->unlockTables();
			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		// Abort on failure
		if (!$result)
		{
			return;
		}

		$daysToDeleteAfter = (int) $this->params->get('logDeletePeriod', 0);
		$now               = Factory::getDate()->toSql();

		if ($daysToDeleteAfter > 0)
		{
			$days = -1 * $daysToDeleteAfter;

			$query->clear()
				->delete($db->quoteName('#__action_logs'))
				->where($db->quoteName('log_date') . ' < ' . $query->dateAdd(':now', ':days', 'DAY'))
				->bind(':now', $now)
				->bind(':days', $days, ParameterType::INTEGER);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				// Ignore it
				return;
			}
		}
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
	{
		$conf = Factory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $clientId)
			{
				try
				{
					$options = [
						'defaultgroup' => $group,
						'cachebase'    => $clientId ? JPATH_ADMINISTRATOR . '/cache' :
							$conf->get('cache_path', JPATH_SITE . '/cache')
					];

					$cache = Cache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * @param   array    $user     Holds the new user data.
	 * @param   boolean  $isNew    True if a new user is stored.
	 * @param   boolean  $success  True if user was successfully stored in the database.
	 * @param   string   $msg      Message.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onUserAfterSave($user, $isNew, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		// Clear access rights in case user groups were changed.
		$userObject = new User($user['id']);
		$userObject->clearAccessRights();

		$authorised = $userObject->authorise('core.admin');
		$userid     = (int) $user['id'];
		$db         = $this->db;

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__action_logs_users'))
			->where($db->quoteName('user_id') . ' = :userid')
			->bind(':userid', $userid, ParameterType::INTEGER);

		try
		{
			$exists = (bool) $db->setQuery($query)->loadResult();
		}
		catch (ExecutionFailureException $e)
		{
			return false;
		}

		$query->clear();

		// If preferences don't exist, insert.
		if (!$exists && $authorised && isset($user['actionlogs']))
		{
			$notify  = (int) $user['actionlogs']['actionlogsNotify'];
			$values  = [':userid', ':notify'];
			$bind    = [$userid, $notify];
			$columns = ['user_id', 'notify'];

			$query->bind($values, $bind, ParameterType::INTEGER);

			if (isset($user['actionlogs']['actionlogsExtensions']))
			{
				$values[]  = ':extension';
				$columns[] = 'extensions';
				$extension = json_encode($user['actionlogs']['actionlogsExtensions']);
				$query->bind(':extension', $extension);
			}

			$query->insert($db->quoteName('#__action_logs_users'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
		}
		elseif ($exists && $authorised && isset($user['actionlogs']))
		{
			// Update preferences.
			$notify = (int) $user['actionlogs']['actionlogsNotify'];
			$values = [$db->quoteName('notify') . ' = :notify'];

			$query->bind(':notify', $notify, ParameterType::INTEGER);

			if (isset($user['actionlogs']['actionlogsExtensions']))
			{
				$values[] = $db->quoteName('extensions') . ' = :extension';
				$extension = json_encode($user['actionlogs']['actionlogsExtensions']);
				$query->bind(':extension', $extension);
			}

			$query->update($db->quoteName('#__action_logs_users'))
				->set($values)
				->where($db->quoteName('user_id') . ' = :userid')
				->bind(':userid', $userid, ParameterType::INTEGER);
		}
		elseif ($exists && !$authorised)
		{
			// Remove preferences if user is not authorised.
			$query->delete($db->quoteName('#__action_logs_users'))
				->where($db->quoteName('user_id') . ' = :userid')
				->bind(':userid', $userid, ParameterType::INTEGER);
		}
		else
		{
			return false;
		}

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (ExecutionFailureException $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Removes user preferences
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was successfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$db     = $this->db;
		$userid = (int) $user['id'];

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__action_logs_users'))
			->where($db->quoteName('user_id') . ' = :userid')
			->bind(':userid', $userid, ParameterType::INTEGER);

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (ExecutionFailureException $e)
		{
			return false;
		}

		return true;
	}
}
