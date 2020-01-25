<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Factory::getDocument()->getWebAssetManager()
	->useScript('webcomponent.toolbar-button');

/**
 * Generic toolbar button layout to open a modal
 * -----------------------------------------------
 * @param   array   $displayData    Button parameters. Default supported parameters:
 *                                  - selector  string  Unique DOM identifier for the modal. CSS id without #
 *                                  - class     string  Button class
 *                                  - icon      string  Button icon
 *                                  - text      string  Button text
 */

$selector = $displayData['selector'];
$id       = isset($displayData['id']) ? $displayData['id'] : '';
$class    = isset($displayData['class']) ? $displayData['class'] : 'btn btn-secondary';
$icon     = isset($displayData['icon']) ? $displayData['icon'] : 'fa fa-download';
$text     = isset($displayData['text']) ? $displayData['text'] : '';
?>

<!-- Render the button -->
<joomla-toolbar-button<?php echo $id; ?> onclick="document.getElementById('modal_<?php echo $selector; ?>').open()" class="<?php echo $class; ?>" data-toggle="modal">
	<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</joomla-toolbar-button>

<!-- Render the modal -->
<?php
echo HTMLHelper::_('bootstrap.renderModal',
	'modal_' . $selector,
	[
		'url'         => $displayData['doTask'],
		'title'       => $text,
		'height'      => '100%',
		'width'       => '100%',
		'modalWidth'  => 80,
		'bodyHeight'  => 60,
		'closeButton' => true,
		'footer'      => '<a class="btn btn-secondary" data-dismiss="modal" type="button"'
						. ' onclick="window.parent.Joomla.Modal.getCurrent().close();">'
						. Text::_('COM_BANNERS_CANCEL') . '</a>'
						. '<button class="btn btn-success" type="button"'
						. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#modal_downloadModal\', buttonSelector: \'#exportBtn\'})">'
						. Text::_('COM_BANNERS_TRACKS_EXPORT') . '</button>',
	]
);

