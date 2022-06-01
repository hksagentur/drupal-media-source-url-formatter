<?php

namespace Drupal\media_source_url_formatter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\media\MediaInterface;

/**
 * Extract the URL of the resource referenced by a media entity.
 */
class MediaUrlExtractor implements UrlExtractorInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * An array of URL extractors grouped by priority.
   *
   * @var array
   */
  protected $urlExtractors;

  /**
   * An array of URL extractors grouped by priority.
   *
   * @var \Drupal\media_source_url_formatter\UrlExtractorInterface[]
   */
  protected $sortedUrlExtractors;

  /**
   * Create a new instance of the MediaUrlExtractor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get a list of URL extractors sorted by priority.
   *
   * @return \Drupal\media_source_url_formatter\UrlExtractorInterface[]
   *   An array of URL extractors.
   */
  public function getUrlExtractors() {
    if (is_null($this->sortedUrlExtractors)) {
      $this->sortedUrlExtractors = $this->sortUrlExtractors();
    }

    return $this->sortedUrlExtractors;
  }

  /**
   * Get the appropriate URL extractor for a given media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to get the extractor for.
   *
   * @return \Drupal\media_source_url_formatter\UrlExtractorInterface|null
   *   The URL extractor appropriate for the given media entity. NULL if no
   *   appropriate extractor could be found.
   */
  public function getUrlExtractorForEntity(MediaInterface $media) {
    foreach ($this->getUrlExtractors() as $url_extractor) {
      if ($url_extractor->isApplicable($media)) {
        return $url_extractor;
      }
    }

    return NULL;
  }

  /**
   * Determine whether a URL extractor for the given media entity exists.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to inspect.
   *
   * @return bool
   *   TRUE if a URL extractor exists for the entity, FALSE otherwise.
   */
  public function hasUrlExtractorForEntity(MediaInterface $media) {
    return $this->getUrlExtractorForEntity($media) !== NULL;
  }

  /**
   * Add an additional URL extractor for a specific media type.
   *
   * @param \Drupal\media_source_url_formatter\UrlExtractorInterface $url_extractor
   *   The URL extractor to add.
   * @param int $priority
   *   (optional) The priority of the extractor beeing added.
   *
   * @return $this
   *   The current extractor instance.
   */
  public function addUrlExtractor(UrlExtractorInterface $url_extractor, int $priority = 0) {
    $this->urlExtractors[$priority][] = $url_extractor;
    $this->sortedUrlExtractors = NULL;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function isApplicable(MediaInterface $media) {
    return $this->hasUrlExtractorForEntity($media);
  }

  /**
   * {@inheritDoc}
   */
  public function getUrl(MediaInterface $media, array $options = []) {
    $url_extractor = $this->getUrlExtractorForEntity($media);

    if (!$url_extractor) {
      return NULL;
    }

    $resource_url = $url_extractor->getUrl($media, $options);

    if (!$resource_url) {
      return NULL;
    }

    $context = [
      'extractor' => $url_extractor,
      'media' => $media,
      'options' => $options,
    ];

    $this->moduleHandler->alter('media_source_url', $resource_url, $media, $context);

    return $resource_url;
  }

  /**
   * Sort the URL extractors by priority.
   *
   * @return \Drupal\media_source_url_formatter\UrlExtractorInterface[]
   *   A sorted array of URL extractors.
   */
  protected function sortUrlExtractors() {
    $sorted_url_extractors = [];

    krsort($this->urlExtractors);

    foreach ($this->urlExtractors as $url_extractors) {
      $sorted_url_extractors = array_merge($sorted_url_extractors, $url_extractors);
    }

    return $sorted_url_extractors;
  }

}
