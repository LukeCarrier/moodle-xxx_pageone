<?php

require_once("generic.php");

/*
* Phone number finder, gets the second valid number from Moodle user profile
* @author Tim Williams for PageOne 2011
* @licence GPL v2
*/

class block_pageone_locale_second_valid extends block_pageone_locale_generic
{

 public function get_locale_name() 
 {
  return get_string('config_mobile_find_second_valid', 'block_pageone');
 }

 public function get_mobile_number($user)
 {
  if (strlen($user->phone2)>0)
   return $user->phone2;

  if (strlen($user->phone1)>0)
   return $user->phone1;

  return "";
 }

}
?>
