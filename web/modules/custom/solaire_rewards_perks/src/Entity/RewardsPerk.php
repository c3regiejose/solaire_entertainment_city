<?php

declare(strict_types=1);

namespace Drupal\solaire_rewards_perks\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\UserInterface;

/**
 * Defines the Rewards & Perk entity.
 *
 * @ContentEntityType(
 *   id = "solaire_rewards_perk",
 *   label = @Translation("Rewards & Perk"),
 *   label_collection = @Translation("Rewards & Perks"),
 *   handlers = {
 *     "list_builder" = "Drupal\solaire_rewards_perks\RewardsPerkListBuilder",
 *     "form" = {
 *       "add" = "Drupal\solaire_rewards_perks\Form\RewardsPerkForm",
 *       "edit" = "Drupal\solaire_rewards_perks\Form\RewardsPerkForm",
 *       "delete" = "Drupal\solaire_rewards_perks\Form\RewardsPerkDeleteForm"
 *     },
 *     "access" = "Drupal\solaire_rewards_perks\RewardsPerkAccessControlHandler"
 *   },
 *   base_table = "solaire_rewards_perk",
 *   admin_permission = "administer solaire rewards perks",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "uid" = "uid"
 *   },
 *   links = {
 *     "collection" = "/admin/content/rewards-perks",
 *     "add-form" = "/admin/content/rewards-perks/add",
 *     "edit-form" = "/admin/content/rewards-perks/{solaire_rewards_perk}/edit",
 *     "delete-form" = "/admin/content/rewards-perks/{solaire_rewards_perk}/delete"
 *   },
 *   field_ui_base_route = "entity.solaire_rewards_perk.collection"
 * )
 */
class RewardsPerk extends ContentEntityBase {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setDescription(new TranslatableMarkup('The title of the Rewards & Perk.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -10,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -10,
      ]);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getCurrentUserId')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 90,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => 90,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time that the entity was last updated.'));

    return $fields;
  }

  /**
   * Returns the current user ID.
   */
  public static function getCurrentUserId(): array {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Returns the entity title.
   */
  public function getTitle(): string {
    return (string) $this->get('title')->value;
  }

  /**
   * Sets the entity title.
   */
  public function setTitle(string $title): static {
    $this->set('title', $title);
    return $this;
  }

  /**
   * Returns the author.
   */
  public function getOwner(): ?UserInterface {
    $owner = $this->get('uid')->entity;
    return $owner instanceof UserInterface ? $owner : NULL;
  }

}
