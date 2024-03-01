<?php

namespace Drupal\context\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'Entity Status' condition.
 *
 * @Condition(
 *   id = "entity_status",
 *   deriver = "\Drupal\context\Plugin\Condition\Deriver\EntityStatus",
 * )
 */
class EntityStatus extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * Get mapping from status to label.
   */
  protected function statusLabelMap() {
    return [
      0 => $this->t('Unpublished'),
      1 => $this->t('Published'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['negate']['#access'] = FALSE;

    $form['status'] = [
      '#title' => $this->t("Status"),
      '#type' => 'radios',
      '#options' => $this->statusLabelMap(),
      '#default_value' => $this->configuration['status'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['status'] = $form_state->getValue('status');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->getContextValue($this->getDerivativeId());
    return $entity->status == $this->configuration['status'];
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Return true for @status @entity_type pages', [
      '@status' => $this->statusLabelMap()[$this->configuration['status']],
      '@entity_type' => $this->getDerivativeId(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'status' => NULL,
    ] + parent::defaultConfiguration();
  }

}
