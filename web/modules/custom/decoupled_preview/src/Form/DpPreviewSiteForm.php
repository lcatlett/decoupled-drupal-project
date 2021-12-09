<?php

namespace Drupal\decoupled_preview\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preview Site form.
 *
 * @property \Drupal\decoupled_preview\DpPreviewSiteInterface $entity
 */
class DpPreviewSiteForm extends EntityForm {

  /**
   * The route building service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * Constructor for DpPreviewSiteForm.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The route building service.
   */
  public function __construct(RouteBuilderInterface $route_builder) {
    $this->routeBuilder = $route_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('Label for the preview site.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\decoupled_preview\Entity\DpPreviewSite::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->get('url'),
      '#description' => $this->t('URL for the preview site.'),
      '#required' => TRUE,
    ];

    $form['secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret'),
      '#maxlength' => 255,
      '#description' => $this->t('Shared secret for the preview site.'),
    ];

    if (empty($this->entity->get('secret'))) {
      $form['secret']['#required'] = TRUE;
    }
    else {
      $form['secret']['#old-value'] = $this->entity->get('secret');
    }

    $form['content_type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select Content Type'),
      '#description' => $this->t('If no content types are specified, the preview site should display for all content types'),
      '#default_value' => !empty($this->entity->get('content_type')) ? array_values($this->entity->get('content_type')) : [],
    ];

    $types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    foreach ($types as $type) {
      $form['content_type']['#options'][$type->getOriginalId()] = $type->label();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('secret'))) {
      $this->entity->set('secret', $form["secret"]["#old-value"]);
    }
    $result = parent::save($form, $form_state);
    // Rebuilding the routes,
    // as this might add/remove the Decoupled Preview local task.
    $this->routeBuilder->setRebuildNeeded();
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new preview site %label.', $message_args)
      : $this->t('Updated preview site %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
