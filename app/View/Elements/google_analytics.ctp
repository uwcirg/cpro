<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 
                '<?php echo Configure::read('google_analytics_acct')?>']);
  _gaq.push(['_trackPageview']);
  _gaq.push(['_gat._anonymizeIp']);// removes last octet of visitor's IP prior to its use and storage

  _gaq.push(['_setCustomVar',
    1,              // This custom var is set to slot #1.  Required parameter.
    'DhairUserId',    // The name of the custom variable.  Required parameter.
    '<?php echo $authd_user_id;?>', // The value of the custom variable.  Required parameter.
    1                 // Sets the scope to visitor-level.  Optional parameter.
  ]); 

  _gaq.push(['_setCustomVar',
    2,              // This custom var is set to slot #1.  Required parameter.
    'IsDhairStaff',    // The name of the custom variable.  Required parameter.
    '<?php
        if ($is_staff) echo 'true';
        else echo 'false';
    ?>', // The value of the custom variable.  Required parameter.
    1                 // Sets the scope to visitor-level.  Optional parameter.
  ]); 


  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

