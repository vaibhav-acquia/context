<?php

namespace Drupal\context\Plugin\ContextReaction;

use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a content reaction that will let you change theme.
 *
 * @ContextReaction(
 *   id = "page_template_suggestions",
 *   label = @Translation("Page template suggestions")
 * )
 */
class PageTemplateSuggestions extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->t('Gives you ability to add template suggestions.');
  }

  /**
   * Executes the plugin.
   */
  public function execute() {
    $config = $this->getConfiguration();
    return $config['suggestions'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $config = $this->getConfiguration();

    $form['suggestions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Page template suggestions'),
      '#default_value' => $config['suggestions'] ?? '',
      '#description' => $this->t('Enter page template suggestions such as "page__front", one per line, in order of preference (using underscores instead of hyphens). Entered template suggestions will override page.html.twig template.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $config['suggestions'] = str_replace("\r\n", "\n", $form_state->getValue('suggestions'));
    $this->setConfiguration($config);
  }

}
