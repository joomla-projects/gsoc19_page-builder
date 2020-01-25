<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Document\Renderer\Html;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\WebAsset\WebAssetItemInterface;

/**
 * JDocument styles renderer
 *
 * @since  4.0.0
 */
class StylesRenderer extends DocumentRenderer
{
	/**
	 * Renders the document stylesheets and style tags and returns the results as a string
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   4.0.0
	 */
	public function render($head, $params = array(), $content = null)
	{
		// Get line endings
		$lnEnd        = $this->_doc->_getLineEnd();
		$tab          = $this->_doc->_getTab();
		$tagEnd       = ' />';
		$buffer       = '';
		$mediaVersion = $this->_doc->getMediaVersion();
		$wam          = $this->_doc->getWebAssetManager();
		$assets       = $wam->getAssets('style', true);
		$assets       = array_merge(array_values($assets), $this->_doc->_styleSheets);
		$renderedUrls = [];

		$defaultCssMimes = array('text/css');

		// Generate stylesheet links
		foreach ($assets as $key => $item)
		{
			$asset = $item instanceof WebAssetItemInterface ? $item : null;

			if ($asset)
			{
				$src = $asset->getUri();

				if (!$src)
				{
					continue;
				}

				$attribs     = $asset->getAttributes();
				$version     = $asset->getVersion();
				$conditional = $asset->getOption('conditional');
			}
			else
			{
				$src     = $key;
				$attribs = $item;
				$version = isset($attribs['options']['version']) ? $attribs['options']['version'] : '';

				// Check if stylesheet uses IE conditional statements.
				$conditional = !empty($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;
			}

			// Prevent double rendering
			if (!empty($renderedUrls[$src]))
			{
				continue;
			}

			$renderedUrls[$src] = true;

			// Check if script uses media version.
			if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto'))
			{
				$src .= '?' . ($version === 'auto' ? $mediaVersion : $version);
			}

			$buffer .= $tab;

			// This is for IE conditional statements support.
			if (!\is_null($conditional))
			{
				$buffer .= '<!--[if ' . $conditional . ']>';
			}

			$buffer .= '<link href="' . $src . '" rel="stylesheet"';

			// Add script tag attributes.
			foreach ($attribs as $attrib => $value)
			{
				// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
				if ($attrib === 'options')
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if (\in_array($attrib, array('type', 'mime')) && $this->_doc->isHtml5() && \in_array($value, $defaultCssMimes))
				{
					continue;
				}

				// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
				if ($attrib === 'mime')
				{
					$attrib = 'type';
				}

				// Add attribute to script tag output.
				$buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

				// Json encode value if it's an array.
				$value = !is_scalar($value) ? json_encode($value) : $value;

				$buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
			}

			$buffer .= $tagEnd;

			// This is for IE conditional statements support.
			if (!\is_null($conditional))
			{
				$buffer .= '<![endif]-->';
			}

			$buffer .= $lnEnd;
		}

		// Generate stylesheet declarations
		foreach ($this->_doc->_style as $type => $contents)
		{
			// Test for B.C. in case someone still store stylesheet declarations as single string
			if (\is_string($contents))
			{
				$contents = [$contents];
			}

			foreach ($contents as $content)
			{
				$buffer .= $tab . '<style';

				if (!\is_null($type) && (!$this->_doc->isHtml5() || !\in_array($type, $defaultCssMimes)))
				{
					$buffer .= ' type="' . $type . '"';
				}

				if ($this->_doc->cspNonce)
				{
					$buffer .= ' nonce="' . $this->_doc->cspNonce . '"';
				}

				$buffer .= '>' . $lnEnd;

				// This is for full XHTML support.
				if ($this->_doc->_mime != 'text/html')
				{
					$buffer .= $tab . $tab . '/*<![CDATA[*/' . $lnEnd;
				}

				$buffer .= $content . $lnEnd;

				// See above note
				if ($this->_doc->_mime != 'text/html')
				{
					$buffer .= $tab . $tab . '/*]]>*/' . $lnEnd;
				}

				$buffer .= $tab . '</style>' . $lnEnd;
			}
		}

		// Generate scripts options
		$scriptOptions = $this->_doc->getScriptOptions();

		if (!empty($scriptOptions))
		{
			$nonce = '';

			if ($this->_doc->cspNonce)
			{
				$nonce = ' nonce="' . $this->_doc->cspNonce . '"';
			}

			$buffer .= $tab . '<script type="application/json" class="joomla-script-options new"' . $nonce . '>';

			$prettyPrint = (JDEBUG && \defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
			$jsonOptions = json_encode($scriptOptions, $prettyPrint);
			$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

			$buffer .= $jsonOptions;
			$buffer .= '</script>' . $lnEnd;
		}

		return ltrim($buffer, $tab);
	}
}
