<?php

namespace Drupal\media_source_url_formatter;

use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\Image;

/**
 * Extract the URL of an image referenced by a media entity.
 */
class ImageUrlExtractor extends FileUrlExtractor {

  /**
   * {@inheritDoc}
   */
  public function isApplicable(MediaInterface $media) {
    return $media->getSource() instanceof Image;
  }

  /**
   * {@inheritDoc}
   */
  public function getUrl(MediaInterface $media, array $options = []) {
    $file_id = $media->getSource()->getSourceFieldValue($media);

    if (empty($file_id)) {
      return NULL;
    }

    $file = $this->loadFile($file_id);

    if (empty($file_id)) {
      return NULL;
    }

    $file_uri = $file->getFileUri();

    /** @var \Drupal\image\ImageStyleInterface */
    $image_style = $options['image_style'] ?? NULL;

    if (!$image_style || !$image_style->supportsUri($file_uri)) {
      return $file->createFileUrl(FALSE);
    }

    return $image_style->buildUrl($file_uri);
  }

}
