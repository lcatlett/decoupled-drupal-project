<?php

namespace Drupal\decoupled_preview\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Url;
use Drupal\Core\Render\RendererInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Decoupled Preview routes.
 */
class DecoupledPreviewController extends ControllerBase {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs an DecoupledPreviewController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   An alias manager to find the alias for the current node path.
   */
  public function __construct(RendererInterface $renderer, AliasManagerInterface $aliasManager) {
    $this->renderer = $renderer;
    $this->aliasManager = $aliasManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('path_alias.manager')
    );
  }

  /**
   * Builds the response.
   */
  public function build($node, $node_preview = FALSE) {
    $markup = '';

    $storage = $this->entityTypeManager()->getStorage('dp_preview_site');
    $sites = $storage->loadMultiple();
    $nodeData = $this->entityTypeManager()->getStorage('node')
      ->load($node);
    $alias = $this->aliasManager->getAliasByPath('/node/' . $node);
    $nodeType = $nodeData->bundle();
    $enablePreview = FALSE;

    foreach ($sites as $site) {
      if ($site->checkEnabledContentType($nodeType)) {
        $enablePreview = TRUE;
      }
    }

    if ($enablePreview) {
      $previewForm = $this->formBuilder()->getForm('Drupal\decoupled_preview\Form\EditPreviewForm', $node_preview, $alias, $node);
      $previewFormHtml = $this->renderer->render($previewForm);
      $markup .= $previewFormHtml;

      $build['content'] = [
        '#type' => 'item',
        '#markup' => Markup::create($markup),
      ];
    }
    else {
      $build['content'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Decoupled Preview has not been configured for this content type.'),
      ];
    }

    if ($node_preview) {
      $build['wrapper'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
      ];
      $build['wrapper']['backlink'] = [
        '#type' => 'link',
        '#title' => $this->t('Back to content editing'),
        '#url' => Url::fromRoute('entity.node.edit_form', ['node' => $node]),
        '#options' => [
          'query' => [
            'uuid' => $node_preview,
          ],
        ],
      ];
    }
    return $build;

  }

  /**
   * Custom access check to determine if preview local task should display.
   *
   * @param int $node
   *   The node id of the current preview.
   *
   * @return Drupal\Core\Access\AccessResult
   *   Indicates access if at least one site is enabled for the current content
   *   type.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function isPreviewEnabled($node) {
    $entity = $this->entityTypeManager()->getStorage('node')->load($node);
    $nodeType = $entity->getType();

    // A lot of this is copied from the build method. May be able to abstract
    // into a function.
    $storage = $this->entityTypeManager()->getStorage('dp_preview_site');
    $sites = $storage->loadMultiple();
    $enablePreview = FALSE;

    foreach ($sites as $site) {
      if ($site->checkEnabledContentType($nodeType)) {
        $enablePreview = TRUE;
      }
    }
    return AccessResult::allowedIf($enablePreview);
  }

}
