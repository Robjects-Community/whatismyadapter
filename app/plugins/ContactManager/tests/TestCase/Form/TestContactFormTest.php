<?php
declare(strict_types=1);

namespace ContactManager\Test\TestCase\Form;

use Cake\TestSuite\TestCase;
use ContactManager\Form\TestContactForm;

/**
 * ContactManager\Form\TestContactForm Test Case
 */
class TestContactFormTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \ContactManager\Form\TestContactForm
     */
    protected $TestContact;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->TestContact = new TestContactForm();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TestContact);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \ContactManager\Form\TestContactForm::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
