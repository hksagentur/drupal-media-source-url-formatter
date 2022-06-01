<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\TestFileCreationTrait;

/**
 * A base class for URL extractor tests.
 */
abstract class AbstractExtractorTestCase extends KernelTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'media_source_url_formatter',
    'media',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installSchema('file', 'file_usage');

    $this->installEntitySchema('file');
    $this->installEntitySchema('media');
  }

}