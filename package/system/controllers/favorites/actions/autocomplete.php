<?php

class actionFavoritesAutocomplete extends cmsAction {

    public function run() {

        if (!$this->request->isAjax()) { cmsCore::error404(); }

        $type  = $this->request->get('type');   // тип запроса
        $query = $this->request->get('query');  // запрос

        if (!$query || !$type==='tags') {  // отправляем "ошибку" ...
            cmsTemplate::getInstance()->renderJSON(array('suggestions' => false));
        }

        $query = mb_strtolower(htmlspecialchars(trim($query)));

        $tags = $this->model->getTagsAutocomplete($query);

        cmsTemplate::getInstance()->renderJSON(array(
            'query' => $query,
            'suggestions' => is_array($tags) ? $tags : false
        ));

    }

}