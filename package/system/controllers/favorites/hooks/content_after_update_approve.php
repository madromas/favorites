<?php

class onFavoritesContentAfterUpdateApprove extends cmsAction {

    public function run($data){

        if (!$this->isUsedFavorite($data['ctype_name'])) { return $data; }

        $ctype_name = $data['ctype_name'];
        $item = $data['item'];

        $ctype = cmsCore::getModel('content')->getContentTypeByName($ctype_name);

        $is_tags = $ctype['is_tags'];

        if (!$is_tags) { return $data; }

        $is_fav = $this->model->isFavoriteExists('content', $ctype_name, $item['id']);

        if (!$is_fav) { return $data; }

        $favorites = $this->model->filterTarget('content', $ctype_name, $item['id'])->getFavorites();

        if (!is_array($favorites)) { return $data; }

        //$user_ids = array_column($favorites, 'user_id');  // array_column - только для php 5.5 и выше
        $user_ids  = array_map(function($item) {
            return $item['user_id'];
        }, $favorites);

        $tags = cmsCore::getModel('tags')->getTagsForTarget('content', $ctype_name, $item['id']);

        $this->model->updateFavTags($tags, $user_ids, 'content', $ctype_name, $item['id']);

        return $data;

    }

}