<?php

namespace Drupal\solaire_sec\Hook\Preprocess;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\AccordionVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\HighlightCardsVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\IconCardsVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ImageGalleryVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\TabsContentByViewVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\TabsContentByReferenceVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\TestimonialCardsVariablesBuilder;
use Psr\Container\ContainerInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\InformationBlock;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\BlockViewsVariablesBuilder;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\PromotionsVariablesBuilder;

class ParagraphPreprocess implements ContainerInjectionInterface {
  /**
   * Block manager to create and inspect block plugin instances.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected BlockManagerInterface $blockManager;

  /**
   * Repository to obtain runtime contexts for context-aware plugins.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected ContextRepositoryInterface $contextRepository;

  /**
   * Handler used to apply context mappings to plugins.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected ContextHandlerInterface $contextHandler;

  /**
   * Entity type manager for loading paragraph entities.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;
  protected EntityRepositoryInterface $entityRepository;
  protected LanguageManagerInterface $languageManager;
  protected FileSystemInterface $fileSystem;
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * ParagraphPreprocess constructor.
   */
  public function __construct(
    BlockManagerInterface $block_manager,
    ContextRepositoryInterface $context_repository,
    ContextHandlerInterface $context_handler,
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository,
    LanguageManagerInterface $language_manager,
    FileSystemInterface $file_system,
    FileUrlGeneratorInterface $file_url_generator
  ) {
    $this->blockManager = $block_manager;
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->languageManager = $language_manager;
    $this->fileSystem = $file_system;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('plugin.manager.block'),
      $container->get('context.repository'),
      $container->get('context.handler'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('language_manager'),
      $container->get('file_system'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Implements hook_preprocess_paragraph().
   */
  #[Hook('preprocess_paragraph')]
  public function preprocess(array &$variables): void {
    $paragraph = $variables['elements']['#paragraph'] ?? NULL;
    
    if (!$paragraph instanceof ParagraphInterface) {
      return;
    }
    $new_variables = $this->buildVariablesFromParagraph($paragraph);
    $variables = array_merge($variables, $new_variables);
  }

  /**
   * Build a variables array from a Paragraph entity.
   *
   * Separated from hook_preprocess for easier unit testing.
   */
  public function buildVariablesFromParagraph(ParagraphInterface $paragraph): array {
    $variables = [];

    // Text Alignment.
    if ($textAlignment = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_text_alignment', $this->entityRepository)) {
      $variables['textAlignment'] = 'text-align-' . $textAlignment;
    }

    // Slide per view.
    if ($slides = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_slide_per_view', $this->entityRepository)) {
      $variables['slidesPerView'] = ($slides === '') ? 3 : (int) $slides;
    }

    // Custom ID
    if ($id = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_id', $this->entityRepository)) {
      $variables['sectionId'] = $id;
    }

    // Custom CSS Class
    if ($class = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_class', $this->entityRepository)) {
      $variables['sectionClass'] = $class;
    }

    // Tabs Content by View.
    if ($paragraph->bundle() === 'tabs_content_by_view') {
      $builder = new TabsContentByViewVariablesBuilder(
        $this->blockManager,
        $this->contextRepository,
        $this->contextHandler,
        $this->entityTypeManager,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildTabsContentByViewVariables($paragraph));
    }

    if ($paragraph->bundle() === 'information_block') {
      $builder = new InformationBlock(
        $this->entityTypeManager,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildInformationBlockVariables($paragraph));
    }

    // Accordion.
    if ($paragraph->bundle() === 'accordion') {
      $builder = new AccordionVariablesBuilder($this->entityTypeManager, $this->entityRepository);
      $variables = array_merge($variables, $builder->buildAccordionVariables($paragraph));
    }

    // Icon Cards.
    if ($paragraph->bundle() === 'icon_cards') {
      $builder = new IconCardsVariablesBuilder($this->entityTypeManager, $this->entityRepository);
      $variables = array_merge($variables, $builder->buildIconCardsVariables($paragraph));
    }

    // Testimonial Cards.
    if ($paragraph->bundle() === 'testimonial_cards') {
      $builder = new TestimonialCardsVariablesBuilder($this->entityTypeManager, $this->entityRepository);
      $variables = array_merge($variables, $builder->buildTestimonialCardsVariables($paragraph));
    }

    // Hightlighted Card.
    if ($paragraph->bundle() == 'highlight_cards') {
      $builder = new HighlightCardsVariablesBuilder(
        $this->entityTypeManager,
        $this->languageManager,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildHighlightCardsVariables($paragraph));
    }

    // Image Gallery.
    if ($paragraph->bundle() === 'image_gallery') {
      $builder = new ImageGalleryVariablesBuilder(
        $this->entityTypeManager, 
        $this->fileSystem, 
        $this->fileUrlGenerator,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildImageGalleryVariables($paragraph));
    }

    // Tabs Content by Reference.
    if ($paragraph->bundle() === 'tabs_content_by_reference') {
      $builder = new TabsContentByReferenceVariablesBuilder(
        $this->entityTypeManager,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildTabsContentByReferenceVariables($paragraph));
    }

    // Blocks.
    if ($paragraph->bundle() === 'block_views') {
      $builder = new BlockViewsVariablesBuilder(
        $this->blockManager,
        $this->contextRepository,
        $this->contextHandler,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildBlockViewsVariables($paragraph));
    }

    // Promotions
    if ($paragraph->bundle() === 'promotions') {
      $builder = new PromotionsVariablesBuilder(
        $this->entityTypeManager,
        $this->entityRepository
      );
      $variables = array_merge($variables, $builder->buildPromotionsVariables($paragraph));
    }

    return $variables;
  }
}
