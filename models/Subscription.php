<?php
/**
 * Created by MPF Framework.
 * Date: 2015-10-19
 * Time: 12:00
 */

namespace mpf\components\notifications\models;

use mpf\datasources\sql\DataProvider;
use mpf\datasources\sql\DbModel;
use mpf\datasources\sql\DbRelations;
use mpf\datasources\sql\ModelCondition;

/**
 * Class Subscription
 * @package app\components\notifications\models
 * @property int $id
 * @property int $type_id
 * @property string $code
 * @property \app\components\notifications\models\Type $type
 */
class Subscription extends DbModel {

    /**
     * @param string $for
     * @return string
     */
    public static function getHash($for){
        return md5($for);
    }

    /**
     * @param $for
     * @return Subscription
     */
    public static function findByName($for) {
        return self::findByCode(self::getHash($for));
    }

    /**
     * @param $code
     * @return Subscription
     */
    public static function findByCode($code){
        return self::findByAttributes(['code' => $code]);
    }

    /**
     * @param string $for
     * @param string $type
     * @return bool
     */
    public static function createByName($for, $type){
        $sub = new self();
        $sub->code = self::getHash($for);
        $sub->type = Type::findByName($type)->id;
        return $sub->save();
    }

    /**
     * Get database table name.
     * @return string
     */
    public static function getTableName() {
        return "notifications_subscriptions";
    }

    /**
     * Get list of labels for each column. This are used by widgets like form, or table
     * to better display labels for inputs or table headers for each column.
     * @return array
     */
    public static function getLabels() {
        return [
             'id' => 'Id',
             'type_id' => 'Type',
             'code' => 'Code'
        ];
    }

    /**
     * Return list of relations for current model
     * @return array
     */
    public static function getRelations(){
        return [
             'type' => [DbRelations::BELONGS_TO, '\app\components\notifications\models\Type', 'type_id']
        ];
    }

    /**
     * List of rules for current model
     * @return array
     */
    public static function getRules(){
        return [
            ["id, type_id, code", "safe", "on" => "search"]
        ];
    }

    /**
     * Gets DataProvider used later by widgets like \mpf\widgets\datatable\Table to manage models.
     * @return \mpf\datasources\sql\DataProvider
     */
    public function getDataProvider() {
        $condition = new ModelCondition(['model' => __CLASS__]);

        foreach (["id", "type_id", "code"] as $column) {
            if ($this->$column) {
                $condition->compareColumn($column, $this->$column, true);
            }
        }
        return new DataProvider([
            'modelCondition' => $condition
        ]);
    }

    /**
     * check if specified user is subscribed
     * @param int $userId
     * @return bool
     */
    public function subscribed($userId){
        $subscriber = Subscriber::findByAttributes([
            'subscription_id' => $this->id,
            'user_id' => $userId
        ]);
        return (bool)$subscriber;
    }

    /**
     * Subscribe to this subscription.
     * @param $userId
     * @return bool
     */
    public function subscribe($userId){
        if (!$userId){
            trigger_error("No user was specified for subscription!");
            return false;
        }
        if ($this->subscribed($userId)){
            return true;
        }
        $subscriber = new Subscriber();
        $subscriber->subscription_id = $this->id;
        $subscriber->user_id = $userId;
        return $subscriber->save();
    }

    /**
     * Unsubscribe specified user from current subscription
     * @param int $userId
     * @return int
     */
    public function unsubscribe($userId){
        return Subscriber::deleteAllByAttributes([
            'subscription_id' => $this->id,
            'user_id' => $userId
        ]);
    }
}
