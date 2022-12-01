<?php

namespace Drupal\media_source_url_formatter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\media\MediaInterface;

/**
 * Get the URL of the resource referenced by a media entity.
 */
class MediaSourceUrlGenerator implements UrlGeneratorInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * An array of URL generators grouped by priority.
   *
   * @var array
   */
  protected $generators;

  /**
   * An array of URL generators grouped by priority.
   *
   * @var \Drupal\media_source_url_formatter\UrlGeneratorInterface[]
   */
  protected $sortedGenerators;

  /**
   * Create a new instance of the MediaSourceUrlGenerator.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get a list of URL generators sorted by priority.
   *
   * @return \Drupal\media_source_url_formatter\UrlGeneratorInterface[]
   *   An array of URL generators.
   */
  public function getGenerators() {
    if (is_null($this->sortedGenerators)) {
      $this->sortedGenerators = $this->sortGenerators();
    }

    return $this->sortedGenerators;
  }

  /**
   * Get the appropriate URL generator for a given media entity.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to get a generator for.
   *
   * @return \Drupal\media_source_url_formatter\UrlGeneratorInterface|null
   *   The URL generator appropriate for the given media entity. NULL if no
   *   appropriate generator could be found.
   */
  public function getGeneratorForEntity(MediaInterface $media) {
    foreach ($this->getGenerators() as $url_generator) {
      if ($url_generator->isApplicable($media)) {
        return $url_generator;
      }
    }

    return NULL;
  }

  /**
   * Determine whether a URL generator for the given media entity exists.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media entity to inspect.
   *
   * @return bool
   *   TRUE if a URL generator exists for the entity, FALSE otherwise.
   */
  public function hasGeneratorForEntity(MediaInterface $media) {
    return $this->getGeneratorForEntity($media) !== NULL;
  }

  /**
   * Add an additional URL generator for a specific media type.
   *
   * @param \Drupal\media_source_url_formatter\UrlGeneratorInterface $url_generator
   *   The URL generator to add.
   * @param int $priority
   *   (optional) The priority of the generator beeing added.
   *
   * @return $this
   *   The current generator instance.
   */
  public function addGenerator(UrlGeneratorInterface $url_generator, int $priority = 0) {
    $this->generators[$priority][] = $url_generator;
    $this->sortedGenerators = NULL;

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function isApplicable(MediaInterface $media) {
    return $this->hasGeneratorForEntity($media);
  }

  /**
   * {@inheritDoc}
   */
  public function generate(MediaInterface $media, array $options = []) {
    $url_generator = $this->getGeneratorForEntity($media);

    if (!$url_generator) {
      return NULL;
    }

    $resource_url = $url_generator->generate($media, $options);

    if (!$resource_url) {
      return NULL;
    }

    $context = [
      'generator' => $url_generator,
      'media' => $media,
      'options' => $options,
    ];

    $this->moduleHandler->alter('media_source_url', $resource_url, $media, $context);

    return $resource_url;
  }

  /**
   * Sort the URL generators by priority.
   *
   * @return \Drupal\media_source_url_formatter\UrlGeneratorInterface[]
   *   A sorted array of URL generators.
   */
  protected function sortGenerators() {
    $sorted_generators = [];

    krsort($this->generators);

    foreach ($this->generators as $generators) {
      $sorted_generators = array_merge($sorted_generators, $generators);
    }

    return $sorted_generators;
  }

}
