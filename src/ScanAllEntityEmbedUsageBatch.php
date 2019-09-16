<?php

namespace Drupal\entity_embed_usage;


class ScanAllEntityEmbedUsageBatch {

  public static function clearAllData(&$context) {
    $context['message'] = t('Deleting all exiting data...');
    \Drupal::database()->truncate('entity_embed_usage')->execute();
    drupal_set_message(t('Deleted all existing data.'));
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

    foreach ($entity_ids as $entity_id) {
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
      entity_embed_usage_scan_for_embeds($entity);
      // Update our progress!
      $context['sandbox']['progress']++;
    }

    // Multistep processing : report progress.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
    if ($context['finished']) {
      drupal_set_message(t('Processed @count @entity_type_label items', ['@count' => $context['sandbox']['max'], '@entity_type_label' => $entity_type->getLabel()]));
    }

    $context['message'] = $message;
  }

  function finishBatchCallback($success, $results, $operations) {
    // TODO: Anything else?
  }
}