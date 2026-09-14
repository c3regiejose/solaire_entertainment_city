<?php

declare(strict_types=1);

namespace Drupal\solaire_rewards_perks\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Confirmation form for deleting a Rewards & Perk.
 */
class RewardsPerkDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion(): string {
    return $this->t('Are you sure you want to delete %label?', [
      '%label' => $this->getEntity()->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('entity.solaire_rewards_perk.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $label = $this->getEntity()->label();
    $this->getEntity()->delete();

    $this->messenger()->addStatus($this->t('Rewards & Perk %label has been deleted.', [
      '%label' => $label,
    ]));

    $form_state->setRedirect('entity.solaire_rewards_perk.collection');
  }

}
