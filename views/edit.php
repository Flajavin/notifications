<?php /* @var $this mpf\components\notifications\controllers\Admin */ ?>
<?php /* @var $model mpf\components\notifications\models\Type */ ?>
<?= \app\components\htmltools\Page::get()->title("Notifications Management - Edit " . $model->name, [
    [
        'url' => [$this->getName(), 'index'],
        'label' => "View All"
    ],
    [
        'url' => [$this->getName(), 'create'],
        'label' => "Add a new Type"
    ]
]); ?>

<?= \mpf\widgets\form\Form::get([
    'name' => 'save',
    'model' => $model,
    'theme' => 'default-wide',
    'fields' => [
        'name',
        'description',
        [
            'name' => 'email',
            'type' => 'textarea'
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