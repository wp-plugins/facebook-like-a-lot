<ul>
<?php
    foreach($this->content as $item) :
?>
        <li><a class="rsswidget" href="<?= $item['url'];?>"><?= $item['title']; ?></a><div class="rssSummary"><?= $item['desc']; ?></div></li>
<?php
    endforeach;
?>
</ul>
<br class="clear" />
<div style="margin-top:10px;border-top:1px solid #ddd;padding-top:10px;text-align:left;position:relative">
    <img src="<?= $this->_assets; ?>img/tiny-logo-plulz.png" style="position:absolute;bottom:0;left:0;" />
    <a href="<?= $this->domain; ?>" style="padding-left:16px;">Wordpress Plugins at Plulz</a>
</div>