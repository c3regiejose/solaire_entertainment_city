<?php

declare(strict_types=1);

namespace Drupal\solaire_rewards_perks;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Builds the administrative listing for Rewards & Perks.
 */
class RewardsPerkListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['title'] = $this->t('Title');
    $header['created'] = $this->t('Created');
    $header['changed'] = $this->t('Updated');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    $row['title'] = Link::fromTextAndUrl(
      $entity->label(),
      Url::fromRoute('entity.solaire_rewards_perk.edit_form', [
        'solaire_rewards_perk' => $entity->id(),
      ])
    );
    $row['created'] = $this->dateFormatter->format($entity->get('created')->value);
    $row['changed'] = $this->dateFormatter->format($entity->get('changed')->value);

    return $row + parent::buildRow($entity);
  }

}
