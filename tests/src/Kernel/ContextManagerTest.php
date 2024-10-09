<?php

namespace Drupal\Tests\context\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests for ContextManager class.
 *
 * @package Drupal\Tests\context\Kernel
 *
 * @group context
 */
class ContextManagerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'context'];

  /**
   * The context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  protected $contextManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('context');

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->contextManager = $this->container->get('context.manager');
  }

  /**
   * Tests the setActiveContexts() method.
   */
  public function testSetActiveContexts() {
    $context_storage = $this->entityTypeManager->getStorage('context');
    $context1 = $context_storage->create([
      'name' => 'test_context_1',
    ]);
    $context1->save();

    $this->contextManager->setActiveContexts([$context1->getName()]);

    $active_contexts = $this->contextManager->getActiveContexts();
    $this->assertCount(1, $active_contexts, 'There is not exactly one context active.');
    $this->assertEquals($context1->id(), $active_contexts[0]->id(), 'The wrong context is set to active.');
  }

  /**
   * Tests the unsetActiveContexts() method.
   */
  public function testUnsetActiveContexts() {
    $context_storage = $this->entityTypeManager->getStorage('context');
    $context1 = $context_storage->create([
      'name' => 'test_context_1',
    ]);
    $context1->save();

    $this->contextManager->setActiveContexts([$context1->getName()]);
    $this->contextManager->unsetActiveContexts([$context1->getName()]);

    $active_contexts = $this->contextManager->getActiveContexts();
    $this->assertCount(0, $active_contexts, 'There are active contexts.');
  }

  /**
   * Tests the isActiveContexts() method.
   */
  public function testIsActiveContexts() {
    $context_storage = $this->entityTypeManager->getStorage('context');
    $context1 = $context_storage->create([
      'name' => 'test_context_1',
    ]);
    $context1->save();
    $context2 = $context_storage->create([
      'name' => 'test_context_2',
    ]);
    $context2->save();

    $this->contextManager->setActiveContexts([$context1->getName(), $context2->getName()]);
    $this->contextManager->unsetActiveContexts([$context2->getName()]);

    $active_contexts = $this->contextManager->getActiveContexts();
    $this->assertCount(1, $active_contexts, 'There is not exactly one context active.');
    $this->assertEquals($context1->id(), $active_contexts[0]->id(), 'The wrong context is set to active.');
  }

}
