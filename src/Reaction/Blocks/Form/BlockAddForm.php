<?php

namespace Drupal\context\Reaction\Blocks\Form;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a form to add a block in the Block reaction.
 */
class BlockAddForm extends BlockFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'context_reaction_blocks_add_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSubmitValue(): TranslatableMarkup {
    return $this->t('Add block');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function prepareBlock(string $block_id): BlockPluginInterface {
    return $this->blockManager->createInstance($block_id);
  }

}
