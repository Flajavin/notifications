<?php /* @var $this mpf\components\notifications\controllers\Admin */ ?>
<?= \app\components\htmltools\Page::get()->title("Notifications Management", [
    [
        'url' => [$this->getName(), 'index'],
        'label' => "View All",
        'htmlOptions' => ['class' => 'selected']
    ],
    [
        'url' => [$this->getName(), 'create'],
        'label' => "Add a new Type"
    ]
]); ?>


<?= \mpf\widgets\datatable\Table::get([
    'dataProvider' => $model->getDataProvider(),
    'columns' => [
        'name',
        'sms',
        'web',
        [
            'class' => 'Actions',
            'buttons' => [
                'delete' => ['class' => 'Delete'],
                'edit' => ['class' => 'Edit']
            ],
            'headerHtmlOptions' => [
                'style' => 'width:60px;'
            ]
        ]
    ]
])->display(); ?>
