<?php $this->addCSS("templates/{$this->name}/css/jquery-jgrowl.css"); ?>
<?php $is_first_widget = $this->addCSSFromContext("templates/{$this->name}/css/favorites/favorites.css"); ?>
<?php $this->addJS("templates/{$this->name}/js/jquery-jgrowl.min.js"); ?>
<?php $this->addJS("templates/{$this->name}/js/favorites/favorites.min.js"); ?>


<?php if ($is_first_widget /*&& $this->isBody()*/) {
    $this->addOutput('
        <div hidden>
            <svg xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <symbol id="icon-star-full" viewBox="0 0 1024 1024">
                        <path class="path1" d="M1024 397.050l-353.78-51.408-158.22-320.582-158.216 320.582-353.784 51.408 256 249.538-60.432 352.352 316.432-166.358 316.432 166.358-60.434-352.352 256.002-249.538z"></path>
                    </symbol>
                </defs>
            </svg>
        </div>
    ');
} ?>

<?php $replacer = $ctype ? $ctype['labels']['create'] : html_spellcount_only(1, LANG_FAVORITES_COMMENTS_SPELLCOUNT) ?>
<?php //$ctype ? $replacer = $ctype['labels']['create'] : $replacer = html_spellcount_only(1, LANG_FAVORITES_COMMENTS_SPELLCOUNT) ?>

<div class="favorite_widget"
    data-target-controller="<?php echo $controller; ?>"
    <?php if ($ctype){ ?>
        data-target-subject="<?php echo $ctype['name']; ?>"
    <?php } ?>
    data-target-id="<?php echo $item['id']; ?>"
>

    <a class="favorite fav_<?php echo $is_fav ? 'delete' : 'add'; ?>" href="javascript:void(0);"
       data-action="<?php echo $is_fav ? 'delete' : 'add'; ?>"
       data-fav-id="<?php if ($is_fav) { echo $fav_item['id']; } ?>"
       title="<?php echo $is_fav ? LANG_FAVORITES_DEL_BUTTON : sprintf(LANG_FAVORITES_ADD_BUTTON, $replacer); ?>"
       onclick="icms.favorites.toggleFavorite(this);">
            <svg class="fav_icon icon_star_full"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-star-full"></use></svg>
    </a>

    <span class="fav_count<?php if ($options['is_user_list_show']) { ?> clickable<?php } ?>"
          <?php if ($options['is_user_list_show']) { ?>data-url="<?php echo $this->href_to('info'); ?>"<?php } ?>
          title="<?php echo sprintf(LANG_FAVORITES_FAVCOUNT_HINT, $replacer); ?>"
          <?php if ($options['is_user_list_show']) { ?>onclick="icms.favorites.showUsersInfo(this);"<?php } ?>>
        <?php if ($favs_count) { echo $favs_count; } ?>
    </span>

</div>

<?php if ($is_first_widget) { ?>
    <script>
        icms.favorites.setOptions({
            lang: { edit_tags: '<?php echo LANG_FAVORITES_USER_TAGS_CHANGE; ?>' }
        });
    </script>
<?php } ?>
