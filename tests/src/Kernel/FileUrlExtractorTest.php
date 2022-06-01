<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

use org\bovigo\vfs\vfsStream;

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

    $this->assertSame(
      vfsStream::url('drupal_root/sites/default/files/catalogue.pdf'),
      $extractor->getUrl($media)
    );
  }

}
