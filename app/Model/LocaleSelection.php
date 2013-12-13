<?php
/** 
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *   
*/
class LocaleSelection extends AppModel{
    var $name = 'LocaleSelection';
    var $useTable = 'locale_selections';

    var $belongsTo = array('User');
    var $order = array(
        'LocaleSelection.time' => 'desc',
        'LocaleSelection.id' => 'desc'
    );
}
