<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Contact Plugin
 *
 * @since  3.2
 */
class PlgContentContact extends CMSPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.3
	 */
	protected $db;

	/**
	 * Plugin that retrieves contact information for contact
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  void
	 */
	public function onContentPrepare($context, &$row, $params, $page = 0)
	{
		$allowed_contexts = array('com_content.category', 'com_content.article', 'com_content.featured');

		if (!in_array($context, $allowed_contexts))
		{
			return;
		}

		// Return if we don't have valid params or don't link the author
		if (!($params instanceof Registry) || !$params->get('link_author'))
		{
			return;
		}

		// Return if an alias is used
		if ((int) $this->params->get('link_to_alias', 0) === 0 && $row->created_by_alias != '')
		{
			return;
		}

		// Return if we don't have a valid article id
		if (!isset($row->id) || !(int) $row->id)
		{
			return;
		}

		$contact        = $this->getContactData($row->created_by);
		$row->contactid = $contact->contactid;
		$row->webpage   = $contact->webpage;
		$row->email     = $contact->email_to;
		$url            = $this->params->get('url', 'url');

		if ($row->contactid && $url === 'url')
		{
			$row->contact_link = Route::_(RouteHelper::getContactRoute($contact->contactid . ':' . $contact->alias, $contact->catid));
		}
		elseif ($row->webpage && $url === 'webpage')
		{
			$row->contact_link = $row->webpage;
		}
		elseif ($row->email && $url === 'email')
		{
			$row->contact_link = 'mailto:' . $row->email;
		}
		else
		{
			$row->contact_link = '';
		}
	}

	/**
	 * Retrieve Contact
	 *
	 * @param   int  $created_by  Id of the user who created the contact
	 *
	 * @return  mixed|null|integer
	 */
	protected function getContactData($created_by)
	{
		static $contacts = array();

		if (isset($contacts[$created_by]))
		{
			return $contacts[$created_by];
		}

		$db         = $this->db;
		$query      = $db->getQuery(true);
		$created_by = (int) $created_by;

		$query->select($db->quoteName('contact.id', 'contactid'))
			->select(
				$db->quoteName(
					[
						'contact.alias',
						'contact.catid',
						'contact.webpage',
						'contact.email_to',
					]
				)
			)
			->from($db->quoteName('#__contact_details', 'contact'))
			->where(
				[
					$db->quoteName('contact.published') . ' = 1',
					$db->quoteName('contact.user_id') . ' = :createdby',
				]
			)
			->bind(':createdby', $created_by, ParameterType::INTEGER);

		if (Multilanguage::isEnabled() === true)
		{
			$query->where(
				'(' . $db->quoteName('contact.language') . ' IN ('
				. implode(',', $query->bindArray([Factory::getLanguage()->getTag(), '*'], ParameterType::STRING))
				. ') OR ' . $db->quoteName('contact.language') . ' IS NULL)'
			);
		}

		$query->order($db->quoteName('contact.id') . ' DESC')
			->setLimit(1);

		$db->setQuery($query);

		$contacts[$created_by] = $db->loadObject();

		return $contacts[$created_by];
	}
}
