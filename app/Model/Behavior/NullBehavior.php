<?php 

/**
    * Replaces empty strings, '', with null values before committing them to the
    * database.
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * SAMPLE USAGE:
    * var $actsAs = array('Null' => array('gender', 'city', 'country'));
    * reference: http://bakery.cakephp.org/articles/view/null-behavior
*/
class NullBehavior extends ModelBehavior {
    
    /**
     * Initializes the fields to which this behavior applies.
     */
    function setup(&$model, $fields = array()) {
        $this->settings[$model->name] = $fields;
    }
    
    /**
     * For the appropriate fields, convert empty strings to null values before
     * saving the data.
     */
    function beforeSave(&$model) {
        foreach ($this->settings[$model->name] as $field) {
            if (isset($model->data[$model->name][$field])
                   && $model->data[$model->name][$field] === '') {
                $model->data[$model->name][$field] = null;
            }            
        }
    }
    
}

?>
