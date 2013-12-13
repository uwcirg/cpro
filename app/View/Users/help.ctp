<?php
/**
 * 
 * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
 *
 */

// Check login status and whether self-initiated password reset is allowed
$loginStatus = false;
$allowPasswordReset = false;
if(isset($authorizedUser) && $authorizedUser) {
    $loginStatus = true;    
}
if (!defined('PATIENT_UNASSISTED_PW_RESET') 
                    || !PATIENT_UNASSISTED_PW_RESET || !$is_staff) {
    $allowPasswordReset = true;
}
?>

<div class="span2">
    <div class="intervention-sidebar" data-spy="affix"  data-offset-top="165">
        <ul id="navbar" class="nav nav-list">
            <div class="section"><?php echo __('Help topics') ?>:</div>
            <?php /*
            <?php if ($allowPasswordReset && !$loginStatus){ ?>
                <li><a class="scroll-on-page" href="#login"><?php echo __('Login help') ?></a></li>
            <?php } 
            if($loginStatus) { ?>
                <li><a class="scroll-on-page" href="#account"><?php echo __('Account settings') ?></a></li>
            <?php } ?>
            <li><a class="scroll-on-page" href="#using"><?php echo __('Using ').SHORT_TITLE ?></a></li> 
            */ ?>
            <?php if ($is_staff && defined('STAFF_GUIDE')){ ?>
                <li><a class="scroll-on-page" href="#staff"><?php echo __('Staff guide') ?></a></li>
            <?php } ?>
            <li><a class="scroll-on-page" href="#contact"><?php echo __('Contact') ?></a></li>
            <li><a class="scroll-on-page" href="#browser"><?php echo __('Browser help') ?></a></li>
            <li><a class="scroll-on-page" href="#about"><?php echo __('About ').SHORT_TITLE ?></a></li>
        </ul>
    </div>
</div>

<div class="span10">
    
    <h2><?php echo __('Help') ?></h2>
    <br/>

    <div class="well span4 pull-right">
    <?php
    // Show either login assistance info or account settings based on whether patient
    // is logged in (and password reset is allowed)
    // This will appear if a user is not logged in and allowPasswordReset is true 
    if ($allowPasswordReset && !$loginStatus){
    ?>
        <h4 id="login"><?php echo __('Login help') ?></h4>
        <br />
        <p><?php echo __('If you\'ve forgotten your username or password, type your email address here:');?></p>

        <?php
        echo $this->Form->create('User', array(
                            'default' =>false // disable submit
        ));
        echo $this->Form->input(
            'email',
            array(
                'id' => 'data[User][email]',
                'class' => 'required',
                'label' => array(
                    'class' => 'control-label',
                    'text' => __('E-mail address:')
                ),
                'placeholder' => __('Enter e-mail address here')
            )
        );
        echo $this->Form->button('Submit', array(
                    'type' => 'button',
                    'class' => 'btn btn-primary',
                    'id' => 'ajaxSubmit'));
        echo $this->Form->end();
    }
    // This will appear if logged in - allows for password change
    if ($loginStatus){    
    ?>
        <h4 id="account">Account settings</h4>
        <br/>
        <?php
        // Account information;
        echo '<ul><li>' . $userName . '</li>';
        echo '<li>' . $firstName . '</li>';
        echo '<li>' . $lastName . '</li>';
        echo '</ul>';
        $helpLink = $this->Html->link(__("contact us"),   "/users/help", array('title'=>"contact"));
        echo '<p>' . __('If any of these need to be updated, please ') . $helpLink . '.</p>';
        echo '<p>'.$this->Html->link(
                    __("Click here to change your password"), "/users/changePasswordOfAuthdUser", array('title' => __("Change Your Password"))).'</p>';       
    } // End - Login Assistance/Account Settings
    ?>
    </div>
    
    <?php
    /* TODO - General help section. To add later (needs content from DFCI)
     * <h4 id="using"><?php echo __('Using ').SHORT_TITLE ?></h4>
     *   <p>Coming soon.</p>
     * 
     * TODO - if we want this feature, make helper element instance specific 
     * echo '<p>General help with using '.SHORT_TITLE.'. Perhaps have version of guided tour available here?</p>';
     */
    ?>
    
<?php
if ($is_staff && defined('STAFF_GUIDE')):
?>
    <h4 id="staff"><?php echo __('Staff guide') ?></h4>

    For more information about <?php echo __(SHORT_TITLE);?>, please see <a href="<?=STAFF_GUIDE;?>" target="_blank">the <?php echo __(SHORT_TITLE);?> staff guide</a>
    <hr />

<?php elseif ((defined('USER_GUIDE') and USER_GUIDE)): ?>
    For for help with <?php echo __(SHORT_TITLE);?>, please see <a href="<?=USER_GUIDE;?>" target="_blank">the <?php echo __(SHORT_TITLE);?> user guide</a>
    <hr />
<?php endif; ?>

    <h4 id="contact"><?php echo __('Contact us') ?></h4>
        
    <dl>
    <?php
    foreach ($clinics as $clinic) {
         echo "<dt>" . $clinic['Clinic']['friendly_name'] . "</dt>\n";
         echo '<dd>E-mail: <a href="mailto:'.$clinic['Clinic']['support_email'].'">'.$clinic['Clinic']['support_email']."</a></dd>\n";
         echo "<dd>Phone: ".$clinic['Clinic']['support_phone']."</dd><br />\n";
    }
    ?>
    </dl>

    <hr />
    
    <h4 id="browser"><?php echo __('Operating systems and web browsers that work with ').SHORT_TITLE ?></h4>
    <dl>
        <!-- fixme these aren't quite right, see check.browser.compat.*.js for exact checks-->
        <dt><strong>Windows 7, Vista and XP:</strong></dt>
        <dd>Firefox: <?php echo __('version %d and above', 2) ?></dd>
        <dd>Internet Explorer: <?php echo __('version %d and above', 8) ?></dd>
        <dd>Google Chrome: <?php echo __('all versions') ?></dd>
        <dt style="margin-top: 10px"><strong>Mac:</strong></dt>
        <dd>Firefox: <?php echo __('version %d and above', 3) ?></dd>
        <dd>Safari: <?php echo __('version %d and above', 3) ?></dd>
        <dd>Google Chrome: <?php echo __('all versions') ?></dd>
        <dt style="margin-top: 10px"><strong>iPad:</strong></dt>
        <dd><?php echo __('Standard iPad web browser (Safari 5 and above)') ?></dd>
    </dl>
    <br />

    <?php
    // always display compat message, just don't redirect to here unless prod
    echo $this->Html->script('browser.detect.msg.js') . "\n";
    ?> 
    <hr />
    
    <h4 id="about"><?php echo __('About ').SHORT_TITLE ?></h4>
    <br />
    <?php
    /* Instead of linking to about page, now have instance-specific element so
     * it appears inline.
     */
    $this->InstanceSpecifics->echo_instance_specific_elem('about');
    ?>
    <br />
    
</div>

<script>
$(document).ready(function() {

    //validatePatientForm("#UserHelpForm");

    $('#ajaxSubmit').click( function() {

        var request = $.ajax ({
            type: "POST",
            url: appRoot + 'users/login_assist.json',
            //url: appRoot + 'users/registerEdit.json',
            dataType: 'json',
            async: false,
            data: {"data[User][email]" : $('input[name="data[User][email]"]').val(), 
                "data[AppController][AppController_id]" : "<?= $this->Session->read(AppController::ID_KEY);?>"
            }
        });

        request.done(function(data, textStatus, jqXHR) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            alert ('<?php
                    echo __('Thank you. You will receive an email with instructions soon.');
                    ?>');
        });

        request.fail(function(jqXHR, textStatus, errorThrown) {
            var responseTxt = jQuery.parseJSON(jqXHR.responseText);
            var alertTxt = '<?php
                    $sorry =  __("Sorry, we can't find you in the system. If you continue to have trouble, contact us via the help e-mail address listed on this page.");
                    $sorry = str_replace("'", "\'", $sorry);
                    echo $sorry;
                    ?>' 
            alert(alertTxt + ' ' + responseTxt.message);
        }); 

    });
    
});

</script>

