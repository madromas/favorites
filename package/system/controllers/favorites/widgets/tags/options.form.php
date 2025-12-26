<?php

class formWidgetFavoritesTagsOptions extends cmsForm {

    public function init() {

        cmsCore::loadControllerLanguage('tags');

        return array(

            array(
                'type' => 'fieldset',
                'title' => LANG_OPTIONS,
                'childs' => array(

                    new fieldList('options:ordering', array(
                        'title' => LANG_WD_FAV_TAGS_ORDERING,
                        'items' => array(
                            'frequency' => LANG_WD_FAV_TAGS_ORDER_BY_FREQ,
                            'tag' => LANG_WD_FAV_TAGS_ORDER_BY_TAG,
                        )
                    )),

                    new fieldNumber('options:limit', array(
                        'title' => LANG_WD_FAV_TAGS_LIMIT,
                        'default' => 20
                    )),

                )
            ),

        );

    }

}