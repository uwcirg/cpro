<?php
/* Force slightly different Cake Session.save behavior:  use a session cookie 
   (lifetime= 0); otherwise the code is the same as Cake's code when 
   Session.save = 'php'

   This might all break when Cake is updated.  Really, they should offer
   an option of using a session cookie without going to Security.level = high
   (which messes up AJAX).
 */
    $this-> cookieLifeTime = 0;

    if (function_exists('ini_set')) {
        ini_set('session.use_trans_sid', 0);
        ini_set('session.name', Configure::read('Session.cookie'));
        ini_set('session.cookie_lifetime', $this->cookieLifeTime);
        ini_set('session.cookie_path', $this->path);
    }
?>
