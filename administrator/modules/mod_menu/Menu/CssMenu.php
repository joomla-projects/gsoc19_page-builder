<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Menu\Administrator\Menu;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Tree based class to render the admin menu
 *
 * @since  1.5
 */
class CssMenu
{
	/**
	 * The root of the menu
	 *
	 * @var    AdministratorMenuItem
	 *
	 * @since  4.0.0
	 */
	protected $root;

	/**
	 * An array of AdministratorMenuItem nodes
	 *
	 * @var    AdministratorMenuItem[]
	 *
	 * @since  4.0.0
	 */
	protected $nodes = [];

	/**
	 * The module options
	 *
	 * @var    Registry
	 *
	 * @since  3.8.0
	 */
	protected $params;

	/**
	 * The menu bar state
	 *
	 * @var    boolean
	 *
	 * @since  3.8.0
	 */
	protected $enabled;

	/**
	 * The application
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $application;

	/**
	 * A counter for unique IDs
	 *
	 * @var   integer
	 *
	 * @since  4.0.0
	 */
	protected $counter = 0;

	/**
	 * CssMenu constructor.
	 *
	 * @param   CMSApplication  $application  The application
	 *
	 * @since 4.0.0
	 */
	public function __construct(CMSApplication $application)
	{
		$this->application = $application;
		$this->root = new AdministratorMenuItem;
	}

	/**
	 * Populate the menu items in the menu tree object
	 *
	 * @param   Registry  $params   Menu configuration parameters
	 * @param   bool      $enabled  Whether the menu should be enabled or disabled
	 *
	 * @return  AdministratorMenuItem  Root node of the menu tree
	 *
	 * @since   3.7.0
	 */
	public function load($params, $enabled)
	{
		$this->params  = $params;
		$this->enabled = $enabled;
		$menutype      = $this->params->get('menutype', '*');

		if ($menutype === '*')
		{
			$name   = $this->params->get('preset', 'default');
			$this->root = MenusHelper::loadPreset($name);
		}
		else
		{
			$this->root = MenusHelper::getMenuItems($menutype, true);

			// Can we access everything important with this menu? Create a recovery menu!
			if ($this->enabled
				&& $this->params->get('check', 1)
				&& $this->check($this->root, $this->params))
			{
				$this->params->set('recovery', true);

				// In recovery mode, load the preset inside a special root node.
				$this->root = new AdministratorMenuItem(['level' => 0]);
				$heading = new AdministratorMenuItem(['title' => 'MOD_MENU_RECOVERY_MENU_ROOT', 'type' => 'heading']);
				$this->root->addChild($heading);

				MenusHelper::loadPreset('default', true, $heading);

				$this->preprocess($this->root);

				$this->root->addChild(new AdministratorMenuItem(['type' => 'separator']));

				// Add link to exit recovery mode
				$uri = clone Uri::getInstance();
				$uri->setVar('recover_menu', 0);

				$this->root->addChild(new AdministratorMenuItem(['title' => 'MOD_MENU_RECOVERY_EXIT', 'type' => 'url', 'url' => $uri->toString()]));

				return $this->root;
			}
		}

		$this->preprocess($this->root);

		return $this->root;
	}

	/**
	 * Method to render a given level of a menu using provided layout file
	 *
	 * @param   string                 $layoutFile  The layout file to be used to render
	 * @param   AdministratorMenuItem  $node        Node to render the children of
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function renderSubmenu($layoutFile, $node)
	{
		if (is_file($layoutFile))
		{
			$children = $node->getChildren();

			foreach ($children as $current)
			{
				$current->level = $node->level + 1;

				// This sets the scope to this object for the layout file and also isolates other `include`s
				require $layoutFile;
			}
		}
	}

	/**
	 * Check the flat list of menu items for important links
	 *
	 * @param   AdministratorMenuItem  $node    The menu items array
	 * @param   Registry               $params  Module options
	 *
	 * @return  boolean  Whether to show recovery menu
	 *
	 * @since   3.8.0
	 */
	protected function check($node, Registry $params)
	{
		$me          = $this->application->getIdentity();
		$authMenus   = $me->authorise('core.manage', 'com_menus');
		$authModules = $me->authorise('core.manage', 'com_modules');

		if (!$authMenus && !$authModules)
		{
			return false;
		}

		$items      = $node->getChildren(true);
		$types      = array_column($items, 'type');
		$elements   = array_column($items, 'element');
		$rMenu      = $authMenus && !\in_array('com_menus', $elements);
		$rModule    = $authModules && !\in_array('com_modules', $elements);
		$rContainer = !\in_array('container', $types);

		if ($rMenu || $rModule || $rContainer)
		{
			$recovery = $this->application->getUserStateFromRequest('mod_menu.recovery', 'recover_menu', 0, 'int');

			if ($recovery)
			{
				return true;
			}

			$missing = array();

			if ($rMenu)
			{
				$missing[] = Text::_('MOD_MENU_IMPORTANT_ITEM_MENU_MANAGER');
			}

			if ($rModule)
			{
				$missing[] = Text::_('MOD_MENU_IMPORTANT_ITEM_MODULE_MANAGER');
			}

			if ($rContainer)
			{
				$missing[] = Text::_('MOD_MENU_IMPORTANT_ITEM_COMPONENTS_CONTAINER');
			}

			$uri = clone Uri::getInstance();
			$uri->setVar('recover_menu', 1);

			$table    = Table::getInstance('MenuType');
			$menutype = $params->get('menutype');

			$table->load(array('menutype' => $menutype));

			$menutype = $table->get('title', $menutype);
			$message  = Text::sprintf('MOD_MENU_IMPORTANT_ITEMS_INACCESSIBLE_LIST_WARNING', $menutype, implode(', ', $missing), $uri);

			$this->application->enqueueMessage($message, 'warning');
		}

		return false;
	}

	/**
	 * Filter and perform other preparatory tasks for loaded menu items based on access rights and module configurations for display
	 *
	 * @param   AdministratorMenuItem  $parent  A menu item to process
	 *
	 * @return  array
	 *
	 * @since   3.8.0
	 */
	protected function preprocess($parent)
	{
		$user       = $this->application->getIdentity();
		$language   = $this->application->getLanguage();

		$noSeparator = true;
		$children = $parent->getChildren();

		/**
		 * Trigger onPreprocessMenuItems for the current level of backend menu items.
		 * $children is an array of AdministratorMenuItem objects. A plugin can traverse the whole tree,
		 * but new nodes will only be run through this method if their parents have not been processed yet.
		 */
		$this->application->triggerEvent('onPreprocessMenuItems', array('com_menus.administrator.module', $children, $this->params, $this->enabled));

		foreach ($children as $item)
		{
			$itemParams = $item->getParams();

			// Exclude item with menu item option set to exclude from menu modules
			if ($itemParams->get('menu_show', 1) == 0)
			{
				$parent->removeChild($item);
				continue;
			}

			$item->scope = $item->scope ?? 'default';
			$item->icon  = $item->icon ?? '';

			// Whether this scope can be displayed. Applies only to preset items. Db driven items should use un/published state.
			if (($item->scope === 'help' && $this->params->get('showhelp', 1) == 0) || ($item->scope === 'edit' && !$this->params->get('shownew', 1)))
			{
				$parent->removeChild($item);
				continue;
			}

			if (substr($item->link, 0, 8) === 'special:')
			{
				$special = substr($item->link, 8);

				if ($special === 'language-forum')
				{
					$item->link = 'index.php?option=com_admin&amp;view=help&amp;layout=langforum';
				}
				elseif ($special === 'custom-forum')
				{
					$item->link = $this->params->get('forum_url');
				}
			}

			// Exclude item if is not enabled
			if ($item->element && !ComponentHelper::isEnabled($item->element))
			{
				$parent->removeChild($item);
				continue;
			}

			// Exclude Mass Mail if disabled in global configuration
			if ($item->scope === 'massmail' && ($this->application->get('mailonline', 1) == 0 || $this->application->get('massmailoff', 0) == 1))
			{
				$parent->removeChild($item);
				continue;
			}

			// Exclude item if the component is not authorised
			$assetName = $item->element;

			if ($item->element === 'com_categories')
			{
				parse_str($item->link, $query);
				$assetName = $query['extension'] ?? 'com_content';
			}
			elseif ($item->element === 'com_fields')
			{
				parse_str($item->link, $query);

				// Only display Fields menus when enabled in the component
				$createFields = null;

				if (isset($query['context']))
				{
					$createFields = ComponentHelper::getParams(strstr($query['context'], '.', true))->get('custom_fields_enable', 1);
				}

				if (!$createFields)
				{
					$parent->removeChild($item);
					continue;
				}

				list($assetName) = isset($query['context']) ? explode('.', $query['context'], 2) : array('com_fields');
			}
			elseif ($item->element === 'com_cpanel' && $item->link === 'index.php')
			{
				continue;
			}
			elseif ($item->link === 'index.php?option=com_cpanel&view=help'
				|| $item->link === 'index.php?option=com_cpanel&view=cpanel&dashboard=help')
			{
				if ($this->params->get('showhelp', 1))
				{
					continue;
				}

				// Exclude help menu item if set such in mod_menu
				$parent->removeChild($item);
				continue;
			}
			elseif ($item->element === 'com_workflow')
			{
				parse_str($item->link, $query);

				// Only display Workflow menus when enabled in the component
				$workflow = null;

				if (isset($query['extension']))
				{
					$workflow = ComponentHelper::getParams($query['extension'])->get('workflows_enable', 1);
				}

				if (!$workflow)
				{
					$parent->removeChild($item);
					continue;
				}

				list($assetName) = isset($query['extension']) ? explode('.', $query['extension'], 2) : array('com_workflow');
			}
			// Special case for components which only allow super user access
			elseif (\in_array($item->element, array('com_config', 'com_privacy', 'com_actionlogs'), true) && !$user->authorise('core.admin'))
			{
				$parent->removeChild($item);
				continue;
			}
			elseif ($item->element === 'com_joomlaupdate' && !$user->authorise('core.admin'))
			{
				$parent->removeChild($item);
				continue;
			}
			elseif ($item->element === 'com_admin')
			{
				parse_str($item->link, $query);

				if (isset($query['view']) && $query['view'] === 'sysinfo' && !$user->authorise('core.admin'))
				{
					$parent->removeChild($item);
					continue;
				}
			}

			if ($assetName && !$user->authorise(($item->scope === 'edit') ? 'core.create' : 'core.manage', $assetName))
			{
				$parent->removeChild($item);
				continue;
			}

			// Exclude if link is invalid
			if (!\in_array($item->type, array('separator', 'heading', 'container')) && trim($item->link) === '')
			{
				$parent->removeChild($item);
				continue;
			}

			// Process any children if exists
			if ($item->hasChildren())
			{
				$this->preprocess($item);
			}

			// Populate automatic children for container items
			if ($item->type === 'container')
			{
				$exclude    = (array) $itemParams->get('hideitems') ?: array();
				$components = MenusHelper::getMenuItems('main', false, $exclude);

				// We are adding the nodes first to preprocess them, then sort them and add them again.
				foreach ($components->getChildren() as $c)
				{
					$item->addChild($c);
				}

				$this->preprocess($item);
				$children = ArrayHelper::sortObjects($item->getChildren(), 'text', 1, false, true);

				foreach ($children as $c)
				{
					$item->addChild($c);
				}
			}

			// Exclude if there are no child items under heading or container
			if (\in_array($item->type, array('heading', 'container')) && !$item->hasChildren() && empty($item->components))
			{
				$parent->removeChild($item);
				continue;
			}

			// Remove repeated and edge positioned separators, It is important to put this check at the end of any logical filtering.
			if ($item->type === 'separator')
			{
				if ($noSeparator)
				{
					$parent->removeChild($item);
					continue;
				}

				$noSeparator = true;
			}
			else
			{
				$noSeparator = false;
			}

			// Ok we passed everything, load language at last only
			if ($item->element)
			{
				$language->load($item->element . '.sys', JPATH_ADMINISTRATOR, null, false, true) ||
				$language->load($item->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->element, null, false, true);
			}

			if ($item->type === 'separator' && $itemParams->get('text_separator') == 0)
			{
				$item->title = '';
			}

			$item->text = Text::_($item->title);
		}

		// If last one was a separator remove it too.
		$last = end($parent->getChildren());

		if ($last && $last->type === 'separator' && $last->getSibling(false) && $last->getSibling(false)->type === 'separator')
		{
			$parent->removeChild($last);
		}
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 *
	 * @param   AdministratorMenuItem  $node  Node to get icon data from
	 *
	 * @return  string	CSS class name
	 *
	 * @since   3.8.0
	 */
	public function getIconClass($node)
	{
		$identifier = $node->class;

		// Top level is special
		if (trim($identifier) == '')
		{
			return null;
		}

		// We were passed a class name
		if (substr($identifier, 0, 6) == 'class:')
		{
			$class = substr($identifier, 6);
		}
		// We were passed background icon url. Build the CSS class for the icon
		else
		{
			if ($identifier == null)
			{
				return null;
			}

			$class = preg_replace('#\.[^.]*$#', '', basename($identifier));
			$class = preg_replace('#\.\.[^A-Za-z0-9\.\_\- ]#', '', $class);
		}

		$html = 'fa fa-' . $class . ' fa-fw';

		return $html;
	}

	/**
	 * Create unique identifier
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getCounter()
	{
		$this->counter++;

		return $this->counter;
	}
}
