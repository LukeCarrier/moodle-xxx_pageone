<?php

/**
* Subclassing of of the MSISDN config parameter (not currently used)
* @author Tim Williams (tmw@autotrain.org) for PageOne
* @package pageone
**/

if (!defined('MOODLE_INTERNAL'))
{
 die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
}

require_once($CFG->dirroot."/blocks/pageone/pageonelib.php");

class admin_setting_pageone_alphatag extends admin_setting
{
 public function __construct($name, $visiblename, $description, $defaultsetting)
 {
  $this->plugin = 'block_pageone';
  parent::__construct($name, $visiblename, $description, $defaultsetting);
 }

 public function get_setting()
 {
  $value=mdl21_getconfigparam("block_pageone", "alpha_tag");
  echo "<br />get=".$value;
  if (is_null($value))
   return NULL;
  return array('value' => $value);
 }

 public function write_setting($data)
 {
  echo "<br />set=".$data['value'];
  if (!isset($data['value']))
   $data['value'] = "";

  $this->config_write($this->name, $data['value']);
  
  return '';
 }

 public function output_html($data, $query='')
 {
  foreach ($data as $k=>$p)
   echo $k.":".$p."<br />";

  if (!isset($data['value']))
            $data['value'] = "";

  $return="<div id=\"pageonealphatagoption\" class=\"form-select defaultsnext\">\n";
  $msisdn_opts=pageone_get_alphatag_list();

  if (count($msisdn_opts)>0)
  {
   $return.="<select id=\"".$this->get_id()."\" name=\"".$this->get_full_name()."\" >";
   foreach ($msisdn_opts as $k=>$p)
   {
    if ($k==$data['value'])
     $return.="<option selected=\"selected\" value=\"".$k."\">".$p."</option>";
    else
     $return.="<option value=\"".$k."\">".$p."</option>";
   }
   $return.="</select>";
   $return.="</div>";
   return format_admin_setting($this, $this->visiblename, $return, $this->description, true, '', "", $query);
  }
  else
  {
   $return.="<input type=\"text\" id=\"".$this->get_id()."\" name=\"".$this->get_full_name()."\" value=\"".$data['value']."\" />";
   return format_admin_setting($this, $this->visiblename, $return,
    "<p>".get_string('no_list', 'block_pageone')."</p>", true, '', "", $query);
  }

 }
}
?>
