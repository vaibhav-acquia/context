<?php

namespace Drupal\context\Plugin\Condition;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\user\Entity\User;
use Drupal\Core\Session\AccountProxy;

/**
 * Provides a 'User profile page status' condition.
 *
 * @Condition(
 *   id = "user_status",
 *   label = @Translation("User profile pages"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User")),
 *   }
 * )
 */
class UserProfilePage extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Service current_route_match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRouteMatch;

  /**
   * Service entity_field.manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * Service current_user.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  private $currentUser;

  /**
   * UserProfilePage constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $currentRouteMatch, EntityFieldManager $entityFieldManager, AccountProxy $currentUser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $currentRouteMatch;
    $this->entityFieldManager = $entityFieldManager;
    $this->currentUser = $currentUser;
  }

  /**
   * UserProfilePage create function.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_field.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $user_fields = $this->entityFieldManager->getFieldDefinitions('user', 'user');
    $ufields = [];
    foreach ($user_fields as $field_key => $field_value) {
      $ufields[$field_key] = $field_key;
    }
    $configuration = $this->getConfiguration();
    $options = [
      'viewing_profile' => $this->t('Viewing user profile.'),
      'logged_viewing_profile' => $this->t('Logged in viewing user profile.'),
      'own_page_true' => $this->t('User viewing own profile.'),
      'field_value' => $this->t('Has a value in selected user field'),
    ];
    $form['user_status'] = [
      '#attributes' => [
        'name' => 'user_status',
      ],
      '#title' => $this->t('User status'),
      '#description' => 'If nothing is checked, the evaluation will return TRUE.',
      '#type' => 'radios',
      '#options' => $options,
      '#default_value' => isset($configuration['user_status']) ? $configuration['user_status'] : FALSE,
    ];

    $form['user_fields'] = [
      '#type' => 'select',
      '#title' => $this->t('User field'),
      '#options' => $ufields,
      '#default_value' => isset($configuration['user_fields']) ? $configuration['user_fields'] : FALSE,
      '#states' => [
        // Show this field only if the radio 'field_value' is selected above.
        'visible' => [
          ':input[name="user_status"]' => ['value' => 'field_value'],
        ],
      ],
    ];

    return parent::buildConfigurationForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['user_status'] = $form_state->getValue('user_status');
    $this->configuration['user_fields'] = $form_state->getValue('user_fields');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('Select user profile page status');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $route = $this->currentRouteMatch->getCurrentRouteMatch();
    $configuration = $this->getConfiguration();
    // Fix for undefined index.
    if (isset($configuration['user_status'])) {
      $user_conf = $configuration['user_status'];
    }
    else {
      $user_conf = NULL;
    }

    // Match all entity.user.* routes having user parameter,
    // which include regular user profile view (entity.user.canonical),
    // user edit form (entity.user.edit_form),...
    if (strpos($route->getRouteName(), 'entity.user.') === 0) {
      $user_id = $this->currentRouteMatch->getRawParameter('user');
      if ($user_id === NULL) {
        return FALSE;
      }

      switch ($user_conf) {
        case "viewing_profile":
          return TRUE;

        case "logged_viewing_profile":
          if ($this->currentUser->isAuthenticated()) {
            return TRUE;
          }
          break;

        case "own_page_true":
          // "Own" assumes user is logged in, if not logged in, id() would be 0.
          if ($this->currentUser->isAuthenticated() && $user_id == $this->currentUser->id()) {
            return TRUE;
          }
          break;

        case "field_value":
          $user = User::load($user_id);
          // Check if field is entity_reference or normal field with values.
          $field_target = $user->get($configuration['user_fields'])->target_id;
          if ($field_target) {
            $field = $field_target;
          }
          else {
            $field = $user->get($configuration['user_fields'])->value;
          }
          // Condition check.
          if ($field || !$field == 0) {
            return TRUE;
          }
          break;

        default:
          return TRUE;
      }
    }
    return TRUE;
  }

}
