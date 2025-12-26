<?php

class actionFavoritesAddTags extends cmsAction{

    public function run(){

        if (!$this->request->isAjax()) { cmsCore::error404(); }

        $csrf_token     = $this->request->get('csrf_token');
        $target_subject = $this->request->get('ts'); // системное имя типа контента
        $target_id      = $this->request->get('ti'); // ID записи
        $tags_string    = $this->request->get('tags_string');

        // Проверяем валидность полученных данных
        $is_valid = ($this->validate_sysname($target_subject)===true) &&
                    is_numeric($target_id) &&
                    cmsForm::validateCSRFToken($csrf_token);
                    // $tags_string проверяем ниже

        // Проверяем возможность доавления в избранное
        $is_add = (cmsUser::isAdmin() || cmsUser::isAllowed('favorites', 'add')) &&
                  ($this->isUsedFavorite($target_subject));

        if (!$is_add && !$is_valid) {
            $result = array('error' => true, 'message' => LANG_FAVORITES_ADDTAGS_ERROR);
            cmsTemplate::getInstance()->renderJSON($result);
        }

        $user_id = cmsUser::getInstance()->id;

        // удаляем старые пользовательские теги (т.е. с флагом $is_user_tag = 1) ...
        $this->model->deleteFavTags('content', $target_subject, $target_id, $user_id, true);

        $tags_string = htmlspecialchars(trim($tags_string));

        if (!$tags_string) {
            cmsTemplate::getInstance()->renderJSON(array(
                'error' => false,
                'html' => ''
            ));
        }

        $tags = explode(",", $tags_string);

        // получаем ранее добавленные теги пользователя
        $tags_inserted = $this->model->getFavTagsForTarget('content', $target_subject, $target_id, $user_id);

        if (!$tags_inserted) { $tags_inserted = array(); }

        if ($tags) {

            foreach($tags as $key => $tag){

                $tag = mb_strtolower(trim($tag));

                if (!$tag){ continue; }

                if (in_array($tag, $tags_inserted)){ unset($tags[$key]); continue; }

                $tags_inserted[] = $tag;

            }

        }

        // записываем новые пользовательские теги ...
        $this->model->addFavTags($tags, 'content', $target_subject, $target_id, $user_id, true);

        cmsTemplate::getInstance()->renderJSON(array(
            'error' => false,
            'html' => '<span class="fav_comma">, </span>' . $this->model->getFavTagsLinksForTarget('content', $target_subject, $target_id, $user_id, true),
        ));

    }

}