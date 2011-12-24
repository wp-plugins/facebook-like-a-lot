<?php $this->PlulzMetabox->createMetabox('Need Assistance?'); ?>

<p>Problems? The links bellow can be very helpful to you</p>
<ul>
<?php
        foreach($this->content as $item):
?>
            <li><a href='<?= $item['link']; ?>' target='_blank' > <?= $item['title']; ?></a></li>
<?php
        endforeach;
?>
</ul>

<?php $this->PlulzMetabox->closeMetabox(); ?>