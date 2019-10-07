<?php

namespace Drupal\entity_embed_usage\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\field\Standard;

/**
 * Default implementation of the base field plugin.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_embed_usage_field_name")
 */
class FieldName extends Standard {
  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->additional_fields['host_entity_type'] = 'host_entity_type';
    $this->additional_fields['host_entity_bundle'] = 'host_entity_bundle';
    $this->additional_fields['host_entity_id'] = 'host_entity_id';
    $this->additional_fields['host_entity_field_name'] = 'host_entity_field_name';
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $this->addAdditionalFields();
    $this->field_alias = $this->table . '_' . $this->field;
  }

  public function render(ResultRow $values) {
    $value = $this->getValue($values, 'host_entity_field_name');
    $entity_type = $this->getValue($values, 'host_entity_type');
    $bundle = $this->getValue($values, 'host_entity_bundle');
    if ($value && $entity_type && $bundle) {
      $field_instance = \Drupal::entityTypeManager()->getStorage('field_config')->load("$entity_type.$bundle.$value");
    
      return $this
        ->sanitizeValue($field_instance->getLabel());
    }
  }
}
