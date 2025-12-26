<?php

class onFavoritesContentAfterDelete extends cmsAction {

    public function run($data){

        if (!$this->isUsedFavorite($data['ctype_name'])) { return $data; }

        if ( $this->model->deleteFavorites('content', $data['ctype_name'], $data['item']['id']) ) {

            // удаляем из БД избранного теги привязанные к записи
            $this->model->deleteFavTags('content', $data['ctype_name'], $data['item']['id']);

        }

        return $data;

    }

}