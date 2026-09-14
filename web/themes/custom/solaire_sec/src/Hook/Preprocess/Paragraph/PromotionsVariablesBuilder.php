<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\solaire_sec\Hook\Preprocess\Paragraph\ParagraphHelper;

class PromotionsVariablesBuilder {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected EntityRepositoryInterface $entityRepository;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * 
   */
  public function buildPromotionsVariables(ParagraphInterface $paragraph): array {
    $result = [];

    $result['promotion_items'] = [];

    if ($paragraph->hasField('field_referenced_content_items') &&
      !$paragraph->get('field_referenced_content_items')->isEmpty()
    ) {
      $ids = array_column(
        $paragraph->get('field_referenced_content_items')->getValue(),
        'target_id'
      );

      $promotionLists = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $ids);
      foreach ($promotionLists as $key => $promotionItemParagraph) {
        $result['promotion_items'] = $this->buildPromotionItem($promotionItemParagraph);
      }
    }

    return $result;
  }

  /**
   * 
   */
  public function buildPromotionItem(ParagraphInterface $paragraph) {

    $promotionItems = [];

    if ($paragraph->hasField('field_referenced_content') && !$paragraph->get('field_referenced_content')->isEmpty()) {
      $content_ids = ParagraphHelper::getParagraphReferenceFieldValue($paragraph, 'field_referenced_content');

      $nodes = $this->entityTypeManager
        ->getStorage('node')
        ->loadMultiple($content_ids);
      $node_view_builder = $this->entityTypeManager->getViewBuilder('node');

      foreach ($content_ids as $content_id) {
        if (isset($nodes[$content_id])) {
          $build = $node_view_builder->view($nodes[$content_id], 'card');
          $build['#attributes']['class'][] = 'swiper-slider';
          $promotionItems[] = $build;
        }
      }

      return $promotionItems;
    }
  }

}
