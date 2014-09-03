<?php
global $CFG;
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/adminlib.php');

/**
* Generic impelementation of a phone number finding locale
* @author Tim Williams for PageOne 2011
* @licence GPL v2
**/

class block_pageone_locale_generic
{
 public function get_locale_name()
 {
  return "Generic";
 }

 public function get_mobile_number($user)
 {
  return "";
 }

 public function process_mobile_number($num)
 {
  $num=preg_replace("/[\s|-]/", "", $num);
  global $CFG;
  if (ereg("^(".$CFG->block_pageone_country_code.")", $num))
   return "0".substr($num, strlen($CFG->block_pageone_country_code));

  return $num;
 }
}
?>
