Fail2Ban add db
========
Fail2BanでBANされたIPアドレスより、国とサブネットを割り出してデータベースに記録します。

データベースは、[ここ](https://github.com/akira345/iplist)の奴を使います。

テーブルはこんな感じで適当に（MySQL）
```
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `fail2ban`;
CREATE TABLE IF NOT EXISTS `fail2ban` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hostname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ip` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `netblock` varchar(19) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_cd` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `netblock` (`netblock`),
  KEY `country_cd` (`country_cd`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
```
Fail2BanのActionに設定します。
```
vi /etc/fail2ban/action.d/add-mysql.conf
```
内容はこんな感じです。
```
[INCLUDES]
before =

[Definition]
actionstart =
actionstop =
actioncheck =
actionban = /usr/bin/php /root/fail2ban.php <name> <ip>
actionunban =

[Init]
```

Fail2BanのActionに
```
action = %(action_mw)s
          add-mysql[name="%(__name__)s", protocol="%(protocol)s",port="%(port)s"]
```
のような感じで追記します。

参考：https://www.saas-secure.com/online-services/fail2ban-ip-sharing.html
