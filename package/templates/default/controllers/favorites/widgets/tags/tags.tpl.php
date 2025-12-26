<?php $this->addCSS("templates/{$this->name}/css/favorites/fav-tags.css"); ?>
<?php $is_first_widget = $this->addJS("templates/{$this->name}/js/favorites/favtags.min.js"); ?>

<?php if (is_array($tags)){ ?>

    <div class="widget_fav_tags">

        <div class="fav_suggest_box">
            <input type="text" id="tags_suggest" class="fav_tags_suggest">
            <span id="suggest_clear" class="fav_suggest_clear" title="<?php echo LANG_WD_FAV_TAGS_CLEAR; ?>" style="display: none;" unselectable="on"></span>
        </div>

        <ul class="fav_tags_list" id="top-tags">

            <?php $top_tags = array_slice($tags, 0, $limit); ?>

            <?php if ($top_tags) { ?>
                <?php foreach($top_tags as $tag){ ?>

                        <li>

                            <a href="<?php echo href_to('users', $user_id, 'favorites') . '?search=' . urlencode(htmlspecialchars($tag['tag'])) ?>"
                                <?php if ($current_tag == $tag['tag']) { ?> class="current" <?php } ?> >
                                <span class="count"><?php html($tag['frequency']); ?></span>
                                <span class="name"><?php html($tag['tag']); ?></span>
                            </a>

                        </li>

                <?php } ?>
            <?php } ?>

        </ul>

        <ul class="fav_tags_list" id="all-tags" style="display:none;"></ul>

    </div>

    <?php
        $all_tags = '';
        foreach ($tags as $tag){

            $ename = urlencode($tag['tag']);

            $all_tags .= "{"
                . ($tag['tag'] == $current_tag ? "current: true, " : "")
                . "name: '{$tag['tag']}', "
                . "ename: '{$ename}', "
                . "count: {$tag['frequency']}"
                . "}, ";

        }
    ?>

    <?php if ($is_first_widget){ ?>
        <script>
            icms.favtags.setOptions({
                url: '<?php echo href_to('users', $user_id, 'favorites') . '?search=' ?>',
                tags: [ <?php echo $all_tags; ?> { name: '', ename: '', count:0 } ]
            });
        </script>
    <?php } ?>

<?php } ?>