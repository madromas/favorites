<?php if (is_array($profiles)) { ?>
    <?php foreach($profiles as $profile) { ?>

        <li>
            <a href="<?php echo href_to('users', $profile['id']); ?>" class="item">
                <?php echo html_avatar_image($profile['avatar'], 'micro'); ?>
                <span class="info"><?php html($profile['nickname']); ?></span>
                <?php if ($profile['is_online']) { ?>
                    <div class="actions">
                        <span class="is_online"><?php echo LANG_ONLINE; ?></span>
                    </div>
                <?php } ?>
            </a>
        </li>

    <?php } ?>
<?php } ?>