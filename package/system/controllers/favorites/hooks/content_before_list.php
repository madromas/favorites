<?php

class onFavoritesContentBeforeList extends cmsAction {

    public function run($data){

        list($ctype, $items) = $data;

        if (!$items) { return $data; }

        $is_show = ( cmsUser::isAdmin() || cmsUser::isAllowed('favorites', 'add') ) && $this->isUsedFavorite($ctype['name']) && cmsUser::isLogged();

        $ctype['is_favorites'] = $is_show;

        if ($is_show && is_array($items)) {

            $user_id = cmsUser::getInstance()->id;

            foreach ($items as $key => $item) {

                $items[$key]['favorite_widget'] = $this->renderFavoriteWidget('content', $ctype, $item);

                $is_fav = $this->model->isFavoriteExists('content', $ctype['name'], $item['id'], $user_id);

                if ($is_fav) {

                    $fav_tags = $this->model->getFavTagsLinksForTarget('content', $ctype['name'], $item['id'], $user_id, true);

                    $items[$key]['favorite_tags'] = ($fav_tags && $items[$key]['tags']) ? '<span class="fav_comma">, </span>' . $fav_tags : $fav_tags;

                    $items[$key]['favorite_tags'] .= '<a href="javascript:void(0);" class="fav_edit_tags" onclick="return icms.favorites.showTagsForm(this, \'' . $ctype['name'] . "', " . $item['id'] . ');">' . LANG_FAVORITES_USER_TAGS_CHANGE . '</a>';
                }

            }

            $ctype['favorite_tags_form'] = $this->renderFavTagsForm($ctype, $item, true);

        }

        $data = array($ctype, $items);

        return $data;

    }

}