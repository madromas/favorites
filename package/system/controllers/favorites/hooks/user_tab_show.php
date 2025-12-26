<?php

class onFavoritesUserTabShow extends cmsAction {

    public function run($profile, $tab_name){

        // если выполняется поиск по тегу ...
        if ($this->request->has('search')) {

            return $this->runAction('search', array($profile, $tab_name));

        }

        // выводим список всех избранных записей ...
        return $this->runAction('view', array($profile, $tab_name));

    }

}