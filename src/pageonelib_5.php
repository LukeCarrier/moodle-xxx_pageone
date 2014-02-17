<?php

/**
 * pageonelib - Contains PHP 5 dependent functions for pageonelib
 * 
 * This code is licenced under the GNU GPLv2 licence (please see gpl.txt for details) and is
 * copyright to PageOne
 *
 * @author Tim Williams (tmw@autotrain.org) for PageOne
 * @package pageone
 **/

/****
* Sets up the SOAP client and logins into pageone server
* @return The Session ID or null if the login failed
**/

function pageone_soap_login()
{
    if (IS_DEBUGGING)
        echo 'Start login<br />';

    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $loginRequest=new DOMElement("m:loginRequest", "", SOAP_NS);
    $doc->appendChild($loginRequest);

    $user_id=new DOMElement("user-id", mdl21_getconfigparam('block_pageone', 'account_num'));
    $loginRequest->appendChild($user_id);

    $pwd=new DOMElement("pwd", mdl21_getconfigparam('block_pageone', 'account_pass'));
    $loginRequest->appendChild($pwd);

    $result = pageone_send_soap('', $doc->saveXML($loginRequest), "login");
    if ($result==null)
        return null;
    else
    {
        $status=$result->getElementsByTagName("status");
        if ($status->item(0) && $status->item(0)->nodeValue=="200")
        {
            $iddoc=$result->getElementsByTagName("session-id");
            if (IS_DEBUGGING)
                echo "session=".$iddoc->item(0)->nodeValue."<br />";
            return $iddoc->item(0)->nodeValue;
        }
        else
            return null;
    }
}

/*
* Logoff from the PageOne SOAP server
* @param $session The session ID
**/

function pageone_soap_logout($session)
{
    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $logoffRequest=new DOMElement("m:logoffRequest", "", SOAP_NS);
    $doc->appendChild($logoffRequest);

    $result = pageone_send_soap(pageone_get_session_header($session), $doc->saveXML($logoffRequest), "logoff");
    if ($result==null)
        return false;
    else
    {
        $status=$result->getElementsByTagName("result");
        if ($status->item(0)->nodeValue=="1")
            return true;
        else
            return false;
    }
}

/**
* Sends a text message to the specified phone numbers
* @param $session The session ID
* @param $numbers An array of the phone number(s) to send to
* @param $messageText A string of the message to send
* @param $from The sending address
* @return the SOAP response obeject
**/

function pageone_soap_send_message($session, $numbers, $messageText, $from, $user_map)
{
    if (count($numbers<100))
    {
        return pageone_soap_send_message_2($session, $numbers, $messageText, $from, $user_map);
    }   
    else
    {
        $consolidated_response=new stdclass;
        $consolidated_response->ok=true;
        $consolidated_response->failednumbers="";

        for ($loop=0; $loop<count($numbers); $loop=$loop+100)
        {
            $split_nums=array_slice($loop, 100);
            $response=pageone_soap_send_message_2($session, $split_nums, $messageText, $from, $user_map);
            if ($response->ok)
                $consolidated_response->failednumbers.=$response->failednumbers;
            else
                $consolidated_response->ok=false;
        }
        return $consolidated_response;
    }
}

function pageone_soap_send_message_2($session, $numbers, $messageText, $from, $user_map)
{
    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $sendMessageRequest=new DOMElement("m:sendMessageRequest", "", SOAP_NS);
    $doc->appendChild($sendMessageRequest); 

    foreach ($numbers as $number)
    {
        $address=new DOMElement("destinationAddress", $number);
        $sendMessageRequest->appendChild($address);
    }

    $message=new DOMElement("message");
    $sendMessageRequest->appendChild($message);
    $message->appendChild(new DOMText($messageText)); 

    if (strlen($from)>0)
    {
        $sendMessageRequest->appendChild(new DOMElement("sourceAddress", $from));
    }
    else
    {
        if (mdl21_configparamisset('block_pageone' ,'alpha_tag') && strlen(mdl21_getconfigparam('block_pageone', 'alpha_tag'))>0)
        {
            $sendMessageRequest->appendChild(new DOMElement("sourceAddress", mdl21_getconfigparam('block_pageone', 'alpha_tag')));
        }
    }

    //$expireDay  = mktime(date("H"), date("i"), date("s"), date("m")  , date("d")+2, date("Y"));
    //$expireAt=new DOMElement("expireAt", date("Y-m-d\TH:i:s\Z", $expireDay));
    //$sendMessageRequest->appendChild($expireAt);

    //$deliverAt=new DOMElement("deliverAt", date("Y-m-d\TH:i:s\Z"));
    //$sendMessageRequest->appendChild($deliverAt);

    $result = pageone_send_soap(pageone_get_session_header($session), $doc->saveXML($sendMessageRequest), "sendMessage");
    $response=new stdclass;
    $response->ok=false;
    if ($result==null)
        return $response;
    else
    {
        /**No longer used**
        $faultcodes=$result->getElementsByTagName("faultcode");
        if ($faultcodes->length>0)
        {
            $response->faultcode=$faultcodes->item(0)->nodeValue;
            $faultstrings=$result->getElementsByTagName("faultstring");
            $response->faultstring=$faultstrings->item(0)->nodeValue;
            return $response;
        }
        ****/

        $failednumbers="";
        $response->ok=true;
        $response->id=$result->getElementsByTagName("transactionID")->item(0)->nodeValue;
        //$response->id=$result->getElementsByTagName("id")->item(0)->nodeValue;

        $status_list=$result->getElementsByTagName("status");
        //If anything has a status higher than 399, then something went wrong

        for($loop=0; $loop<$status_list->length; $loop++)
        {
            $status=$status_list->item($loop);
            if (intval($status->nodeValue)>399)
            {
                $response->ok=false;
                $failednumbers.=$user_map[$status->getAttribute('description')].','.$status->nodeValue.',';
            }
        }

        $response->failednumbers=$failednumbers;
        return $response;
    }
}

/**
* Gets the Session header XML
* @param $session The Session ID
* @return The session header XML as a string
**/

function pageone_get_session_header($session)
{
    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $p1Header=new DOMElement("m:pageoneHeader", "", SOAP_NS);
    $doc->appendChild($p1Header);

    $session_id=new DOMElement("session-id", $session);
    $p1Header->appendChild($session_id); 

    return $doc->saveXML($p1Header);
}

/**
* Gets the available account credit
* @return account credit
**/

function pageone_available_credit()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $getCreditsRequest=new DOMElement("m:getCreditsRequest", "", SOAP_NS);
    $doc->appendChild($getCreditsRequest);

    $result=pageone_send_soap(pageone_get_session_header($session), $doc->saveXML($getCreditsRequest), "getCredits");
    pageone_soap_logout($session);

    if ($result==null)
        return -1;
    else
    {
        $credits=$result->getElementsByTagName("creditsRemaining");
        return $credits->item(0)->nodeValue;
    }
}

/**
* Returns a list of the alphatags which are valid for this PageOne account
* @return An array of the alphatags
**/

function pageone_get_alphatags()
{
    $session=pageone_soap_login();
    if ($session==null)
        return PAGEONE_LOGIN_FAILED;

    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $msisdnRequest=new DOMElement("m:getMsisdnRequest", "", SOAP_NS);
    $doc->appendChild($msisdnRequest);

    $result=pageone_send_soap(pageone_get_session_header($session), $doc->saveXML($msisdnRequest), "getMsisdn");
    pageone_soap_logout($session);

    if ($result==null)
        return array();
    else
    {
        $msisdns=$result->getElementsByTagName("msisdn");
        $items=array();
        for ($i = 0; $i < $msisdns->length; $i++)
           $items[$i]=$msisdns->item($i)->nodeValue;

        return $items;
    }
}

/**
* Creates an XML document object from the supplied data
* @param $xmldata The XML to process
* @return The XML document
**/

function pageone_get_xml_document($xmldata)
{
    $doc=new DomDocument();
    $doc->loadXML($xmldata);
    return $doc;
}

/*****Callback methods*****/
/*****These methods are deprecated and may be removed in the future*****/

/**
* Gets the Session header XML
* @param $session The Session ID
* @return The session header XML as a string
**/

function pageone_get_callback_session_header($session)
{
    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $pageoneHeader=new DOMElement("m:pageoneHeader", "", SOAP_CALLBACK_NS);
    $doc->appendChild($pageoneHeader);

    $session_id=new DOMElement("session-id", $session);
    $pageoneHeader->appendChild($session_id); 

    return $doc->saveXML($pageoneHeader);
}

/**
* Adds a call back service URL to the PageOne server
* @param $session The server session to use
* @param $host The host URL to add
* @param $request soap element name for the call back type should be "deliveryReportListenerRequest" or
*                 "receivedMessageListenerRequest"
* @param $method soap method to call. Should be "registerDeliveryReportListener" or "registerReceivedMessageListener"
* @return $object->value=pageone response code $object->text=Textual description of the response
**/

function pageone_add_callback_service($session, $host, $request, $method)
{
    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $addCallBackRequest=new DOMElement("m:".$request, "", SOAP_CALLBACK_NS);
    $doc->appendChild($addCallBackRequest);

    $wsdlUrl=new DOMElement("wsdlUrl", $host); 
    $addCallBackRequest->appendChild($wsdlUrl);

    $result = pageone_send_soap(pageone_get_callback_session_header($session), $doc->saveXML($addCallBackRequest), $method, SOAP_CALLBACK_URL, SOAP_CALLBACK_NS);

    return pageone_check_callback_response($result);
}

/**
* Removes a call back service URL to the PageOne server
* @param $session The server session to use
* @param $entryID The id of the callback to remove
* @return $object->value=pageone response code $object->text=Textual description of the response
**/

function pageone_remove_callback($session, $entryID)
{
    if (IS_DEBUGGING)
        echo "Removing callback ".$entryID."<br />";

    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $removeCallBackRequest=new DOMElement("m:unregisterListenerRequest", "", SOAP_CALLBACK_NS);
    $doc->appendChild($removeCallBackRequest);

    $id=new DOMElement("id", $entryID); 
    $removeCallBackRequest->appendChild($id);

    $result = pageone_send_soap(pageone_get_callback_session_header($session), $doc->saveXML($removeCallBackRequest), "unregisterListener", SOAP_CALLBACK_URL, SOAP_CALLBACK_NS);

    return pageone_check_callback_response($result);
}

/**
* Checks the callback add/remove call response
* @param $result The callback response
* @param $entryID The id of the callback to remove
* @return $object->value=pageone response code $object->text=Textual description of the response
**/

function pageone_check_callback_response($result)
{
    if ($result==null)
    {
        $data->value=-1;
        $data->text=get_string("server_fail", "block_pageone");
        return $data;
    }
    else
    {
        $status=$result->getElementsByTagName("status");
        $data->value=intval($status->item(0)->nodeValue);
        $data->text=$status->item(0)->attributes->getNamedItem("description")->value;
        return $data;
    }
}

/**
* Lists the callback services registered on the PageOne server
* @param $session The server session to use
* @return An Array of values in the following format $a[$index]->entryID=The callback ID
*         $a[$index]->service=The callback type, should be CALLBACK_REPLY or CALLBACK_DELIVERY
*         $a[$index]->host=The registered URL
**/

function pageone_list_callbacks($session)
{
    if (IS_DEBUGGING)
        echo "Listing callbacks<br />";

    $doc=new DOMDocument('1.0');
    $doc->formatOutput = IS_DEBUGGING;

    $listRequest=new DOMElement("m:listListenersRequest", "", SOAP_CALLBACK_NS);
    $doc->appendChild($listRequest);

    $result = pageone_send_soap(pageone_get_callback_session_header($session),
        $doc->saveXML($listRequest), "listListeners", SOAP_CALLBACK_URL, SOAP_CALLBACK_NS);

    if ($result==null)
        return array();
    else
    {
        $items=array();
        $services=$result->getElementsByTagName("listenerWsdlUrl");
        for ($loop=0; $loop< $services->length; $loop++)
        {
             $service=$services->item($loop);
             $items[$loop]->entryID=$service->getAttribute("id");
             if ($service->getAttribute("type")=="InboundListener")
                 $items[$loop]->service=CALLBACK_REPLY;
             else
             if ($service->getAttribute("type")=="DeliveryRecieptListener")
                 $items[$loop]->service=CALLBACK_DELIVERY;

             $items[$loop]->host=$service->nodeValue;
        }
        return $items;
    }

}


?>
