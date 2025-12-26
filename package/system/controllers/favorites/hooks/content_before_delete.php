<?php

class onFavoritesContentBeforeDelete extends cmsAction {

    public function run($data){

        if (!$this->isUsedFavorite($data['ctype_name'])) { return $data; }

        // удаляем из БД избранного все связанные с контентом комментарии
        $this->model->deleteFavComments('content', $data['ctype_name'], $data['item']['id']);

        return $data;

    }

}