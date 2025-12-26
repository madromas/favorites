<?php

class onFavoritesCommentsAfterHide extends cmsAction {

    public function run($comment_id){

        if (!$comment_id) { return $comment_id; }

        $this->model->deleteFavComment($comment_id);

        return $comment_id;

    }

}