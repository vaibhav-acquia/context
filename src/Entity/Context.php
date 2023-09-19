<?php

namespace Drupal\context\Entity;

use Drupal\context\ContextInterface;
use Drupal\context\ContextReactionInterface;
use Drupal\context\Plugin\ContextReactionPluginCollection;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Context entity.
 *
 * @ConfigEntityType(
 *   id = "context",
 *   label = @Translation("Context"),
 *   handlers = {
 *     "access" = "Drupal\context\Entity\ContextAccess",
 *     "list_builder" = "Drupal\context_ui\ContextListBuilder",
 *     "form" = {
 *       "add" = "Drupal\context_ui\Form\ContextAddForm",
 *       "edit" = "Drupal\context_ui\Form\ContextEditForm",
 *       "delete" = "Drupal\context_ui\Form\ContextDeleteForm",
 *       "disable" = "Drupal\context_ui\Form\ContextDisableForm",
 *       "duplicate" = "Drupal\context_ui\Form\ContextDuplicateForm",
 *     }
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/context/{context}",
 *     "delete-form" = "/admin/structure/context/{context}/delete",
 *     "disable-form" = "/admin/structure/context/{context}/disable",
 *     "duplicate-form" = "/admin/structure/context/{context}/duplicate",
 *     "collection" = "/admin/structure/context",
 *   },
 *   admin_permission = "administer contexts",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "name",
 *     "label",
 *     "group",
 *     "description",
 *     "requireAllConditions",
 *     "disabled",
 *     "conditions",
 *     "reactions",
 *     "weight",
 *   }
 * )
 */
class Context extends ConfigEntityBase implements ContextInterface {

  /**
   * The machine name of the context.
   *
   * @var string|null
   */
  protected ?string $name;

  /**
   * The label of the context.
   *
   * @var string|null
   */
  protected ?string $label;

  /**
   * A description for this context.
   *
   * @var string
   */
  protected string $description = '';

  /**
   * The group this context belongs to.
   *
   * @var string|null
   */
  protected ?string $group = self::CONTEXT_GROUP_NONE;

  /**
   * If all conditions must validate for this context.
   *
   * @var bool
   */
  protected bool $requireAllConditions = FALSE;

  /**
   * The context conditions as a collection.
   *
   * @var \Drupal\Core\Condition\ConditionPluginCollection
   */
  protected ConditionPluginCollection $conditionsCollection;

  /**
   * The context reactions as a collection.
   *
   * @var \Drupal\context\Plugin\ContextReactionPluginCollection
   */
  protected ContextReactionPluginCollection $reactionsCollection;

  /**
   * A list of conditions this context should react to.
   *
   * @var array
   */
  protected array $conditions = [];

  /**
   * A list of reactions that should be taken when conditions match.
   *
   * @var array
   */
  protected array $reactions = [];

  /**
   * If the context is disabled or not.
   *
   * @var bool
   */
  protected bool $disabled = FALSE;

  /**
   * The weight for this context.
   *
   * @var int
   */
  protected int $weight = 0;

  /**
   * Returns the ID of the context.
   *
   * The ID is the unique machine name of the context.
   *
   * @return string
   *   The ID of the context.
   */
  public function id(): string {
    return !empty($this->name) ? $this->name : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return !empty($this->name) ? $this->name : '';
  }

  /**
   * {@inheritdoc}
   */
  public function setName(string $name): Context {
    $this->name = $name;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(): string {
    return !empty($this->label) ? $this->label : '';
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel(string $label): Context {
    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription(string $description): Context {
    $this->description = $description;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(): ?string {
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  public function setGroup(?string $group): Context {
    $this->group = (is_string($group) && !empty($group)) ? $group : self::CONTEXT_GROUP_NONE;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight(int $weight): Context {
    $this->weight = $weight;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function requiresAllConditions(): bool {
    return $this->requireAllConditions;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequireAllConditions(bool $require): Context {
    $this->requireAllConditions = $require;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConditions(): ConditionPluginCollection {
    if (empty($this->conditionsCollection)) {
      $conditionManager = \Drupal::service('plugin.manager.condition');
      $this->conditionsCollection = new ConditionPluginCollection($conditionManager, $this->conditions);
    }

    return $this->conditionsCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition(string $condition_id): ConditionInterface {
    return $this->getConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition(array $configuration): string {
    // Add an UUID to the condition to make sure the configuration is saved
    // since the configuration export from the conditions collection wont
    // export configuration that has not been "configured".
    $configuration['uuid'] = $this->uuidGenerator()->generate();

    $this->getConditions()->addInstanceId($configuration['id'], $configuration);

    return $configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition(string $condition_id): Context {
    $this->getConditions()->removeInstanceId($condition_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasCondition(string $condition_id): bool {
    return $this->getConditions()->has($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getReactions(): ContextReactionPluginCollection {
    if (empty($this->reactionsCollection)) {
      $reactionManager = \Drupal::service('plugin.manager.context_reaction');
      $this->reactionsCollection = new ContextReactionPluginCollection($reactionManager, $this->reactions);
    }

    return $this->reactionsCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getReaction(string $reaction_id): ContextReactionInterface {
    return $this->getReactions()->get($reaction_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addReaction(array $configuration): string {
    // Add an UUID to the condition to make sure the configuration is saved
    // since the configuration export from the conditions collection wont
    // export configuration that has not been "configured".
    $configuration['uuid'] = $this->uuidGenerator()->generate();

    $this->getReactions()->addInstanceId($configuration['id'], $configuration);

    return $configuration['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeReaction(string $reaction_id): ContextInterface {
    $this->getReactions()->removeInstanceId($reaction_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasReaction(string $reaction_id): bool {
    return $this->getReactions()->has($reaction_id);
  }

  /**
   * Gets the plugin collections used by this entity.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection[]
   *   An array of plugin collections, keyed by the property name they use to
   *   store their configuration.
   */
  public function getPluginCollections(): array {
    return [
      'reactions' => $this->getReactions(),
      'conditions' => $this->getConditions(),
    ];
  }

  /**
   * Disable context.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function disable(): void {
    $this->disabled = !$this->disabled();
    $this->save();
  }

  /**
   * Disables the context.
   */
  public function disabled(): bool {
    return $this->disabled;
  }

  /**
   * Duplicates the context.
   *
   * @param string $label
   *   The label of the new context.
   * @param string $name
   *   The name of the new context.
   * @param string $description
   *   The description of the new context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function duplicate(string $label, string $name, string $description): void {
    $context = $this->entityTypeManager()->getStorage('context')->load($this->id());
    $clone = $context->createDuplicate();
    $clone->setName($name);
    $clone->setLabel($label);
    $clone->setDescription($description);

    $clone->save();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    /** @var \Drupal\context\Plugin\ContextReactionPluginCollection $reaction_collection */
    $reaction_collection = $this->getReactions();
    /** @var \Drupal\Core\Condition\ConditionPluginCollection $condition_collection */
    $condition_collection = $this->getConditions();
    $this->calculateConditionDependencies($condition_collection);
    $this->calculateReactionDependencies($reaction_collection);

    return $this;
  }

  /**
   * Set context dependencies based on the reactions set.
   *
   * @param \Drupal\context\Plugin\ContextReactionPluginCollection $reaction_collection
   *   The Reaction Plugin collection.
   */
  public function calculateReactionDependencies(ContextReactionPluginCollection $reaction_collection): void {
    $instance_ids = $reaction_collection->getInstanceIds();
    foreach ($instance_ids as $instance_id) {
      /** @var \Drupal\context\ContextReactionPluginBase $plugin */
      $plugin = $reaction_collection->get($instance_id);
      $plugin_dependencies = $this->getPluginDependencies($plugin);
      $this->addDependencies($plugin_dependencies);
    }
  }

  /**
   * Set context dependencies based on the conditions set.
   *
   * @param \Drupal\Core\Condition\ConditionPluginCollection $condition_collection
   *   The Condition Plugin collection.
   */
  public function calculateConditionDependencies(ConditionPluginCollection $condition_collection): void {
    $instance_ids = $condition_collection->getInstanceIds();
    foreach ($instance_ids as $instance_id) {
      /** @var \Drupal\Core\Condition\ConditionPluginBase $plugin */
      $plugin = $condition_collection->get($instance_id);
      $plugin_dependencies = $this->getPluginDependencies($plugin);
      $this->addDependencies($plugin_dependencies);
    }
  }

}
