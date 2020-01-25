<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Highlight
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * System plugin to highlight terms.
 *
 * @since  2.5
 */
class PlgSystemHighlight extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * Method to catch the onAfterDispatch event.
	 *
	 * This is where we setup the click-through content highlighting for.
	 * The highlighting is done with JavaScript so we just
	 * need to check a few parameters and the JHtml behavior will do the rest.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterDispatch()
	{
		// Check that we are in the site application.
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		// Set the variables.
		$input     = $this->app->input;
		$extension = $input->get('option', '', 'cmd');

		// Check if the highlighter is enabled.
		if (!ComponentHelper::getParams($extension)->get('highlight_terms', 1))
		{
			return;
		}

		// Check if the highlighter should be activated in this environment.
		if ($input->get('tmpl', '', 'cmd') === 'component' || $this->app->getDocument()->getType() !== 'html')
		{
			return;
		}

		// Get the terms to highlight from the request.
		$terms = $input->request->get('highlight', null, 'base64');
		$terms = $terms ? json_decode(base64_decode($terms)) : null;

		// Check the terms.
		if (empty($terms))
		{
			return;
		}

		// Clean the terms array.
		$filter     = InputFilter::getInstance();

		$cleanTerms = array();

		foreach ($terms as $term)
		{
			$cleanTerms[] = htmlspecialchars($filter->clean($term, 'string'));
		}

		// Activate the highlighter.
		HTMLHelper::_('behavior.highlighter', $cleanTerms);

		// Adjust the component buffer.
		$doc = $this->app->getDocument();
		$buf = $doc->getBuffer('component');
		$buf = '<br id="highlighter-start" />' . $buf . '<br id="highlighter-end" />';
		$doc->setBuffer($buf, 'component');
	}

	/**
	 * Method to catch the onFinderResult event.
	 *
	 * @param   FinderIndexerResult  $item   The search result
	 * @param   array                $query  The search query of this result
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onFinderResult($item, $query)
	{
		static $params;

		if (is_null($params))
		{
			$params = ComponentHelper::getParams('com_finder');
		}

		// Get the route with highlighting information.
		if (!empty($query->highlight)
			&& empty($item->mime)
			&& $params->get('highlight_terms', 1))
		{
			$item->route .= '&highlight=' . base64_encode(json_encode($query->highlight));
		}
	}
}
