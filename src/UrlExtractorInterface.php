<?php

namespace Drupal\media_source_url_formatter;

use Drupal\media\MediaInterface;

/**
 * Defines an interface for URL extractors.
 */
interface UrlExtractorInterface {

  /**
   * Determine whether extractor can be used for the provided media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to inspect.
   *
   * @return bool
   *   TRUE if the extractor can be used, FALSE otherwise.
   */
  public function isApplicable(MediaInterface $media);

  /**
   * Get the url of the resource referenced by a given media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to inspect.
   * @param array $options
   *   (optional) An associative array of additional options.
   *
   * @return string|null
   *   The URL of the refererenced resource.
   */
  public function getUrl(MediaInterface $media, array $options = []);

}
