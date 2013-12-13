<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

$lang = '';
if (in_array('locale_selections', Configure::read('modelsInstallSpecific')) && isset($patient)){
    if ( $this->Session->read('Config.language') == 'es_MX' ) {
        $lang = 'lang-sp';
    }                    
}

  echo "<textarea class='textbox-question auto-focus $lang span6 ".$options[0]['ValueRestriction'];
  if ( $answer == '' ) {
      // Adds hint as background image if blank
      echo " textbox-background";
  }

  echo "' id='styled' rows='8' name='$question[id]'>$answer</textarea>";
  // Auto-focus and add hint as image. Needed for non-HTML5 browsers.
  // A more stripped-down version of:
  // http://www.ajaxblender.com/howto-add-hints-form-auto-focus-using-javascript.html
  // http://fuelyourcoding.com/scripts/infield/ - another possibility
?>
<script type="text/javascript">

<?php
    if ($numQs == 1){
?>
    //  Focus auto-focus fields
    $(function(){ 
        $('.auto-focus:first').focus();
    });
<?php
    }
?>

    //  Remove hint on keydown
    $('INPUT.textbox-background, TEXTAREA.textbox-background').one('keydown', function(){
        $(this).removeClass('textbox-background');
    });
</script>

