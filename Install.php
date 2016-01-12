<?php
/**
 * Created by PhpStorm.
 * User: mirel
 * Date: 09.11.2015
 * Time: 11:12
 */

namespace mpf\components\notifications;


use mpf\base\App;
use mpf\base\LogAwareObject;

/**
 * Class Install
 * Creates the tables required by the "notifications" models;
 * @package mpf\components\notifications
 */
class Install extends LogAwareObject {

    /**
     * @var Install
     */
    protected static $instance;

    /**
     * @param array $config
     * @return Install
     */
    public static function get($config = []) {
        if (!self::$instance) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function run() {
        $this->debug("Installing notifications");
        App::get()->sql()->execQuery("DROP TABLE IF EXISTS `notifications`");
        App::get()->sql()->execQuery("CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` tinyint(3) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned NOT NULL,
  `read` tinyint(1) unsigned NOT NULL,
  `sent` tinyint(1) unsigned NOT NULL,
  `read_method` tinyint(3) unsigned DEFAULT NULL,
  `read_time` datetime DEFAULT NULL,
  `url_json` varchar(200) NOT NULL,
  `vars_json` varchar(200) NOT NULL,
  `subscription_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $this->debug("Installing subscribers");
        App::get()->sql()->execQuery("DROP TABLE IF EXISTS `notifications_subscribers`");
        App::get()->sql()->execQuery("CREATE TABLE `notifications_subscribers` (
  `subscription_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`subscription_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $this->debug("Installing subscriptions");
        App::get()->sql()->execQuery("DROP TABLE IF EXISTS `notifications_subscriptions`");
        App::get()->sql()->execQuery("CREATE TABLE `notifications_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` smallint(5) unsigned NOT NULL,
  `code` char(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $this->debug("Installing categories");
        App::get()->sql()->execQuery("DROP TABLE IF EXISTS `notification_user2types`");
        App::get()->sql()->execQuery("CREATE TABLE `notifications_user2types` (
  `user_id` int(10) unsigned NOT NULL,
  `type_id` smallint(5) unsigned NOT NULL,
  `email` tinyint(3) unsigned NOT NULL,
  `sms` tinyint(3) unsigned NOT NULL,
  `mobile` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        App::get()->sql()->execQuery("DROP TABLE IF EXISTS `notification_types`");
        App::get()->sql()->execQuery("CREATE TABLE `notification_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` varchar(256) NOT NULL,
  `email` text NOT NULL,
  `sms` varchar(256) NOT NULL,
  `web` varchar(256) NOT NULL,
  `mobile` varchar(256) NOT NULL,
  `group_email` text NOT NULL,
  `group_url` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        $this->debug("All done! :)");
        return true;
    }

}