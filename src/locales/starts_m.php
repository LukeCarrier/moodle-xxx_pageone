<?php

require_once("generic.php");

/*
* Phone number finder, gets phone number prefixed with an m from Moodle user profile
* @author Tim Williams for PageOne 2011
* @licence GPL v2
*/

class block_pageone_locale_starts_m extends block_pageone_locale_generic
{
 public function get_locale_name() 
 {
  return get_string('config_mobile_find_starts_m', 'block_pageone');
 }

 public function get_mobile_number($user)
 {
  if ($this->has_m($user->phone1))
   return $user->phone1;

  if ($this->has_m($user->phone2))
   return $user->phone2;

  return "";
 }

 private function has_m($num)
 {
  if (strlen($m)>0)
  {
   $f=substr($m,0,1);
   if ($f=="m" || $f=="M")
    return true;
  }

  return false;
 }
}

?>
