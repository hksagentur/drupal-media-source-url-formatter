<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;

/**
 * @group media_source_url_formatter
 */
class FileUrlExtractorTest extends AbstractExtractorTestCase {

  public function testFileUrlExtractor(): void {
    $extractor = $this->container->get('media_source_url_formatter.file_url_extractor');

    $test_files = $this->getTestFiles('binary');

    $file = File::create([
      'uri' => $test_files[0]->uri,
      'uuid' => 'a2cb2b6f-7bf8-4da4-9de5-316e93487518',
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    $file->save();

    $media = Media::create([
      'bundle' => 'file',
      'name' => 'File 1',
      'field_media_file' => ['target_id' => $file->id()],
    ]);
    $media->save();

    $this->assertSame($file->createFileUrl(FALSE), $extractor->getUrl($media));
  }

}
