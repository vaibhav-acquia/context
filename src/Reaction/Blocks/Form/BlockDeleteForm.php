<?php

namespace Drupal\context\Reaction\Blocks\Form;

use Drupal\context\ContextInterface;
use Drupal\context\ContextManager;
use Drupal\context\Plugin\ContextReaction\Blocks;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to delete a block from block reaction.
 */
class BlockDeleteForm extends ConfirmFormBase {

  /**
   * The context that the block is being removed from.
   *
   * @var \Drupal\context\ContextInterface
   */
  protected ContextInterface $context;

  /**
   * The blocks' reaction.
   *
   * @var \Drupal\context\Plugin\ContextReaction\Blocks
   */
  protected Blocks $reaction;

  /**
   * The block that is being removed.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected BlockPluginInterface $block;

  /**
   * The Context module context manager.
   *
   * @var \Drupal\context\ContextManager
   */
  protected ContextManager $contextManager;

  /**
   * Construct a condition delete form.
   *
   * @param \Drupal\context\ContextManager $contextManager
   *   The context manager.
   */
  public function __construct(ContextManager $contextManager) {
    $this->contextManager = $contextManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): BlockDeleteForm {
    return new static(
      $container->get('context.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'context_reaction_blocks_delete_block_form';
  }

  /**
   * Returns the question to ask the user.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion(): TranslatableMarkup {
    return $this->t('Are you sure you want to remove the %label block?', [
      '%label' => $this->block->getConfiguration()['label'],
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getCancelUrl(): Url {
    return $this->context->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContextInterface $context = NULL, $block_id = NULL): array {
    $this->context = $context;

    $this->reaction = $this->context->getReaction('blocks');
    $this->block = $this->reaction->getBlock($block_id);

    $form = parent::buildForm($form, $form_state);

    // Remove the cancel button if this is an AJAX request since Drupals built
    // in modal dialogues does not handle buttons that are not a primary
    // button very well.
    if ($this->getRequest()->isXmlHttpRequest()) {
      unset($form['actions']['cancel']);
      // Submit the form with AJAX if possible.
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::submitFormAjax',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $configuration = $this->block->getConfiguration();

    $this->reaction->removeBlock($configuration['uuid']);

    $this->context->save();

    // If this is not an AJAX request then redirect and show a message.
    if (!$this->getRequest()->isXmlHttpRequest()) {
      $this->messenger()->addMessage($this->t('The %label block has been removed.', [
        '%label' => $configuration['label'],
      ]
      ));

      $form_state->setRedirectUrl($this->getCancelUrl());
    }
  }

  /**
   * Handle when the form is submitted trough AJAX.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitFormAjax(): AjaxResponse {
    $contextForm = $this->contextManager->getForm($this->context);

    $response = new AjaxResponse();

    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new ReplaceCommand('#context-reactions', $contextForm['reactions']));

    return $response;
  }

}
