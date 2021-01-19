<?php

namespace Drupal\Tests\context\Kernel;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\KernelTests\KernelTestBase;
use Drupal\system\Tests\Routing\MockAliasManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests that the Request Path Exclusion Condition, provided by the context
 * module, is working properly.
 *
 * @package Drupal\Tests\context\Kernel
 *
 * @group context
 */
class RequestPathExclusionTest extends KernelTestBase {

  /**
   * The condition plugin manager used for testing.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $pluginManager;

  /**
   * The path alias manager used for testing.
   *
   * @var \Drupal\system\Tests\Routing\MockAliasManager
   */
  protected $aliasManager;

  /**
   * The request stack used for testing.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc }
   */
  public static $modules = ['system', 'path', 'field', 'context'];

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentPath;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);

    $this->pluginManager = $this->container->get('plugin.manager.condition');

    // Set a mock alias manager in the container.
    $this->aliasManager = new MockAliasManager();
    $this->container->set('path_alias.manager', $this->aliasManager);

    // Set the test request stack in the container.
    $this->requestStack = new RequestStack();
    $this->container->set('request_stack', $this->requestStack);

    $this->currentPath = new CurrentPathStack($this->requestStack);
    $this->container->set('path.current', $this->currentPath);

  }

  /**
   * Tests the request path exclusion condition.
   */
  public function testRequestPathExclusion() {

    // Get the request path exclusion condition and configure it to check against
    // different patterns and requests.
    $pages = "/my/exclude/page\r\n/my/exclude/page2\r\n/excludefoo";

    $request = Request::create('/my/exclude/page2');
    $this->requestStack->push($request);

    /* @var \Drupal\system\Plugin\Condition\RequestPath $condition */
    $condition = $this->pluginManager->createInstance('request_path_exclusion');
    $condition->setConfig('pages', $pages);

    $this->aliasManager->addAlias('/my/exclude/page2', '/my/exclude/page2');

    $this->assertFalse($condition->execute(), 'The request path matches a standard path');
    $this->assertEquals('Do not return true on the following pages: /my/exclude/page, /my/exclude/page2, /excludefoo', $condition->summary(), 'The condition summary matches for a standard path');
  }

}


