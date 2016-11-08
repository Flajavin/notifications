<?php /* @var $this mpf\components\notifications\controllers\Admin */ ?>
<?= \app\components\htmltools\Page::get()->title("Notifications Management - Create a new Type", [
    [
        'url' => [$this->getName(), 'index'],
        'label' => "View All"
    ],
    [
        'url' => [$this->getName(), 'create'],
        'label' => "Add a new Type",
        'htmlOptions' => ['class' => 'selected']
    ]
]); ?>

<?= \mpf\widgets\form\Form::get([
    'name' => 'save',
    'model' => $model,
    'theme' => 'default-wide',
    'fields' => [
        'name',
        'title',
        'description',
        [
            'name' => 'email',
            'type' => 'textarea',
            'htmlOptions' => ['rows' => 30]
        ],
        'sms',
        'web',
        'mobile',
        [
            'name' => 'group_email',
            'type' => 'textarea'
        ],
        'group_url'
    ]
])->display(); ?>

