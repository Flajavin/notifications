<?php
/**
 * Created by MPF Framework.
 * Date: 2015-10-19
 * Time: 12:01
 */

namespace app\components\notifications\models;

use mpf\datasources\sql\DataProvider;
use mpf\datasources\sql\DbModel;
use mpf\datasources\sql\DbRelations;
use mpf\datasources\sql\ModelCondition;

/**
 * Class Subscriber
 * @package app\components\notifications\models
 * @property int $subscription_id
 * @property int $user_id
 * @property string $time
 */
class Subscriber extends DbModel {

    /**
     * Get database table name.
     * @return string
     */
    public static function getTableName() {
        return "notifications_subscribers";
    }

    /**
     * Get list of labels for each column. This are used by widgets like form, or table
     * to better display labels for inputs or table headers for each column.
     * @return array
     */
    public static function getLabels() {
        return [
             'subscription_id' => 'Subscription',
             'user_id' => 'User',
             'time' => 'Time'
        ];
    }

    /**
     * Return list of relations for current model
     * @return array
     */
    public static function getRelations(){
        return [
             
        ];
    }

    /**
     * List of rules for current model
     * @return array
     */
    public static function getRules(){
        return [
            ["subscription_id, user_id, time", "safe", "on" => "search"]
        ];
    }

    /**
     * Gets DataProvider used later by widgets like \mpf\widgets\datatable\Table to manage models.
     * @return \mpf\datasources\sql\DataProvider
     */
    public function getDataProvider() {
        $condition = new ModelCondition(['model' => __CLASS__]);

        foreach (["subscription_id", "user_id", "time"] as $column) {
            if ($this->$column) {
                $condition->compareColumn($column, $this->$column, true);
            }
        }
        return new DataProvider([
            'modelCondition' => $condition
        ]);
    }
}
