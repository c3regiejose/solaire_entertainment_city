<?php

declare(strict_types=1);

namespace Drupal\solaire_rewards_perks\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Rewards & Perk add/edit forms.
 */
class RewardsPerkForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    $entity = $this->getEntity();
    $status = $entity->save();

    $message = $status === SAVED_NEW
      ? $this->t('Rewards & Perk %label has been created.', ['%label' => $entity->label()])
      : $this->t('Rewards & Perk %label has been updated.', ['%label' => $entity->label()]);

    $this->messenger()->addStatus($message);

    $form_state->setRedirect('entity.solaire_rewards_perk.collection');
  }

}
