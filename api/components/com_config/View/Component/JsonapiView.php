<?php
/**
 * @package     Joomla.API
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\View\Component;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;

/**
 * The component view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   array|null  $items  Array of items
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList(array $items = null)
	{
		try
		{
			$component = ComponentHelper::getComponent($this->get('component_name'));

			if ($component === null || !$component->enabled)
			{
				// TODO: exception component unavailable
				throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_INVALID_COMPONENT_NAME'), 400);
			}

			$data = $component->getParams()->toObject();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_SERVER'), 500, $e);
		}

		$items = [];

		foreach ($data as $key => $value)
		{
			$item    = (object) [$key => $value];
			$items[] = $this->prepareItem($item);
		}

		// Set up links for pagination
		$currentUrl = Uri::getInstance();
		$currentPageDefaultInformation = ['offset' => 0, 'limit' => 20];
		$currentPageQuery = $currentUrl->getVar('page', $currentPageDefaultInformation);

		$offset              = $currentPageQuery['offset'];
		$limit               = $currentPageQuery['limit'];
		$totalItemsCount     = count($items);
		$totalPagesAvailable = ceil($totalItemsCount / $limit);

		$items = array_splice($items, $offset, $limit);

		$firstPage = clone $currentUrl;
		$firstPageQuery = $currentPageQuery;
		$firstPageQuery['offset'] = 0;
		$firstPage->setVar('page', $firstPageQuery);

		$nextPage = clone $currentUrl;
		$nextPageQuery = $currentPageQuery;
		$nextOffset = $currentPageQuery['offset'] + $limit;
		$nextPageQuery['offset'] = ($nextOffset > ($totalPagesAvailable * $limit)) ? $totalPagesAvailable - $limit : $nextOffset;
		$nextPage->setVar('page', $nextPageQuery);

		$previousPage = clone $currentUrl;
		$previousPageQuery = $currentPageQuery;
		$previousOffset = $currentPageQuery['offset'] - $limit;
		$previousPageQuery['offset'] = $previousOffset >= 0 ? $previousOffset : 0;
		$previousPage->setVar('page', $previousPageQuery);

		$lastPage = clone $currentUrl;
		$lastPageQuery = $currentPageQuery;
		$lastPageQuery['offset'] = $totalPagesAvailable - $limit;
		$lastPage->setVar('page', $lastPageQuery);

		$collection = (new Collection($items, new JoomlaSerializer($this->type)));

		// Set the data into the document and render it
		$this->document->addMeta('total-pages', $totalPagesAvailable)
			->setData($collection)
			->addLink('self', (string) $currentUrl)
			->addLink('first', (string) $firstPage)
			->addLink('next', (string) $nextPage)
			->addLink('previous', (string) $previousPage)
			->addLink('last', (string) $lastPage);

		return $this->document->render();
	}

	/**
	 * Prepare item before render.
	 *
	 * @param   object  $item  The model item
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	protected function prepareItem($item)
	{
		$item->id = ExtensionHelper::getExtensionRecord($this->get('component_name'))->extension_id;

		return $item;
	}
}
