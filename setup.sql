# Добавляем компонент в список контроллеров InstantCMS
DELETE FROM `{#}controllers` WHERE `name` = 'favorites' AND `author` = 'Val';
INSERT INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`) VALUES
('Избранное', 'favorites', 1, '', 'Val', 'http://www.instantcms.ru/users/Val', '2.0', 1);

# Создаем таблицу favorites
DROP TABLE IF EXISTS `{#}favorites`;
CREATE TABLE IF NOT EXISTS `{#}favorites` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `target_controller` varchar(32) NOT NULL COMMENT 'Системное имя компонента',
  `target_subject` varchar(32) NOT NULL COMMENT 'Системное имя типа контента',
  `target_id` int(11) unsigned DEFAULT NULL COMMENT 'ID записи',
  `user_id` int(11) unsigned NOT NULL COMMENT 'ID пользователя',
  `date_pub` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата добавления избранного',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# Создаем таблицу favorites_tags
DROP TABLE IF EXISTS `{#}favorites_tags`;
CREATE TABLE IF NOT EXISTS `{#}favorites_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(128) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `frequency` int(11) unsigned NOT NULL DEFAULT '1',
  `hash` varchar(100) NOT NULL COMMENT 'Хэш тега и ID пользователя',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# Создаем таблицу favorites_tags_bind
DROP TABLE IF EXISTS `{#}favorites_tags_bind`;
CREATE TABLE IF NOT EXISTS `{#}favorites_tags_bind` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) unsigned DEFAULT NULL,
  `target_controller` varchar(32) DEFAULT NULL,
  `target_subject` varchar(32) DEFAULT NULL,
  `target_id` int(11) unsigned DEFAULT NULL,
  `is_user_tag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

# Добавляем правиля для групп пользователей
INSERT INTO `{#}perms_rules` (`controller`, `name`, `type`, `options`) VALUES
('favorites', 'add', 'flag', NULL);

# Добавляем вкладку избранного в профиль пользователя
INSERT INTO `{#}users_tabs` (`title`, `controller`, `name`, `is_active`, `ordering`) VALUES 
('Избранное', 'favorites', 'favorites', '1', '21');

# Добавляем запись о виджете тегов
INSERT INTO `{#}widgets` (`controller`, `name`, `title`, `author`, `url`, `version`) VALUES
('favorites', 'tags', 'Теги избранного', 'Val', 'http://www.instantcms.ru/users/Val', '1.0');