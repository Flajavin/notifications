<?php
/**
 * Created by PhpStorm.
 * User: mirel
 * Date: 16.10.2015
 * Time: 12:14
 */

namespace mpf\components\notifications\controllers;


use app\components\Controller;
use app\components\htmltools\Messages;
use mpf\components\notifications\models\Type;

class Admin extends Controller {

    public function init($config = array()) {
        $this->viewsFolder = dirname(__DIR__) . "/views";
        return parent::init($config);
    }

    public function actionIndex() {
        $model = Type::model();
        $model->setAttributes(isset($_GET['Type'])?$_GET['Type']:[]);
        $this->assign("model", $model);
    }

    public function actionCreate() {
        $model = new Type();
        if (isset($_POST['Type'])){
            if ($model->setAttributes($_POST['Type'])->save()){
                Messages::get()->success("Type saved!");
                $this->goToAction('index');
            }
        }
        $this->assign('model', $model);
    }

    public function actionEdit($id) {
        $model = Type::findByPk($id);
        if (isset($_POST['Type'])){
            if ($model->setAttributes($_POST['Type'])->save()){
                Messages::get()->success("Type saved!");
                $this->goToAction('index');
            }
        }
        $this->assign('model', $model);
    }

    public function actionDelete() {
        $models = Type::findAllByPk($_POST['Type']);
        $number = 0;
        foreach ($models as $model){
            $model->delete();
            $number++;
        }
        if (1 === $number){
            Messages::get()->success("Type deleted!");
        } else {
            Messages::get()->success("$number types deleted!");
        }
    }

}