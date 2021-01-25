<?php
declare(strict_types=1);

namespace CockpitTests\Test;

use PHPUnit\Framework\TestCase;

/**
 * Test Cockpit's \Forms
 */
class FormsTest extends TestCase {

      /** @var string - Mock Form id **/
      protected static $mockFormId;

      /** @var array - Mock Form items **/
      protected static $mockFormItems;

      // Generate uniqe form id before starting tests
      public static function setUpBeforeClass() {
          static::$mockFormId   = 'test-'.uniqid();
          static::$mockFormItems = [
            ['uid' => uniqid(), 'email' => 'mock1@example.com'],
            ['uid' => uniqid(), 'email' => 'mock2@example.com'],
            ['uid' => uniqid(), 'email' => 'mock3@example.com']
          ];
      }

      // Create "test" form before each test
      protected function setUp() {
          cockpit('forms:createForm', static::$mockFormId);
          foreach (static::$mockFormItems as $data) {
            cockpit('forms:save', static::$mockFormId, $data);
          }
      }

      // Delete "test" form after each test
      protected function tearDown() {
          cockpit('forms:removeForm', static::$mockFormId);
      }

      // Clean things after all tests have been executed
      public static function tearDownAfterClass() {
      }

      /**
       * @covers \Forms::exists
       */
      public function testExits() {
        $path = cockpit('forms:exists', static::$mockFormId);
        $this->assertTrue(!empty($path));
      }

      /**
       * @covers \Forms::UpdateForm
       */
      public function testUpdateForm() {
        $form = cockpit('forms:updateForm', static::$mockFormId, ['label' => 'hello']);
        $this->assertTrue($form['label'] === 'hello');
      }

      /**
       * @covers \Forms::saveForm
       */
      public function testSaveForm() {
        $data  = ['_id' => static::$mockFormId, 'label' => 'hello'];
        $saved = cockpit('forms:saveForm', static::$mockFormId, $data);
        $this->assertTrue($saved['label'] === 'hello');
      }

      /**
       * @covers \Forms::removeForm
       */
      public function testRemoveForm() {
        $removed = cockpit('forms:removeForm', static::$mockFormId);
        $this->assertTrue($removed);
      }

      /**
       * @covers \Forms::forms
       */
      public function testForms() {
        $forms = cockpit('forms:forms', static::$mockFormId.'-'.uniqid());
        $this->assertTrue(!empty($forms) && is_array($forms));
      }

      /**
       * @covers \Forms::form
       */
      public function testForm() {
        $form = cockpit('forms:form', static::$mockFormId);
        $this->assertTrue(!empty($form) && $form['_id'] === static::$mockFormId);
      }

      /**
       * @covers \Forms::entries
       */
      public function testEntries() {
        $entries = cockpit('forms:entries', static::$mockFormId);
        $this->assertTrue($entries instanceof \MongoLite\Collection);
      }

      /**
       * @covers \Forms::find
       */
      public function testFind() {
        $entries = cockpit('forms:find', static::$mockFormId);
        $this->assertTrue(count($entries) === count(static::$mockFormItems));
      }

      /**
       * @covers \Forms::findOne
       */
      public function testFindOne() {
        $criteria = [ 'uid' => static::$mockFormItems[0]['uid'] ];
        $entry    = cockpit('forms:findOne', static::$mockFormId);
        $this->assertTrue($entry['uid'] === $criteria['uid']);
      }

      /**
       * @covers \Forms::save
       */
      public function testSave() {
        $data  = ['uid' => uniqid(), 'email' => 'hello@example.com'];
        $saved = cockpit('forms:save', static::$mockFormId, $data);
        $this->assertTrue($saved['uid'] === $data['uid'] && $saved['email'] === $data['email']);
      }

      /**
       * @covers \Forms::remove
       */
      public function testRemove() {
        $criteria = [ 'uid' => static::$mockFormItems[0]['uid'] ];
        $removed  = cockpit('forms:remove', static::$mockFormId, $criteria);
        $this->assertTrue($removed === 1);
      }

      /**
       * @covers \Forms::count
       */
      public function testCount() {
        $entries = cockpit('forms:count', static::$mockFormId);
        $this->assertTrue($entries === count(static::$mockFormItems));
      }

      /**
       * @covers \Forms::submit
       */
      public function testSubmit() {
        $data     = ['uid' => uniqid(), 'email' => 'hello@example.com'];
        $response = cockpit('forms:submit', static::$mockFormId, $data);
        $this->assertTrue($response === $data);
      }

}
