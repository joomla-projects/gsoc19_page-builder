<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Component\Exception\MissingComponentException;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Registry\Registry;

/**
 * Component helper class
 *
 * @since  1.5
 */
class ComponentHelper
{
	/**
	 * The component list cache
	 *
	 * @var    ComponentRecord[]
	 * @since  1.6
	 */
	protected static $components = array();

	/**
	 * Get the component information.
	 *
	 * @param   string   $option  The component option.
	 * @param   boolean  $strict  If set and the component does not exist, the enabled attribute will be set to false.
	 *
	 * @return  ComponentRecord  An object with the information for the component.
	 *
	 * @since   1.5
	 */
	public static function getComponent($option, $strict = false)
	{
		$components = static::getComponents();

		if (isset($components[$option]))
		{
			return $components[$option];
		}

		$result = new ComponentRecord;
		$result->enabled = $strict ? false : true;
		$result->setParams(new Registry);

		return $result;
	}

	/**
	 * Checks if the component is enabled
	 *
	 * @param   string  $option  The component option.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public static function isEnabled($option)
	{
		$components = static::getComponents();

		return isset($components[$option]) && $components[$option]->enabled;
	}

	/**
	 * Checks if a component is installed
	 *
	 * @param   string  $option  The component option.
	 *
	 * @return  integer
	 *
	 * @since   3.4
	 */
	public static function isInstalled($option)
	{
		$components = static::getComponents();

		return isset($components[$option]) ? 1 : 0;
	}

	/**
	 * Gets the parameter object for the component
	 *
	 * @param   string   $option  The option for the component.
	 * @param   boolean  $strict  If set and the component does not exist, false will be returned
	 *
	 * @return  Registry  A Registry object.
	 *
	 * @see     Registry
	 * @since   1.5
	 */
	public static function getParams($option, $strict = false)
	{
		return static::getComponent($option, $strict)->getParams();
	}

	/**
	 * Applies the global text filters to arbitrary text as per settings for current user groups
	 *
	 * @param   string  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @since   2.5
	 */
	public static function filterText($text)
	{
		// Punyencoding utf8 email addresses
		$text = InputFilter::getInstance()->emailToPunycode($text);

		// Filter settings
		$config     = static::getParams('com_config');
		$user       = Factory::getUser();
		$userGroups = Access::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$blackListTags       = array();
		$blackListAttributes = array();

		$customListTags       = array();
		$customListAttributes = array();

		$whiteListTags       = array();
		$whiteListAttributes = array();

		$whiteList  = false;
		$blackList  = false;
		$customList = false;
		$unfiltered = false;

		// Cycle through each of the user groups the user is in.
		// Remember they are included in the Public group as well.
		foreach ($userGroups as $groupId)
		{
			// May have added a group by not saved the filters.
			if (!isset($filters->$groupId))
			{
				continue;
			}

			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType = strtoupper($filterData->filter_type);

			if ($filterType === 'NH')
			{
				// Maximum HTML filtering.
			}
			elseif ($filterType === 'NONE')
			{
				// No HTML filtering.
				$unfiltered = true;
			}
			else
			{
				// Blacklist or whitelist.
				// Preprocess the tags and attributes.
				$tags           = explode(',', $filterData->filter_tags);
				$attributes     = explode(',', $filterData->filter_attributes);
				$tempTags       = array();
				$tempAttributes = array();

				foreach ($tags as $tag)
				{
					$tag = trim($tag);

					if ($tag)
					{
						$tempTags[] = $tag;
					}
				}

				foreach ($attributes as $attribute)
				{
					$attribute = trim($attribute);

					if ($attribute)
					{
						$tempAttributes[] = $attribute;
					}
				}

				// Collect the blacklist or whitelist tags and attributes.
				// Each list is cumulative.
				if ($filterType === 'BL')
				{
					$blackList           = true;
					$blackListTags       = array_merge($blackListTags, $tempTags);
					$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
				}
				elseif ($filterType === 'CBL')
				{
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes)
					{
						$customList           = true;
						$customListTags       = array_merge($customListTags, $tempTags);
						$customListAttributes = array_merge($customListAttributes, $tempAttributes);
					}
				}
				elseif ($filterType === 'WL')
				{
					$whiteList           = true;
					$whiteListTags       = array_merge($whiteListTags, $tempTags);
					$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the blacklist uses both sets of arrays).
		$blackListTags        = array_unique($blackListTags);
		$blackListAttributes  = array_unique($blackListAttributes);
		$customListTags       = array_unique($customListTags);
		$customListAttributes = array_unique($customListAttributes);
		$whiteListTags        = array_unique($whiteListTags);
		$whiteListAttributes  = array_unique($whiteListAttributes);

		if (!$unfiltered)
		{
			// Custom blacklist precedes Default blacklist
			if ($customList)
			{
				$filter = InputFilter::getInstance(array(), array(), 1, 1);

				// Override filter's default blacklist tags and attributes
				if ($customListTags)
				{
					$filter->tagBlacklist = $customListTags;
				}

				if ($customListAttributes)
				{
					$filter->attrBlacklist = $customListAttributes;
				}
			}
			// Blacklists take second precedence.
			elseif ($blackList)
			{
				// Remove the whitelisted tags and attributes from the black-list.
				$blackListTags       = array_diff($blackListTags, $whiteListTags);
				$blackListAttributes = array_diff($blackListAttributes, $whiteListAttributes);

				$filter = InputFilter::getInstance($blackListTags, $blackListAttributes, 1, 1);

				// Remove whitelisted tags from filter's default blacklist
				if ($whiteListTags)
				{
					$filter->tagBlacklist = array_diff($filter->tagBlacklist, $whiteListTags);
				}

				// Remove whitelisted attributes from filter's default blacklist
				if ($whiteListAttributes)
				{
					$filter->attrBlacklist = array_diff($filter->attrBlacklist, $whiteListAttributes);
				}
			}
			// Whitelists take third precedence.
			elseif ($whiteList)
			{
				// Turn off XSS auto clean
				$filter = InputFilter::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
			}
			// No HTML takes last place.
			else
			{
				$filter = InputFilter::getInstance();
			}

			$text = $filter->clean($text, 'html');
		}

		return $text;
	}

	/**
	 * Render the component.
	 *
	 * @param   string  $option  The component option.
	 * @param   array   $params  The component parameters
	 *
	 * @return  string
	 *
	 * @since   1.5
	 * @throws  MissingComponentException
	 */
	public static function renderComponent($option, $params = array())
	{
		$app = Factory::getApplication();
		$lang = Factory::getLanguage();

		if (!$app->isClient('api'))
		{
			// Load template language files.
			$template = $app->getTemplate(true)->template;
			$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
			|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);
		}

		if (empty($option))
		{
			throw new MissingComponentException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('beforeRenderComponent ' . $option);
		}

		// Record the scope
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $option;

		// Build the component path.
		$option = preg_replace('/[^A-Z0-9_\.-]/i', '', $option);

		// Define component path.

		if (!\defined('JPATH_COMPONENT'))
		{
			/**
			 * Defines the path to the active component for the request
			 *
			 * Note this constant is application aware and is different for each application (site/admin).
			 *
			 * @var    string
			 * @since  1.5
			 * @deprecated 5.0 without replacement
			 */
			\define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		}

		if (!\defined('JPATH_COMPONENT_SITE'))
		{
			/**
			 * Defines the path to the site element of the active component for the request
			 *
			 * @var    string
			 * @since  1.5
			 * @deprecated 5.0 without replacement
			 */
			\define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		}

		if (!\defined('JPATH_COMPONENT_ADMINISTRATOR'))
		{
			/**
			 * Defines the path to the admin element of the active component for the request
			 *
			 * @var    string
			 * @since  1.5
			 * @deprecated 5.0 without replacement
			 */
			\define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);
		}

		// If component is disabled throw error
		if (!static::isEnabled($option))
		{
			throw new MissingComponentException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		ob_start();
		$app->bootComponent($option)->getDispatcher($app)->dispatch();
		$contents = ob_get_clean();

		// Revert the scope
		$app->scope = $scope;

		if (JDEBUG)
		{
			Profiler::getInstance('Application')->mark('afterRenderComponent ' . $option);
		}

		return $contents;
	}

	/**
	 * Load the installed components into the components property.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	protected static function load()
	{
		$loader = function ()
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(['extension_id', 'element', 'params', 'enabled'], ['id', 'option', null, null]))
				->from($db->quoteName('#__extensions'))
				->where(
					[
						$db->quoteName('type') . ' = ' . $db->quote('component'),
						$db->quoteName('state') . ' = 0',
						$db->quoteName('enabled') . ' = 1',
					]
				);

			$components = [];
			$db->setQuery($query);

			foreach ($db->getIterator() as $component)
			{
				$components[$component->option] = new ComponentRecord((array) $component);
			}

			return $components;
		};

		/** @var CallbackController $cache */
		$cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController('callback', ['defaultgroup' => '_system']);

		try
		{
			static::$components = $cache->get($loader, array(), __METHOD__);
		}
		catch (CacheExceptionInterface $e)
		{
			static::$components = $loader();
		}

		return true;
	}

	/**
	 * Get installed components
	 *
	 * @return  ComponentRecord[]  The components property
	 *
	 * @since   3.6.3
	 */
	public static function getComponents()
	{
		if (empty(static::$components))
		{
			static::load();
		}

		return static::$components;
	}

	/**
	 * Returns the component name (eg. com_content) for the given object based on the class name.
	 * If the object is not namespaced, then the alternative name is used.
	 *
	 * @param   object  $object           The object controller or model
	 * @param   string  $alternativeName  Mostly the value of getName() from the object
	 *
	 * @return  string  The name
	 *
	 * @since   4.0.0
	 */
	public static function getComponentName($object, string $alternativeName): string
	{
		$reflect = new \ReflectionClass($object);

		if (!$reflect->getNamespaceName() || \get_class($object) == ComponentDispatcher::class)
		{
			return 'com_' . strtolower($alternativeName);
		}

		$from = strpos($reflect->getNamespaceName(), '\\Component');
		$to   = strpos(substr($reflect->getNamespaceName(), $from + 11), '\\');

		return 'com_' . strtolower(substr($reflect->getNamespaceName(), $from + 11, $to));
	}
}
