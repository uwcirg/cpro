<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <title><?php echo $title_for_layout ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    echo $this->Html->charset() . "\n";
    echo $this->Html->meta('favicon.ico', '/' . INSTANCE_ID . '.favicon.ico', array('type' =>'icon')). "\n";
    echo $this->Html->css('jquery.ui.all') . "\n";
    echo $this->Html->css('bootstrap') . "\n";
    echo $this->Html->css('bootstrap-responsive') . "\n";
    // Use alternate for font-awesome in IE9 and below. Needed because of issues
    // with icons experienced by people at UW and KP. Related to their browsers not
    // being able to correctly save the font file. 
    echo "<!--[if lte IE 9]>\n" ;
    echo $this->Html->css('font-awesome-alt') . "\n";
    echo "<![endif]-->\n";
    // If not IE (or if IE10 or above -- IE10 does not support conditional comments
    // and handles font-awesome just fine) then use regular font-awesome
    echo "<!--[if !IE]> -->\n" ;
    echo $this->Html->css('font-awesome') . "\n";
    echo "<!-- <![endif]-->\n";
    echo $this->Html->css('cpro') . "\n";
    //echo $this->Html->css('guiders') . "\n";
    echo $this->Html->css('survey.specific') . "\n";
    $instanceCSS = CProUtils::getInstanceSpecificCSSName();
    if ($instanceCSS)
        echo $this->Html->css($instanceCSS) . "\n";

    $js_to_minify = array('jquery.js', 'bootstrap.js', 'jquery.template.js', 'ui.base.js', 'ui.widget.js', 'ui.position.js', 'ui.tabs.js', 'ui.dialog.js', 'cpro.js', 'cpro.jquery.js', 'cpro.p3p.js'/*, 'guiders.js'*/);

    // A couple additional styles for older IE
    echo "<!--[if lte IE 8]>\n" ;
    echo $this->Html->script('excanvas.js') . "\n";
    echo $this->Html->css('ie', "stylesheet") . "\n";
    // Respond.js used to enable responsive design (w/ Bootstap) in IE8 and below
    echo $this->Html->script('respond.js') . "\n";
    echo "<![endif]-->\n";
    $this->Minify->js($js_to_minify);

?>
<script type="text/javascript">
    var controller = "<?php echo $this->request->params['controller']; ?>";
    var action = "<?php echo $this->request->params['action']; ?>";
    var appRoot = "<?php echo Router::url("/"); ?>";
    var pageTitle = "<?=PAGE_TITLE; ?>";
</script>
<?php

// value of the form variable used to guard against cross-site
// request forgeries
echo $this->Html->scriptBlock('var acidValue = "' .
                            $this->Session->read(AppController::ID_KEY) . '";')
     . "\n";

if (!empty($timeout_for_layout)) {
  echo $this->Html->scriptBlock('var timeout = "' . $timeout_for_layout . '";')
       . "\n";
  echo $this->Html->scriptBlock('var prefix = "' .  Router::url("/") . '";')
       . "\n";
  echo $this->Html->script('timeout.js') . "\n";
}

if (empty($no_javascript_required_for_layout)) {
  echo "<noscript>\n";
  echo '  <meta http-equiv="refresh" content="0;url=' .
           Router::url("/pages/noscript") . "\">\n";
  echo "</noscript>\n";
}

if (defined('google_analytics_acct'))
    echo $this->element('google_analytics');

?>
</head>

<body class="survey">

<div id="wrapper" class="container">

    <!-- start_pdf_no -->
    <?php /* Slightly different navs for desktop vs tablet/phone. Uses Bootstrap
     * "visible" classes to control display. */ ?>
    <div class="esrac-header row">

        <?php if (INSTANCE_ID == 'p3p') {
            // If Spanish then change .png files names to display correct banner
            $langLink = "";
            if ( $this->Session->read('Config.language') == 'es_MX' ) $langLink = "Spanish";
            ?>
        <div class="span2 visible-desktop">
          <?php
            $bannerImage = "banner_".INSTANCE_ID."_left.png";
            $bannerImageProps = array('alt' => SHORT_TITLE, 'class' => 'header-logo');
            echo $this->Html->image($bannerImage, $bannerImageProps);
          ?>
        </div>
        <div class="span6 header-middle visible-desktop">
          <?php
            $bannerImage = "banner_".INSTANCE_ID."_right".$langLink.".png";
            $bannerImageProps = array('alt' => SHORT_TITLE, 'style' => 'padding: 0');
            echo $this->Html->image($bannerImage, $bannerImageProps);
          ?>
        </div>
        <div class="span8 hidden-desktop">
          <?php
            $bannerImage = "banner" . "_" . INSTANCE_ID  . $langLink . ".png";
            $bannerImageProps = array('alt' => SHORT_TITLE, 'style' => 'padding: 0');
            echo $this->Html->image($bannerImage, $bannerImageProps);
          ?>
        </div>
        <?php } else { ?>
        <div class="span8">
          <?php
            $bannerImage = "banner" . "_" . INSTANCE_ID . ".png";
            function begins_with($haystack, $needle) {
                return strpos($haystack, $needle) === 0;
            }
            if (begins_with(INSTANCE_ID, "paintracker")) {
                $bannerImageProps = array('alt' => SHORT_TITLE, 'class' => 'header-logo paintracker-logo');
            } else {
                $bannerImageProps = array('alt' => SHORT_TITLE, 'class' => 'header-logo');
            }
            echo $this->Html->image($bannerImage, $bannerImageProps);
          ?>
        </div>
        <?php } ?>
        <div class="span4">
            <div class="pull-right header-links print-hidden" style="margin-top: 1em">
                    <?php
                    if (
                        in_array('locale_selections', Configure::read('modelsInstallSpecific')) and
                        isset($patient) and

                        // Site-specific checks
                        (
                            !isset($user['Clinic']['id']) or
                            !Configure::check('multiLangClinics') or
                            in_array($user['Clinic']['id'], Configure::read('multiLangClinics'))
                        )
                    ){
                        if ( $this->Session->read('Config.language') == 'en_US' || $this->Session->read('Config.language') == '' ) {
                            echo "<a href='' id='langSwitch' name='es_MX' class='btn btn-small lang-link' style='margin-right: 12px' title='Cambiar el idioma a español'>Español</a>";
                        } else {
                            echo "<a href='' id='langSwitch' name='en_US' class='btn btn-small lang-link' style='margin-right: 12px' title='Change language to English'>English</a>";
                        }
                        // Add modal for language switch confirmation
                        echo $this->element('lang_switch_modal');

                    } // End language toggle
                    echo $this->InstanceSpecifics->echo_instance_specific_elem('help_button');
                    echo $this->Html->link("<i class='icon-time'></i> ".__("Take a break"), "/surveys/break_session", array('class'=>'btn btn-small', 'escape' => false));
                ?>
            </div>
        </div>
    </div>

    <div class="row">
        <?php
        // Changes page layout width.
        // needs more horizontal space, so uses wider "span12"
        if ($project['ui_small']){
            echo '<div class="span12">';
            if (isset($project['header'])) echo '<h1>'.__($project['header']).'</h1>';
        } else {
        ?>
        <div class="span2 visible-desktop" id="surveySidebar">
            <?php
            // no content in left bar for now. Could add project details, progress
            // details or other status info.
            ?>
        </div>
        <div class="span10 survey-container">
        <?php }
        // display flash status messages
        // cakephp makes this div class "message" by default
        if ($this->Session->check('Message.flash')): echo $this->Session->flash(); endif;
        ?>

            <div id="surveyPage" class="survey-page-box">
                <?php echo $content_for_layout ?>
            </div>

        </div>
    </div>
</div>

<?php echo $this->element('sql_dump'); ?>

<?php
echo $this->Js->writeBuffer();
?>

</body>
</html>

