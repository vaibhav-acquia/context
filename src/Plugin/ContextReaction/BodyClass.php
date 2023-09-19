<?php

namespace Drupal\context\Plugin\ContextReaction;

use Drupal\context\ContextReactionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a content reaction that adds a new css class.
 *
 * @ContextReaction(
 *   id = "body_class",
 *   label = @Translation("Body class")
 * )
 */
class BodyClass extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return parent::defaultConfiguration() + [
      'body_class' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->getConfiguration()['body_class'];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array &$vars = []): array {
    return [
      'class' => explode(' ', $this->getConfiguration()['body_class']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['body_class'] = [
      '#title' => $this->t('Section class'),
      '#type' => 'textfield',
      '#description' => $this->t('Provides this text as additional body class in the html.html.twig.'),
      '#default_value' => $this->getConfiguration()['body_class'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->setConfiguration([
      'body_class' => $form_state->getValue('body_class'),
    ]);
  }

}
