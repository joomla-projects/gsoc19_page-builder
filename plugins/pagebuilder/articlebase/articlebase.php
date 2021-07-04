<?php
/**
 * @package    Joomla.Plugin
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin to set a code base for article overrides in the Page Builder
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderArticleBase extends CMSPlugin
{
	/**
	 * Set basic php code for template overrides
	 *
	 * @param   string  $context  Indicates the type of the rendered element
	 * @param   array   $data     Options set in pagebuilder editor
	 *
	 * @return  string   php code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPageBuilderBeforeRenderElement($context, $data)
	{
		if (!$this->checkContext($context, $data))
		{
			return '';
		}

		return '<?php
		    defined(\'_JEXEC\') or die;
			use Joomla\CMS\Factory;
			use Joomla\CMS\HTML\HTMLHelper;
			use Joomla\CMS\Language\Associations;
			use Joomla\CMS\Language\Text;
			use Joomla\CMS\Layout\FileLayout;
			use Joomla\CMS\Layout\LayoutHelper;
			use Joomla\CMS\Router\Route;
			use Joomla\CMS\Uri\Uri;
			use Joomla\Component\Content\Administrator\Extension\ContentComponent;
			use Joomla\Component\Content\Site\Helper\RouteHelper;

			/* Create shortcuts to some parameters.*/
			$params  = $this->item->params;
			$images  = json_decode($this->item->images);
			$urls    = json_decode($this->item->urls);
			$canEdit = $params->get(\'access-edit\');
			$user    = Factory::getUser();
			$info    = $params->get(\'info_block_position\', 0);
		?>';
	}

	/**
	 * Check if this plugin code intended to render
	 *
	 * @param   string $context  the current purpose
	 * @param   array  $data     additional data for the rendering
	 *
	 * @return boolean
	 * @since 4.0
	 */
	private function checkContext($context, $data)
	{
		if (array_key_exists('file', $data))
		{
			$fileName = base64_decode($data['file']);

			if (strpos($fileName, 'article') === false)
			{
				return false;
			}
		}

		if (strpos($context, 'com_templates.template') === false)
		{
			return false;
		}

		return true;
	}
}
