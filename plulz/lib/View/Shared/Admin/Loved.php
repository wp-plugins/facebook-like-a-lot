<?php $this->PlulzMetabox->createMetabox('Loved this Plugin?'); ?>

    <p>Below are some links to help spread this plugin to other users</p>
    <ul>
        <?php
                foreach($this->content as $item) :
        ?>
                    <li><a href="<?= $item['link']; ?>" target="_blank"><?= $item['title']; ?></a></li>
        <?php
                endforeach;
        ?>
    </ul>

<?php $this->PlulzMetabox->closeMetabox(); ?>