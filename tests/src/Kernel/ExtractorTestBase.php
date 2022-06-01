<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

use Drupal\Tests\media\Kernel\MediaKernelTestBase;

/**
 * A base class for URL extractor tests.
 */
abstract class ExtractorTestBase extends MediaKernelTestBase {

  /**
   * {@inheritDoc}
   */
  public static $modules = [
    'media_source_url_formatter',
  ];

}
