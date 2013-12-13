<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
?>

<div class="span2 visible-desktop">

</div>

<div class="span10 esrac-main">

<?php
    echo $this->element('check_browser_compat');
?>

    <h1><?php echo $welcome_text; ?></h1>
    
    <div class="row">
        <div class="span6">
            <br />
            <p><?= __('%s research study.', LOGIN_WELCOME); ?></p>
            <?php echo $this->element('session_appt_links'); ?>
        </div>
        <div class="span4">

            <div id="video" class="well yellow-background" data-spy="affix"  data-offset-top="235">
                <div class="video-container">
                    <?php
                    $autoPlay = "0";
                    // Turn on autoplay if not staff AND if the number of sessions
                    // initiated is one or fewer -- basically only show before
                    // they start the main assessment. 1st session will likely
                    // be eligibility assessment so we use < 2
                    if (!$is_staff && $start_check < 2) $autoPlay = "1";
                    if ( $this->Session->read('Config.language') == 'en_US' || $this->Session->read('Config.language') == '' ) {
                        echo '<iframe id="player" src="https://www.youtube.com/embed/KBzFf5Tl_BU?controls=1&autohide=0&modestbranding=1&autoplay='.$autoPlay.'&rel=0&showinfo=0&fs=0" onload="videoCall()" frameborder="0" allowfullscreen></iframe>';
                    } else {
                        echo '<iframe id="player" src="https://www.youtube.com/embed/BBfu7GBxOBk?controls=1&autohide=0&modestbranding=1&autoplay='.$autoPlay.'&rel=0&showinfo=0&fs=0" onload="videoCall()" frameborder="0" allowfullscreen></iframe>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

</div>
<?php
// Show YouTube video logging functions
// Need to set userId and videoName so they can be passed to .js file
?>
<script type="text/javascript">
    var userId = '<?= $authd_user_id ?>';
    var videoName = 'intro';
</script>
<!-- YouTube API for use in getting play callbacks. TODO: host locally? -->
<script src="//www.youtube.com/player_api"></script>
<?php
// All tracking done within this file
echo $this->Html->script('cpro.youtube.tracking');
?>

