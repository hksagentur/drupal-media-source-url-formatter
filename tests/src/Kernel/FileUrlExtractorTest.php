<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

/**
 * @group media_source_url_formatter
 */
class FileUrlExtractorTest extends ExtractorTestBase {

  public function testFileUrlExtractor(): void {
    /** @var \Drupal\media_source_url_formatter\FileUrlExtractor */
    $extractor = $this->container->get('media_source_url_formatter.file_url_extractor');

    /** @var \Drupal\media\MediaTypeInterface */
    $media_type = $this->createMediaType('file');

    /** @var \Drupal\media\MediaInterface */
    $media = $this->generateMedia('catalogue.pdf', $media_type);

    /** @var \Drupal\file\FileInterface */
    $file = $media->field_media_file->first()->entity;

    file_put_contents($file->getFileUri(), $this->randomMachineName());

    $this->assertSame($file->createFileUrl(FALSE), $extractor->getUrl($media));
  }

}
