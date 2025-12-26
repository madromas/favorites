<?php

class actionFavoritesSearch extends cmsAction {

    public function run($profile, $tab_name){

        $search_tag = $this->request->get('search');

        if (!$search_tag) { cmsCore::error404(); }

        $tag_id = $this->model->getFavTagId($search_tag, $profile['id']); // получаем id тега по профилю пользователя, а не $user->id

        $targets = $tag_id ? $this->model->getFavTagTargets($tag_id) : false;

        $ctype_names = $this->options['folders_names'];

        if (!$targets || !$tag_id || !$ctype_names) {
            return cmsTemplate::getInstance()->renderInternal($this, 'favorites_list', array(
                'is_results' => false,
                'is_search' => true,
                'profile' => $profile,
                'tag' => $search_tag,
            ));
        }

        $folder = $this->request->get('folder');

        $is_first_tab = !$folder;

        $content_controller = cmsCore::getController('content', $this->request);

        $ctypes = $content_controller->model->getContentTypes();

        if ($ctypes) {

            foreach($ctypes as $id => $type){
                if (!in_array($type['name'], $ctype_names)) {
                    unset($ctypes[$id]);
                    continue;
                }
            }

            foreach($ctypes as $id => $type){
                if (!$folder){
                    if (in_array($type['name'], $targets['content'])){
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

        }

        if (!$ctype) { cmsCore::error404(); }

        $content_controller->model->
                join('favorites_tags_bind', 't', "t.target_id = i.id AND t.target_subject = '{$folder}' AND t.target_controller = 'content'")->
                filterEqual('t.tag_id', $tag_id);

        $page_url = $is_first_tab ?
                        href_to('users', $profile['id'], $this->name) . "?search={$search_tag}" :
                        href_to('users', $profile['id'], $this->name) . "?search={$search_tag}&folder={$folder}";


        $items_list_html = $content_controller->renderItemsList($ctype, $page_url, true);

        return cmsTemplate::getInstance()->renderInternal($this, 'favorites_list', array(
            'is_results' => true,
            'is_search' => true,
            'tag' => $search_tag,
            'targets' => $targets,
            'folders' => $ctypes,
            'folder_current' => $ctype['name'],
            'profile' => $profile,
            'items_list_html' => $items_list_html
        ));

    }

}
