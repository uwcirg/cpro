<?php
// Enable OAuth, including option on login page
define('OAUTH_LOGIN', true);

CakePlugin::load('Opauth', array('routes' => true, 'bootstrap' => true));

/* NB client_id and client_secret are install / application specific.

   For each install, new values must be obtained from each 3rd party 
   identifier provider (IdP), i.e. per configured Strategy.

    https://docs.google.com/document/d/1lsEueY4kwmDdr95N98KPhIwEOKOXWI6CqdZluPvhJ6c/edit#heading=h.54w0u3h1qonm
 */
Configure::write('Opauth.Strategy.Live', array(
    'client_id' => '0000000048104834',
    'client_secret' => 'OHBTiwaGK8rUYgageXzn2lHshU5anJQ6',
    'scope' => 'wl.emails'
));

Configure::write('Opauth.Strategy.Google', array(
    'client_id' => '714818336889.apps.googleusercontent.com',
    'client_secret' => 'volovTw3HUqildpBWwDIoRma'
));


/* As we serve the CakeApp out of a named directory, 'Opauth.path'
 * must be set.  Replace with a dynamic lookup if available, rather
 * than hardcoding to arbitrary install path.
 */
Configure::write('Opauth.path', '/pbugni/auth/');

?>
