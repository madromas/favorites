<?php

class actionFavoritesGetTags extends cmsAction {

    public function run(){

        if (!$this->request->isAjax()) { cmsCore::error404(); }

        $id      = $this->request->get('fav_id');
        $is_user = $this->request->get('is_user');

        if (!$id || !is_numeric($id)) { cmsCore::error404(); }

        $favorite = $this->model->getFavorite($id);

        if (!$favorite){
            $result = array('error' => true, 'message' => LANG_FAVORITES_DEL_ERROR);
            cmsTemplate::getInstance()->renderJSON($result);
        }

        $user = cmsUser::getInstance();

        $is_can = $favorite['user_id'] == $user->id || cmsUser::isAdmin();

        if (!$is_can) {
            $result = array('error' => true, 'message' => LANG_FAVORITES_DEL_ERROR);
            cmsTemplate::getInstance()->renderJSON($result);
        }

        $result['error'] = false;

        $result['tags'] = array_values(
            $this->model->
                getFavTagsForTarget($favorite['target_controller'],
                                    $favorite['target_subject'],
                                    $favorite['target_id'],
                                    $favorite['user_id'],
                                    $is_user ? true : null)
            );

        cmsTemplate::getInstance()->renderJSON($result);

    }

}