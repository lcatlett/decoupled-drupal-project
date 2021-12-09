<?php

namespace Drupal\decoupled_preview\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\decoupled_preview\DpPreviewSiteInterface;

/**
 * Defines the preview site entity type.
 *
 * @ConfigEntityType(
 *   id = "dp_preview_site",
 *   label = @Translation("Preview Site"),
 *   label_collection = @Translation("Preview Sites"),
 *   label_singular = @Translation("preview site"),
 *   label_plural = @Translation("preview sites"),
 *   label_count = @PluralTranslation(
 *     singular = "@count preview site",
 *     plural = "@count preview sites",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\decoupled_preview\DpPreviewSiteListBuilder",
 *     "form" = {
 *       "add" = "Drupal\decoupled_preview\Form\DpPreviewSiteForm",
 *       "edit" = "Drupal\decoupled_preview\Form\DpPreviewSiteForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "dp_preview_site",
 *   admin_permission = "administer dp_preview_site",
 *   links = {
 *     "collection" = "/admin/structure/dp-preview-site",
 *     "add-form" = "/admin/structure/dp-preview-site/add",
 *     "edit-form" = "/admin/structure/dp-preview-site/{dp_preview_site}",
 *     "delete-form" = "/admin/structure/dp-preview-site/{dp_preview_site}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "url",
 *     "secret",
 *     "content_type",
 *   }
 * )
 */
class DpPreviewSite extends ConfigEntityBase implements DpPreviewSiteInterface {

  /**
   * The preview site ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The preview site label.
   *
   * @var string
   */
  protected $label;

  /**
   * The preview site url.
   *
   * @var string
   */
  protected $url;

  /**
   * The dp_preview_site sec.
   *
   * @var string
   */
  protected $secret;

  /**
   * The specific content type.
   *
   * @var array
   */
  protected $content_type;

  /**
   * {@inheritdoc}
   */
  public function checkEnabledContentType($nodeType) {
    $contentType = $this->get('content_type');
    if (!empty($contentType)) {
      if (in_array($nodeType, array_values($contentType), TRUE) || empty(array_filter(array_values($contentType)))) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    else {
      return TRUE;
    }
  }

}
