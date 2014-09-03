<?php

/**
 * pageonelib - Contains PageOnelibrary for sending text messages and associated functions
 * 
 * This code is licenced under the GNU GPLv2 licence (please see gpl.txt for details) and
 * is copyright to PageOne
 *
 * @author Tim Williams (tmw@autotrain.org) for PageOne
 * @package pageone
 **/


global $CFG;
if ($CFG->version>=2010000000)
 require_once("lib-mdl2.php");
else
 require_once("lib-mdl1.php");

/*********************Some help with turning debug on/off***********************************/

define ("IS_DEBUGGING", false);
define ("ALLOW_CALLBACK_TEST", false);

/*********************Some constants to use in the system***********************************/

define ("TYPE_TEXT_EMAIL", 0);
define ("TYPE_TEXT", 1);
define ("TYPE_EMAIL", 2);
define ("TYPE_TEXT_MM", 3);
define ("TYPE_MM", 4);

define ("MTYPE_EMAIL", 0);
define ("MTYPE_MM",1);

define ("RECEIVE_IGNORE", 0);
define ("RECEIVE_EMAIL", 1);
define ("RECEIVE_DEFAULT_MBOX", 2);
define ("RECEIVE_REJECT", 3);

define ("CALLBACK_BOTH", 0);
define ("CALLBACK_DELIVERY", 1);
define ("CALLBACK_REPLY", 2);

//define ("SOAP_NS", "http://schemas.oventus.com");
define("SOAP_NS", "http://jaxb.liquidsoap.pageone.com");
define ("SOAP_CALLBACK_NS", "http://jaxb.liquidcallbackregister.pageone.com");

define ("SOAP_SERVER", "https://m2m.oventus.com");
//define ("SOAP_SERVER", "https://soap.oventus.com");
//define ("SOAP_SERVER", "https://sandbox.oventus.com");

//define ("SOAP_WEBSERVICES_URL", "/webservices/soap");
define ("SOAP_WEBSERVICES_URL", "/LiquidWS/MessageService");
define ("SOAP_CALLBACK_URL", "/LiquidWS/CallbackRegisterService");

/****************Some error response codes for externally called methods*********************/

/**
* Returned when the login fails
**/
define ("PAGEONE_LOGIN_FAILED", 0);

/**
* Returned when everything has worked
**/
define ("PAGEONE_SUCESS", 1);

/**
* For log, message sent with no errors
**/

define("PAGEONE_NO_ERRORS", 0);

/**
* For log, some errors during message sending
**/

define("PAGEONE_ERRORS", 1);

/**
* For log, callback was recieved and everything is good
**/

define("PAGEONE_CONFIRMED", 2);

/**
* For log, callback was recieved but there were some errors, either in the original send or the callback
**/

define("PAGEONE_CONFIRMED_ERRORS", 3);

//The Moodle nusoap library has broken proxy support, so include our alternative version here
//However, moodle includes it's version of nusoap and this lib on some admin pages and that causes a fatal error
//so we must test for nusoap classes to ensure this doesn't happen

if (!class_exists("nusoap_base"))
 require_once("lib/nusoap.php");


/**Import the PHP 5 pageone library, keep these functions seperate in case future PHP releases require another library seperation**/

require_once("pageonelib_5.php");


/**********************SOAP connection stuff*******************************/

/**
* This method will send a SOAP request and return it's result
* @param $header The SOAP header XML as a String
* @param $body The SOAP body XML as a String
* @param $action The SOAP method to call
* @param $soapurl The SOAP url to send the method too on the server. Defaults to SOAP_WEBSERVICES_URL
* @return A DOMDocument containing the SOAP response
**/

function pageone_send_soap($header, $body, $action, $soapurl=SOAP_WEBSERVICES_URL)
{
    global $CFG;

    if (IS_DEBUGGING)
        echo 'Start soap send <br />';

    $client=new nusoap_client(SOAP_SERVER.$soapurl);
    if (!empty($CFG->proxyhost))
    {
        $pp=false;
        if (!empty($CFG->proxyport))
            $pp=$CFG->proxyport;

        $pu=false;
        $ps=false;
        if (!empty($CFG->proxyuser) && !empty($CFG->proxypassword))
        {
            $pu=$CFG->proxyuser;
            $ps=$CFG->proxypassword;
        }
        if (IS_DEBUGGING)
            echo "proxy host:".$CFG->proxyhost." port:".$pp." user:".$pu." pass:".$ps;
        $client->setHTTPProxy($CFG->proxyhost, $pp, $pu, $ps);
    }
    $err = $client->getError();

    if (IS_DEBUGGING)
        echo 'Done client<br />';

    if ($err)
    {
	pageone_show_error("SOAP Constructor error", $err."<br /><br />client->response='".$client->response."'");
        return null;
    }


    $soapxml = $client->serializeEnvelope($body, $header, array(), 'document', 'literal');
    $result = $client->send($soapxml, $action);
    if (IS_DEBUGGING)
    {
        echo 'Sending <br /><textarea cols="100" rows="20">'.$client->request.'</textarea><br />';
        echo 'Response <br /><textarea cols="100" rows="20">'.$client->response.'</textarea><br />';
    }

    if ($client->fault)
    {
	pageone_show_error(get_string('soap_fault', 'block_pageone'), get_r($result), get_string('soap_fault_help', 'block_pageone'));
        return null;
    }
    else
    {
	$err = $client->getError();
	if ($err)
        {
            pageone_show_error(get_string('send_error', 'block_pageone'), $err.'<br/><br/>'.$client->response, get_string('send_error_help', 'block_pageone'));
            return null;
        }
    }

    return pageone_get_xml_document($client->responseData);
}

/**
* Shows an error on the page
* @param $title The error title
* @param $err The error message
**/

function pageone_show_error($title, $err, $help="")
{
    echo '<h2>'.$title.'</h2><pre>'.$err.'</pre>';
    if (strlen($help)>0)
     echo '<p>'.$help.'</p>';
}

/***********************Methods which are intended for external use**************************/

/**
* Call this to see if the config details have been entered
* @return true if the PageOne username & password has been entered
**/

function pageone_is_configured()
{
    if (mdl21_configparamisset('block_pageone', 'account_num') && strlen(mdl21_getconfigparam('block_pageone', 'account_num'))>0 &&
        mdl21_configparamisset('block_pageone', 'account_pass') && strlen(mdl21_getconfigparam('block_pageone', 'account_pass'))>0)
        return true;

    return false;
}

/**
* This method tests the pageone account details
* @return error response code
**/

function pageone_test_account()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;
    pageone_soap_logout($session);
    return PAGEONE_SUCESS;
}

/**
* Sends a text message to a group of users
* @param $userstotext An array of Users who should be texted
* @param $sender The message sender User
* @param $subject The message subject
* @param $message The message text
* @param $includefrom true if the message sender is to be included in the text message body
* @return a result object containing any any errors
**/

function pageone_send_text($userstotext, $sender, $subject, $message, $includefrom)
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $messageText=pageone_get_message_text($sender, $subject, $message, $includefrom);

    $numbers=array();
    $user_map=array();

    foreach($userstotext as $user)
    {
        $num=pageone_find_mobile_number($user, true);
        if (strlen($num)>0)
        {
            if (IS_DEBUGGING)
                echo 'sending to '.$user->username.' '.$num.'<br />';
            array_push($numbers, $num);
            $user_map[$num]=$user->id;
        }
    }

    $result=pageone_soap_send_message($session, $numbers, $messageText, pageone_get_custom_alphatag($sender->id), $user_map);
    pageone_soap_logout($session);
    $result->valid_users=$user_map;

    return $result;
}

/**
* Standard method for constrcuting the text message
* @param $sender The message sender User
* @param $subject The message subject
* @param $message The message text
* @param $includefrom true if the message sender is to be included in the text message body
* @return The text message
**/

function pageone_get_message_text($sender, $subject, $message, $includefrom)
{
    $messageText="";
    if (strlen($subject)>0)
     $messageText.=$subject."\n";

    $messageText.=trim($message);
    if ($includefrom)
     $messageText=get_string("from", "block_pageone").":".$sender->firstname." ".$sender->lastname."\n".$messageText;

    return $messageText;
}

/**
* Checks the length of the text message
* @param $sender The message sender User
* @param $subject The message subject
* @param $message The message text
* @param $includefrom true if the message sender is to be included in the text message body
* @return true if the message is too long
**/

function pageone_char_limit_exceeded($sender, $subject, $message, $includefrom)
{
    $messageText=pageone_get_message_text($sender, $subject, $message, $includefrom);
    if (strlen($messageText)>mdl21_getconfigparam("block_pageone", "char_limit"))
        return true;
    else
        return false;
}

/**
* Sends a text message to a specific number from the system administrator
* @param $number The number to text
* @param $message The message text
* @return a result object containing any any errors
**/

function pageone_send_text_to_number($number, $message)
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $numbers=array();
    array_push($numbers, $number);

    $result=pageone_soap_send_message($session, $numbers, $message, "", array());
    pageone_soap_logout($session);

    return $result;
}

function pageone_send_mm_text($userfrom, $userto, $messagetext, $mm_include_user)
{
     if (pageone_char_limit_exceeded($userfrom, "", $messagetext, $mm_include_user))
     {
      $messagetext=substr(pageone_get_message_text($userfrom, "", $messagetext, $mm_include_user),
          0,mdl21_getconfigparam("block_pageone", "char_limit")-3)."...";
     }

     $textresult=pageone_send_text(array($userto), $userfrom, "", $messagetext, $mm_include_user);

     $log = new stdClass;
     $log->ovid       = $textresult->id;

     $log->courseid=pageone_find_course($userto->id, $userfrom->id);
     if ($log->courseid==null)
         $log->courseid=1;

     $log->userid     = $userfrom->id;
     $log->mailto     = $userto->id;
     $log->subject    = addslashes(substr($messagetext, 0, 20).'...');
     $log->message    = addslashes($messagetext);
     $log->attachment = '';
     $log->format     = FORMAT_PLAIN;
     $log->timesent   = time();
     $log->messagetype = TYPE_TEXT_MM;
     $log->includefrom=true;

     if ($textresult->ok==false)
     {
         $log->status=PAGEONE_ERRORS;
         $log->failednumbers=$textresult->failednumbers;
     }
     else
     {
         $log->status=PAGEONE_NO_ERRORS;
         $log->failednumbers="";
     }

     global $DB;
     if (!$DB->insert_record('block_pageone_log', $log))
     {
         print_error('Text Message not logged.');
     }

}

/**********************Mobile phone number methods**************************/

/**
* Checks to see if the user has a valid phone number
* @param $user The use to test
* @return true if a valid number has been found
**/

function pageone_has_valid_mobile_number($user)
{
    if (strlen(pageone_find_mobile_number($user))>0)
        return true;

    return false;
}

/**
* Gets the current phone number locale object
**/

function pageone_get_number_locale()
{
    $locale=mdl21_getconfigparam('block_pageone', 'locale');
    if (!file_exists('locales/'.$locale.'.php'))
        $locale="always_second";

    require_once('locales/'.$locale.'.php');
    return eval('return new block_pageone_locale_'.$locale.'();');
}

/**
* Finds the mobile phone number of the supplied user
* @param $user The user to test
* @param $process If the number should be processed according to locale rules to remove internationalisation
* @return The users phone number (or an empty string if it's not found
**/

function pageone_find_mobile_number($user, $process=false)
{
    $locale=pageone_get_number_locale();
    $num=$locale->get_mobile_number($user);
    if ($process)
     $num=$locale->process_mobile_number($num);

    return $num;
}

/**
* Gets the available locales for detecting mobile phone numbers
* @return An array of the locales
**/

function pageone_get_mobile_locales()
{
    global $CFG;
    $handle=opendir($CFG->dirroot.'/blocks/pageone/locales');
    $files=array();

    while (false !== ($file = readdir($handle)))
        if ($file!=".." && $file!="." && is_file($CFG->dirroot.'/blocks/pageone/locales/'.$file) && $file!="generic.php")
            array_push($files, substr($file, 0, strlen($file)-4));

    closedir($handle);

    return $files;
}

/**
* Gets the name of the specified locale
* @param $locale The required locale
* @return The locale name
**/

function pageone_get_locale_name($locale)
{
    require_once('locales/'.$locale.'.php');
    $locale=eval('return new block_pageone_locale_'.$locale.'();');
    return $locale->get_locale_name();
}

/************************Alphatag editing functions**************************/

/**
* Adds a custom alphatag
* @param $userid The id of the user
* @param $alphatag The custom alphatag to set
* @return true if the tag was successfully set
**/

function pageone_add_user_alphatag($userid, $alphatag, $receive)
{
    global $DB;
    if (pageone_has_custom_alphatag($userid))
        return false;
    else
    {
        $data->userid=$userid;
        $data->alphatag=$alphatag;
        $data->receive=$receive;
        $DB->insert_record("block_pageone_alphatags", $data);
        return true;
    }
}

/**
* Updates the custom alphatag of the specified user
* @param $userid The id of the user
* @param $alphatag The custom alphatag to set
**/

function pageone_update_user_alphatag($id, $alphatag, $receive)
{
    global $DB;
    $data->id=$id;
    $data->alphatag=$alphatag;
    $data->receive=$receive;
    $DB->update_record("block_pageone_alphatags", $data);
}

/**
* Tests to see if the users has a custom alphatag
* @param $userid The id of the user to test
* @return true if the user has a custom alphatag
**/

function pageone_has_custom_alphatag($userid)
{
    global $DB;
    $r=$DB->get_record("block_pageone_alphatags", array("userid"=>$userid));
    return isset($r->id);
}

/**
* Tests to see if the users has a custom alphatag
* @param $userid The id of the user to test
* @return true if the user has a custom alphatag
**/

function pageone_get_custom_alphatag($userid)
{
    global $DB;
    $r=$DB->get_record("block_pageone_alphatags", array("userid"=>$userid));
    if (isset($r->id))
    {
        return $r->alphatag;
    }
    else
        return "";
}

/**
* Delete the custom alpha tag of a user
* @param $id The id of the tag to delete
**/

function pageone_delete_user_alphatag($id)
{
    global $DB;
    $DB->delete_records("block_pageone_alphatags", array("id"=>$id));
}

/**
* Returns an HTML drop down box of the currently valid alphatags for this account.
* This will return a textbox if a list of the alpha tags cannot be obtained.
* @param $name The HTML tag name to use for the box
* @param $id The HTML tag id to use
* @param $set The currently selected alhpatag (optional)
* @return An HTML formatted string
**/

function pageone_get_alphatagoptions($name, $id, $set="")
{
    //*****If the module isn't configured, return the current value*****
    if (!pageone_is_configured())
        return "<i>".get_string("no_list", "block_pageone")."</i><input type=\"hidden\" name=\"".$name."\" id=\"".$id."\" value=\"\" />";

    //*****Pick up the alphatags. If the list is blank, send back a text box*****
    $tags=pageone_get_alphatags();
    if ($tags==PAGEONE_LOGIN_FAILED || count($tags)==0)
        return "<i>".get_string("no_list", "block_pageone")."</i><input type=\"hidden\" name=\"".$name."\" id=\"".$id."\" value=\"\" />";

    $data='<select name="'.$name.'" id="'.$id.'">';
    if ($set=="")
     $data.='<option value="" selected="selected">'.get_string("please_select", "block_pageone").'</option>';

    foreach ($tags as $tag)
    {
        $inuse='';
        if (pageone_alphatag_user($tag)>-1)
            $inuse=' *';
        if ($tag==mdl21_getconfigparam('block_pageone', 'alpha_tag'))
            $inuse=' #';

        if ($tag==$set)
            $data .= '<option value="'.$tag.'" selected="selected">'.$tag.$inuse.'</option>';
        else
            $data .= '<option value="'.$tag.'">'.$tag.$inuse.'</option>';
    }
    return $data.'</select>';
}


/**
* Returns the array of available MSISDN's/Alphatags for use on the settings page
**/

function pageone_get_alphatag_list()
{
 if (!pageone_is_configured())
  return false;

 $tags=pageone_get_alphatags();
 if ($tags==PAGEONE_LOGIN_FAILED || count($tags)==0)
  return false;

 $opts=array();
 $sysdefault=mdl21_getconfigparam('block_pageone', 'alpha_tag');
 foreach ($tags as $tag)
 {
  $inuse='';
  if (pageone_alphatag_user($tag)>-1)
   $inuse=' *';
  if ($tag==$sysdefault)
   $inuse=' #';

  $opts[$tag]=$tag.$inuse;
 }

 return $opts;
}

function pageone_alphatag_user($tag)
{
    //global $DB;
    $r=mdl21_get_DB()->get_record("block_pageone_alphatags", array("alphatag"=>$tag));
    if (isset($r->id))
        return $r->id;

    return -1;
}

/********************Account settings code*****************/

function pageone_get_account_settings_html()
{
 if (ALLOW_CALLBACK_TEST)
  $callbacks_ok=pageone_check_callbacks();
 else
  $callbacks_ok=false;

 $html="<table width=\"90%\" style=\"margin-left:auto;margin-right:auto;\"><tr>\n".
  " <td style=\"text-align:left;\">".
  pageone_get_settings_links().
  " </td><td>\n".
  pageone_get_server_check($callbacks_ok).
  " </td>\n".
  " </tr><tr><td colspan=\"2\">".pageone_get_callback_info()."</td></tr>".
  "</table>\n";

  if (ALLOW_CALLBACK_TEST)
   $html.=pageone_get_fix_callbacks_links($callbacks_ok);

 return $html;
}

function pageone_get_server_check($callbacks_ok)
{
 $html="<table class=\"generaltable generalbox\" style=\"margin-left:auto;margin-right:auto;\"><tr>\n".
       " <td class=\"cell\" style=\"padding:5px;font-weight:bold;\">".get_string("account_works", "block_pageone")."</td>\n".
       " <td class=\"cell\" style=\"padding:5px;\">";

 if (pageone_test_account()==PAGEONE_SUCESS)
  $html.=get_string("ok");
 else
  $html.=get_string("failed", "block_pageone");

 $html.=" </td>\n".
        "</tr>";
 if (ALLOW_CALLBACK_TEST)
 {
  $html.="<tr>\n".
        " <td class=\"cell\" style=\"padding:5px;font-weight:bold;\">".get_string("callback_ok", "block_pageone")."<br />\n".
        "  <span style=\"font-size:x-small;\">(".get_string("callback_ok_des", "block_pageone").")</span></td>\n".
        " <td class=\"cell\" style=\"padding:5px;\">";

  if ($callbacks_ok)
   $html.=get_string("registered", "block_pageone");
  else
  {
   $callbacks_ok=pageone_fix_callbacks();
   if ($callbacks_ok)
    $html.=get_string("ok");
   else
    $html.=get_string("failed", "block_pageone");
  }

 $html.="</td>\n".
        "</tr>";
 }

 $html.="<tr>\n".
        " <td class=\"cell\" style=\"padding:5px;font-weight:bold;\">".get_string("available_credit", "block_pageone")."</td>\n".
        " <td class=\"cell\" style=\"padding:5px;\">".pageone_available_credit()."</td>\n".
        "</tr></table>";

 return $html;
}

function pageone_get_callback_info()
{
 include("services/callback_url.php");
 $callbackURL=$CALLBACK_URL."?wsdl";

 return "<div><h3>".get_string("callback_ok", "block_pageone")."</h3>".
  "<p>".get_string("callback_instructions", "block_pageone")."</p>".
  "<p style=\"text-align:center;font-weight:bold;\">".$callbackURL."</p></div>";
}

function pageone_get_fix_callbacks_links($callbacks_ok)
{
 global $CFG;
 if ($callbacks_ok==false)
  return "<div class=\"informationbox\" style=\"text-align:left\"><p>".get_string("callback_problem", "block_pageone")."</p>".
   "<ul>".
   " <li><a href=\"".$CFG->wwwroot."/blocks/pageone/callback_edit.php?action=addignore\">\n".
   get_string("callback_problem_1", "block_pageone")."</a></li>\n".
   " <li><a href=\"".$CFG->wwwroot."/blocks/pageone/callback_edit.php?action=deletealladd\">\n".
   get_string("callback_problem_2", "block_pageone")." </a></li>\n".
   "<li><a href=\"".$CFG->wwwroot."/blocks/pageone/callback_edit.php\">\n".
   get_string("callback_problem_3", "block_pageone")." </a></li>\n".
   "</ul></div>";
 else
  return "<p>".get_string("callback_delay", "block_pageone")."</p>";

}

function pageone_get_settings_links()
{
 global $CFG;
 $html='<h3>'.get_string("account_info2", "block_pageone").'</h3>'.
       '<ul>'.
       ' <li><a href="'.$CFG->wwwroot.'/blocks/pageone/editalpha.php">'.get_string("config_edit_alpha_link", "block_pageone").'</a></li>'.
       ' <li><a href="'.$CFG->wwwroot.'/blocks/pageone/emaillog.php?courseid=1&amp;show=all">'.get_string("config_edit_log_link", "block_pageone").'</a></li>';
 if (IS_DEBUGGING)
  $html.=' <li><a href="'.$CFG->wwwroot.'/blocks/pageone/callback_edit.php">'.get_string("callback_problem_3", "block_pageone").'</a></li>';

 $html.='</ul>';

 return $html;
}

/********************Callback code**********************/

/**
* This method sets callbacks on the PageOne server
* @param $host The callback host URL to add (defaults to current server)
* @param $serv The type of callback to add. Defaults to both. Should be CALLBACK_BOTH, CALLBACK_DELIVERY or CALLBACK_REPLY
**/

function pageone_set_callback($host="", $serv=CALLBACK_BOTH)
{
    global $CFG;
    if ($host=="")
       $host=$CFG->wwwroot."/blocks/pageone/callback.php?wsdl";

    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    pageone_add_callback($session, $host, $serv);

    pageone_soap_logout($session);
}

/**
* Sets the specificed callback on the pageone server
* @param $session The server session to use
* @param $host The callback URL to add
* @param $serv The type of callback to add. Defaults to both. Should be CALLBACK_BOTH, CALLBACK_DELIVERY or CALLBACK_REPLY
**/

function pageone_add_callback($session, $host, $serv)
{
    if (IS_DEBUGGING)
        echo "Adding callback host=".$host."<br />";

    if ($serv==CALLBACK_DELIVERY || $serv==CALLBACK_BOTH)
    {
        $r=pageone_add_callback_service($session, $host, "deliveryReportListenerRequest", "registerDeliveryReportListener");
        if ($r->value!=200)
            pageone_show_error(get_string('callback_reg_error_delivery', 'block_pageone'), $r->text." (".$r->value.")");
    }

    if ($serv==CALLBACK_REPLY || $serv==CALLBACK_BOTH)
    {
        $r=pageone_add_callback_service($session, $host, "receivedMessageListenerRequest", "registerReceivedMessageListener");
        if ($r->value!=200)
            pageone_show_error(get_string('callback_reg_error_reply', 'block_pageone'), $r->text." (".$r->value.")");
    }
}

/**
* This method removes all of the callbacks currently registered on the PageOne server
**/

function pageone_remove_all_callbacks()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $callbacks=pageone_list_callbacks($session);
    foreach ($callbacks as $cb)
        pageone_remove_callback($session, $cb->entryID);

    pageone_soap_logout($session);
}

/**
* This method removes a specific callback on the PageOne server
* @param $entryID The id of the callback to remove
**/

function pageone_remove_specific_callback($entryID)
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    pageone_remove_callback($session, $entryID);

    pageone_soap_logout($session);
}

/**
* This method gets a list of the currently registered callbacks
* @return An Array of values in the following format $a[$index]->entryID=The callback ID
*         $a[$index]->service=The callback type, should be CALLBACK_REPLY or CALLBACK_DELIVERY
*         $a[$index]->host=The registered URL
**/

function pageone_get_callback_list()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $callbacks=pageone_list_callbacks($session);

    pageone_soap_logout($session);
    return $callbacks;
}

/**
* This method checks to see if the currently registered callbacks are likely to work
* @return true or false
**/

function pageone_check_callbacks()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $callbacks=pageone_list_callbacks($session);
    $okreply=false;
    $okdelivery=false;
    require_once("services/callback_url.php");
    $callbackURL=$CALLBACK_URL."?wsdl";
   
    foreach ($callbacks as $cb)
    {
        if ($cb->host==$callbackURL) 
        {
            if ($cb->service==CALLBACK_DELIVERY)
                $okdelivery=true;
            else
            if ($cb->service==CALLBACK_REPLY)
                $okreply=true;
        }
    }

    pageone_soap_logout($session);
    if ($okreply && $okdelivery)
        return true;
    else
        return false;
}

/**
* This method tries to automatically install/fix the callback settings on the PageOne server when it detects that they are missing
**/

function pageone_fix_callbacks()
{
 $callbacks=pageone_get_callback_list();

 if (count($callbacks)>0)
  return false;

 pageone_set_callback();
 return pageone_check_callbacks();
}

/**************************Debug helper methods*******************************/

/**
* Result checking method
* @param $client The SOAP client proxy to check
* @param $result The result object generated by the request (should only contain errors) 
**/

function check_result($proxy, $result)
{
   if (!IS_DEBUGGING)
       return;

   echo '<h3>Sending</h3><textarea cols="100" rows="20">'.$proxy->request.'</textarea><br />'.
        '<h3>Response</h3><textarea cols="100" rows="20">'.$proxy->response.'</textarea><br />';

   if ($proxy->fault)
   {
       echo '<h2>Fault</h2><pre>';
       print_r($result);
       echo '</pre>';
   }
   else
       check_error($proxy, "Send error");

   echo "<p>Sending done</p>";
}

/**
* Checks client for errors
* @param $client The SOAP client to check
* @param $message A message to print when an error is found
**/

function check_error($client, $message)
{
    $err = $client->getError();
    if ($err)
    {
        echo '<h2>'.$message.'</h2><pre>'.$err.'</pre>';
        return true;
    }
    return false;
}

/***************************Misc helper methods**********************************/

/**
* This function tries to find a course in which this user is allowed to use MoodleMobile and of which the recipient is a member
* @param $to The id of the user the message is to
* @param $from The id of the user the message is from
**/

function pageone_find_course($to, $from, $noerror=false)
{
    $courses=get_user_capability_course('block/pageone:cansend', $from);
    if (count($courses)==0)
     if ($noerror==false)
        error(get_string('no_permission', 'block_pageone'));
     else
        return null;

    foreach ($courses as $course)
    {
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        if (has_capability('moodle/course:view', $context, $to))
            return $course->id;
    }
    return 1;
}

function pageone_get_user($id)
{
    global $DB;
    $user=$DB->get_record("user", array("id"=>$id));

    if (!$user)
    {
        $user=new stdclass;
        $user->username=get_string("user_not_found", "block_pageone");
        $user->firstname=get_string("user_not_found", "block_pageone");
        $user->lastname=" (id=".$id.")";
        $user->id=$id;
    }

    return $user;
}

?>
