<?php
 
namespace app\common\behavior;
use think\Exception;
use think\Response;


class Cors
{
    public function run(&$params)
    {
        header("Access-Control-Allow-Origin:*");
		header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Authorization,Content-Type,Accept,Origin,User-Agent,DNT,Cache-Control,X-Mx-ReqToken,X-Data-Type,X-Requested-With,X-Data-Type,X-Auth-Token,X-Api-Key,Merchant-Id,Device-id,Device-Name,Width,Height,Os,Os-Version,Is-Root,Network,Wifi-Ssid,Wifi-Mac,Xyz,Version-Name,Api-Version,Channel,App-Name,Dpi,Api-Level,Operator,Idfa,Idfv,Open-Udid,Wlan-Ip,Time");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
		
        if (request()->isOptions()) {
            exit();
        }
    }
}