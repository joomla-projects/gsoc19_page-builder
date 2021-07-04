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
 * Plugin to add a container element to the pagebuilder
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgPagebuilderItemTitle extends CMSPlugin
{
	/**
	 * Load plugin language files automatically
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add container element which can have every other element as child
	 *
	 * @param   string  $context  Where the Page Builder is called
	 * @param   array   $params   Data for the element
	 *
	 * @return  array   data for the element inside the editor
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPageBuilderAddElement($context, $params)
	{
		if (strpos($context, 'com_templates.template') === false)
		{
			return array();
		}

		Text::script('PLG_PAGEBUILDER_ITEM_TITLE_NAME');

		return array(
			'title'       => Text::_('PLG_PAGEBUILDER_ITEM_TITLE_NAME'),
			'description' => Text::_('PLG_PAGEBUILDER_ITEM_TITLE_DESC'),
			'id'          => 'itemtitle',
			'parent'      => array('root', 'column'),
			'children'    => false,
			'component'   => false,
			'message'     => false,
			'config'      => array()
		);
	}

	/**
	 * Get rendering options for frontend templates
	 *
	 * @param   string  $context  Indicates the type of the rendered element
	 * @param   array   $data     Options set in pagebuilder editor like classes, size etc.
	 *
	 * @return  array   tags and their attributes to render the element
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onPageBuilderRenderElement($context, $data)
	{
		if ($context !== 'itemtitle')
		{
			return array();
		}

		$classes = array('itemtitle');

		if (isset($data->options))
		{
			if (!empty($data->options->class))
			{
				$classes[] = $data->options->class;
			}
		}

		$html = '<div class="' . implode(' ', $classes) . '">
			<h2 itemprop="headline">
				<?php echo $this->escape($this->item->title); ?>
			</h2>
			<?php if ($this->item->condition == ContentComponent::CONDITION_UNPUBLISHED) : ?>
				<span class="badge badge-warning"><?php echo Text::_("JUNPUBLISHED"); ?></span>
			<?php endif; ?>
			<?php if (strtotime($this->item->publish_up) > strtotime(Factory::getDate())) : ?>
				<span class="badge badge-warning"><?php echo Text::_("JNOTPUBLISHEDYET"); ?></span>
			<?php endif; ?>
			<?php if (!is_null($this->item->publish_down) && (strtotime($this->item->publish_down) < strtotime(Factory::getDate()))) : ?>
				<span class="badge badge-warning"><?php echo Text::_("JEXPIRED"); ?></span>
			<?php endif; ?>';

		return array(
			'title'  => Text::_('PLG_PAGEBUILDER_ITEM_TITLE_NAME'),
			'config' => $data->options,
			'id'     => 'itemtitle',
			'start'  => $html,
			'end'    => '</div>'
		);
	}
}
