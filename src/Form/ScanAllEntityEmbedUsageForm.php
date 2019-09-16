<?php

namespace Drupal\entity_embed_usage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Class ScanAllEntityEmbedUsageForm.
 */
class ScanAllEntityEmbedUsageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scan_all_entity_embed_usage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#type' => 'item',
      '#markup' => '<p>This page helps to scan all entity embeds in various content entities. The usage data will be stored in database table. It is safe to run this process any time as you see any discrepancies in entity embed usage data.</p>',
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Scan for Entity Embed Usage'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $operations = [
      [
        '\Drupal\entity_embed_usage\ScanAllEntityEmbedUsageBatch::clearAllData',
        [],
      ]
    ];

    $entity_definitions =  \Drupal::entityTypeManager()->getDefinitions();

    $supported_field_types = entity_embed_usage_supported_field_types();

    foreach ($entity_definitions as $entity_type_id => $entity_type) {
      if (!$entity_type->entityClassImplements(ContentEntityInterface::class) || !$entity_type->getBaseTable()) {
        continue;
      }
      $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type_id);
      foreach ($bundles as $bundle_name => $bundle_info) {
        $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type_id, $bundle_name);
        foreach ($fields as $field_info) {
          $field_type = $field_info->getType();
          if (in_array($field_type, array_keys($supported_field_types))) {
            $operations[] = [
              '\Drupal\entity_embed_usage\ScanAllEntityEmbedUsageBatch::scanForFieldInstance',
              [$entity_type_id],
            ];
            break 2;
          }
        }
      }
    }

    $batch = [
      'title' => t('Scanning for embedded entities...'),
      'operations' => $operations,
      'finished' => '\Drupal\entity_embed_usage\ScanAllEntityEmbedUsageBatch::finishBatchCallback',
    ];

    batch_set($batch);
  }
}