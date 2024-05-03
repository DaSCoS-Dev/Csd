<?php
use SKien\PNServer\PNVapid;

function getMyVapid()
{
    /**
	 * set the generated VAPID key and rename to MyVapid.php
	 *
	 * you can generate your own VAPID key on https://tools.reactpwa.com/vapid.
	 */
     $oVapid = new PNVapid(
 "mailto:newsletter@fattureweb.com",
  "BPrk_rINSQJMF0scQJQYawQde-6ZmkHggNXRMF3BkwFldczITrEozkytd_WwSwzhI4xgTM4p7BZvSGcmXC8ub3M",
  "o8fMXbnqiCBySi7XFKRC087fhdRJ1mNcoydocTAuJz8"
     );
    return $oVapid;    
}