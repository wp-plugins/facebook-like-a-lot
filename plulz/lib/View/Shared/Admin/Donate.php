<?php $this->PlulzMetabox->createMetabox('Donate via PayPal', array('id' => 'donate')); ?>

    <p><?php echo $this->content['desc']; ?></p>
    <p id="paypal"><?php echo $this->content['form']; ?></p>

<?php $this->PlulzMetabox->closeMetabox(); ?>