<?php

require_once("generic.php");

/*
* Phone number finder, gets the first phone number from Moodle user profile
* @author Tim Williams for PageOne 2011
* @licence GPL v2
*/

class block_pageone_locale_always_first extends block_pageone_locale_generic
{
 public function get_locale_name() 
 {
  return get_string('config_mobile_find_always_first', 'block_pageone');
 }

 public function get_mobile_number($user)
 {
  return $user->phone1;
 }
}

?>
