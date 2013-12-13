<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<form class="form-inline">
<?php
  echo "<input class='auto-focus {$options[0]['ValueRestriction']}' type='text' id='$question[id]-{$options[0]['id']}' name='$question[id]' value='$answer'";
  if ($options[0]["MaxCharacters"] != Null  && $options[0]["MaxCharacters"] != 0) {
    echo " maxlength='" .        $options[0]["MaxCharacters"] . "'";
  }
  echo "/>";
  //http://fuelyourcoding.com/scripts/infield/
?>
</form>
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
    $('INPUT.textbox-background, TEXTAREA.textbox-background').focus().one('keydown', function(){
        $(this).removeClass('textbox-background');
    });
</script>
