<?php

namespace Drupal\context\Theme;

use Drupal\context\ContextManager;
use Drupal\context\Plugin\ContextReaction\Theme;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Context Theme Switcher Negotiator.
 */
class ThemeSwitcherNegotiator implements ThemeNegotiatorInterface {

  /**
   * ContextManager.
   *
   * @var \Drupal\context\ContextManager
   */
  private $contextManager;

  /**
   * Theme machine name.
   *
   * @var string
   */
  protected $theme;

  /**
   * A boolean indicating if the applies method has already been evaluated.
   *
   * @var bool
   */
  protected $evaluated;
  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ThemeSwitcherNegotiator object..
   *
   * @param \Drupal\context\ContextManager $contextManager
   *   ContextManager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config.factory service.
   */
  public function __construct(ContextManager $contextManager, ConfigFactoryInterface $config_factory) {
    $this->contextManager = $contextManager;
    $this->configFactory = $config_factory;
    $this->evaluated = FALSE;
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
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    // If there is no Theme reaction set or this method has already been
    // executed, do not try to get active reactions, since this causes infinite
    // loop.
    if ($this->evaluated) {
      $this->evaluated = FALSE;
      return FALSE;
    }
    $theme_reaction = FALSE;
    foreach ($this->contextManager->getContexts() as $context) {
      foreach ($context->getReactions() as $reaction) {
        if ($reaction instanceof Theme) {
          $theme_reaction = TRUE;
          break;
        }
      }
    }

    if ($theme_reaction) {
      $this->evaluated = TRUE;
      foreach ($this->contextManager->getActiveReactions('theme') as $theme_reaction) {
        $configuration = $theme_reaction->getConfiguration();
        // Be sure the theme key really exists.
        if (isset($configuration['theme'])) {
          switch ($configuration['theme']) {
            case '_admin':
              $this->theme = $this->configFactory->get('system.theme')->get('admin');
              return TRUE;

            case '_default':
              $this->theme = $this->configFactory->get('system.theme')->get('default');
              return TRUE;

            default:
              $this->theme = $configuration['theme'];
              return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->theme;
  }

}
