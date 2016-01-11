<?php
/**
 * Created by PhpStorm.
 * User: mirel
 * Date: 11.01.2016
 * Time: 16:51
 */

namespace mpf\components\notifications\commands;


use app\components\Command;
use mpf\components\notifications\models\Notification;

class Notifier extends Command{

    public function actionIndex(){
        $unread = Notification::findAllByAttributes(['read' => 0, 'sent' => 0]);

    }

}