<?php

require_once("generic.php");

/*
* Phone number finder, uses pattern matching to find a UK phone number from Moodle user profile
* @author Tim Williams for PageOne 2011
* @licence GPL v2
*/

class block_pageone_locale_uk extends block_pageone_locale_generic
{

 public function get_locale_name() 
 {
  return get_string('config_mobile_find_uk_auto', 'block_pageone');
 }

 public function get_mobile_number($user)
 {
  if ($this->is_valid_mobile_number($user->phone1))
   return $user->phone1;

  if ($this->is_valid_mobile_number($user->phone2))
   return $user->phone2;

  return "";
 }

 private function is_valid_mobile_number($num)
 {
  //Do some processing to remove spaces/hyphens and make life simpler
  $num=preg_replace("/[\s|-]/", "", $num);

  //Regular expression to match all of the possible starting sequences for a UK mobile number
  return ereg("^(07|00447|447|\+447|\(07|\+44\(0\)7)", $num);
 }

 public function process_mobile_number($num)
 {
  $num=preg_replace("/[\s|-]/", "", $num);

  if (ereg("^(07)", $num))
   return $num;

  if (ereg("^(00447)", $num))
   return "0".substr($num, 4);

  if (ereg("^(\+447)", $num))
   return "0".substr($num, 3);

  if (ereg("^(447)", $num))
   return "0".substr($num, 2);

  if (ereg("^(\(07)", $num))
   return preg_replace("/[\(|\)]/", "", $num);

  if (ereg("^(\+44\(0\)7)", $num))
   return "0".substr($num, 6);

  return $num;
 }

}

?>
