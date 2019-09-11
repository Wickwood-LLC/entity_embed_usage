<?php

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Implements hook_views_data().
 */
function entity_embed_usage_views_data() {
  $data['entity_embed_usage']['table']['group'] = t('Entity Embed Usage');

  $data['entity_embed_usage']['table']['base'] = [
    // Identifier (primary) field in this table for Views.
    'field' => 'entity_embed_usage_id',
    // Label in the UI.
    'title' => t('Entity embed usage'),
    // Longer description in the UI. Required.
    'help' => t('Shows info about embedded entities in rich text fields.'),
  ];


  $data['entity_embed_usage']['entity_embed_usage_id'] = [
    'title' => t('Entity Embed Usage Id'),
    'help' => t('Unique id for usage'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['entity_embed_usage']['embedded_entity_type'] = [
    'title' => t('Embedded entity type'),
    'help' => t('Embedded entity type'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['entity_embed_usage']['embedded_entity_id'] = [
    'title' => t('Embedded entity id'),
    'help' => t('Embedded entity id'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['entity_embed_usage']['host_entity_type'] = [
    'title' => t('Host entity type'),
    'help' => t('Host entity type'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['entity_embed_usage']['host_entity_id'] = [
    'title' => t('Host entity id'),
    'help' => t('Host entity id'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['entity_embed_usage']['host_entity_field_name'] = [
    'title' => t('Host entity field name'),
    'help' => t('Host entity field name'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  $data['entity_embed_usage']['host_entity_field_delta'] = [
    'title' => t('Host entity filed delta'),
    'help' => t('Host entity field delta'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  $data['entity_embed_usage']['host_entity_field_property'] = [
    'title' => t('Host entity field property'),
    'help' => t('Host entity field property'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  return $data;
}

/**
 * Implements hook_views_data_alter().
 */
function entity_embed_usage_views_data_alter(&$data) {
  $entity_definitions =  \Drupal::entityTypeManager()->getDefinitions();
  foreach ($entity_definitions as $entity_type_id => $entity_type) {
    if (!$entity_type->entityClassImplements(ContentEntityInterface::class) || !$entity_type->getBaseTable()) {
      continue;
    }

    $base_table = $entity_type->getDataTable() ?: $entity_type->getBaseTable();
    $args = ['@entity_type' => $entity_type_id, '@entity_type_label' => $entity_type->getLabel()];

    $data['entity_embed_usage'][$entity_type_id . '_host'] = [
      'title' => t('@entity_type as host entities', $args),
      'help' => t('Relate all @entity_type as host.', $args),
      'relationship' => [
        'group' => t('Host @entity_type_label', $args),
        'label' => t('Host @entity_type_label', $args),
        'base' => $base_table,
        'base field' => $entity_type->getKey('id'),
        'relationship field' => 'host_entity_id',
        'id' => 'standard',
        'extra' => [
          [
            'left_field' => 'host_entity_type',
            'value' => $entity_type_id,
          ],
        ],
      ],
    ];

    $data['entity_embed_usage'][$entity_type_id . '_embedded'] = [
      'title' => t('@entity_type as embedded entities', $args),
      'help' => t('Relate all @entity_type as embedded entities.', $args),
      'relationship' => [
        'group' => t('Embedded @entity_type_label', $args),
        'label' => t('Embedded @entity_type_label', $args),
        'base' => $base_table,
        'base field' => $entity_type->getKey('id'),
        'relationship field' => 'embedded_entity_id',
        'id' => 'standard',
        'extra' => [
          [
            'left_field' => 'embedded_entity_type',
            'value' => $entity_type_id,
          ],
        ],
      ],
    ];
  }
}