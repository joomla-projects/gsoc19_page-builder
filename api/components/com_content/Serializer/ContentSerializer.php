<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Serializer;

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

/**
 * Temporary serializer
 *
 * @since  4.0.0
 */
class ContentSerializer extends JoomlaSerializer
{
	/**
	 * Build content relationships by associations
	 *
	 * @param   \stdClass  $model Item model
	 *
	 * @return Relationship
	 *
	 * @since 4.0
	 */
	public function languageAssociations($model)
	{
		$resources = [];

		// TODO: This can't be hardcoded in the future?
		$serializer = new JoomlaSerializer($this->type);

		foreach ($model->associations as $association)
		{
			$resources[] = (new Resource($association, $serializer))
				->addLink('self', Route::link('site', Uri::root() . '/api/index.php/v1/content/article/' . $association->id));
		}

		$collection = new Collection($resources, $serializer);

		return new Relationship($collection);
	}
}
