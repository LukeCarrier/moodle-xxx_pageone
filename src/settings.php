<?php

/**
* Contains the global config page for MoodleMobile
* @author Tim Williams (tmw@autotrain.org) for PageOne
* @package pageone
**/

require_once($CFG->dirroot.'/blocks/pageone/block_pageone.php');
//require_once($CFG->dirroot . '/blocks/pageone/settingslib.php');

/**Account number/username**/
$settings->add(new admin_setting_configtext('block_pageone_account_num', get_string("config_account_num", "block_pageone"),
                   "", "", PARAM_TEXT));

/**Account password**/
$settings->add(new admin_setting_configpasswordunmask('block_pageone_account_pass', get_string("config_account_pass", "block_pageone"),
                   "", "", PARAM_TEXT));

/**Locales setting**/
$full_locales=array();
$locales=pageone_get_mobile_locales();

for ($loop=0; $loop<count($locales); $loop++)
 $full_locales[$locales[$loop]]=pageone_get_locale_name($locales[$loop]);

$settings->add(new admin_setting_configselect('block_pageone_locale', get_string('config_mobile_find', 'block_pageone'),
                  get_string('config_mobile_find_help', 'block_pageone') , "always_second", $full_locales));

/**Country code**/
$settings->add(new admin_setting_configtext('block_pageone_country_code', get_string("config_country_code", "block_pageone"),
                   get_string("config_country_code_help", "block_pageone"), 44, PARAM_INT));

/**Message delivery setting**/
$mtype=array(
  MTYPE_EMAIL=>get_string('config_mtype_'.MTYPE_EMAIL, 'block_pageone')
 );

$mtype_message=get_string('config_mtype_help', 'block_pageone');
if ($CFG->messaging==true)
 $mtype[MTYPE_MM]=get_string('config_mtype_'.MTYPE_MM, 'block_pageone');
else
 $mtype_message=get_string('config_mtype_help', 'block_pageone')." ".get_string('mm_disabled', 'block_pageone');

$settings->add(new admin_setting_configselect('block_pageone_mtype', get_string('config_mtype', 'block_pageone'),
                   $mtype_message, MTYPE_EMAIL, $mtype));

/**Unidentified receipient rule**/
$receive_opts=array();
for ($loop=0; $loop<4; $loop++)
 $receive_opts[$loop]=get_string('config_receive_'.$loop, 'block_pageone');

$settings->add(new admin_setting_configselect('block_pageone_receive', get_string('config_receive', 'block_pageone'),
 get_string('config_receive_help', 'block_pageone'), RECEIVE_IGNORE, $receive_opts));

/**Default mailbox for unidentified recepients**/
$settings->add(new admin_setting_configtext('block_pageone_default_mbox', get_string("config_default_mbox", "block_pageone"),
 get_string("config_default_mbox_help", "block_pageone"), "", PARAM_TEXT));

/**System default MSISDN**/

 $msisdn_opts=pageone_get_alphatag_list();
 if ($msisdn_opts)
  $settings->add(new admin_setting_configselect('block_pageone_alpha_tag', get_string('config_alpha_tag', 'block_pageone'),
   get_string('config_alpha_tag_help', 'block_pageone')." ".get_string('alphatag_help', 'block_pageone'), "", $msisdn_opts));
 else
  $settings->add(new admin_setting_configtext('block_pageone_alpha_tag', get_string("config_alpha_tag", "block_pageone"),
   get_string('no_list', 'block_pageone'), "", PARAM_TEXT));


/***Sub classing method to avoid triggering SOAP calls when they are not needed***
 ***This doesn't work yet (the parameter is persistently truncated), so is disabled***
$settings->add(new admin_setting_pageone_alphatag('alpha_tag', get_string("config_alpha_tag", "block_pageone"), 
 get_string('config_alpha_tag_help', 'block_pageone')." ".get_string('alphatag_help', 'block_pageone'),
 ""), PARAM_TEXT);
***/

/**Https end point**/
$https=array(0=>get_string("no"), 1=>get_string("yes"));
$settings->add(new admin_setting_configselect('block_pageone_https', get_string('config_https', 'block_pageone'),
 get_string('config_https_help', 'block_pageone'), 0, $https));

/**Character limit**/
$settings->add(new admin_setting_configtext('block_pageone_char_limit', get_string("config_char_limit", "block_pageone"),
                   get_string("config_char_limit_help", "block_pageone"), 1000, PARAM_INT));

/**Other account info and config**/
 $settings->add(new admin_setting_heading('pageone_account_info', get_string("account_info", "block_pageone"),
  pageone_get_account_settings_html()));
