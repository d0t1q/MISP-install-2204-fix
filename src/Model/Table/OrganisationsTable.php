<?php

namespace App\Model\Table;

use App\Model\Table\AppTable;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

class OrganisationsTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('UUID');
        //$this->addBehavior('Timestamp');
        $this->addBehavior('AuditLog');
        /*$this->addBehavior('NotifyAdmins', [
            'fields' => ['uuid', 'name', 'url', 'nationality', 'sector', 'type', 'contacts', 'modified', 'meta_fields'],
        ]);*/
        $this->setDisplayField('name');
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if ($entity->isNew()) {
            $entity->date_created = date('Y-m-d H:i:s');
        }
        $entity->date_modified = date('Y-m-d H:i:s');
        return;
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('name')
            ->notEmptyString('uuid')
            ->requirePresence(['name', 'uuid'], 'create');
        return $validator;
    }

    public function captureOrg($org): ?int
    {
        if (!empty($org['uuid'])) {
            $existingOrg = $this->find()->where(
                [
                'uuid' => $org['uuid']
                ]
            )->first();
        } else {
            return null;
        }
        if (empty($existingOrg)) {
            $entityToSave = $this->newEmptyEntity();
            $this->patchEntity(
                $entityToSave,
                $org,
                [
                'accessibleFields' => $entityToSave->getAccessibleFieldForNew()
                ]
            );
        } else {
            $this->patchEntity($existingOrg, $org);
            $entityToSave = $existingOrg;
        }
        $entityToSave->setDirty('modified', false);
        $savedEntity = $this->save($entityToSave, ['associated' => false]);
        if (!$savedEntity) {
            return null;
        }
        return $savedEntity->id;
    }

    public function fetchOrg($id)
    {
        if (empty($id)) {
            return false;
        }
        $conditions = ['Organisations.id' => $id];
        if (Validation::uuid($id)) {
            $conditions = ['Organisations.uuid' => $id];
        } elseif (!is_numeric($id)) {
            $conditions = ['LOWER(Organisations.name)' => strtolower($id)];
        }
        $org = $this->find(
            'all',
            [
            'conditions' => $conditions,
            'recursive' => -1
            ]
        )->disableHydration()->first();
        return (empty($org)) ? false : $org;
    }
}