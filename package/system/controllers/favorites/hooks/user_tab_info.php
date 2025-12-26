<?php

class onFavoritesUserTabInfo extends cmsAction {

    public function run($profile, $tab_name){

        $this->count = $this->model->getUserFavoritesCount($profile['id']);

	$this->model->resetFilters();

        if (!$this->count){ return false; }

        return array('counter'=>$this->count);

    }

}