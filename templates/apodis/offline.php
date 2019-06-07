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
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var JDocumentHtml $this */

$app      = Factory::getApplication();
$sitename = htmlspecialchars($app->get('sitename'), ENT_QUOTES, 'UTF-8');
$logo     = HTMLHelper::_('image', $this->baseurl . '/templates/' . $this->template . '/images/logo.svg', 'Logo');


// Load optional RTL Bootstrap CSS
HTMLHelper::_('bootstrap.loadCss', false, $this->direction);

$this->setMetaData('viewport', 'width=device-width, initial-scale=1');
?>
<!DOCTYPE html>
<html lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="metas"/>
	<jdoc:include type="styles"/>
	<jdoc:include type="scripts"/>
</head>
<body class="site">
<div class="outer">
	<div class="offline-card">
		<div class="header">
			<?php if (!empty($logo)) : ?>
				<h1><?php echo $logo; ?></h1>
			<?php else : ?>
				<h1><?php echo $sitename; ?></h1>
			<?php endif; ?>
			<?php if ($app->get('offline_image') && file_exists($app->get('offline_image'))) : ?>
				<img src="<?php echo $app->get('offline_image'); ?>" alt="<?php echo $sitename; ?>">
			<?php endif; ?>
			<?php if ($app->get('display_offline_message', 1) == 1 && str_replace(' ', '', $app->get('offline_message')) != '') : ?>
				<p><?php echo $app->get('offline_message'); ?></p>
			<?php elseif ($app->get('display_offline_message', 1) == 2) : ?>
				<p><?php echo Text::_('JOFFLINE_MESSAGE'); ?></p>
			<?php endif; ?>
		</div>
		<div class="login">
			<jdoc:include type="message"/>
			<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
				<fieldset>
					<label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
					<input name="username" class="form-control" id="username" type="text">

					<label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
					<input name="password" class="form-control" id="password" type="password">

					<?php if (count(AuthenticationHelper::getTwoFactorMethods()) > 1) : ?>
						<label for="secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
						<input name="secretkey" class="form-control" id="secretkey" type="text">
					<?php endif; ?>

					<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo Text::_('JLOGIN'); ?>">

					<input type="hidden" name="option" value="com_users">
					<input type="hidden" name="task" value="user.login">
					<input type="hidden" name="return" value="<?php echo base64_encode(Uri::base()); ?>">
					<?php echo HTMLHelper::_('form.token'); ?>
				</fieldset>
			</form>
		</div>
	</div>
</div>
</body>
</html>
