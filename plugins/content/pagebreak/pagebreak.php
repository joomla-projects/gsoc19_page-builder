<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Utility\Utility;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\String\StringHelper;

/**
 * Page break plugin
 *
 * <strong>Usage:</strong>
 * <code><hr class="system-pagebreak" /></code>
 * <code><hr class="system-pagebreak" title="The page title" /></code>
 * or
 * <code><hr class="system-pagebreak" alt="The first page" /></code>
 * or
 * <code><hr class="system-pagebreak" title="The page title" alt="The first page" /></code>
 * or
 * <code><hr class="system-pagebreak" alt="The first page" title="The page title" /></code>
 *
 * @since  1.6
 */
class PlgContentPagebreak extends CMSPlugin
{
	/**
	 * Plugin that adds a pagebreak into the text and truncates text at that point
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   &$row     The article object.  Note $article->text is also available
	 * @param   mixed    &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		$canProceed = $context === 'com_content.article';

		if (!$canProceed)
		{
			return;
		}

		$style = $this->params->get('style', 'pages');

		// Expression to search for.
		$regex = '#<hr(.*)class="system-pagebreak"(.*)\/?>#iU';

		$input = Factory::getApplication()->input;

		$print = $input->getBool('print');
		$showall = $input->getBool('showall');

		if (!$this->params->get('enabled', 1))
		{
			$print = true;
		}

		if ($print)
		{
			$row->text = preg_replace($regex, '<br>', $row->text);

			return;
		}

		// Simple performance check to determine whether bot should process further.
		if (StringHelper::strpos($row->text, 'class="system-pagebreak') === false)
		{
			if ($page > 0)
			{
				throw new Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
			}

			return;
		}

		$view = $input->getString('view');
		$full = $input->getBool('fullview');

		if (!$page)
		{
			$page = 0;
		}

		if ($full || $view !== 'article' || $params->get('intro_only') || $params->get('popup'))
		{
			$row->text = preg_replace($regex, '', $row->text);

			return;
		}

		// Load plugin language files only when needed (ex: not needed if no system-pagebreak class exists).
		$this->loadLanguage();

		// Find all instances of plugin and put in $matches.
		$matches = array();
		preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);

		if ($showall && $this->params->get('showall', 1))
		{
			$hasToc = $this->params->get('multipage_toc', 1);

			if ($hasToc)
			{
				// Display TOC.
				$page = 1;
				$this->_createToc($row, $matches, $page);
			}
			else
			{
				$row->toc = '';
			}

			$row->text = preg_replace($regex, '<br>', $row->text);

			return;
		}

		// Split the text around the plugin.
		$text = preg_split($regex, $row->text);

		if (!isset($text[$page]))
		{
			throw new Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
		}

		// Count the number of pages.
		$n = count($text);

		// We have found at least one plugin, therefore at least 2 pages.
		if ($n > 1)
		{
			$title  = $this->params->get('title', 1);
			$hasToc = $this->params->get('multipage_toc', 1);

			// Adds heading or title to <site> Title.
			if ($title && $page && isset($matches[$page - 1], $matches[$page - 1][2]))
			{
				$attrs = Utility::parseAttributes($matches[$page - 1][1]);

				if (isset($attrs['title']))
				{
					$row->page_title = $attrs['title'];
				}
			}

			// Reset the text, we already hold it in the $text array.
			$row->text = '';

			if ($style === 'pages')
			{
				// Display TOC.
				if ($hasToc)
				{
					$this->_createToc($row, $matches, $page);
				}
				else
				{
					$row->toc = '';
				}

				// Traditional mos page navigation
				$pageNav = new Pagination($n, $page, 1);

				// Flag indicates to not add limitstart=0 to URL
				$pageNav->hideEmptyLimitstart = true;

				// Page counter.
				$row->text .= '<div class="pagenavcounter">';
				$row->text .= $pageNav->getPagesCounter();
				$row->text .= '</div>';

				// Page text.
				$text[$page] = str_replace('<hr id="system-readmore" />', '', $text[$page]);
				$row->text .= $text[$page];

				// $row->text .= '<br>';
				$row->text .= '<div class="pager">';

				// Adds navigation between pages to bottom of text.
				if ($hasToc)
				{
					$this->_createNavigation($row, $page, $n);
				}

				// Page links shown at bottom of page if TOC disabled.
				if (!$hasToc)
				{
					$row->text .= $pageNav->getPagesLinks();
				}

				$row->text .= '</div>';
			}
			else
			{
				$t[] = $text[0];

				if ($style === 'tabs')
				{
					$t[] = (string) HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'article' . $row->id . '-' . $style . '0'));
				}
				else
				{
					$t[] = (string) HTMLHelper::_('bootstrap.startAccordion', 'myAccordion', array('active' => 'article' . $row->id . '-' . $style . '0'));
				}

				foreach ($text as $key => $subtext)
				{
					$index = 'article' . $row->id . '-' . $style . $key;

					if ($key >= 1)
					{
						$match = $matches[$key - 1];
						$match = (array) Utility::parseAttributes($match[0]);

						if (isset($match['alt']))
						{
							$title = stripslashes($match['alt']);
						}
						elseif (isset($match['title']))
						{
							$title = stripslashes($match['title']);
						}
						else
						{
							$title = Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $key + 1);
						}
					}

					if ($style === 'tabs')
					{
						$t[] = (string) HTMLHelper::_('uitab.addTab', 'myTab', $index, $title);
					}
					else
					{
						$t[] = (string) HTMLHelper::_('bootstrap.addSlide', 'myAccordion', $title, $index);
					}

					$t[] = (string) $subtext;

					if ($style === 'tabs')
					{
						$t[] = (string) HTMLHelper::_('uitab.endTab');
					}
					else
					{
						$t[] = (string) HTMLHelper::_('bootstrap.endSlide');
					}
				}

				if ($style === 'tabs')
				{
					$t[] = (string) HTMLHelper::_('uitab.endTabSet');
				}
				else
				{
					$t[] = (string) HTMLHelper::_('bootstrap.endAccordion');
				}

				$row->text = implode(' ', $t);
			}
		}
	}

	/**
	 * Creates a Table of Contents for the pagebreak
	 *
	 * @param   object   &$row      The article object.  Note $article->text is also available
	 * @param   array    &$matches  Array of matches of a regex in onContentPrepare
	 * @param   integer  &$page     The 'page' number
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function _createToc(&$row, &$matches, &$page)
	{
		$heading     = $row->title ?? Text::_('PLG_CONTENT_PAGEBREAK_NO_TITLE');
		$input       = Factory::getApplication()->input;
		$limitstart  = $input->getUInt('limitstart', 0);
		$showall     = $input->getInt('showall', 0);
		$headingtext = '';
		$list        = array();

		if ($this->params->get('article_index', 1) == 1)
		{
			$headingtext = Text::_('PLG_CONTENT_PAGEBREAK_ARTICLE_INDEX');

			if ($this->params->get('article_index_text'))
			{
				$headingtext = htmlspecialchars($this->params->get('article_index_text'), ENT_QUOTES, 'UTF-8');
			}
		}

		// TOC first Page link.
		$list[1]         = new stdClass;
		$list[1]->link   = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language);
		$list[1]->title  = $heading;
		$list[1]->active = ($limitstart === 0 && $showall === 0);

		$i = 2;

		foreach ($matches as $bot)
		{
			if (@$bot[0])
			{
				$attrs2 = Utility::parseAttributes($bot[0]);

				if (@$attrs2['alt'])
				{
					$title = stripslashes($attrs2['alt']);
				}
				elseif (@$attrs2['title'])
				{
					$title = stripslashes($attrs2['title']);
				}
				else
				{
					$title = Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $i);
				}
			}
			else
			{
				$title = Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $i);
			}

			$list[$i]         = new stdClass;
			$list[$i]->link   = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language) . '&limitstart=' . ($i - 1);
			$list[$i]->title  = $title;
			$list[$i]->active = ($limitstart === $i - 1);

			$i++;
		}

		if ($this->params->get('showall'))
		{
			$list[$i]         = new stdClass;
			$list[$i]->link   = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=1';
			$list[$i]->title  = Text::_('PLG_CONTENT_PAGEBREAK_ALL_PAGES');
			$list[$i]->active = ($limitstart === $i - 1);
		}

		$path = PluginHelper::getLayoutPath('content', 'pagebreak', 'toc');
		ob_start();
		include $path;
		$row->toc = ob_get_clean();
	}

	/**
	 * Creates the navigation for the item
	 *
	 * @param   object  &$row  The article object.  Note $article->text is also available
	 * @param   int     $page  The total number of pages
	 * @param   int     $n     The page number
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _createNavigation(&$row, $page, $n)
	{
		$links = array(
			'next' => '',
			'previous' => ''
		);

		if ($page < $n - 1)
		{
			$links['next'] = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language) . '&limitstart=' . ($page + 1);
		}

		if ($page > 0)
		{
			$links['previous'] = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language);

			if ($page > 1)
			{
				$links['previous'] .= '&limitstart=' . ($page - 1);
			}
		}

		$path = PluginHelper::getLayoutPath('content', 'pagebreak', 'navigation');
		ob_start();
		include $path;
		$row->text .= ob_get_clean();
	}
}
