<?php
class widgetFavoritesTags extends cmsWidget {

    public function run(){

        $core = cmsCore::getInstance();
        $user_id = cmsUser::getInstance()->id;

        $is_show = $core->uri_controller == 'users' && $core->uri_action == $user_id && reset($core->uri_params) == $this->controller;

        if (!$is_show) { return false; }

        $ordering = $this->getOption('ordering', 'frequency');
        $limit = $this->getOption('limit');

        $current_tag = !empty($core->uri_query['search']) ? $core->uri_query['search'] : false;

        $model = cmsCore::getModel('favorites');

        switch($ordering) {
            case 'tag': $model->orderBy('tag', 'asc'); break;
            case 'frequency': $model->orderBy('frequency', 'desc'); break;
        }

        $tags = $model->getFavTags($user_id);

        if (!$tags) { return false; }

        return array(
            'tags' => $tags,
            'current_tag' => $current_tag,
            'limit' => $limit,
            'user_id' => $user_id
        );

    }

}