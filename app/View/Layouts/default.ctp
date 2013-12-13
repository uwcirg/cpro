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

    echo $this->fetch('metaBlock');

$checkBrowserCompatJs =
    CProUtils::get_instance_specific_js_name('check.browser.compat') . '.js';

$jsOrder= array(
    'jquery.js',
    'respond.js',
    'bootstrap.js',
    //'guiders.js',
    'jquery.template.js',
    'ui.base.js',
    'ui.position.js',
    'ui.widget.js',
    'ui.menu.js',
    'ui.mouse.js',
    'ui.dialog.js',
    'ui.draggable.js',
    'ui.tabs.js',
    'ui.autocomplete.js',
    'jquery.tablesorter.js',
    'cpro.jquery.js',
    'jquery.jeditable.js',
    'ui.datepicker.js',
    'jquery.validate.js',
    'cpro.jquery.validate.js',
    'fix.png.js',
    'cpro.controllers.js',
    'jquery.textboxhint.js',
    'jquery.dataTables.js',
    'cpro.diaries.js',
    'cpro.datatables.js',
    'cpro.p3p.js',
    'cpro.editable.js',
    'highcharts.src.js',
    'browser.detect.js',
    'cpro.js',
    $checkBrowserCompatJs
//    'check.browser.compat.' . INSTANCE_ID . '.js'
);
//$this->log("jsOrder = " . print_r($jsOrder, true), LOG_DEBUG);
//$this->log("jsAddnsToLayout = " . print_r($jsAddnsToLayout, true), LOG_DEBUG);

$jsArray = array(
    'jquery.js',
    'bootstrap.js',
    //'guiders.js',
    'ui.base.js',
    'cpro.js'
);

$jsArray = array_merge($jsArray, $jsAddnsToLayout);

$ordered = array_intersect($jsOrder, $jsArray);

// Add unordered javascript files to end
$jsArray = array_merge($ordered, array_diff($jsArray, $ordered));

if (in_array('jquery.dataTables.js', $jsArray)){
    echo $this->Html->css('datatable') . "\n";
}
echo $this->Html->css('bootstrap') . "\n";
echo "<!-- start_pdf_no -->\n";
echo $this->Html->css('bootstrap-responsive', null, array('media' => 'screen')) . "\n";
echo "<!-- end_pdf_no -->\n";
echo "<!-- add_pdf_css -->\n";
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
echo $this->Html->css('jquery.ui.all') . "\n";
echo $this->Html->css('https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700|Open+Sans:400,700') . "\n";
echo $this->Html->css('cpro') . "\n";

$instanceCSS = CProUtils::getInstanceSpecificCSSName();
if ($instanceCSS)
    echo $this->Html->css($instanceCSS) . "\n";

//echo $this->Html->css('guiders') . "\n";

// A couple additional styles for older IE
echo "<!--[if lte IE 8]>\n" ;
echo $this->Html->css('ie', "stylesheet") . "\n";
// Respond.js used to enable responsive design (w/ Bootstap) in IE8 and below
echo $this->Html->script('respond.js') . "\n";
echo "<![endif]-->\n";

//$this->log("jsArray = " . print_r($jsArray, true), LOG_DEBUG);

$this->Minify->js($jsArray);
?>

<script type="text/javascript">
    var controller = "<?php echo $this->request->params['controller']; ?>";
    var action = "<?php echo $this->request->params['action']; ?>";
    var appRoot = "<?php echo Router::url("/"); ?>";
    var pageTitle = "<?=PAGE_TITLE; ?>";
    var shortTitle = "<?=SHORT_TITLE; ?>";
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

echo $scripts_for_layout;

if (empty($no_javascript_required_for_layout)) {
  echo "<noscript>\n";
  echo '  <meta http-equiv="refresh" content="0; url=' .
           Router::url("/pages/noscript") . "\"/>\n";
  echo "</noscript>\n";
}
?>

<?php
if (defined('GOOGLE_ANALYTICS_ACCT'))
    echo $this->element('google_analytics');
?>

</head>

<?php
$classMod = '';
$classLone = '';
if(($this->request->params['action'] == 'accrualReport') || ($this->request->params['controller'] == 'logs')){
    $classMod .= ' wider';
}
elseif(($this->request->params['controller'] == 'chart_codings') &&
        in_array($this->request->params['action'], array('code', 'review')) ||
        (($this->request->params['controller'] == 'audio_files') &&
        in_array($this->request->params['action'], array('code', 'review'))))
{
    $classMod .= ' audiocode';
}
elseif (($this->request->params['controller'] == 'patients') &&
        ($this->request->params['action'] == 'offStudy'))
{
    $classMod .= ' widerstill';
}
if ($classMod != ''){
    $classLone = 'class="' . $classMod . '"';
}
?>

<body class="home">


    <?php
    if(!Configure::read('isProduction')){
    ?>
    <!-- start_pdf_no -->
    <div class="dev-warning left-attach hidden-phone" id="devWarning" data-toggle="tooltip" title="Development system - use only for testing!">
        <span><i class="icon icon-white icon-exclamation-sign"></i></span>
    </div>
    <script type="text/javascript">
    $("#devWarning").tooltip({placement: 'right'});
    </script>
    <!-- end_pdf_no -->
    <?php
    }
    ?>

<div class="container">

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
            echo $this->Html->link(
                $this->Html->image($bannerImage, $bannerImageProps)
                , '/users', array('escape' => false), false);
          ?>
        </div>
        <div class="span6 header-middle visible-desktop">
          <?php
            $bannerImage = "banner_".INSTANCE_ID."_right".$langLink.".png";
            $bannerImageProps = array('alt' => SHORT_TITLE, 'style' => 'padding: 0');
            echo $this->Html->link(
                $this->Html->image($bannerImage, $bannerImageProps)
                , '/users', array('escape' => false), false);
          ?>
        </div>
        <div class="span8 hidden-desktop">
          <?php
            $bannerImage = "banner" . "_" . INSTANCE_ID  . $langLink . ".png";
            $bannerImageProps = array('alt' => SHORT_TITLE, 'style' => 'padding: 0');
            echo $this->Html->link(
                $this->Html->image($bannerImage, $bannerImageProps)
                , '/users', array('escape' => false), false);
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
            echo $this->Html->link(
                $this->Html->image($bannerImage, $bannerImageProps)
                , '/users', array('escape' => false), false);
          ?>
        </div>
        <?php } ?>
        <div class="span4">
            <div class="pull-right header-links" style="margin-top: 1em">
                <?php
                // Display language toggle if used in this instance and
                // the logged in user is a patient (not admin)
                // $this->log('user: '.print_r($user, true), LOG_DEBUG);
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
                if(isset($authorizedUser) && $authorizedUser) {
                    echo $this->Html->link("<i class='icon-signout'></i> ".__("Log Out"), "/users/logout", array('class'=>'btn btn-small', 'escape' => false));
                }
                ?>
            </div>
        </div>
    </div>



    <?php
    // Creates navigation - uses tab_html.php in /helpers/
    // don't let tabs display fail due to an empty selected_tab
    if (isset($authorizedUser) && $authorizedUser && !isset($selected_tab)) {
        $selected_tab = '';
    }
    if (isset($tabs_for_layout, $selected_tab, $tabControllerActionMap,
            $tabsToDisable, $is_staff)){
                echo $this->TabHtml->display($tabs_for_layout, $selected_tab,
                        $tabControllerActionMap, $tabsToDisable, $is_staff);
    }

    // If there's a message.flash, display this container with message
    if ($this->Session->check('Message.flash')) {
    ?>
    <div class="container message-container">
        <div class="row">
            <div class="span2 visible-desktop"></div>
            <div class="span10">
            <?php
            /**
             * Uncomment for testing
            echo "APP: " . APP . "<br/>";
            echo "APP_PATH: " . APP_PATH . "<br/>";
            echo "CACHE: " . CACHE . "<br/>";
            echo "TMP: " . TMP . "<br/>";
            echo "WWW_ROOT: " . WWW_ROOT . "<br/>";
            echo "REQUEST_URI: " . $_SERVER["REQUEST_URI"] . "<br/>";
            */
            ?>
            <?php
            // display flash status messages
            // cakephp makes this div class "message" by default
            echo $this->Session->flash();
            ?>
            </div>
        </div>
    </div>
    <?php } ?>
    <!-- Begin main content area -->
    <div class="container intervention-container">
        <div class="row">
            <?php echo $content_for_layout ?>

        </div>
    </div> <!-- /main content area -->

    <?php
    // Adds UW logo for PainTracker. TODO: make part of config options.
    if (INSTANCE_ID == 'paintracker') {
        echo "<div class='row'><div class='span10 offset2'>";
        echo $this->Html->image('uw_signature.png', array('alt' => 'University of Washington', 'title' => 'University of Washington', 'class' => 'bottom-logo'));
        echo "</div></div>";
    }
    ?>

</div>

<?php
if(!Configure::read('isProduction')){
?>
<div class="container" id="logContainer">
    <div class="row">
        <div class="span12">
        <?php
        echo $this->element('sql_dump');
        ?>
        </div>
    </div>
</div>
<?php
}

echo $this->Js->writeBuffer();
?>

</body>
</html>

