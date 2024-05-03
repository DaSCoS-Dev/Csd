<?php
function waypoint_handler($sWaypointName, $sWaypointData)
{
    $objResponse = new xajaxResponse();
    if ($sWaypointName!='')
    {
        $aWaypointData = decodeWaypointData($sWaypointData);

        //TODO: recover your content using the parameters

        $objResponse->assign("content", "innerHTML", "YOUR RECOVERED CONTENT");
        //or maybe
        $objResponse->script("xajax_topmenu('".$aWaypointData[0]."')");
    }
    return $objResponse;
}
?>