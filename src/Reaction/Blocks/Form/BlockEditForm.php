<?php

namespace Drupal\context\Reaction\Blocks\Form;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a form to edit a block in the Block reaction.
 */
class BlockEditForm extends BlockFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'context_reaction_blocks_edit_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getSubmitValue(): TranslatableMarkup {
    return $this->t('Update block');
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareBlock(string $block_id): BlockPluginInterface {
    return $this->reaction->getBlock($block_id);
  }

}
