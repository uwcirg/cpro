<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title><?php echo $title_for_layout ?></title>
    <!-- <meta http-equiv="X-UA-Compatible" content="IE=7"/> Removing b/c no longer need to specify IE7. FIXME - test this. -->
    <?php
    echo $this->Html->charset() . "\n";
    echo $this->Html->meta('favicon.ico', '/' . INSTANCE_ID . '.favicon.ico', array('type' =>'icon')). "\n";
    echo $this->Html->css('https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700|Open+Sans:400,700') . "\n";
    echo $this->Html->css('print') . "\n";
    ?>

<script type="text/javascript">
    var controller = "<?php echo $this->request->params['controller']; ?>";
    var action = "<?php echo $this->request->params['action']; ?>";
    var appRoot = "<?php echo Router::url("/"); ?>";
    var pageTitle = "<?=PAGE_TITLE; ?>";
</script>

</head>

<body class="home" onload="window.print()">

        <?php echo $content_for_layout ?>

</body>
</html>

