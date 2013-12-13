<?
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * See http://www.debuggable.com/posts/cakephp-pluralize-helper:480f4dfe-fbf8-464a-95da-4764cbdd56cb
    * Just a use of the standard cakephp inflector 
*/
class PluralHelper extends Helper {
    function ize($s, $c) {
      if($c==0 || $c > 1) {
        $inflect = new Inflector();
        return $c . ' ' . $inflect->pluralize($s);
      } else{
        return $c . ' ' . $s;
      }
    }
  }
?>
