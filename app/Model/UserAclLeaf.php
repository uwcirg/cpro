<?php
/** 
    * UserAclLeaf class
    * Defines User membership to Aros and Acos
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
class UserAclLeaf extends AppModel
{
    var $name = "UserAclLeaf";
    var $useTable = 'user_acl_leafs';
    var $belongsTo = array("User");
    
}
