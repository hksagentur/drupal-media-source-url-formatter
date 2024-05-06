<?php

namespace Drupal\Tests\media_source_url_formatter\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\media\MediaTypeInterface;
use Drupal\user\Entity\User;

/**
 * A base class for URL generator tests.
 */
abstract class GeneratorTestBase extends KernelTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritDoc}
   */
  public static $modules = [
    'media',
    'media_test_source',
    'image',
    'user',
    'field',
    'system',
    'file',
    'media_source_url_formatter',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installEntitySchema('media');

    $this->installSchema('file', 'file_usage');
    $this->installSchema('system', 'sequences');

    $this->installConfig(['field', 'system', 'image', 'file', 'media']);

    $user = User::create([
      'name' => 'testuser',
      'status' => 1,
    ]);
    $user->save();

    $this->container
      ->get('current_user')
      ->setAccount($user);

    $this->user = $user;
  }

  /**
   * A helper method to generate a file entity.
   *
   * @param string $filename
   *   The name of the file to create including the file extension.
   *
   * @return \Drupal\file\Entity\File
   *   The generated file entity.
   */
  protected function generateFile($filename) {
    file_put_contents('public://' . $filename, $this->randomString(512));

    $file = File::create([
      'uri' => 'public://' . $filename,
      'uid' => $this->user->id(),
    ]);

    $file->setPermanent();

    return $file;
  }

  /**
   * A helper method to generate a media entity for a given file.
   *
   * @param string $filename
   *   The name of the file to associate with the media entity.
   * @param \Drupal\media\MediaTypeInterface $media_type
   *   The media type.
   *
   * @return \Drupal\media\Entity\Media
   *   The generated media entity.
   */
  protected function generateMedia($filename, MediaTypeInterface $media_type) {
    $file = $this->generateFile($filename);
    $file->save();

    $source_field = $media_type
      ->getSource()
      ->getSourceFieldDefinition($media_type)
      ->getName();

    $media = Media::create([
      'bundle' => $media_type->id(),
      'name' => pathinfo($filename, PATHINFO_FILENAME),
      $source_field => [
        'target_id' => $file->id(),
      ],
    ]);

    return $media;
  }

}
