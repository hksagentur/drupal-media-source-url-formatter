<?php

namespace Drupal\media_source_url_formatter;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\File;

/**
 * Get the URL of a generic file referenced by a media entity.
 */
class FileUrlGenerator implements UrlGeneratorInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Create a new instance of the FileUrlGenerator.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function isApplicable(MediaInterface $media) {
    return $media->getSource() instanceof File;
  }

  /**
   * {@inheritDoc}
   */
  public function generate(MediaInterface $media, array $options = []) {
    $file_id = $media->getSource()->getSourceFieldValue($media);

    if (empty($file_id)) {
      return NULL;
    }

    $file = $this->loadFile($file_id);

    if (empty($file_id)) {
      return NULL;
    }

    return $file->createFileUrl(FALSE);
  }

  /**
   * Load a file entity from storage by a ID.
   *
   * @param int $id
   *   The ID of the file entity to load.
   *
   * @return \Drupal\file\FileInterface|null
   *   The file entity. NULL if no file entity with the given ID is found.
   */
  protected function loadFile($id) {
    return $this->entityTypeManager
      ->getStorage('file')
      ->load($id);
  }

}
