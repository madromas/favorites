<?php

class onFavoritesContentBeforeItem extends cmsAction {

    public function run($data){

        list($ctype, $item, $fields) = $data;

        $is_show = ( cmsUser::isAdmin() || cmsUser::isAllowed('favorites', 'add') ) && $this->isUsedFavorite($ctype['name']) && cmsUser::isLogged();

        $ctype['is_favorites'] = $is_show;

        if ($is_show) {

            $user_id = cmsUser::getInstance()->id;

            $item['favorite_widget'] = $this->renderFavoriteWidget('content', $ctype, $item);

            $ctype['favorite_tags_form'] = $this->renderFavTagsForm($ctype, $item);

            $is_fav = $this->model->isFavoriteExists('content', $ctype['name'], $item['id'], cmsUser::getInstance()->id);

            if ($is_fav) {

                $fav_tags = $this->model->getFavTagsLinksForTarget('content', $ctype['name'], $item['id'], $user_id, true);

                $item['favorite_tags'] = ($fav_tags && $item['tags']) ? '<span class="fav_comma">, </span>' . $fav_tags : $fav_tags;

                $item['favorite_tags'] .= '<a href="javascript:void(0);" class="fav_edit_tags" onclick="return icms.favorites.showTagsForm(this, \'' . $ctype['name'] . "', " . $item['id'] . ');">' . LANG_FAVORITES_USER_TAGS_CHANGE . '</a>';

            }

        }

        return array($ctype, $item, $fields);

    }

}