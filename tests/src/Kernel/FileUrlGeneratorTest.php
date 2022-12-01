<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

/**
 * @group media_source_url_formatter
 */
class FileUrlGeneratorTest extends GeneratorTestBase {

  public function testFileUrlGenerator(): void {
    /** @var \Drupal\media_source_url_formatter\FileUrlGenerator */
    $generator = $this->container->get('media_source_url_formatter.file_url_generator');

    /** @var \Drupal\media\MediaTypeInterface */
    $media_type = $this->createMediaType('file');

    /** @var \Drupal\media\MediaInterface */
    $media = $this->generateMedia('foo.txt', $media_type);

    /** @var \Drupal\file\FileInterface */
    $file = $media->field_media_file->entity;

    $this->assertSame($file->createFileUrl(FALSE), $generator->generate($media));
  }

}
