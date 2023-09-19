<?php

namespace Drupal\context\Plugin\ContextReaction;

use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuParentFormSelector;
use Drupal\Core\Menu\MenuParentFormSelectorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a content reaction that adds a css 'active' class to menu item.
 *
 * @ContextReaction(
 *   id = "menu",
 *   label = @Translation("Menu")
 * )
 */
class Menu extends ContextReactionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The menu parent form selector service.
   *
   * @var \Drupal\Core\Menu\MenuParentFormSelector
   */
  protected MenuParentFormSelector $menuParentFormSelector;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MenuParentFormSelectorInterface $menu_parent_form_selector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->menuParentFormSelector = $menu_parent_form_selector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): Menu {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.parent_form_selector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->t('Set active menu item based on conditions.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array &$vars = []) {
    $config = $this->getConfiguration();
    return $config['menu'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $parent_element = $this->menuParentFormSelector->parentSelectElement('main:');
    $config = $this->getConfiguration();
    $form['menu_items'] = [
      '#title' => $this->t('Menu'),
      '#type' => 'select',
      '#options' => $parent_element['#options'],
      '#multiple' => TRUE,
      '#default_value' => $config['menu'] ?? '',
      '#size' => 15,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $values = array_keys($form_state->getValue('menu_items'));

    $this->setConfiguration([
      'menu' => $values,
    ]);
  }

}
