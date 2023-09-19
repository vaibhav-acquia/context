<?php

namespace Drupal\context_ui\Form;

use Drupal\context\ContextInterface;
use Drupal\context\ContextManager;
use Drupal\context\ContextReactionInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a context reaction delete form.
 */
class ReactionDeleteForm extends ConfirmFormBase implements ContainerInjectionInterface {

  /**
   * The Context module context manager.
   *
   * @var \Drupal\context\ContextInterface
   */
  protected ContextInterface $context;

  /**
   * The context reaction.
   *
   * @var \Drupal\context\ContextReactionInterface
   */
  protected ContextReactionInterface $reaction;

  /**
   * The Context module context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  protected ContextManager $contextManager;

  /**
   * Construct.
   *
   * @param \Drupal\context\ContextManager $contextManager
   *   The Context module context manager.
   */
  public function __construct(ContextManager $contextManager) {
    $this->contextManager = $contextManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): ReactionDeleteForm {
    return new static(
      $container->get('context.manager')
    );
  }

  /**
   * Returns the question to ask the user.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to remove the %reaction reaction.', [
      '%reaction' => $this->reaction->getPluginDefinition()['label'],
    ]);
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getCancelUrl(): Url {
    return $this->context->toUrl();
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId(): string {
    return 'context_reaction_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContextInterface $context = NULL, $reaction_id = NULL): array {
    $this->context = $context;
    $this->reaction = $this->context->getReaction($reaction_id);

    $form = parent::buildForm($form, $form_state);

    // Remove the cancel button if this is an AJAX request since Drupals built
    // in modal dialogues does not handle buttons that are not a primary
    // button very well.
    if ($this->getRequest()->isXmlHttpRequest()) {
      unset($form['actions']['cancel']);
    }

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::submitFormAjax',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $definition = $this->reaction->getPluginDefinition();

    $this->context->removeReaction($this->reaction->getPluginId());

    $this->context->save();

    // If this is not an AJAX request then redirect and show a message.
    if (!$this->getRequest()->isXmlHttpRequest()) {
      $this->messenger()->addMessage($this->t('The %label context reaction has been removed.', [
        '%label' => $definition['label'],
      ]
      ));

      $form_state->setRedirectUrl($this->getCancelUrl());
    }
  }

  /**
   * Handle when the form is submitted through AJAX.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response.
   */
  public function submitFormAjax(): AjaxResponse {
    $response = new AjaxResponse();

    $contextForm = $this->contextManager->getForm($this->context);

    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new ReplaceCommand('#context-reactions', $contextForm['reactions']));

    return $response;
  }

}
