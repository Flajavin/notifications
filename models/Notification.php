<?php

namespace mpf\components\notifications\models;

use mpf\base\App;
use mpf\datasources\sql\DataProvider;
use mpf\datasources\sql\DbModel;
use mpf\datasources\sql\DbRelations;
use mpf\datasources\sql\ModelCondition;
use mpf\helpers\MailHelper;
use mpf\WebApp;

/**
 * Class Notification
 * @package mpf\notifications
 * @property int $id
 * @property int $type_id
 * @property string $time
 * @property int $user_id
 * @property int $read
 * @property int $sent
 * @property int $read_method
 * @property string $read_time
 * @property string $url_json
 * @property string $vars_json
 * @property int $subscription_id
 * @property \app\models\User $user
 * @property \mpf\components\notifications\models\Type $type
 */
class Notification extends DbModel
{

    const METHOD_WEB = 1;
    const METHOD_EMAIL = 2;

    /**
     * Used to generate email links
     * @var string
     */
    public $domain;

    /**
     * Get database table name.
     * @return string
     */
    public static function getTableName()
    {
        return "notifications";
    }

    /**
     * Get list of labels for each column. This are used by widgets like form, or table
     * to better display labels for inputs or table headers for each column.
     * @return array
     */
    public static function getLabels()
    {
        return [
            'id' => 'Id',
            'type_id' => 'Type',
            'time' => 'Time',
            'user_id' => 'User',
            'read' => 'Read',
            'sent' => 'Sent',
            'read_method' => 'Read Method',
            'read_time' => 'Read Time',
            'url_json' => 'Url Json',
            'vars_json' => 'Vars Json'
        ];
    }

    /**
     * Return list of relations for current model
     * @return array
     */
    public static function getRelations()
    {
        return [
            'user' => [DbRelations::BELONGS_TO, '\app\models\User', 'user_id'],
            'type' => [DbRelations::BELONGS_TO, '\mpf\components\notifications\models\Type', 'type_id']
        ];
    }

    /**
     * List of rules for current model
     * @return array
     */
    public static function getRules()
    {
        return [
            ["id, type_id, time, user_id, read, sent, read_method, read_time, url_json, vars_json", "safe", "on" => "search"]
        ];
    }

    /**
     * Gets DataProvider used later by widgets like \mpf\widgets\datatable\Table to manage models.
     * @return \mpf\datasources\sql\DataProvider
     */
    public function getDataProvider()
    {
        $condition = new ModelCondition(['model' => __CLASS__]);

        foreach (["id", "type_id", "time", "user_id", "read", "sent", "read_method", "read_time", "url_json", "vars_json"] as $column) {
            if ($this->$column) {
                $condition->compareColumn($column, $this->$column, true);
            }
        }
        return new DataProvider([
            'modelCondition' => $condition
        ]);
    }

    public function markReadFromEmail(){
        $this->read = 1;
        $this->read_method = self::METHOD_EMAIL;
        $this->read_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function markReadFromWeb(){
        $this->read = 1;
        $this->read_method = self::METHOD_WEB;
        $this->read_time = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * @return string
     */
    public function getURL()
    {
        $url = json_decode($this->url_json, true);
        if (is_string($url)) {
            return $url;
        }
        if (is_a(App::get(), WebApp::className())) {
            return WebApp::get()->request()->createURL($url[0], isset($url[1]) ? $url[1] : null, isset($url[2]) ? $url[2] : [], isset($url[3]) ? $url[3] : null);
        } else {
            return $this->domain . (isset($url[3]) ? '/' . $url[3] : '')
            . '/' . $url[0] . '/' . (isset($url[1]) ? $url[1] : 'index') . '.html'
            . (isset($url[2]) ? '?' . http_build_query($url[2]) : '');
        }

    }

    /**
     * @param string $for Values: web, sms, email, mobile
     * @return string
     */
    public function getMessage($for = 'web')
    {
        $vars = json_decode($this->vars_json, true);
        $vars['_url'] = $this->getURL();
        $vars['_time'] = $this->time;
        $vars['_myUserName'] = $this->user->name;
        $vars['_notificationId'] = $this->id;
        return $this->type->getProcessedText($for, $vars);
    }

    public function sendMail()
    {
        if (!$this->type->wantsEmail($this->user_id)) { // no need to mail this;
            return true;
        }
        $this->debug("Send email to #" . $this->user_id);
        $this->sent = 1;
        $this->save();
        return MailHelper::get()->send($this->user->email, $this->type->title, $this->getMessage('email'));
    }
}