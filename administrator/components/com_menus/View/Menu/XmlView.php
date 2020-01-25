<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\View\Menu;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

/**
 * The HTML Menus Menu Item View.
 *
 * @since  3.8.0
 */
class XmlView extends BaseHtmlView
{
	/**
	 * @var  \stdClass[]
	 *
	 * @since  3.8.0
	 */
	protected $items;

	/**
	 * @var  \JObject
	 *
	 * @since  3.8.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function display($tpl = null)
	{
		$app      = Factory::getApplication();
		$menutype = $app->input->getCmd('menutype');

		if ($menutype)
		{
			$root = MenusHelper::getMenuItems($menutype, true);
		}

		if ($root->hasChildren())
		{
			Log::add(Text::_('COM_MENUS_SELECT_MENU_FIRST_EXPORT'), Log::WARNING, 'jerror');

			$app->redirect(Route::_('index.php?option=com_menus&view=menus', false));

			return;
		}

		$this->items = $root->getChildren();

		$xml = new \SimpleXMLElement('<menu ' .
			'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
			'xmlns="urn:joomla.org"	xsi:schemaLocation="urn:joomla.org menu.xsd"' .
			'></menu>'
		);

		foreach ($this->items as $item)
		{
			$this->addXmlChild($xml, $item);
		}

		if (headers_sent($file, $line))
		{
			Log::add("Headers already sent at $file:$line.", Log::ERROR, 'jerror');

			return;
		}

		header('content-type: application/xml');
		header('content-disposition: attachment; filename="' . $menutype . '.xml"');
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header('Pragma: private');

		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = true;
		$dom->formatOutput = true;
		$dom->loadXML($xml->asXML());

		echo $dom->saveXML();

		$app->close();
	}

	/**
	 * Add a child node to the xml
	 *
	 * @param   \SimpleXMLElement  $xml   The current XML node which would become the parent to the new node
	 * @param   \stdClass          $item  The menuitem object to create the child XML node from
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	protected function addXmlChild($xml, $item)
	{
		$node = $xml->addChild('menuitem');

		$node['type'] = $item->type;

		if ($item->title)
		{
			$node['title'] = $item->title;
		}

		if ($item->link)
		{
			$node['link'] = $item->link;
		}

		if ($item->element)
		{
			$node['element'] = $item->element;
		}

		if ($item->class)
		{
			$node['class'] = $item->class;
		}

		if ($item->access)
		{
			$node['access'] = $item->access;
		}

		if ($item->browserNav)
		{
			$node['target'] = '_blank';
		}

		if (count($item->getParams()))
		{
			$hideitems = $item->getParams()->get('hideitems');

			if (count($hideitems))
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true);

				$query
					->select($db->quoteName('e.element'))
					->from($db->quoteName('#__extensions', 'e'))
					->join('INNER', $db->quoteName('#__menu', 'm'), $db->quoteName('m.component_id') . ' = ' . $db->quoteName('e.extension_id'))
					->whereIn($db->quoteName('m.id'), $hideitems);

				$hideitems = $db->setQuery($query)->loadColumn();

				$item->getParams()->set('hideitems', $hideitems);
			}

			$node->addChild('params', (string) $item->getParams());
		}

		foreach ($item->submenu as $sub)
		{
			$this->addXmlChild($node, $sub);
		}
	}
}
