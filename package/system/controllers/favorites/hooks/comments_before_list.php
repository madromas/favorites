<?php

class onFavoritesCommentsBeforeList extends cmsAction {

    public function run($items){

        if (empty($items)) { return $items; }

        $is_show = ( cmsUser::isAdmin() || cmsUser::isAllowed('favorites', 'add') ) &&
                    $this->options['is_comment_folder'] && cmsUser::isLogged();

        if ($is_show && is_array($items)) {

            foreach ($items as $key=>$item) {

                if ($item['is_deleted']) { continue; }

                $items[$key]['favorite_widget'] = $this->renderFavoriteWidget('comments', NULL, $item);

            }
            unset($item);

        }

        return $items;

    }

}