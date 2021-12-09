<?php

namespace Drupal\decoupled_preview\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a Decoupled Preview form.
 */
class EditPreviewForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor for EditPreviewForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'decoupled_preview_edit_preview';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $uuid = FALSE, $alias = NULL, $nid = FALSE) {
    $entityTypeManager = $this->entityTypeManager;
    $storage = $entityTypeManager->getStorage('dp_preview_site');
    $sites = $storage->loadMultiple();
    $nodeType = $entityTypeManager->getStorage('node')
      ->load($nid)
      ->bundle();

    foreach ($sites as $site) {
      if ($site->checkEnabledContentType($nodeType)) {
        $title = $site->label();
        $url = $site->get('url');
        $secret = $site->get('secret');

        if ($uuid) {
          $options = [
            'query' => [
              'secret' => $secret,
              'slug' => $alias,
              'key' => $this->currentUser()->id() . '_' . $uuid,
            ],
          ];
        }
        else {
          $options = [
            'query' => [
              'secret' => $secret,
              'slug' => $alias,
              'key' => $nid,
            ],
          ];
        }

        $url = Url::fromUri($url, $options)->toString();
        $view_mode_options[$url] = $title;
      }
    }
    if (isset($view_mode_options)) {
      $form['preview_site'] = [
        '#type' => 'select',
        '#title' => $this->t('Preview Site'),
        '#options' => $view_mode_options,
      ];

      $form['actions'] = [
        '#type' => 'actions',
      ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Preview'),
      ];

      $form['#attributes']['target'] = '_blank';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $response = new RedirectResponse($form_state->getValue('preview_site'));

    $response->send();
  }

}
