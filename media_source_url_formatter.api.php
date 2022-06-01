<?php

/**
 * @file
 * Hooks related to the Media Source Url Formatter.
 */

/**
 * Alter the URL of a resource referenced by a given media entity.
 *
 * @param string $resource_url
 *   The URL of the resource referenced by a media entity.
 * @param \Drupal\media\MediaInterface $media
 *   The media entity referencing the resource.
 * @param array $context
 *   An associative array containing the following context information:
 *   - 'extractor': The URL extractor selected for the current media entity.
 *   - 'media': The media entity referencing the resource.
 *   - 'options': An associative array of additional options provided to the
 *      URL extractor.
 */
function hook_media_source_url_alter(string &$resource_url, \Drupal\media\MediaInterface $media, array &$context) {
  $resource_url = preg_replace('/^http:\/\//', 'https://', $resource_url);
}
