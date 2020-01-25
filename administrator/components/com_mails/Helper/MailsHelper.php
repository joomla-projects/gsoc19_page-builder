<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Mailtags HTML helper class.
 *
 * @since  4.0.0
 */
abstract class MailsHelper
{
	/**
	 * Display a clickable list of tags for a mail template
	 *
	 * @param   object  $mail       Row of the mail template.
	 * @param   string  $fieldname  Name of the target field.
	 *
	 * @return  string  List of tags that can be inserted into a field.
	 *
	 * @since   4.0.0
	 */
	public static function mailtags($mail, $fieldname)
	{
		$app = Factory::getApplication();
		Factory::getApplication()->triggerEvent('onMailBeforeTagsRendering', array($mail->template_id, &$mail));

		if (!isset($mail->params['tags']) || !count($mail->params['tags']))
		{
			return '';
		}

		$html = '<ul class="list-group">';

		foreach ($mail->params['tags'] as $tag)
		{
			$html .= '<li class="list-group-item">'
				. '<a href="#" class="edit-action-add-tag" data-tag="{' . strtoupper($tag) . '}" data-target="' . $fieldname . '"'
					. ' title="' . $tag . '">' . $tag . '</a>'
				. '</li>';
		}

		$html .= '</ul>';

		return $html;
	}
}
