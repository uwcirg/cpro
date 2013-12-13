<?php
/*
    * Item class
    * Models a single survey "item": an abstract representation
    * of a single question int he survey as it fits into the 
    * scales of instruments that the survey can measure.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    * 
*/

class Item extends AppModel
{
    var $name = "Item";
    var $useTable = 'items';

    var $belongsTo = array("Question", "Subscale");

    /** FIXME This is from when there was only one item per question...	
    function findByQuestion($question_id) {
    	$question = $this->Question->findById($question_id);
    	return $this->findById($question["Item"]["id"]);
    }
    */

}
