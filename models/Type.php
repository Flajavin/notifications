<?php
/**
 * Created by PhpStorm.
 * User: mirel
 * Date: 15.10.2015
 * Time: 15:38
 */

namespace mpf\components\notifications\models;


use mpf\base\App;
use mpf\datasources\sql\DataProvider;
use mpf\datasources\sql\DbModel;
use mpf\datasources\sql\ModelCondition;

/**
 * Class NotificationType
 * @package app\models
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property string $email
 * @property string $sms
 * @property string $web
 * @property string $mobile
 * @property string $group_email
 * @property string $group_url
 */
class Type extends DbModel {

    /**
     * @var self[string]
     */
    protected static $types;

    /**
     * Get database table name.
     * @return string
     */
    public static function getTableName() {
        return "notification_types";
    }

    /**
     * Get a Type model with the specified name.
     * @param string $name
     * @param bool $noError
     * @return self|null;
     */
    public static function findByName($name, $noError = false) {
        if (is_null(self::$types)) {
            $types = self::findAll();
            self::$types = [];
            foreach ($types as $type) {
                self::$types[$type->name] = $type;
            }
        }
        if (!isset(self::$types[$name])) {
            if ($noError){
                return null;
            }
            trigger_error("A notification type for $name was not found!");
        }
        if (!is_string($name)){
            trigger_error("Invalid value: " . print_r($name, true));
        }
        return self::$types[$name];
    }

    /**
     * Get list of labels for each column. This are used by widgets like form, or table
     * to better display labels for inputs or table headers for each column.
     * @return array
     */
    public static function getLabels() {
        return [
            'id' => 'Id',
            'name' => 'Name',
            'title' => 'Title',
            'description' => 'Description',
            'email' => 'Email',
            'sms' => 'Sms',
            'web' => 'Web',
            'mobile' => 'Mobile',
            'group_email' => 'Group Email',
            'group_url' => 'Group Url'
        ];
    }

    /**
     * Return list of relations for current model
     * @return array
     */
    public static function getRelations() {
        return [

        ];
    }

    /**
     * List of rules for current model
     * @return array
     */
    public static function getRules() {
        return [
            ["id, name, description, email, sms, web, mobile, group_email, group_url", "safe", "on" => "search"]
        ];
    }

    /**
     * Gets DataProvider used later by widgets like \mpf\widgets\datatable\Table to manage models.
     * @return \mpf\datasources\sql\DataProvider
     */
    public function getDataProvider() {
        $condition = new ModelCondition(['model' => __CLASS__]);

        foreach (["id", "name", "description", "email", "sms", "web", "mobile", "group_email", "group_url"] as $column) {
            if ($this->$column) {
                $condition->compareColumn($column, $this->$column, true);
            }
        }
        return new DataProvider([
            'modelCondition' => $condition
        ]);
    }


    /**
     * Will process text using the structure specified in the text.
     * How to define a variable name:
     * "{$var1|tableName:key>value}" => it will search in the specified table the column with the selected value for the key
     * "{$var2|ModelName:value}" => for models it starts with upper case or it contains \ and it will search by PK. The Value in this case can be a method but must end with ()
     * "{$var3|\others\models\ModelName:value()}" => another model from a different component; it will call value() method for the result
     * "{$var4|\namespace\Class::method()}" => call a method with this parameter;
     * "{$var5}" => simple variable, it will be written as found
     *
     * Example:
     * Vars: [W
     *    'id' => 23,
     *    'city' => 'Bucharest'
     * ]
     *
     * Text: "<a href="{$id|User:getURL()}">{$id|users:id>name}</a> registered from {$city}!"
     *
     * @param $for
     * @param $vars
     * @return string
     */
    public function getProcessedText($for, $vars) {
        $text = $this->$for;
        preg_match_all('/{\$[a-z0-9|:>-_^{}\(\)]+}/i', $text, $matches);
        foreach ($matches[0] as $match) {
            $match = substr($match, 2, strlen($match) - 3);
            $parts = explode('|', $match);
            if (count($parts) > 1) {
                $var  = $parts[0];
                $details = explode('::', $parts[1]);
                if (count($details) == 2){
                    $dbValue = call_user_func(str_replace('()', '', $parts[1]), $vars[$var]);
                } else {
                    $details = explode(':', $parts[1]);
                    $tName = $details[0];
                    $details = explode('>', $details);
                    $key = isset($details[1]) ? $details[0] : null;
                    $value = isset($details[1]) ? $details[1] : $details[0];
                    if (ucfirst($tName) != $tName || false !== strpos($tName, '\\')) { // model;
                        $model = false !== strpos($tName, '\\') ? $tName : '\\app\\models\\' . $tName;
                        /* @var $model \mpf\datasources\sql\DbModel */
                        if ($key) {
                            $model = $model::findByAttributes([$key => $vars[$var]]);
                        } else {
                            $model = $model::findByPk($vars[$var]);
                        }
                        $dbValue = ('()' != substr($value, -2)) ? $model->$value : $model->$value();
                    } else { // table;
                        $dbValue = App::get()->sql()->table($tName)->fields('`' . $value . '`')->where([$key => $vars[$var]])->first();
                        $dbValue = $dbValue[$value];
                    }
                }
            } else {
                $dbValue = $vars[$match];
            }
            $text = str_replace("{\$$match}", $dbValue, $text);
        }
        return nl2br($text);
    }

    public function wantsEmail($userId){
        if (!$this->email){
            return false;
        }

        $settings = $this->getDb()->table("notifications_user2types")->compare(['user_id' => $userId, 'type_id' => $this->id])->first();
        if (!$settings){
            return true;
        }
        return (bool)$settings['email'];
    }

    public function wantsSMS($userId){
        if (!$this->sms){
            return false;
        }

        $settings = $this->getDb()->table("notifications_user2types")->compare(['user_id' => $userId, 'type_id' => $this->id])->first();
        if (!$settings){
            return true;
        }
        return (bool)$settings['sms'];
    }

    public function wantsMobile($userId){
        if (!$this->mobile){
            return false;
        }

        $settings = $this->getDb()->table("notifications_user2types")->compare(['user_id' => $userId, 'type_id' => $this->id])->first();
        if (!$settings){
            return true;
        }
        return (bool)$settings['mobile'];
    }
}