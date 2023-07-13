<?php

namespace Drupal\context_ui\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Context\ContextManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'context inspector' block.
 *
 * @Block(
 *   id = "context_inspector",
 *   admin_label = @Translation("Context inspector"),
 *   category = @Translation("Debugging")
 * )
 */
class ContextInspector extends BlockBase {
  /**
   * The Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;
  /**
   * The Current User service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;
  /**
   * The Context Manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextManagerInterface
   */
  protected $contextManager;

  /**
   * Contructs a new objects.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, AccountInterface $currentUser, ContextManagerInterface $contextManager) {
    $this->moduleHandler = $moduleHandler;
    $this->currentUser = $currentUser;
    $this->contextManager = $contextManager;
  }

  /**
   * Creates an instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to resolve services.
   *
   * @return static
   *   The instance of this class.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('context.manager')

    );
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $module = $this->moduleHandler->moduleExists('devel');
    $permission = $this->currentUser->hasPermission('access devel information');
    if ($module && $permission) {
      /** @codingStandardsIgnoreStart * */
      $output = kpr($this->contextManager->getActiveContexts(), TRUE);
      /** @codingStandardsIgnoreEnd * */
    }
    elseif ($module && !$permission) {
      $output = $this->t('You do not have permissions to view debug content.');
    }
    elseif (!$module) {
      $output = $this->t('Please enable the devel module to use the context inspector.');
    }
    $build = [
      '#type' => 'markup',
      '#markup' => $output,
    ];
    return isset($output) ? $build : [];
  }

}
