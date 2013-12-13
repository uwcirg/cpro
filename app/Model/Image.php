<?php
/**
    * Image class
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/
class Image extends AppModel
{
    var $name = 'Image';
    // var $useTable = 'clinics';
    var $belongsTo = array('Answer');
    var $bindModels = true;




}
