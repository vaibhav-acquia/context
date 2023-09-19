<?php

namespace Drupal\context_ui\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\MachineName;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Form for duplicating Context.
 */
class ContextDuplicateForm extends ContextFormBase {

  /**
   * Returns the question for the confirmation form.
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to duplicate the %label context?', [
      '%label' => $this->entity->getLabel(),
    ]);
  }

  /**
   * Returns the description for the confirmation form.
   */
  public function getDescription(): TranslatableMarkup {
    return $this->t('This action will duplicate the %label context.', [
      '%label' => $this->entity->getLabel(),
    ]);
  }

  /**
   * Returns the URL to redirect to after the form is canceled.
   */
  public function getCancelUrl(): Url {
    return new Url('entity.context.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['general'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General details'),
    ];

    $form['general']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->t('Duplicate of @label', ['@label' => $this->entity->getLabel()]),
      '#required' => TRUE,
      '#description' => $this->t('Enter label for this context.'),
    ];

    $form['general']['name'] = [
      '#type' => 'machine_name',
      '#default_value' => '',
      '#machine_name' => [
        'source' => ['general', 'label'],
        'exists' => [$this, 'contextExists'],
      ],
    ];

    $form['general']['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->getDescription(),
      '#description' => $this->t('Enter a description for this context definition.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Duplicate',
    ];

    // Remove the cancel button if this is an AJAX request since Drupals built
    // in modal dialogues does not handle buttons that are not a primary
    // button very well.
    if ($this->getRequest()->isXmlHttpRequest()) {
      unset($form['actions']['cancel']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    MachineName::validateMachineName($form["general"]["name"], $form_state, $form);
    $label = $form["general"]["label"]["#value"];
    $name = $form["general"]["name"]["#value"];
    $description = $form["general"]["description"]["#value"];
    $this->entity->duplicate($label, $name, $description);
    $this->messenger()->addMessage($this->t('The context %title has been duplicated.', [
      '%title' => $this->entity->getLabel(),
    ]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
