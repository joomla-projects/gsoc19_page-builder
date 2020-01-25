<?php
/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Acceptance\Administrator;

use Exception;
use Page\Acceptance\Administrator\FieldListPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Field extends Admin
{
	/**
	 * Method to create a Field.
	 *
	 * @param   string  $type   Type of the Field
	 * @param   string  $title  Title of the Field
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function createField($type, $title)
	{
		$I = $this;
		$I->amOnPage(FieldListPage::$url);
		$I->clickToolbarButton('New');
		$I->fillField(FieldListPage::$titleField, $title);
		$I->selectOption(FieldListPage::$fieldType, $type);
		$I->clickToolbarButton('Save & Close');
		$I->assertSuccessMessage(FieldListPage::$successMessage);
	}

	/**
	 * Method to see success message.
	 *
	 * @param   string   $message  Message
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function assertSuccessMessage($message)
	{
		$I = $this;
		$I->waitForText($message, $I->getConfig('timeout'), FieldListPage::$systemMessageContainer);
		$I->see($message, FieldListPage::$systemMessageContainer);
	}

	/**
	 * Method to trash a Field.
	 *
	 * @param   string   $title    Field Title
	 * @param   string   $message  Message
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function trashField($title, $message)
	{
		$I = $this;
		$I->amOnPage(FieldListPage::$url);
		$I->waitForElement(FieldListPage::$searchField, $I->getConfig('timeout'));
		$I->fillField(FieldListPage::$searchField, $title);
		$I->Click(FieldListPage::$filterSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('Action');
		$I->wait(2);
		$I->click('Trash');
		$I->assertSuccessMessage($message);
	}

	/**
	 * Method to delete a Field.
	 *
	 * @param   string   $title    Field Title
	 * @param   string   $message  Message
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function deleteField($title, $message)
	{
		$I = $this;
		$I->amOnPage(FieldListPage::$url);
		$I->waitForElement(FieldListPage::$searchField, $I->getConfig('timeout'));
		$I->click("//div[@class='js-stools-container-bar']//button[contains(text(), 'Filter')]");
		$I->wait(2);
		$I->selectOptionInChosenByIdUsingJs('filter_state', "Trashed");
		$I->fillField(FieldListPage::$searchField, $title);
		$I->Click(FieldListPage::$filterSearch);
		$I->checkAllResults();
		$I->wait(2);
		$I->clickToolbarButton('Empty trash');
		$I->assertSuccessMessage($message);
	}
}
