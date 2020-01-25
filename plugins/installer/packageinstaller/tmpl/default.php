<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\FilesystemHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('form.csrf');

Text::script('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');
Text::script('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');

$return  = Factory::getApplication()->input->getBase64('return');
$maxSize = FilesystemHelper::fileUploadMaxSize();
?>

<legend><?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>

<hr>

<div id="uploader-wrapper">
	<div id="dragarea" data-state="pending">
		<div id="dragarea-content" class="text-center">
			<p>
				<span id="upload-icon" class="icon-upload" aria-hidden="true"></span>
			</p>
			<div id="upload-progress" class="upload-progress">
				<div class="progress progress-striped active">
					<div class="bar bar-success"
						 style="width: 0;"
						 role="progressbar"
						 aria-valuenow="0"
						 aria-valuemin="0"
						 aria-valuemax="100"
					></div>
				</div>
				<p class="lead">
					<span class="uploading-text">
						<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOADING'); ?>
					</span>
					<span class="uploading-number">0</span><span class="uploading-symbol">%</span>
				</p>
			</div>
			<div class="install-progress">
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"></div>
				</div>
				<p class="lead">
					<span class="installing-text">
						<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_INSTALLING'); ?>
					</span>
				</p>
			</div>
			<div class="upload-actions">
				<p class="lead">
					<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_DRAG_FILE_HERE'); ?>
				</p>
				<p>
					<button id="select-file-button" type="button" class="btn btn-success">
						<span class="icon-copy" aria-hidden="true"></span>
						<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_SELECT_FILE'); ?>
					</button>
				</p>
				<p>
					<?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="legacy-uploader" class="hidden">
	<div class="control-group">
		<label for="install_package" class="control-label"><?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
		<div class="controls">
			<input class="form-control-file" id="install_package" name="install_package" type="file">
			<small class="form-text text-muted"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?></small>
		</div>
	</div>
	<div class="form-actions">
		<button class="btn btn-primary" type="button" id="installbutton_package">
			<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
		</button>
	</div>

	<input id="installer-return" name="return" type="hidden" value="<?php echo $return; ?>">
</div>
