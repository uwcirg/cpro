<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<script id="source" language="javascript" type="text/javascript">

// ATTEMPTS TO PREVENT CREATION OF EXTRA SESSIONS DUE TO RAPID CLICKING OF SESSION LAUNCH LINKS

jQuery(function($) { 

    var hasFired = false;

    $(".session_launch").click(function(event){
        if (hasFired == true) {
            event.preventDefault();
            return false;
        }
        else {
            hasFired = true;
        }
    });
});

</script>

