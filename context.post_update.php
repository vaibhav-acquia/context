<?php

/**
 * @file
 * Post update functions for the Context module.
 */

/**
 * Update node type conditions from node_type to entity_bundle.
 */
function context_post_update_update_node_type_conditions() {
  $updated_contexts = [];
  foreach (\Drupal::configFactory()->listAll('context.context.') as $context_config_name) {
    $context_config = \Drupal::configFactory()->getEditable($context_config_name);

    // Load context entities and swap the "node_type" plugin for the
    // "entity_bundle:node" plugin.
    if ($context_config->get('conditions.node_type')) {
      $conditions = [];
      foreach ($context_config->get('conditions') as $condition_id => $condition) {
        if ($condition_id === 'node_type') {
          $condition_id = 'entity_bundle:node';
          $condition['id'] = 'entity_bundle:node';
        }
        $conditions[$condition_id] = $condition;
      }
      $context_config
        ->set('conditions', $conditions)
        ->save();
      $updated_contexts[] = $context_config->get('name');
    }
  }
  if ($updated_contexts) {
    return t('Updated "node_type" condition IDs on %contexts', [
      '%contexts' => implode(', ', $updated_contexts),
    ]);
  }

  return NULL;
}
