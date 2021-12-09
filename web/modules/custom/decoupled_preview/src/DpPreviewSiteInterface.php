<?php

namespace Drupal\decoupled_preview;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a preview site entity type.
 */
interface DpPreviewSiteInterface extends ConfigEntityInterface {

  /**
   * Check if preview is enabled for a given content type.
   *
   * @param string $nodeType
   *   Node type.
   *
   * @return bool
   *   Whether the node is enabled or not.
   */
  public function checkEnabledContentType($nodeType);

}
