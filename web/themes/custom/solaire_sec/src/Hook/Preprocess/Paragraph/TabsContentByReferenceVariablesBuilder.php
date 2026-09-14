<?php

namespace Drupal\solaire_sec\Hook\Preprocess\Paragraph;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\ParagraphInterface;

class TabsContentByReferenceVariablesBuilder {
  protected EntityTypeManagerInterface $entityTypeManager;

  protected EntityRepositoryInterface $entityRepository;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityRepositoryInterface $entity_repository
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  public function buildTabsContentByReferenceVariables(ParagraphInterface $paragraph): array {
    $result = [];

    if ($title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository)) {
      $result['tab_content_title'] = $title;
    }

    if ($content = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_content', $this->entityRepository)) {
      $result['tab_content_content'] = $content;
    }

    $result['tab_content_items'] = [];
    $item_ids = ParagraphHelper::getParagraphReferenceFieldValue($paragraph, 'field_referenced_content_items');
    $items = ParagraphHelper::loadParagraphsByIds($this->entityTypeManager, $item_ids);

    foreach ($items as $item) {
      $tab = $this->buildTabContentByReferenceItem($item);
      if (!empty($tab)) {
        $result['tab_content_items'][$item->id()] = $tab;
      }
    }

    return $result;
  }

  protected function buildTabContentByReferenceItem(ParagraphInterface $paragraph): array {
    $result = [];
    $title = ParagraphHelper::getParagraphFieldValue($paragraph, 'field_title', $this->entityRepository);

    if ($title) {
      $result['heading'] = $title;
      $result['heading_slug'] = str_replace(' ', '-', strtolower($title));
    }

    $content_ids = ParagraphHelper::getParagraphReferenceFieldValue($paragraph, 'field_referenced_content');
    if (!$content_ids) {
      return $result;
    }

    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($content_ids);
    $node_view_builder = $this->entityTypeManager->getViewBuilder('node');

    foreach ($content_ids as $content_id) {
      if (isset($nodes[$content_id])) {
        $result['content'][] = $node_view_builder->view($nodes[$content_id], 'card');
      }
    }

    return $result;
  }
}
