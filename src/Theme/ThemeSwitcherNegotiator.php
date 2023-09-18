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
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * ContextManager.
   *
   * @var \Drupal\context\ContextManager
   */
  private ContextManager $contextManager;

  /**
   * Theme machine name.
   *
   * @var string
   */
  protected string $theme;

  /**
   * A boolean indicating if the applies method has already been evaluated.
   *
   * @var bool
   */
  protected bool $evaluated;

  /**
   * Service constructor.
   *
   * @param \Drupal\context\ContextManager $contextManager
   *   ContextManager parameter.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ContextManager $contextManager, ConfigFactoryInterface $configFactory) {
    $this->contextManager = $contextManager;
    $this->configFactory = $configFactory;
    $this->evaluated = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('context.manager'),
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
