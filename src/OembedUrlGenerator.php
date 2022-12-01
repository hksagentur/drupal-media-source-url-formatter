<?php

namespace Drupal\media_source_url_formatter;

use Drupal\Component\Utility\Html;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;

/**
 * Generate the URL of an OEmbed resource referenced by a media entity.
 */
class OembedUrlGenerator implements UrlGeneratorInterface {

  /**
   * {@inheritDoc}
   */
  public function isApplicable(MediaInterface $media) {
    return $media->getSource() instanceof OEmbedInterface;
  }

  /**
   * {@inheritDoc}
   */
  public function generate(MediaInterface $media, array $options = []) {
    $media_source = $media->getSource();
    $resource_url = $media_source->getMetadata($media, 'url');

    if (!$resource_url) {
      return $resource_url;
    }

    // Some OEembed resource do not provide the URL as property but an HTML
    // snippet instead (e.g. the YouTube endpoint). In these cases try to
    // search for the corresponding URL within the HTML snippet.
    $html_document = $this->tryParseSnippet(
      $media_source->getMetadata($media, 'html')
    );

    if (!$html_document) {
      return NULL;
    }

    return $this->extractUrlFromSnippet($html_document);
  }

  /**
   * Try to parse an HTML snippet.
   *
   * @param string $markup
   *   The HTML snippet to parse.
   *
   * @return \DOMDocument|null
   *   A \DOMDocument that represents the loaded HTML snippet.
   */
  protected function tryParseSnippet($markup) {
    return empty($markup) ? NULL : Html::load($markup);
  }

  /**
   * Extract the resource URL from a given HTML snippet.
   *
   * @param \DOMDocument $document
   *   The \DOMDocument to search within.
   *
   * @return string|null
   *   The URL of the resource. NULL if no URL could be found.
   */
  protected function extractUrlFromSnippet(\DOMDocument $document) {
    foreach ($document->getElementsByTagName('iframe') as $node) {
      if ($attribute = $node->attributes->getNamedItem('src')) {
        return $attribute->nodeValue;
      }
    }

    return NULL;
  }

}
