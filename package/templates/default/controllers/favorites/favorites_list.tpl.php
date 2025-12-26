<?php $this->addCSS("templates/{$this->name}/css/favorites/favorites.css"); ?>

<?php

    $this->setPageTitle(LANG_FAVORITES, $profile['nickname']);

    $this->addBreadcrumb(LANG_USERS, href_to('users'));

    $this->addBreadcrumb($profile['nickname'], href_to('users', $profile['id']));

    $is_search = !empty($is_search);

    if ($is_search) {  // если выводим результат поиска по тегу указываем это в навигационной цепочке

        $this->addBreadcrumb(LANG_FAVORITES, href_to('users', $profile['id'], 'favorites'));

        $this->addBreadcrumb(sprintf(LANG_FAVORITES_SEARCH_BY_TAG, $tag));

    } else {

        $this->addBreadcrumb(LANG_FAVORITES);

    }

    if ($is_results){

        $folders_menu = array();
        $active_ids   = array();

        if (is_array($folders)) {

            foreach($folders as $folder){

                $is_in_menu = false;
                if (is_array($targets)) {
                    foreach ($targets as $target) {
                        if (is_array($target)) {
                            $is_in_menu = in_array($folder['name'], $target) ? true : false;
                            if ($is_in_menu) { break; }
                        }
                    }
                }

                if (!$is_in_menu) { continue; }

                $uri_query = http_build_query(array(
                    'search' => $is_search ? $tag : NULL,
                    'folder' => $folder['name']
                ));

                $folders_menu[] = array(
                    'title'        => $folder['title'],
                    'url'          => href_to('users', $profile['id'], 'favorites') . '?' . $uri_query,
                    //'counter'    => isset($folder['counter']) ? $folder['counter'] : '',
                    'level'        => 1,
                    'disabled'     => false,
                    'childs_count' => 0
                );
                if ($folder['name']==$folder_current) { $active_ids[] = count($folders_menu) - 1; }
            }

        }

        $uri_query = http_build_query(array(
            'search' => $is_search ? $tag : NULL
        ));

        $folders_menu[0]['url'] = href_to('users', $profile['id'], 'favorites') . ( !empty($uri_query) ? '?' : '' ) . $uri_query;

        $this->addMenuItems('favorites_tabs', $folders_menu);

    }

    if (cmsUser::isAdmin()){
        $this->addToolButton(array(
            'class' => 'page_gear',
            'title' => LANG_FAVORITES_SETTINGS,
            'href'  => href_to('admin', 'controllers', array('edit', 'favorites'))
        ));
    }

?>

<div class="favorites_list">

    <?php if ($is_search){ ?>
        <a href="<?php echo href_to('users', $profile['id'], 'favorites') ?>" class="search_cancel" onclick="goBack(); return false;" title="<?php echo sprintf(LANG_FAVORITES_SEARCH_BY_TAG_CANCEL, $tag); ?>"></a>
        <h2><?php echo sprintf(LANG_FAVORITES_SEARCH_BY_TAG, $tag); ?></h2>
    <?php } ?>

    <?php if (!$is_results){ ?>
        <span><?php echo $is_search ? LANG_FAVORITES_SEARCH_NO_RESULTS : LANG_FAVORITES_NOITEMS; ?></span>
    <?php } ?>

    <?php if ($is_results){ ?>

        <div class="favorites_pills">
            <?php $this->renderMenu($this->menus['favorites_tabs'], $active_ids, 'pills-menu-small'); ?>
        </div>

        <?php echo $items_list_html; ?>

    <?php } ?>

</div>