# Обновляем версию компонента в списке контроллеров InstantCMS
DELETE FROM `{#}controllers` WHERE `name` = 'favorites' AND `author` = 'Val';
INSERT INTO `{#}controllers` (`title`, `name`, `is_enabled`, `options`, `author`, `url`, `version`, `is_backend`) VALUES
('Избранное', 'favorites', 1, '', 'Val', 'http://www.instantcms.ru/users/Val', '2.0', 1);

# Удаляем столбец ctype_id из таблицы favorites
# ALTER TABLE  `{#}favorites` DROP  `ctype_id` ;

# Изменяем названия столбцов таблицы favorites
# ALTER TABLE  `{#}favorites` CHANGE  `controller`  `target_controller` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Системное имя компонента';
# ALTER TABLE  `{#}favorites` CHANGE  `ctype_name`  `target_subject` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Системное имя типа контента';
# ALTER TABLE  `{#}favorites` CHANGE  `item_id`  `target_id` INT( 11 ) NULL DEFAULT NULL COMMENT  'ID записи';

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

# Добавляем запись о виджете тегов
INSERT INTO `{#}widgets` (`controller`, `name`, `title`, `author`, `url`, `version`) VALUES
('favorites', 'tags', 'Теги избранного', 'Val', 'http://www.instantcms.ru/users/Val', '1.0');