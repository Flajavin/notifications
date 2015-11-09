<?php

namespace mpf\components\notifications;

use mpf\components\notifications\models\Notification;
use mpf\components\notifications\models\Subscriber;
use mpf\components\notifications\models\Subscription;
use mpf\components\notifications\models\Type;
use mpf\base\Object;
use mpf\helpers\ArrayHelper;
use mpf\WebApp;

/**
 * Created by PhpStorm.
 * User: mirel
 * Date: 16.10.2015
 * Time: 10:56
 */
class Notifications extends Object {

    /**
     * Add a new notification. For more details about the notifications please read the documentation.
     * @param string $type Type name as defined in notifications_types table.
     * @param string[]|string $url An array that is used to generate the url. A string is also accepted with the full url, if external, but an array is recommended so that it won't have problems if URL structure is later changed
     * @param string[] $vars List of variables used to generate the log message. This variables will be used to generate the text as is defined in the "notification_types" table.
     * @param int $user Id of the user for whom the notification is generated
     * @param int|string $time If notification is not for now then specify the time as string or timestamp
     * @return bool
     */
    public static function add($type, $url, $vars, $user, $time = null) {
        $notification = new Notification();
        $notification->type_id = Type::findByName($type);
        $notification->user_id = $user;
        if (!is_null($time)) {
            $notification->time = is_numeric($time) ? date('Y-m-d H:i:s', $time) : $time;
        }
        $notification->url_json = json_encode($url);
        $notification->vars_json = json_encode($vars);
        return $notification->save();
    }

    /**
     * @param string $subscription
     * @param string[] $url
     * @param string[] $vars
     * @param int|string $time
     * @param int|int[] $excludeUser Will exclude loggedin user or any other specified users;
     * @return bool|void
     */
    public static function addForSubscription($subscription, $url, $vars, $time = null, $excludeUser = null) {
        $subscription = Subscription::findByName($subscription);
        $excludeUser = is_null($excludeUser) ? WebApp::get()->user()->id : $excludeUser;
        if (!$subscription) {
            WebApp::get()->error("Subscription not found!");
            return false; // subscription  not found
        }
        $subscribers = Subscriber::findAllByAttributes(['subscription_id' => $subscription->id]);
        if (!$subscribers) {
            return true; // no subscribers found
        }
        foreach ($subscribers as $subscriber) {
            if ($excludeUser && $subscriber->user_id == $excludeUser) {
                continue;
            }
            Notification::insert([
                'type_id' => $subscription->type_id,
                'user_id' => $subscriber->user_id,
                'time' => is_null($time) ? date('Y-m-d H:i:s') : (is_numeric($time) ? date('Y-m-d H:i:s', $time) : $time),
                'url_json' => json_encode($url),
                'vars_json' => json_encode($vars),
                'subscription_id' => $subscription->id
            ]);
        }
        return count($subscribers);
    }

    /**
     * Get number of new notifications for active or specified user. By default will get all types, but certain categories can be specified.
     * @param string $type
     * @param int $user
     * @return int
     */
    public static function getNewNumber($type = null, $user = null) {
        $user = is_null($user) ? WebApp::get()->user()->id : $user;
        $a = ['user_id' => $user, 'read' => 0];
        if ($type) {
            $a['type_id'] = ArrayHelper::get()->transform(Type::findAllByAttributes(['name' => $type]), 'id');
        }
        return Notification::countByAttributes($a);
    }

    /**
     * @param null $type
     * @param int $limit
     * @param int $offset
     * @param null $user
     * @return Notification[]
     */
    public static function getLatestNew($type = null, $limit = 10, $offset = 0, $user = null) {
        $user = is_null($user) ? WebApp::get()->user()->id : $user;
        $a = ['user_id' => $user, 'read' => 0];
        if ($type) {
            $a['type_id'] = ArrayHelper::get()->transform(Type::findAllByAttributes(['name' => $type]), 'id');
        }
        return Notification::findAllByAttributes($a, ['limit' => $limit, 'offset' => $offset, 'order' => 'id DESC', 'with' => ['type']]);
    }

    /**
     * @param string $for
     * @param string $type
     * @return bool
     */
    public static function createSubscription($for, $type) {
        if (Subscription::findByName($for)) {
            return false; /// already created;
        }
        return Subscription::createByName($for, $type);
    }

    /**
     * Subscribe specified user to specified subscription
     * @param string $to
     * @param int $userId If no user is specified then the active user will be selected
     * @return bool
     */
    public static function subscribe($to, $userId = null) {
        return Subscription::findByName($to)->subscribe($userId ? $userId : WebApp::get()->user()->id);
    }

    /**
     * Unsubscribe for specified subscription
     * @param $from
     * @param null $userId
     * @return int
     */
    public static function unsubscribe($from, $userId = null) {
        return Subscription::findByName($from)->unsubscribe($userId ? $userId : WebApp::get()->user()->id);
    }
}