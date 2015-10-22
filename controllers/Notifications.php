<?php
/**
 * Created by PhpStorm.
 * User: mirel
 * Date: 16.10.2015
 * Time: 12:14
 */

namespace mpf\components\notifications\controllers;


use app\components\Controller;

class Notifications extends Controller {

    public function actionIndex() {

    }

    /**
     * For ajax calls return number of new notifications for select categories or all categories for active user.
     * @return int
     */
    public function actionNew() {
        return \app\php\components\notifications\Notifications::getNewNumber(isset($_POST['type']) ? $_POST['type'] : null);
    }

    public function actionNewList() {
        $notifications = \app\php\components\notifications\Notifications::getLatestNew(isset($_POST['type']) ? $_POST['type'] : null, isset($_POST['limit']) ? $_POST['limit'] : 10, isset($_POST['offset']) ? $_POST['offset'] : 0);
        $list = [];
        foreach ($notifications as $notification){
            $list[] = [
                'id' => $notification->id,
                'url' => $notification->getURL(),
                'message' => $notification->getMessage(),
                'time' => $notification->time,
                'category' => $notification->type->name
            ];
        }
        return $list;
    }
}