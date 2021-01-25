<?php
namespace CockpitTests\Test;

use PHPUnit\Framework\TestCase;

class FormsTest extends TestCase {

      protected static $mockFormId;

      protected static $mockFormData;

      // Generate uniqe form id before starting tests
      public static function setUpBeforeClass() {
          static::$mockFormId   = 'test-'.uniqid();
          static::$mockFormData = [
            ['uid' => uniqid(), 'email' => 'mock1@example.com'],
            ['uid' => uniqid(), 'email' => 'mock2@example.com'],
            ['uid' => uniqid(), 'email' => 'mock3@example.com']
          ];
      }

      // Create "test" form before each test
      protected function setUp() {
          cockpit('forms:createForm', static::$mockFormId);
          foreach (static::$mockFormData as $data) {
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

      public function testExits() {
        $path = cockpit('forms:exists', static::$mockFormId);
        $this->assertTrue(!empty($path));
      }

      public function testUpdateForm() {
        $form = cockpit('forms:updateForm', static::$mockFormId, ['label' => 'hello']);
        $this->assertTrue($form['label'] === 'hello');
      }

      public function testSaveForm() {
        $data  = ['_id' => static::$mockFormId, 'label' => 'hello'];
        $saved = cockpit('forms:saveForm', static::$mockFormId, $data);
        $this->assertTrue($saved['label'] === 'hello');
      }

      public function testRemoveForm() {
        $removed = cockpit('forms:removeForm', static::$mockFormId);
        $this->assertTrue($removed);
      }

      public function testForms() {
        $forms = cockpit('forms:forms', static::$mockFormId.'-'.uniqid());
        $this->assertTrue(!empty($forms) && is_array($forms));
      }

      public function testForm() {
        $form = cockpit('forms:form', static::$mockFormId);
        $this->assertTrue(!empty($form) && $form['_id'] === static::$mockFormId);
      }

      public function testEntries() {
        $entries = cockpit('forms:entries', static::$mockFormId);
        $this->assertTrue($entries instanceof \MongoLite\Collection);
      }

      public function testFind() {
        $entries = cockpit('forms:find', static::$mockFormId);
        $this->assertTrue(count($entries) === count(static::$mockFormData));
      }

      public function testFindOne() {
        $criteria = [ 'uid' => static::$mockFormData[0]['uid'] ];
        $entry    = cockpit('forms:findOne', static::$mockFormId);
        $this->assertTrue($entry['uid'] === $criteria['uid']);
      }

      public function testSave() {
        $data  = ['uid' => uniqid(), 'email' => 'hello@example.com'];
        $saved = cockpit('forms:save', static::$mockFormId, $data);
        $this->assertTrue($saved['uid'] === $data['uid'] && $saved['email'] === $data['email']);
      }

      public function testRemove() {
        $criteria = [ 'uid' => static::$mockFormData[0]['uid'] ];
        $removed  = cockpit('forms:remove', static::$mockFormId, $criteria);
        $this->assertTrue($removed === 1);
      }

      public function testCount() {
        $entries = cockpit('forms:count', static::$mockFormId);
        $this->assertTrue($entries === count(static::$mockFormData));
      }

      public function testSubmit() {
        $data     = ['uid' => uniqid(), 'email' => 'hello@example.com'];
        $response = cockpit('forms:submit', static::$mockFormId, $data);
        $this->assertTrue($response === $data);
      }

}
