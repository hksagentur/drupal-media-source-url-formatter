<?php

namespace Drupal\media_source_url_formatter;

use Drupal\media\MediaInterface;

/**
 * Defines an interface for URL generator.
 */
interface UrlGeneratorInterface {

  /**
   * Determine whether the genrator can be used for the provided media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to inspect.
   *
   * @return bool
   *   TRUE if the generator can be used, FALSE otherwise.
   */
  public function isApplicable(MediaInterface $media);

  /**
   * Get the url of the resource referenced by a given media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to generate the URL for.
   * @param array $options
   *   (optional) An associative array of additional options.
   *
   * @return string|null
   *   The absolute URL of the refererenced media resource.
   */
  public function generate(MediaInterface $media, array $options = []);

}
