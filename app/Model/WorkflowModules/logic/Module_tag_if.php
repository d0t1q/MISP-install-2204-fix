<?php
include_once APP . 'Model/WorkflowModules/WorkflowBaseModule.php';

class Module_tag_if extends WorkflowBaseLogicModule
{
    public $id = 'tag-if';
    public $name = 'IF :: Tag';
    public $description = 'Tag IF / ELSE condition block. The `then` output will be used if the encoded conditions is satisfied, otherwise the `else` output will be used.';
    public $icon = 'code-branch';
    public $inputs = 1;
    public $outputs = 2;
    public $html_template = 'if';
    public $params = [];

    private $Tag;
    private $operators = [
        'in_or' => 'Is tagged with any (OR)',
        'in_and' => 'Is tagged with all (AND)',
        'not_in_or' => 'Is not tagged with any (OR)',
        'not_in_and' => 'Is not tagged with all (AND)',
    ];

    public function __construct()
    {
        parent::__construct();
        $conditions = [
            'Tag.is_galaxy' => 0,
        ];
        $this->Tag = ClassRegistry::init('Tag');
        $tags = $this->Tag->find('all', [
            'conditions' => $conditions,
            'recursive' => -1,
            'order' => ['name asc'],
            'fields' => ['Tag.id', 'Tag.name']
        ]);
        $tags = array_column(array_column($tags, 'Tag'), 'name', 'id');
        $this->params = [
            [
                'type' => 'select',
                'options' => [
                    'event' => __('Event'),
                    'attribute' => __('Attribute'),
                    'event_attribute' => __('Inherited Attribute'),
                ],
                'default' => 'event',
                'label' => 'Scope',
            ],
            [
                'type' => 'select',
                'label' => 'Condition',
                'default' => 'in_or',
                'options' => $this->operators,
            ],
            [
                'type' => 'picker',
                'multiple' => true,
                'label' => 'Tags',
                'options' => $tags,
                'placeholder' => __('Pick a tag'),
            ],
        ];
    }

    public function exec(array $node, WorkflowRoamingData $roamingData, array &$errors=[]): bool
    {
        parent::exec($node, $roamingData, $errors);
        $params = $this->getParamsWithValues($node);

        $value = $params['Tags']['value'];
        $operator = $params['Condition']['value'];
        $scope = $params['Scope']['value'];
        $data = $roamingData->getData();
        $extracted = $this->__getTagFromScope($scope, $data);
        $eval = $this->evaluateCondition($extracted, $operator, $value);
        return !empty($eval);
    }

    private function __getTagFromScope($scope, array $data): array
    {
        $path = '';
        if ($scope == 'attribute') {
            $path = 'Event.Attribute.0.Tag.{n}[inherited=0].id';
        } elseif ($scope == 'event_attribute') {
            $path = 'Event.Attribute.0.Tag.{n}.id';
        } else {
            $path = 'Event.Attribute.0.Tag.{n}[inherited=1].id';
        }
        return Hash::extract($data, $path) ?? [];
    }
}