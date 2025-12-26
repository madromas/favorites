<?php $this->addJS("templates/{$this->name}/js/jquery-autocomplete.min.js"); ?>

<form action="favorites/add_tags" method="post" id="fav_tag_form" class="fav_tag_form" style="display: none;">

    <?php echo html_csrf_token(); ?>
    <input type="hidden" name="ts" value="<?php echo $ctype['name']; ?>">
    <input type="hidden" name="ti" value="<?php echo $item['id']; ?>">

    <label>
        <?php echo LANG_FAVORITES_USER_TAGS; ?>
    </label>

    <input type="text" name="tags_string" class="tags_string" autocomplete="off">

    <div class="description">
        <?php echo LANG_FAVORITES_USER_TAGS_HINT; ?>
    </div>

    <input type="submit" value="<?php echo LANG_FAVORITES_USER_TAGS_SAVE; ?>" class="button-submit fav_submit">

    <a href="javascript:void(0);" class="fav_close">
        <?php echo LANG_FAVORITES_USER_TAGS_CANCEL; ?>
    </a>

</form>