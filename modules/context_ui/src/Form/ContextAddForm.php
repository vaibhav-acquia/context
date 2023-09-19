<?php

namespace Drupal\context_ui\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form to add a context.
 */
class ContextAddForm extends ContextFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    $status = parent::save($form, $form_state);

    if ($status) {
      $this->messenger()->addMessage($this->t('The context %label has been added.', [
        '%label' => $this->entity->getLabel(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The context was not saved.'));
    }

    $form_state->setRedirect('entity.context.edit_form', [
      'context' => $this->entity->id(),
    ]);
  }

}
