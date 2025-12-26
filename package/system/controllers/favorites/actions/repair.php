<?php

class actionFavoritesRepair extends cmsAction{

    public function run(){

        if (!cmsUser::isAdmin()) { cmsCore::error404(); }

        $content_model = cmsCore::getModel('content');

        //----- Удаляем избранные записи контента который был удален ранее -----

        // получаем разрешенные типы контента
        $folders_names = $this->options['folders_names'];

        if ($folders_names && !is_array($folders_names)){ $folders_names = array($folders_names); }

        if ($folders_names){

            // для каждого типа контента...
            foreach ($folders_names as $ctype_name) {

                //...получаем существующие записи
                $items = $content_model->getContentItems($ctype_name);

                if (!$items || !is_array($items)) { continue; }

                // удаляем лишние избранные записи
                $this->model->
                        filterEqual('target_controller', 'content')->
                        filterEqual('target_subject', $ctype_name)->
                        filterNotIn('target_id', array_keys($items))->
                        deleteFiltered('favorites');

            }

        }

        // смотрим зачистку избранных для комментариев
        $is_comment = (bool)$this->options['is_comment_folder'];

        if ($is_comment){

            // получаем все комментарии в системе
            $comments = cmsCore::getModel('comments')->
                            filterIsNull('is_deleted')->
                            getComments();

            // удаляем лишние избранные записи
            $this->model->
                    filterEqual('target_controller', 'comments')->
                    filterNotIn('target_id', array_keys($comments))->
                    deleteFiltered('favorites');

        }

        cmsCache::getInstance()->clean("favorites.favorites");


        //----- Перезаписываем теги для всех избранных записей -----------------

        // получаем массив типов контента для которых включены теги
        $ctypes = $content_model->filterNotNull('is_tags')->getContentTypes();

        if (!$ctypes) { return; }

        // формируем массив имён типов контента с включёнными тегами
        $ctype_names = array_map(function($ctype) {
            return $ctype['name'];
        }, $ctypes);

        // получаем избранное только для контента с тегами
        $favorites = $this->model->
                        filterEqual('target_controller', 'content')->
                        filterIn('target_subject', $ctype_names)->
                        getFavorites();


        if (!$favorites || !is_array($favorites)) { return; }

        // очищаем старые записи
        $tags_count = $this->model->filterEqual('is_user_tag', 0)->getCount('favorites_tags_bind');

        if ($tags_count) {

            $this->model->deleteFavTags(false, false, false, false, 0);

        }

        $tags_model = cmsCore::getModel('tags');


        $this->model->resetFilters();

        foreach ($favorites as $favorite) {

            $fav_tags = $tags_model->getTagsForTarget('content', $favorite['target_subject'], $favorite['target_id']);

            if ($fav_tags) {
                $this->model->addFavTags($fav_tags, 'content', $favorite['target_subject'], $favorite['target_id'], $favorite['user_id'], false);
            }

        }

        cmsUser::addSessionMessage(LANG_FAVORITES_INFO_REPAIR_OK, 'success');
        $this->redirectBack();

    }

}