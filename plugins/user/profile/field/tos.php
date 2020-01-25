<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/**
 * Provides input for TOS
 *
 * @since  2.5.5
 */
class JFormFieldTos extends \Joomla\CMS\Form\Field\RadioField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5.5
	 */
	protected $type = 'Tos';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   2.5.5
	 */
	protected function getLabel()
	{
		$label = '';

		if ($this->hidden)
		{
			return $label;
		}

		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$text = $this->translateLabel ? Text::_($text) : $text;

		// Set required to true as this field is not displayed at all if not required.
		$this->required = true;

		// Build the class for the label.
		$class = !empty($this->description) ? 'hasPopover' : '';
		$class = $class . ' required';
		$class = !empty($this->labelClass) ? $class . ' ' . $this->labelClass : $class;

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description))
		{
			$label .= ' data-content="' . htmlspecialchars(
				$this->translateDescription ? Text::_($this->description) : $this->description,
				ENT_COMPAT,
				'UTF-8'
			) . '"';

			if (Factory::getLanguage()->isRtl())
			{
				$label .= ' data-placement="left"';
			}
		}

		$tosArticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

		if ($tosArticle)
		{
			$attribs                = [];
			$attribs['data-toggle'] = 'modal';
			$attribs['data-target'] = '#tosModal';

			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, alias, catid, language')
				->from('#__content')
				->where('id = ' . $tosArticle);
			$db->setQuery($query);
			$article = $db->loadObject();

			if (Associations::isEnabled())
			{
				$tosAssociated = Associations::getAssociations('com_content', '#__content', 'com_content.item', $tosArticle);
			}

			$currentLang = Factory::getLanguage()->getTag();

			if (isset($tosAssociated) && $currentLang !== $article->language && array_key_exists($currentLang, $tosAssociated))
			{
				$url  = RouteHelper::getArticleRoute(
					$tosAssociated[$currentLang]->id,
					$tosAssociated[$currentLang]->catid,
					$tosAssociated[$currentLang]->language
				);
				$link = HTMLHelper::_('link', Route::_($url . '&tmpl=component'), $text, $attribs);
			}
			else
			{
				$slug = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
				$url  = RouteHelper::getArticleRoute($slug, $article->catid, $article->language);
				$link = HTMLHelper::_('link', Route::_($url . '&tmpl=component'), $text, $attribs);
			}

			echo HTMLHelper::_(
				'bootstrap.renderModal',
				'tosModal',
				array(
					'url'    => Route::_($url . '&tmpl=component'),
					'title'  => $text,
					'height' => '100%',
					'width'  => '100%',
					'modalWidth'  => '800',
					'bodyHeight'  => '500',
					'footer' => '<button type="button" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">'
						. Text::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
				)
			);
		}
		else
		{
			$link = $text;
		}

		// Add the label text and closing tag.
		$label .= '>' . $link . '<span class="star">&#160;*</span></label>';

		return $label;
	}
}
