<?php

function install_package() {

    $core   = cmsCore::getInstance();

    $config = cmsConfig::getInstance();
    $admin  = cmsCore::getController('admin');

    $path = $config->upload_path . $admin->installer_upload_path;

    $version = '2.0';

    $result = true;  // результат выполнения функции

    $controller = $core->db->getRow('controllers', 'name="favorites"');

    // компонент еще не установлен
    if (!$controller) {

        $result = importPackageDump( $path . '/' . 'setup.sql' );

    }

    // если обновляемся
    if ($controller) {

        // в зависимости от установленной версии компонента
        switch (strnatcasecmp($controller['version'], $version)) {

            // установленная версия ниже текущей
            case -1:

                $fields=$core->db->getTableFields('favorites');

                if (is_array($fields)){

                    // удаляем столбец ctype_id из таблицы favorites
                    if (in_array('ctype_id', $fields)){
                        $sql = "ALTER TABLE `{#}favorites` DROP `ctype_id`";
                        $core->db->query($sql);

                    }

                    // изменяем названия столбцов таблицы favorites
                    if (in_array('controller', $fields)){

                        $sql = "ALTER TABLE  `{#}favorites` CHANGE  `controller`  `target_controller` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Системное имя компонента'";
                        $core->db->query($sql);

                    }

                    if (in_array('ctype_name', $fields)){

                        $sql = "ALTER TABLE  `{#}favorites` CHANGE  `ctype_name`  `target_subject` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  'Системное имя типа контента'";
                        $core->db->query($sql);

                    }

                    if (in_array('item_id', $fields)){

                        $sql = "ALTER TABLE  `{#}favorites` CHANGE  `item_id`  `target_id` INT( 11 ) NULL DEFAULT NULL COMMENT  'ID записи'";
                        $core->db->query($sql);

                    }

                }

                $result = importPackageDump( $path . '/' . 'update.sql' );
                break;

            // установлена текущая версия
            case 0:
                break;

            // установлена более новая версия
            case 1:
                $result = false;
                break;

        }

    }

    return $result;

}

function importPackageDump($file){

    if (!file_exists($file)) { return false; }

    $db = cmsDatabase::getInstance();

    return $db->importDump($file);

}