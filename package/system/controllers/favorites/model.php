<?php

class modelFavorites extends cmsModel {

    public function filterTarget($controller, $subject, $id){

        $this->filterEqual('target_controller', $controller);

        //if ($controller=='content' && $subject) {
        if (!empty($subject)) {
            $this->filterEqual('target_subject', $subject);
        }

        $this->filterEqual('target_id', $id);

        return $this;

    }

//------------ Favorites model -----------------------------------------------//

    public function isFavoriteExists($controller, $subject, $id, $user_id=false) {

        $this->useCache('favorites.favorites');

        if ($user_id) { $this->filterEqual('user_id', $user_id); }

        $this->filterTarget($controller, $subject, $id);

        $result = (bool)$this->getCount('favorites');

        $this->resetFilters();

        return $result;

    }


    public function addFavorite($favorite) {

        // если избранное содержит теги
        if (!empty($favorite['is_tags'])) {

            $tags_model = cmsCore::getModel('tags');

            $fav_tags = $tags_model->getTagsForTarget('content', $favorite['target_subject'], $favorite['target_id']);

            if ($fav_tags) {
                $this->addFavTags($fav_tags, 'content', $favorite['target_subject'], $favorite['target_id']);
            }

            unset($favorite['is_tags']);
        }

        // добавляем избранное в БД
        $id = $this->insert('favorites', $favorite);

            // чистим кеш избранного, контента и комментариев
            $cache = cmsCache::getInstance();
            $cache->clean("favorites.favorites");
            if ($favorite['target_controller']=='content') { $cache->clean("content.list.{$favorite['target_subject']}"); }
            if ($favorite['target_controller']=='comments') { $cache->clean("comments.list"); }

        return $id;

    }

    // возвращает избранное по id
    public function getFavorite($id) {

        $this->useCache('favorites.favorites');

        return $this->getItemById('favorites', $id);

    }

    // возвращает избранное по данным контроллера (контент или комментарии) и id-пользователя
    public function getFavoriteByFields($controller, $subject, $id, $user_id) {

        $this->useCache('favorites.favorites');

        $this->filterEqual('user_id', $user_id);

        $this->filterTarget($controller, $subject, $id);

        return $this->getItem('favorites');

    }

    public function getFavorites() {

        $this->useCache("favorites.favorites");

        return $this->get('favorites');

    }

    // возвращаем контроллеры избранного и типы контента избранного, для контроллера "контент"
    public function getFavoritesTargets($user_id){

        $this->useCache('favorites.favorites');

        $binds = $this->filterEqual('user_id', $user_id)->get('favorites');

        if (!$binds) { return false; }

        $targets = array();

        foreach ($binds as $bind){
            $targets[$bind['target_controller']][] = $bind['target_subject'] ? $bind['target_subject'] : $bind['target_controller'];
        }

        if ($targets['content']) { $targets['content'] = array_unique($targets['content']); }

        return $targets;

    }

    // количество записей в конкретной папке у пользователя
    public function getFolderItemsCount($user_id, $controller, $subject=NULL) {

        $this->useCache('favorites.favorites');

        $count = $this->filterEqual('user_id', $user_id)->
                        filterEqual('target_controller', $controller)->
                        filterEqual('target_subject', $subject)->
                        getCount('favorites');

        $this->resetFilters();

        return $count;

    }

    // всего записей в избранном у пользователя $user_id
    public function getUserFavoritesCount($user_id){

        $this->useCache("favorites.favorites");

        $fav_options = cmsController::loadOptions('favorites');

        // комментарии ...
        if (!$fav_options['is_comment_folder']) { $this->filterNotEqual('target_controller', 'comments'); }
        else { $this->filterEqual('target_controller', 'comments')->filterEqual('user_id', $user_id)->filterOr(); }

        // контент ...
        if ($fav_options['folders_names']) { $this->filterIn('target_subject', $fav_options['folders_names']); }
        else { $this->filterNotEqual('target_controller', 'content'); }

        $count = $this->filterEqual('user_id', $user_id)->
                        getCount('favorites');

        $this->resetFilters();

        return $count;

    }

    // получает количество добавленных избранных записей для указанного контроллера
    // (тоже самое, что количество пользователей добавивших указанную запись в избранное)
    public function getItemFavoritesCount($controller, $subject, $id){

        $this->useCache("favorites.favorites");

        $this->filterTarget($controller, $subject, $id);

        $count = $this->getCount('favorites');

        $this->resetFilters();

        return $count;

    }

    public function getUsersForTarget($controller, $subject, $id) {

        $this->useCache("favorites.favorites");

        $this->joinUser();

        $this->select('online.user_id', 'is_online');
        $this->joinLeft('sessions_online', 'online', 'i.user_id = online.user_id');

        $this->filterTarget($controller, $subject, $id);

        return $this->get('favorites', function($item, $model){

            $itm = array(
                'id' => $item['user_id'],
                'nickname' => $item['user_nickname'],
                'avatar' => $item['user_avatar'],
                'is_online' => (bool)$item['is_online'],
                //'is_online' => cmsUser::userIsOnline($item['user_id'])
            );

            return $itm;

        });

    }

    public function deleteFavorite($favorite) {

        $cache = cmsCache::getInstance();
        $cache->clean("favorites.favorites");
        if ($favorite['target_controller']=='content') { $cache->clean("content.list.{$favorite['target_subject']}"); }
        if ($favorite['target_controller']=='comments') { $cache->clean("comments.list"); }

        return $this->delete('favorites', $favorite['id']);

    }

    public function deleteFavorites($controller, $subject, $id, $user_id=false) {

        $cache = cmsCache::getInstance();
        $cache->clean("favorites.favorites");
        if ($controller=='content') { $cache->clean("content.list.{$subject}"); }
        if ($controller=='comments') { $cache->clean("comments.list"); }

        if ($user_id) { $this->filterEqual('user_id', $user_id); }

        $this->filterTarget($controller, $subject, $id);

        return $this->deleteFiltered('favorites');

    }

    public function deleteUserFavorites($user_id) {

        cmsCache::getInstance()->clean("favorites.favorites");

        return $this->filterEqual('user_id', $user_id)->deleteFiltered('favorites');

    }

//------------ Favorites Comments model --------------------------------------//

    public function deleteFavComment($comment_id) {

        //  проверяем статус комментария перед удалением
        //    $comments_model = cmsCore::getModel('comments');
        //    $comments_model->useCache("comments.list");
        //    $is_deleted = $comments_model->getItemById('comments', $comment_id,
        //        function($item){
        //            return (bool)$item['is_deleted'];
        //        });
        //    if (!$is_deleted) { return false; }

        $this->filterTarget('comments', NULL, $comment_id);

        $this->deleteFiltered('favorites');

        cmsCache::getInstance()->clean("favorites.favorites");

        return true;

    }

    // возвращает массив id комментариев
    private function getCommentsIds($params) {

            $params = is_array($params) ? $params : func_get_args();

            if (count($params)==1) {  // если передан user_id
                $user_id    = $params[0];
            }
            elseif (count($params)==3) {  // если переданы данные targets
                list($controller, $subject, $id) = $params;
            }
            else { return false; }

        $comments_model = cmsCore::getModel('comments');

        if (isset($user_id)) { $comments_model->filterEqual('user_id', $user_id); }

        if (isset($controller) && isset($subject) && isset($id)) {

            $comments_model->
                    filterEqual('target_controller', $controller)->
                    filterEqual('target_subject', $subject)->
                    filterEqual('target_id', $id);

        }

        $comments_model->useCache("comments.list");

        return $comments_model->get('comments', function($item, $model){

            return $item['id'];

        });

    }

    // Удаляет избранные записи комментариев из БД
    // на вход получает или $user_id или target-данные ($controller, $subject, $id)
    public function deleteFavComments() {

        if (!func_num_args()) { return false; }

        $comments_ids = $this->getCommentsIds( func_get_args() );

        if (!$comments_ids) { return; }

        $this->filterEqual('target_controller', 'comments');
        $this->filterIn('target_id', $comments_ids);

        $this->deleteFiltered('favorites');

        cmsCache::getInstance()->clean("favorites.favorites");

        return true;

    }

//------------ Favorites Tags model ------------------------------------------//

    public function addFavTags($tags, $controller, $subject, $id, $user_id=false, $is_user_tag=false) {

        if (!$tags || !is_array($tags)) { return false; }

        $tags_ids = array();
        $tags_inserted = array();

        if (!$user_id) { $user_id = cmsUser::getInstance()->id; }

        foreach($tags as $tag){

            $tag = mb_strtolower(trim($tag));

            if (!$tag) { continue; }

            if (in_array($tag, $tags_inserted)) { continue; }

            $tag_id = $this->registerFavTag($tag, $user_id);

            if (!$tag_id) { continue; }

            $this->insert('favorites_tags_bind', array(
                'tag_id' => $tag_id,
                'target_controller' => $controller,
                'target_subject' => $subject,
                'target_id' => $id,
                'is_user_tag' => $is_user_tag ? 1 : 0
            ));

            $tags_inserted[] = $tag;
            $tags_ids[] = $tag_id;

        }

        $this->recountFavTagsFrequency($tags_ids);

        cmsCache::getInstance()->clean("favorites.tags");

        return true;

    }

    public function recountFavTagsFrequency($tags_ids=array()) {

        $this->
            select('t.id', 'tag_id')->
            select('COUNT(i.tag_id)', 'frequency')->
            joinRight('favorites_tags', 't', 't.id = i.tag_id')->
            groupBy('t.id');

        if ($tags_ids) {
            $this->filterIn('t.id', $tags_ids);
        }

        $binds = $this->get('favorites_tags_bind');

        if (is_array($binds)) {
            foreach ($binds as $item) {
                if ($item['frequency']) {
                    $this->update('favorites_tags', $item['tag_id'], array('frequency' => $item['frequency']));
                } else {
                    $this->deleteFavTag($item['tag_id']);
                }
            }
        }

        cmsCache::getInstance()->clean("favorites.tags");

    }

    public function updateFavTags($tags, $user_ids, $controller, $subject, $id) {

        $this->filterTarget($controller, $subject, $id);

        $this->filterEqual('is_user_tag', '0');

        $this->lockFilters();

        $tags_ids = $this->get('favorites_tags_bind', function($item, $model){
            return $item['tag_id'];
        });

        $this->unlockFilters();

        $this->deleteFiltered('favorites_tags_bind');

        if ($tags_ids) { $this->recountFavTagsFrequency($tags_ids); }

        if (is_array($user_ids)) {
            foreach ($user_ids as $user_id) {
                $this->addFavTags($tags, $controller, $subject, $id, $user_id);
            }
        }

        return true;

    }

    public function registerFavTag($tag, $user_id) {

        $id = $this->insertOrUpdate('favorites_tags', array(
                'tag' => htmlspecialchars($tag),
                'user_id' => $user_id,
                'hash' => md5($tag.$user_id)
            ));

        if (!$id) { return $this->getFavTagId($tag, $user_id); }

        return $id;

    }

    public function getFavTagId($tag, $user_id) {

        return $this->filterEqual('hash', md5($tag.$user_id))->getFieldFiltered('favorites_tags', 'id');

    }

    public function getFavTagTargets($tag_id) {

        $binds = $this->filterEqual('tag_id', $tag_id)->get('favorites_tags_bind');

        if (!$binds) { return false; }

        $targets = array();

        foreach ($binds as $bind){
            $targets[$bind['target_controller']][] = $bind['target_subject'];
        }

        return $targets;

    }

    public function getFavTagsForTarget($controller, $subject, $id, $user_id=false, $is_user_tags=null) {

        $this->useCache('favorites.tags');

        $this->filterTarget($controller, $subject, $id);

        if (!is_null($is_user_tags)) {  // если $is_user_tags не задан выбираем все привязанные теги

            $is_user_tags ? $this->filterEqual('is_user_tag', 1) :  // выбираем только пользовательские теги
                            $this->filterEqual('is_user_tag', 0);   // выбираем только связанные с контентом теги

        }

        $this->select('t.tag', 'tag');

        $user_id ? $this->join('favorites_tags', 't', "t.id = i.tag_id AND t.user_id = {$user_id}") :
                   $this->join('favorites_tags', 't', "t.id = i.tag_id");

        return $this->get('favorites_tags_bind', function($item, $model){
            return $item['tag'];
        });

    }

    public function getFavTagsLinksForTarget($controller, $subject, $id, $user_id, $is_user_tags=null) {

        $tags = $this->getFavTagsForTarget($controller, $subject, $id, $user_id, $is_user_tags);

        if (!$tags) { return ''; }

        foreach($tags as $id => $tag){
            $tag = trim($tag);
            $tags[$id] = '<a href="'.href_to('users', $user_id, 'favorites').'?search='.urlencode(htmlspecialchars($tag)).'" class="fav_tag">'.$tag.'</a>';
        }

        $tags_bar = implode('<span class="fav_comma">, </span>', $tags);

        return $tags_bar;

    }

    public function getFavTags($user_id) {

        $this->useCache('favorites.tags');

        $this->filterEqual('user_id', $user_id);

        return $this->get('favorites_tags');

    }

    public function deleteFavTag($tag_id) {

        $this->delete('favorites_tags', $tag_id);

        $this->filterEqual('tag_id', $tag_id)->deleteFiltered('favorites_tags_bind');

        cmsCache::getInstance()->clean("favorites.tags");

    }

    public function deleteFavTags($controller, $subject, $id, $user_id=false, $is_user_tags=null) {

        if ($user_id) {
            $this->select('t.tag', 'tag');
            $this->join('favorites_tags', 't', "t.id = i.tag_id AND t.user_id = {$user_id}" );
        }

        if (isset($is_user_tags)) { $this->filterEqual('is_user_tag', $is_user_tags); }

        if ($controller && $id) {
            $this->filterTarget($controller, $subject, $id);
        }

        $tags_ids = $this->get('favorites_tags_bind', function($item, $model){
                        return $item['tag_id'];
                    });

        if(!$tags_ids){ return; }

        $this->filterIn('id', array_keys($tags_ids))->deleteFiltered('favorites_tags_bind');

        $this->recountFavTagsFrequency(array_unique($tags_ids));

        return true;

    }

    public function getTagsAutocomplete($query) {

        $tag_model = cmsCore::getModel('tags');

        $tag_model->useCache('tags.tags');

        $query = $tag_model->db->escape($query);

        $sql = "SELECT `tag` FROM `{#}tags` WHERE `tag` LIKE '%{$query}%'
                GROUP BY `tag`
                ORDER BY CASE
                    WHEN `tag` LIKE '{$query}%' THEN 0
                    WHEN `tag` LIKE '%{$query}%' THEN 1
                    ELSE 2
                END, `tag`";

        $result = $tag_model->db->query($sql);

        // если запрос ничего не вернул, возвращаем ложь
        if (!$tag_model->db->numRows($result)){ return false; }

        $items = array();

        // перебираем все вернувшиеся строки
        while($item = $tag_model->db->fetchAssoc($result)){

            $items[] = $item['tag'];

        }

        $tag_model->db->freeResult($result);

        return $items; // возвращаем теги

    }

}