<?php

namespace Drupal\entity_embed_usage;


class ScanAllEntityEmbedUsageBatch {

  public static function clearAllData(&$context) {
    $context['message'] = t('Deleting all exiting data...');
    \Drupal::database()->truncate('entity_embed_usage')->execute();
    \Drupal::messenger()->addStatus(t('Deleted all existing data.'));
  }

  public static function scanForFieldInstance($entity_type_id, &$context){
    $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
    $message = t('Scanning for all entities of type @entity_type_label...', array('@entity_type_label' => $entity_type->getLabel()));

    // Initiate multistep processing.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = \Drupal::entityQuery($entity_type_id)
        ->count()
        ->execute();
      //$context['sandbox']['revisions'] = 0;
    }

    // Process the next 100 if there are at least 100 left. Otherwise,
    // we process the remaining number.
    $batch_size = 10;
    $max = $context['sandbox']['progress'] + $batch_size;
    if ($max > $context['sandbox']['max']) {
      $max = $context['sandbox']['max'];
    }

    // Start where we left off last time.
    $start = $context['sandbox']['progress'];

    $query = \Drupal::entityQuery($entity_type_id)
      ->range($context['sandbox']['progress'], $batch_size); //specify results to return
    $entity_ids = $query->execute();

    $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    $id_key = $entity_type->getKey('id');
    $revision_key = $entity_type->getKey('revision');

    foreach ($entity_ids as $entity_id) {
      $entity = $entity_storage->load($entity_id);
      if ($entity_type->isRevisionable()) {
        //$revision_ids = $entity_storage->revisionIds($entity);
        $revision_ids =  \Drupal::database()->query(
          'SELECT ' . $revision_key . ' FROM {' . $entity_storage->getRevisionTable() . '} WHERE ' .  $id_key . '=:id ORDER BY ' . $revision_key,
          [':id' => $entity->id()]
        )->fetchCol();
        // Record entity embed usage in each revision up to current one.
        foreach ($revision_ids as $revision_id) {
          $entity_revision = $entity_storage->loadRevision($revision_id);
          $data = entity_embed_usage_get_embeds_data($entity_revision);
          entity_embed_usage_save_embeds_data($data);
          //$context['sandbox']['revisions']++;
        }
      }
      else {
        $data = entity_embed_usage_get_embeds_data($entity);
        entity_embed_usage_save_embeds_data($data);
        //$context['sandbox']['revisions']++;
      }
      // Update our progress!
      $context['sandbox']['progress']++;
    }

    // Multistep processing : report progress.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
    if ($context['finished']) {
      \Drupal::messenger()->addStatus(t(
        'Processed @count @entity_type_label items.',
        [
          '@count' => $context['sandbox']['max'],
          '@entity_type_label' => $entity_type->getLabel(),
          //'@revisions' => $context['sandbox']['revisions']
        ]
      ));
    }

    $context['message'] = $message;
  }

  function finishBatchCallback($success, $results, $operations) {
    // TODO: Anything else?
  }
}