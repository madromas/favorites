<div id="favorite_info_window">

    <?php if (!$profiles){ ?>
        <p><?php echo LANG_FAVORITES_NO_USERS; ?></p>
    <?php } ?>

    <?php if ($profiles){ ?>

        <ul class="favorite_info_list">

            <?php $this->renderChild('info_list', array('profiles'=>$profiles)); ?>

        </ul>

        <?php if ($pages > 1){ ?>
            <div class="favorite_info_pagination"
                data-target-controller="<?php echo $target_controller; ?>"
                data-target-subject="<?php echo $target_subject; ?>"
                data-target-id="<?php echo $target_id; ?>"
                data-url="<?php echo $this->href_to('info'); ?>"
                >
                <?php for($p=1; $p<=$pages; $p++){ ?>
                    <a href="#<?php echo $p; ?>" data-page="<?php echo $p; ?>"<?php if ($p==$page) { ?> class="active"<?php } ?>><?php echo $p; ?></a>
                <?php } ?>
            </div>
            <script>icms.favorites.bindUsersInfoPages();</script>
        <?php } ?>

    <?php } ?>

</div>