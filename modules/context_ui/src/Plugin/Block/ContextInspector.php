<?php

namespace Drupal\context_ui\Plugin\Block;

use Drupal\context\ContextManager;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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

  use StringTranslationTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  protected $contextManager;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\context\ContextManager $context_manager
   *   The context manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, AccountInterface $current_user, ContextManager $context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
    $this->contextManager = $context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
    /** @var \Drupal\Core\Extension\ModuleHandler $moduleHandler */
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
