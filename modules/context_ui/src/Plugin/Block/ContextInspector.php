<?php

namespace Drupal\context_ui\Plugin\Block;

use Drupal\context\ContextManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
class ContextInspector extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The context modules context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  private ContextManager $contextManager;

  /**
   * The account interface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $account;

  /**
   * Constructs a new Drupal\context_ui\Plugin\Block\ContextInspector object.
   *
   * @param array $configuration
   *   Container configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Current user account.
   * @param \Drupal\context\ContextManager $contextManager
   *   Context modules container manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, AccountInterface $account, ContextManager $contextManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->account = $account;
    $this->contextManager = $contextManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ContextInspector {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
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
    $permission = $this->account->hasPermission('access devel information');
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
