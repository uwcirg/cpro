<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
?>

<?php

    if (Configure::check('searchMetaTags')){
        $this->start('metaBlock');
        echo "\n";
        $metaTags = Configure::read('searchMetaTags');
        foreach ($metaTags as $name => $content){
            //echo $name . $content;
            echo $this->Html->meta(array('name' => $name, 'content' => $content)); 
            echo "\n";
        }
        echo "\n";
        $this->end();
    }

    echo $this->Html->script('jquery.validate.js') . "\n";
?>

<div class="span2 visible-desktop">
</div>

<div class="span10">
    
    <h1><?php echo __("Welcome to " . SHORT_TITLE)?></h1>
    
    <?php
    // Display language toggle if used in this instance
    if (in_array('locale_selections',
        Configure::read('modelsInstallSpecific'))){
    ?>
    <div class="well pull-right language-login">
        <?php
        if ( $this->Session->read('Config.language') == 'en_US' || $this->Session->read('Config.language') == '' ) {
        ?>
        <strong>Español</strong><br />
        <a href="" id="langSwitch"  name="es_MX">Cambiar el idioma</a> a español.
        <?php } else { ?>
        <strong>English</strong><br />
        <a href="" id="langSwitch" name="en_US">Switch language</a> to English.
        <?php } ?>   
    </div>
    <?php
    } // End language toggle
    ?>
    
    <p><?= __(LOGIN_WELCOME).'. '.__('Please sign in below.');?></p>
           
        
        <br class="clearfix" />
        <div class="well">  
        <?php
        // Help link
        echo $this->Html->link('<i class="icon-question-sign"></i> ' . __('Login help'), 'help',array('class' =>'btn btn-mini', 'style' => 'float: right', 'escape' => false));
        ?>
        <h2 style="margin-top: 0"><?php echo __('Sign In')?></h2>
        <?php
        echo $this->Session->flash();
        echo $this->Form->create('User', array(
            'class' => 'form-horizontal',
            'inputDefaults' => array(
                'format' => array('before', 'label', 'between', 'input', 'error', 'after'),
                'div' => array('class' => 'control-group'),
                'label' => array('class' => 'control-label'),
                'between' => '<div class="controls">',
                'after' => '</div>',
                'error' => array('attributes' => array('wrap' => 'span', 'class' => 'help-inline'))
        )));
        echo $this->Form->hidden('language');
        ?>
        <fieldset>
            <br />
        <?php
            // Outputs fields
            echo $this->Form->input('username', array(
                'label' => array(
                    'class' => 'control-label',
                    'text' => __('Username:')
                ),
                'placeholder' => 'Enter username here'
            ));

            echo $this->Form->input('password', array(
                'label' => array(
                    'class' => 'control-label',
                    'text' => __('Password:')
                ),
                'placeholder' => 'Enter password here'
            ));
            echo '<div class="control-group"><div class="controls">';
            echo $this->Form->submit("Log in", array(
                "class"=>"btn btn-primary"
            ));
            echo '</div></div>';
        ?>
        </fieldset>
        
        <div id="uwLogin" class="control-group<?php if (!defined('UWNETID_LOGIN') || !UWNETID_LOGIN) echo ' hide'; ?>">
            <hr>
            <div class="controls">
            <?php
            if (!(array_key_exists('Shib-Session-ID', $_SERVER) &&
                (array_key_exists('REMOTE_USER', $_SERVER)))){
                echo $this->Html->link(
                    $this->Html->image("icon-uw-small.png", array('style' =>'margin: 0 4px 3px 0')) . __('Log in with UW NetID'), FULL_BASE_URL . '/Shibboleth.sso/Login?target=' . urlencode(Router::url('/', true)) . '&entityID=urn:mace:incommon:washington.edu',array('class' =>'btn btn-small', 'escape' => false)
                );
            }
            ?>
            </div>
        </div>
        
        <div id="oauth" class="control-group<?php if (!defined('OAUTH_LOGIN') || !OAUTH_LOGIN) echo ' hide'; ?>">
            <hr>
            <div class="controls">
            <?php
                echo $this->Html->link(
                    $this->Html->image("icon-google-small.png", array('style' =>'margin: 0 4px 3px 0')) . __('Log in with Google'), '/auth/google', array('class' =>'btn btn-small', 'escape' => false)
                );

                echo $this->Html->link(
                    $this->Html->image("icon-live-small.png", array('style' =>'margin: 0 4px 3px 0')) . __('Log in with Windows Live'), '/auth/live', array('class' =>'btn btn-small', 'escape' => false)
                );
            ?>
            </div>
        </div>

        <?php echo $this->Form->end();?>
        
    </div>
    
    <p><?php echo __('Learn more') . ' ' . $this->Html->link(__('about ' . SHORT_TITLE), 'help#about'); ?>.</p>
        

</div>

<?php 
// Modal for language switcher confirmation
echo $this->element('lang_switch_modal');
?>

<script>
$(document).ready(function() {   
    $("#UserUsername").focus();
    // Validation rules
    $("#UserLoginForm").validate({
        // not using our normal rules for User because
        // we don't want to reveal them to the unauthenticated
        rules: {
            "data[User][username]": {
                required: true
            },
            "data[User][password]": {
                required: true
            }
        }
    }); 
    
    <?php if (defined('UWNETID_LOGIN') && !UWNETID_LOGIN) { ?>
    typeForNetId();
    <?php } ?>
    
});    
      
</script>
