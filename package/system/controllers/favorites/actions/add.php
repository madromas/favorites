<?php

class actionFavoritesAdd extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) { cmsCore::error404(); }

        $target_controller  = $this->request->get('tc'); // название контроллера
        $target_subject     = $this->request->get('ts'); // системное имя типа контента
        $target_id          = $this->request->get('ti'); // ID записи

        // Проверяем валидность полученных данных
        $is_valid = ($this->validate_sysname($target_controller)===true) &&
                    ($this->validate_sysname($target_subject)===true) &&
                    is_numeric($target_id);

        $is_add = (cmsUser::isAdmin() || cmsUser::isAllowed('favorites', 'add')) &&
                  ($this->isUsedFavorite($target_subject) || $this->options['is_comment_folder']);

        if (!$is_add && !$is_valid) {
            $result = array('error' => true, 'message' => LANG_FAVORITES_ADD_ERROR);
            cmsTemplate::getInstance()->renderJSON($result);
        }

        $favorite['target_controller'] = htmlspecialchars($target_controller);
        $favorite['target_subject'] = false;

        if ($favorite['target_controller'] == 'content') { // добавляем тип контента ...

            if (!$target_subject) {
                $result = array('error' => true, 'message' => LANG_FAVORITES_ADD_ERROR);
                cmsTemplate::getInstance()->renderJSON($result);
            }

            $content_controller = cmsCore::getController('content', $this->request);
            $ctype = $content_controller->model->getContentTypeByName($target_subject);

            $favorite['target_subject'] = $ctype['name'];

            $favorite['is_tags'] = $ctype['is_tags'];

        }

        $favorite['target_id'] = (int)htmlspecialchars($target_id);
        $favorite['user_id'] = cmsUser::getInstance()->id;

        $is_fav = $this->model->isFavoriteExists($favorite['target_controller'], $favorite['target_subject'], $favorite['target_id'],  $favorite['user_id']);

        if (!$is_fav) {

            $fav_id = $this->model->addFavorite($favorite);

            if ($fav_id) {
                    $result['error'] = false;
                    $result['message'] = LANG_FAVORITES_ADD_SUCCESS;
                    $result['fav_id'] = $fav_id;
                    $result['link_title'] = LANG_FAVORITES_DEL_BUTTON;
            } else {
                    $result['error'] = true;
                    $result['message'] = LANG_FAVORITES_ADD_ERROR;
            }

        } else {

            $result['error'] = true;
            $result['message'] = LANG_FAVORITES_ADDED;

        }

        $this->cms_template->renderJSON($result);
        //cmsTemplate::getInstance()->renderJSON($result);

    }

}