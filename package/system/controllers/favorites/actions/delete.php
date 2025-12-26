<?php

class actionFavoritesDelete extends cmsAction {

    public function run(){

        if (!$this->request->isAjax()) { cmsCore::error404(); }

        $id = $this->request->get('fav_id');

        if (!$id || !is_numeric($id)) { cmsCore::error404(); }

        $favorite = $this->model->getFavorite($id);

        if (!$favorite){
            $result = array('error' => true, 'message' => LANG_FAVORITES_DEL_ERROR);
            cmsTemplate::getInstance()->renderJSON($result);
        }

        $is_can_delete = $favorite['user_id'] == cmsUser::getInstance()->id || cmsUser::isAdmin();

        if (!$is_can_delete) {
            $result = array('error' => true, 'message' => LANG_FAVORITES_DEL_ERROR);
            cmsTemplate::getInstance()->renderJSON($result);
        }

        $result['error'] = false;

        if ($this->model->deleteFavorite($favorite)) {

            if ($favorite['target_controller']=='comments') {
                $replacer = html_spellcount_only(1, LANG_FAVORITES_COMMENTS_SPELLCOUNT);
            } else {
                $content_controller = cmsCore::getController('content', $this->request);
                $ctype = $content_controller->model->getContentTypeByName($favorite['target_subject']);
                $ctype ? $replacer = $ctype['labels']['create'] : '';

                $this->model->deleteFavTags('content', $favorite['target_subject'], $favorite['target_id'], $favorite['user_id']);
            }

            $result['message'] = LANG_FAVORITES_DEL_SUCCESS;
            $result['link_title'] = sprintf(LANG_FAVORITES_ADD_BUTTON, $replacer);

        } else {

            $result['error'] = true;
            $result['message'] = LANG_FAVORITES_DEL_ERROR;

        }

        cmsTemplate::getInstance()->renderJSON($result);

    }

}