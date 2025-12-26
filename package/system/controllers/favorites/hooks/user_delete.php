<?php

class onFavoritesUserDelete extends cmsAction {

    public function run($user){

        // удаляем все комментарии пользователя которые выбрали другие люди себе в избранное
        $this->model->deleteFavComments($user['id']);

        //---- Чистим БД избранного от пользователя --------------------------//

        $favorites = $this->model->filterEqual('user_id', $user['id'])->getFavorites();

        if (!$favorites) { return $user; }

        // удаляем "личные" теги пользователя
        foreach ($favorites as $favorite) {

            if ($favorite['target_controller']=='content') {

                $this->model->deleteFavTags('content', $favorite['target_subject'], $favorite['target_id'], $user['id']);

            }

        }
        // удаляем избранное (записи и комментарии) которое пользователь добавил себе
        $this->model->deleteUserFavorites($user['id']);

        //--------------------------------------------------------------------//

        return $user;

    }

}