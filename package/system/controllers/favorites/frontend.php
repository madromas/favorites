<?php

class favorites extends cmsFrontend {

    protected $useOptions = true;

//============================================================================//
    public function actionIndex() {

        cmsCore::error404();

    }

//============================================================================//
    /*
     * Проверяет возможность добавлять в избранное записи из переданного типа контента
     */
    public function isUsedFavorite($ctype_name){

        $folders_names = $this->options['folders_names'];

        if (!$ctype_name || !$folders_names) { return false; }

        $folders_names = is_array($folders_names) ? $folders_names : array($folders_names);

        return in_array($ctype_name, $folders_names);

    }

//============================================================================//

    public function renderFavoriteWidget($controller, $ctype, $item){

        $user = cmsUser::getInstance();

        $fav_item = $this->model->getFavoriteByFields($controller, $ctype ? $ctype['name'] : NULL, $item['id'], $user->id);

        $favs_count = $this->model->getItemFavoritesCount($controller, $ctype ? $ctype['name'] : NULL, $item['id']);

        return cmsTemplate::getInstance()->renderInternal($this, 'favorite_widget', array(
            'options' => $this->getOptions(),
            'is_fav' => $fav_item ? true : false,
            'controller' => $controller,
            'ctype' => $ctype ? $ctype : '',
            'item' => $item,
            'fav_item' => $fav_item,
            'favs_count' => $favs_count,
            'user' => $user,
        ));

    }

//============================================================================//

    public function renderFavTagsForm($ctype, $item, $is_list=false){

        $is_tags = $is_list ? $ctype['is_tags'] && !empty($ctype['options']['is_tags_in_list'])
                            : $ctype['is_tags'] && !empty($ctype['options']['is_tags_in_item']);

        if (!$is_tags) { return ''; }

        return cmsTemplate::getInstance()->renderInternal($this, 'tags_form', array(
            'ctype' => $ctype,
            'item' => $item,
        ));

    }

//============================================================================//

    public function getFavoriteWidget($controller, $ctype, $item){

        $is_show = (cmsUser::isAdmin() || cmsUser::isAllowed('favorites', 'add'));

        if ($controller=='comments'){ $is_show = $is_show && $this->options['is_comment_folder']; }

        if ($controller=='content' && $ctype){ $is_show = $is_show && $this->isUsedFavorite($ctype['name']); }

        if ($is_show) {

            return $this->renderFavoriteWidget($controller, $ctype, $item);

        }

    }

}