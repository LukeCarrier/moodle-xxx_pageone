<?php
/**
* These are the IP addresses of the PageOne Oventus/SOAP servers. Restricting access to these IP's
* should prevent third parties from injecting unauthorised messages into the system
* @author Tim Williams (tmw@autotrain.org) for PageOne
**/

$ALLOWED_IPS=array("195.157.52.220", "91.220.160.70", "91.220.160.69", "91.220.160.197", "91.220.160.198");

if (DEBUG_LOG)
{
    array_push($ALLOWED_IPS, "83.170.97.106");
    array_push($ALLOWED_IPS, "147.188.192.41");
    array_push($ALLOWED_IPS, "195.157.52.215");
}
?>
