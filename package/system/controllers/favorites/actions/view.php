<?php

class actionFavoritesView extends cmsAction {

    public function run($profile, $tab_name){

        $folders_names = $this->options['folders_names'];
        $is_comment_folder = (bool)$this->options['is_comment_folder'];

        // получаем "типы" избранного
        $targets = $this->model->getFavoritesTargets($profile['id']);

        if ( !$targets || !($folders_names || $is_comment_folder) ) {
            return cmsTemplate::getInstance()->renderInternal($this, 'favorites_list', array('is_results' => false));
        }

        // текущий тип избранного
        $folder = $this->request->get('folder');

        $is_first_tab = !$folder;

        $content_controller = cmsCore::getController('content', $this->request);
        $ctypes = $content_controller->model->getContentTypes();

        // если разрешено добавлять в избранное комментарии
        if ($is_comment_folder) {

            $comments_controller = cmsCore::getController('comments', $this->request);
            $ctypes['comments'] = array(
                    'name' => 'comments',
                    'title' => $comments_controller -> title,
                    //'counter' => $this->model->getFolderItemsCount($profile['id'], 'comments')
                );

            if (!is_array($folders_names)) { $folders_names = array(); }
            $folders_names[] = 'comments';

        }

        if ($ctypes) {

            // оставляем выбранные в настройках типы контента
            foreach ($ctypes as $id => $type) {
                if (!in_array($type['name'], $folders_names)) {
                    unset( $ctypes[$id] );
                    continue;
                }
                //else { $ctypes[$id]['counter'] = $this->model->getFolderItemsCount($profile['id'], 'content', $type['name']); }
            }

            if (!is_array($targets['content'])) { $targets['content'] = array($targets['content']); }

            // определяем текущую папку и ее тип контента
            foreach ($ctypes as $id => $type) {

                if (!$folder){
                    if ( in_array($type['name'], $targets['content']) ){
                        $folder = $type['name'];
                        $ctype = $type;
                        break;
                    }
                } else {
                    if ($folder == $type['name']){
                        $ctype = $type;
                        break;
                    }
                }

            }
            if (!$folder && $targets['comments']) { $folder = 'comments'; }

        }

        if (!$ctypes ) { return cmsTemplate::getInstance()->renderInternal($this, 'favorites_list', array('is_results' => false)); }

        $page_url =  $is_first_tab ?
                        href_to('users', $profile['id'], $this->name) :
                        href_to('users', $profile['id'], $this->name) . "?folder={$folder}";

        $this->model->resetFilters();

        // формируем список записей для текущей папки
        if ($folder != 'comments') {  // если тип контента

            $ctype = $content_controller->model->getContentTypeByName($folder);
            if (!$ctype) { return cmsTemplate::getInstance()->renderInternal($this, 'favorites_list', array('is_results' => false)); }

            $content_controller->model->
                    join('favorites', 'fav', "fav.target_id = i.id AND fav.target_subject = '{$folder}' AND fav.target_controller = 'content'")->
                    filterEqual('fav.user_id', $profile['id'])->
                    orderBy('fav.date_pub', 'desc');

            // Получаем HTML списка записей
            $items_list_html = $content_controller->renderItemsList($ctype, $page_url, true);

        } else {  // если комментарии

            $comments_controller->model->
                    join('favorites', 'fav', "fav.target_id = i.id AND fav.target_controller = 'comments'")->
                    filterEqual('fav.user_id', $profile['id'])->
                    orderBy('fav.date_pub', 'desc');  // перекрывается в renderCommentsList

            // Получаем HTML списка комментариев
            $items_list_html = $comments_controller->renderCommentsList($page_url);

        }

        $this->model->resetFilters();

        return cmsTemplate::getInstance()->renderInternal($this, 'favorites_list', array(
            'is_results' => true,
            'targets' => $targets,
            'folders' => $ctypes,
            'folder_current' => $folder,
            'profile' => $profile,
            'items_list_html' => $items_list_html
        ));

    }

}