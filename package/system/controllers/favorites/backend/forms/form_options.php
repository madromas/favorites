<?php

class formFavoritesOptions extends cmsForm {

    public function init(){

                $model = cmsCore::getModel('content');
                $tree = $model->getContentTypes();

                $items = array();

                if ($tree) {
                    foreach ($tree as $item) {
                        $items[$item['name']] = $item['title'];
                    }
                }

        return array(

                array(
                    'type' => 'fieldset',
                    'title' => LANG_FAVORITES_OPTIONS_FOLDERS,
                    'childs' => array(

                        new fieldListMultiple('folders_names', array(
                            'title' => LANG_CONTENT_TYPE,
                            'items' => $items,
                            'hint' => LANG_FAVORITES_OPTIONS_CTYPE_HINT
                        )),

                        new fieldCheckbox('is_comment_folder', array(
                            'title' =>  LANG_FAVORITES_OPTIONS_SET_COMMENTS,
                            'default' => false
                        )),

                        new fieldCheckbox('is_user_list_show', array(
                            'title' =>  LANG_FAVORITES_OPTIONS_FAVUSERS_SHOW,
                            'default' => false
                        )),

                    )

                )

        );

    }

}