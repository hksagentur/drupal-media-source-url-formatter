<?php

namespace Drupal\media_source_url_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_source_url_formatter\UrlExtractorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Output the URL of the resource referenced by a media entity.
 *
 * @FieldFormatter(
 *   id = "media_source_url_formatter",
 *   label = @Translation("Media Source URL Formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class MediaSourceUrlFormatter extends EntityReferenceFormatterBase {

  /**
   * The image style storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * The URL extractor.
   *
   * @var \Drupal\media_source_url_formatter\UrlExtractorInterface
   */
  protected $urlExtractor;

  /**
   * Create a new instance of the MediaSourceUrlFormatter class.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\image\ImageStyleStorageInterface $image_style_storage
   *   The image style storage.
   * @param \Drupal\media_source_url_formatter\UrlExtractorInterface $url_extractor
   *   The URL extractor.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    EntityStorageInterface $image_style_storage,
    UrlExtractorInterface $url_extractor
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings
    );

    $this->imageStyleStorage = $image_style_storage;
    $this->urlExtractor = $url_extractor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('media_source_url_formatter.media_url_extractor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition
      ->getFieldStorageDefinition()
      ->getSetting('target_type');

    return $target_type === 'media';
  }

    /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
    ] + parent::defaultSettings();
  }

  /**
   * Get the image style provided in the settings of the formatter instance.
   *
   * @return \Drupal\image\ImageStyleInterface|null
   *   The selected image style instance or NULL if no style is selected.
   */
  public function getImageStyle() {
    $image_style_name = $this->getSetting('image_style');

    if (!$image_style_name) {
      return NULL;
    }

    return $this->imageStyleStorage->load($image_style_name);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => $this->t('None (original file)'),
      '#options' => image_style_options(FALSE),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $image_style_name = $this->getSettings('image_style');
    $image_style_options = image_style_options(FALSE);

    if (!array_key_exists($image_style_name, $image_style_options)) {
      $summary[] = $this->t('Original file');
    } else {
      $summary[] = $this->t('Image style: @style', [
        '@style' => $image_style_options[$image_style_name],
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $cacheable_metadata = new CacheableMetadata();
    $image_style = $this->getImageStyle();

    if ($image_style) {
      $cacheable_metadata->addCacheableDependency($image_style);
    }

    /** @var \Drupal\media\MediaInterface $media */
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $media) {
      $resource_url = $this->urlExtractor->getUrl($media, ['image_style' => $image_style]);

      if (!$resource_url) {
        continue;
      }

      $elements[$delta] = [
        '#markup' => $resource_url,
      ];

      $cacheable_metadata
        ->addCacheableDependency($media)
        ->applyTo($elements[$delta]);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    if ($image_style = $this->getImageStyle()) {
      $dependencies[$image_style->getConfigDependencyKey()][] = $image_style->getConfigDependencyName();
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $changed = parent::onDependencyRemoval($dependencies);

    if ($image_style = $this->getImageStyle()) {
      if (!empty($dependencies[$image_style->getConfigDependencyKey()][$image_style->getConfigDependencyName()])) {
        if ($image_style_replacement = $this->imageStyleStorage->getReplacementId($image_style->id())) {
          $this->setSetting('image_style', $image_style_replacement);
          $changed = TRUE;
        }
      }
    }

    return $changed;
  }

}
