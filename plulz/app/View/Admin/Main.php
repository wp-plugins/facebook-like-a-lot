<div id="plulzwrapper" class="wrap">

    <a id="plulzico" href="<?= $this->domain; ?>" target="_blank">Plulz</a>
    <h2>Facebook Like a Lot Configuration</h2>

<?php

    $this->PlulzMetabox->createMetaboxArea('70%');

        $this->PlulzForm->create($this->_adminOptionsUrl);

            settings_fields( $this->group );

            $this->PlulzMetabox->createMetabox('APP Config');
?>
                <p class="help">If you need help you can find <a href="http://www.plulz.com/how-to-create-a-facebook-app" target="_blank">here</a> on How to Create Your Facebook APP</p>
                <table class="form-table">
                    <tbody>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>   'app',
                                    'type'      =>  'text',
                                    'label'     =>  __('App ID', $this->_name),
                                    'required'  =>  true,
                                    'small'     =>  __('Whithout the App ID, your plugin will not work. <a href="http://www.plulz.com/how-to-create-a-facebook-app" target="_blank">Know How to Create Your Facebook App</a>', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  'secret',
                                    'type'      =>  'text',
                                    'label'     =>  __('App Secret', $this->_name),
                                    'required'  =>  true,
                                    'small'     =>  __('Without the secret, your app cant talk with facebook', $this->_name)
                                ), $this->data, true );

?>                      </tr>
                        <tr>
<?php

                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  'language',
                                    'type'      =>  'text',
                                    'label'     =>  __('Language', $this->_name),
                                    'required'  =>  true,
                                    'small'     =>  __('For brazilian portuguese the code above would be <strong>pt_BR</strong>', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  'share',
                                    'type'      =>  'checkbox',
                                    'label'     =>  __('Share', $this->_name),
                                    'required'  =>  true,
                                    'small'     =>  __('Help us make more great plugins like this one by sharing our link.', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        </tbody>
                </table>
<?php
            $this->PlulzMetabox->closeMetabox();

            $this->PlulzMetabox->createMetabox('Like Button Default Config');
?>
                <table class="form-table">
                    <tbody>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                        'name'      =>   'openGraphTags',
                                        'type'      =>  'checkbox',
                                        'label'     =>  __('Add Open Graph Tags', $this->_name),
                                        'small'     =>  __('Only check this if there is NO other plugin already creating Open Graph Tags', $this->_name)
                                    ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  array('like' => 'width'),
                                    'type'      =>  'text',
                                    'label'     =>  __('Like Width', $this->_name),
                                    'required'  =>  true,
                                    'value'     =>  $this->likeOptions['width'],
                                    'small'     =>  __('The width must be in px. Ex.: 500px', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  array( 'like' => 'send'),
                                    'type'      =>  'checkbox',
                                    'label'     =>  __('Send Button', $this->_name),
                                    'required'  =>  true,
                                    'value'     =>  false,
                                    'small'     =>  __('Check to also show the send button', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  array( 'like' => 'action'),
                                    'type'      =>  'select',
                                    'label'     =>  __('Verb to Display', $this->_name),
                                    'options'   =>  $this->likeOptions['action'],
                                    'small'     =>  __('Choose which verb to display for the like action', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  array( 'like' => 'layout' ),
                                    'type'      =>  'select',
                                    'label'     =>  __('Layout Style', $this->_name),
                                    'options'   =>  $this->likeOptions['layout'],
                                    'small'     =>  __('The layout diagram for the Like button', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  array( 'like' => 'colorscheme'),
                                    'type'      =>  'select',
                                    'label'     =>  __('Colour Scheme', $this->_name),
                                    'options'   =>  $this->likeOptions['colorscheme']
                                ), $this->data, true );
?>                      </tr>
                    </tbody>
                </table>
<?php
            $this->PlulzMetabox->closeMetabox();

            $this->PlulzMetabox->createMetabox('Default Places to Show the Like Button');
?>
                <table class="form-table">
                    <tbody>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>   'content',
                                    'type'      =>  'checkbox',
                                    'label'     =>  __('Show in Content', $this->_name),
                                    'small'     =>  __('Check to show the like button before the content', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
<?php
                           echo $this->PlulzForm->addRow(array(
                                   'name'      =>   'contentPlace',
                                   'type'      =>  'select',
                                   'options'   =>   array( 'after' => 'after', 'before' => 'before', 'before and after' => 'before and after'),
                                   'label'     =>  __('Placement in Content', $this->_name),
                                   'small'     =>  __('Select if the Like button should be <strong>show after</strong> or <strong>before</strong> the post content', $this->_name)
                               ), $this->data, true );
?>
                       </tr>
                    </tbody>
                </table>
<?php
            $this->PlulzMetabox->closeMetabox();

            $this->PlulzMetabox->createMetabox('Advanced Configuration');
?>
                <p class="help"> ** Only change the config below if you know what you doing ** </p>
                <table class="form-table">
                    <tbody>
                        <tr>
<?php
                            echo $this->PlulzForm->addRow(array(
                                    'name'      =>  'advanced',
                                    'type'      =>  'text',
                                    'label'     =>  __('Hook to Append', $this->_name),
                                    'small'     =>  __('Choose any hook you like to append the Like Button. For example: \'the_title\' would be a valid place', $this->_name)
                                ), $this->data, true );
?>
                        </tr>
                        <tr>
                            <td colspan="2">You can find a list of places where you can insert the Like Button in the <a href="http://codex.wordpress.org/Plugin_API/Filter_Reference">Plugin API/Filter Reference</a></td>
                        </tr>
                        <tr>
                            <td colspan="2">The method above will only work on Hooks that handle String variables, arrays and anything else won't work for now</td>
                        </tr>
                    </tbody>
                </table>

<?php
            $this->PlulzMetabox->closeMetabox();
?>
            <p class="submit">
                <?php   echo $this->PlulzForm->addInput('submit', 'submit', 'enviar', 'Save Changes', array('class' => 'button-primary')); ?>
            </p>

<?php
        $this->PlulzForm->close();

        $this->PlulzMetabox->closeMetaboxArea();

        $this->PlulzMetabox->createMetaboxArea('29%');

            $this->lovedMetabox();
            $this->donateMetabox();
            $this->helpMetabox();

        $this->PlulzMetabox->closeMetaboxArea();
?>
    </div>