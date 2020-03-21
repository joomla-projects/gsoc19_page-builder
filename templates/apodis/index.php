<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Templates\Administrator\Helper\RenderHelper;

/** @var JDocumentHtml $this */

$app  = Factory::getApplication();
$lang = $app->getLanguage();
$wa   = $this->getWebAssetManager();

// Detecting Active Variables
$option   = $app->input->getCmd('option');
$view     = $app->input->getCmd('view');
$layout   = $app->input->getCmd('layout');
$task     = $app->input->getCmd('task');
$itemid   = $app->input->getInt('Itemid');
$menu     = $app->getMenu()->getActive();

$pageGrid = $this->params->get('grid');

// Enable assets
$wa->usePreset('template.apodis.' . ($this->direction === 'rtl' ? 'rtl' : 'ltr'));

// Load specific language related CSS
HTMLHelper::_('stylesheet', 'language/' . $lang->getTag() . '/' . $lang->getTag() . '.css');

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');

$bgcolor = $this->params->get('baseBG', '#dedede');
$baseColor = $this->params->get('baseColor', '#212529');
$linkColor = $this->params->get('linkColor', '#283365');

$css = '
	body {
		color: ' . $baseColor . ';
		background-color: ' . $bgcolor . ';
	}
	a {
		color: ' . $linkColor . ';
	}
';

$this->addStyleDeclaration($css);
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas"/>
	<jdoc:include type="styles"/>
	<jdoc:include type="scripts"/>
</head>

<body class="site <?php echo $option
	. ' view-' . $view
	. ($layout ? ' layout-' . $layout : ' no-layout')
	. ($task ? ' task-' . $task : ' no-task')
	. ($itemid ? ' itemid-' . $itemid : '')
	. ($this->direction == 'rtl' ? ' rtl' : '');
?>">

	<?php echo $pageGrid; ?>

</body>
</html>
