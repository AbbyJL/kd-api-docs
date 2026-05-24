<?php

use think\Db;
use think\Request;
use think\Response;
use app\admin\controller\Auth;
use think\Lang;

use app\common\model\Setting as SettingModel;



function getConfigKey($key = 'site'){
   $result = Db::name('setting')->where(array('k'=>$key))->find();
	$data = unserialize($result['v']);
    return $data;
}

function getHost($key = 'site'){
    return 'https://demo8.jintaocms.top';
}


//设置cookie
function setUid($user_id,$last_login_time){
	$cookie = jiami("{$user_id}.{$last_login_time}");
	$session = jiami("{$user_id}.{$last_login_time}");
    cookie('JINTAOCMS_TOKEN',$cookie,3600*72*3); //存2小时
	session('JINTAOCMS_TOKEN',$cookie,3600*72*3); //存2小时
    return true;
}
//清除cookie
function clearUid(){
    cookie('JINTAOCMS_TOKEN',null);
	cookie('subscribe',null);

	session('JINTAOCMS_TOKEN',null);
	session('subscribe',null);

    return true;
}


//根据cookie获取uid
function getuid(){
	$cookie = cookie('JINTAOCMS_TOKEN');
	if(!$cookie){
		$cookie = session('JINTAOCMS_TOKEN');
	}

	$cookie = explode(".", jiemi($cookie));

	$uid = empty($cookie[0])?0:$cookie[0];
	return $uid;
}

//加密函数
function jiami($txt, $key = null){
	empty($key) && $key = config('data_auth_key');
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
	$nh = rand(0, 64);
	$ch = $chars[$nh];
	$mdKey = md5($key . $ch);
	$mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
	$txt = base64_encode($txt);
	$tmp = '';
	$k = 0;
	for ($i = 0; $i < strlen($txt); $i++) {
		$k = $k == strlen($mdKey) ? 0 : $k;
		$j = ($nh + strpos($chars, $txt [$i]) + ord($mdKey[$k++])) % 64;
		$tmp .= $chars[$j];
	}
	return $ch . $tmp;
}

//解密函数
function jiemi($txt, $key = null){
	empty($key) && $key = config('data_auth_key');
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=_";
	$ch = $txt[0];
	$nh = strpos($chars, $ch);
	$mdKey = md5($key . $ch);
	$mdKey = substr($mdKey, $nh % 8, $nh % 8 + 7);
	$txt = substr($txt, 1);
	$tmp = '';
	$k = 0;
	for ($i = 0; $i < strlen($txt); $i++) {
		$k = $k == strlen($mdKey) ? 0 : $k;
		$j = strpos($chars, $txt[$i]) - $nh - ord($mdKey[$k++]);
		while ($j < 0) {
			$j += 64;
		}
		$tmp .= $chars[$j];
	}
	return base64_decode($tmp);
}

/**
 * 获取域名
 * @param string $https 是不是开启https
 * @return string  返回带http或者https的完整域名
 */
function getServerHttpHost($https){
	if($https == 1){
		return 'https://' . $_SERVER['HTTP_HOST'];
	}else{
		return 'http://' . $_SERVER['HTTP_HOST'];
	}
	return 'http://' . $_SERVER['HTTP_HOST'];//默认返回
}

/**
 * 是否存在方法
 * @param string $module 模块
 * @param string $controller 待判定控制器名
 * @param string $action 待判定控制器名
 * @return number 方法结果，0不存在控制器 1存在控制器但是不存在方法 2存在控制和方法
 */
function has_action($module,$controller,$action)
{
	$arr=\ReadClass::readDir(APP_PATH . $module. DS .'controller');
    if((!empty($arr[$controller])) && $arr[$controller]['class_name']==$controller ){
		$method_name=array_map('array_shift',$arr[$controller]['method']);
        if(in_array($action, $method_name)){
           return 2;
        }else{
           return 1;
        }
    }else{
        return 0;
    }
}
/**
 * 获取客户端操作系统信息包括win10
 * @author  Jea杨
 * @return string
 */
function getOs(){
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/win/i', $agent) && strpos($agent, '95')){
		$os = 'Windows 95';
	}else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')){
		$os = 'Windows ME';
	}else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)){
		$os = 'Windows 98';
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)){
		$os = 'Windows Vista';
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)){
		$os = 'Windows 7';
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)){
		$os = 'Windows 8';
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)){
		$os = 'Windows 10';#添加win10判断
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)){
		$os = 'Windows XP';
	}else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)){
		$os = 'Windows 2000';
	}else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)){
		$os = 'Windows NT';
	}else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)){
		$os = 'Windows 32';
	}else if (preg_match('/linux/i', $agent)){
		$os = 'Linux';
	}else if (preg_match('/unix/i', $agent)){
		$os = 'Unix';
	}else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)){
		$os = 'SunOS';
	}else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)){
		$os = 'IBM OS/2';
	}else if (preg_match('/Mac/i', $agent)){
		$os = 'Mac';
	}else if (preg_match('/PowerPC/i', $agent)){
		$os = 'PowerPC';
	}else if (preg_match('/AIX/i', $agent)){
		$os = 'AIX';
	}else if (preg_match('/HPUX/i', $agent)){
		$os = 'HPUX';
	}else if (preg_match('/NetBSD/i', $agent)){
		$os = 'NetBSD';
	}else if (preg_match('/BSD/i', $agent)){
		$os = 'BSD';
	}else if (preg_match('/OSF1/i', $agent)){
		$os = 'OSF1';
	}else if (preg_match('/IRIX/i', $agent)){
		$os = 'IRIX';
	}else if (preg_match('/FreeBSD/i', $agent)){
		$os = 'FreeBSD';
	}else if (preg_match('/teleport/i', $agent)){
		$os = 'teleport';
	}else if (preg_match('/flashget/i', $agent)){
		$os = 'flashget';
	}else if (preg_match('/webzip/i', $agent)){
		$os = 'webzip';
	}else if (preg_match('/offline/i', $agent)){
		$os = 'offline';
	}elseif (preg_match('/ucweb|MQQBrowser|J2ME|IUC|3GW100|LG-MMS|i60|Motorola|MAUI|m9|ME860|maui|C8500|gt|k-touch|X8|htc|GT-S5660|UNTRUSTED|SCH|tianyu|lenovo|SAMSUNG/i', $agent)) {
		$os = 'mobile';
	}else{
		$os = '未知操作系统';
	}
	return $os;
}

/**
 * 获取客户端浏览器信息 添加win10 edge浏览器判断
 * @author  Jea杨
 * @return string
 */


function getBroswer(){
	$sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
	if (stripos($sys, "Firefox/") > 0) {
		preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
		$exp[0] = "Firefox";
		$exp[1] = $b[1];  //获取火狐浏览器的版本号
	} elseif (stripos($sys, "Maxthon") > 0) {
		preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
		$exp[0] = "傲游";
		$exp[1] = $aoyou[1];
	} elseif (stripos($sys, "MSIE") > 0) {
		preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
		$exp[0] = "IE";
		$exp[1] = $ie[1];  //获取IE的版本号
	} elseif (stripos($sys, "OPR") > 0) {
		preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
		$exp[0] = "Opera";
		$exp[1] = $opera[1];
	} elseif (stripos($sys, "Edge") > 0) {
		//win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
		preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
		$exp[0] = "Edge";
		$exp[1] = $Edge[1];
	} elseif (stripos($sys, "Chrome") > 0) {
		preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
		$exp[0] = "Chrome";
		$exp[1] = $google[1];  //获取google chrome的版本号
	} elseif (stripos($sys, 'rv:') > 0 && stripos($sys, 'Gecko') > 0) {
		preg_match("/rv:([\d\.]+)/", $sys, $IE);
		$exp[0] = "IE";
		$exp[1] = $IE[1];
	} elseif (stripos($sys, 'Safari') > 0) {
		preg_match("/safari\/([^\s]+)/i", $sys, $safari);
		$exp[0] = "Safari";
		$exp[1] = $safari[1];
	} else {
		$exp[0] = "未知浏览器";
		$exp[1] = "";
	}
	return $exp[0] . '(' . $exp[1] . ')';
}

//设置全局配置到文件
function sys_config_setbykey($key, $value){
    $file = ROOT_PATH.'data/conf/config.php';
    $cfg = array();
    if (file_exists($file)) {
        $cfg = include $file;
    }
    $item = explode('.', $key);
    switch (count($item)) {
        case 1:
            $cfg[$item[0]] = $value;
            break;
        case 2:
            $cfg[$item[0]][$item[1]] = $value;
            break;
    }
    return file_put_contents($file, "<?php\nreturn " . var_export($cfg, true) . ";");
}


//设置全局配置到文件
function sys_config_setbyarr($data){
    $file = ROOT_PATH.'data/conf/config.php';
    if(file_exists($file)){
        $configs=include $file;
    }else {
        $configs=array();
    }
    $configs=array_merge($configs,$data);
    return file_put_contents($file, "<?php\treturn " . var_export($configs, true) . ";");
}


//获取全局配置
function sys_config_get($key){
    $file = ROOT_PATH.'data/conf/config.php';
    $cfg = array();
    if (file_exists($file)) {
        $cfg = (include $file);
    }
    return isset($cfg[$key]) ? $cfg[$key] : null;
}
//返回带协议的域名
function get_host(){
    $host=$_SERVER["HTTP_HOST"];
    $protocol=Request::instance()->isSsl()?"https://":"http://";
    return $protocol.$host;
}



//根据用户id获取用户组,返回值为数组
function get_groups($uid){
    $auth = new Auth();
    $group = $auth->getGroups($uid);
    return $group[0]['title'];
}



//josn转化为array
function object2array_pre(&$object) {
        if (is_object($object)) {
            $arr = (array)($object);
        } else {
            $arr = &$object;
        }
        if (is_array($arr)) {
            foreach($arr as $varName => $varValue){
                $arr[$varName] = object2array($varValue);
            }
        }
        return $arr;
    }
	//josn转化为array
function object2array(&$object) {
      $object =  json_decode( json_encode( $object),true);
      return  $object;
}

//判断索引数组
 function is_assoc($array) {
        if(is_array($array)) {
            $keys = array_keys($array);
            return $keys != array_keys($keys);
        }
        return false;
    }




//判断中文姓名
function isChineseName($name){
	if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name)) {
		return true;
	} else {
		return false;
	}
}
//判断输入的字符串是否是一个合法的电话号码（仅限中国大陆）
function isPhone($string) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('/^1[3456789]{1}\d{9}$/', $mobile) ? true : false;
}

//判断手机号
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('/^1[3456789]{1}\d{9}$/', $mobile) ? true : false;
}
//微信访问
function is_weixin() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
}



function isWx() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
}
function isBSL() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'bsl');
}
function isWx2() {
	return strpos($_SERVER['HTTP_USER_AGENT'], 'bsl/1.0 MicroMessenger');
}
function isApp() {
   return strpos($_SERVER['HTTP_USER_AGENT'], 'Html5Plus/1.0');
}

//小程序访问
function is_miniprogram(){
	$miniprogram = strpos($_SERVER['HTTP_USER_AGENT'], 'miniprogram');
	if(!$miniprogram){
		$miniprogram = strpos($_SERVER['HTTP_USER_AGENT'], 'miniProgram');
	}
    return $miniprogram;
}

//QQ访问
function is_QQBrowser() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'QQBrowser');
}
//判断邮箱
function isMail($email) {
	$pattern = "/^[a-zA-Z][a-zA-z0-9-]*[@]([a-zA-Z0-9]*[.]){1,3}[a-zA-Z]*/";
	if(preg_match($pattern,$email)!= 1){
		return false;
	}else{
		return true;
	}
}
//判断IOS设备
function isIos(){
	$is_iphone = (strpos($agent, 'iphone')) ? true : false;
	$is_ipad = (strpos($agent, 'ipad')) ? true : false;
	if($is_iphone==true || $is_ipad == true){
		return true;
	}else{
		return false;
	}
}

 // 自动转换字符集 支持数组转换
function auto_charset($fContents, $from = 'gbk', $to = 'utf-8') {
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset($key, $from, $to);
            $fContents[$_key] = auto_charset($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    }
    else {
        return $fContents;
    }
}

//发送短信时候需要的
function tmplToStr($str, $datas) {
    preg_match_all('/{(.*?)}/', $str, $arr);
    foreach ($arr[1] as $k => $val) {
        $v = isset($datas[$val]) ? $datas[$val] : '';
        $str = str_replace($arr[0][$k], $v, $str);
    }
    return $str;
}




include(ROOT_PATH . 'extend/phpqrcode/phpqrcode.php' );//引入二维码生成图片
//新版生成二维码
function ToQrCode($token,$url,$size,$patchs=''){
	$config = SettingModel::config();
	$name = date('Y/m/d/',time());
	$md5 = md5($token);
	if($patchs){
		//如果有定义
		$patch =ROOT_PATH.'/attachs/'.$patchs.'/'.$name;
	}else{
		$patch =ROOT_PATH.'/attachs/'.'weixin/'.$name;
	}
    if(!file_exists($patch)){
        mkdir($patch,0755,true);
    }
	if($patchs){
		//如果有定义
		$file = '/attachs/'.$patchs.'/'.$name.$md5.'.png';
    	$fileName  = ROOT_PATH.''.$file;
	}else{
		$file = '/attachs/weixin/'.$name.$md5.'.png';
    	$fileName  =ROOT_PATH.''.$file;
	}
    if(!file_exists($fileName)){
        $level = 'L';
        if(strstr($url,$config['site']['host'])){
            $data = $url;
        }else{
            $data =$config['site']['host']. $url;
        }

        QRcode::png($data, $fileName, $level, $size,2,true);
    }
    return $file;
}


//生成二维码特殊情况下，一般微信用
function buildCode($token,$url2){
	$config = SettingModel::config();
	$name = date('Y/m/d/',time());
	$md5 = md5($token);
	$patch =ROOT_PATH.'/attachs/'.'weixin/'.$name;
	if(!file_exists($patch)){
		mkdir($patch,0755,true);
	}
	$file = '/attachs/weixin/'.$name.$md5.'.png';
	$fileName  =ROOT_PATH.''.$file;
	if(!file_exists($fileName)){
		$level = 'L';
		QRcode::png($url2,$fileName,$level,$size = 8,2,true);
	}
	return $file;
}




//验证手机
function is_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi",
    "android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio",
    "au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu",
    "cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ",
    "fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi",
    "htc","huawei","hutchison","inno","ipad","ipaq","iphone","ipod","jbrowser","kddi",
    "kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo",
    "mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-",
    "moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia",
    "nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-",
    "playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo",
    "samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank",
    "sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit",
    "tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin",
    "vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce",
    "wireless","xda","xde","zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}

//验证邮箱函数
function is_valid_email($email, $test_mx = false){
    if(eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
        if($test_mx){
            list($username, $domain) = split("@", $email);
            return getmxrr($domain, $mxrecords);
        }
        else
            return true;
    else
        return false;
}



function isSecondDomain($domain){
    return (boolean) preg_match('/^[a-z0-9]{4,10}$/i', $domain);
}


function secsToStr($secs){
    if($secs>=86400){$days=floor($secs/86400);
    $secs=$secs%86400;
    $r=$days.' 天';
    if($days<>1){$r.='';}
    if($secs>0){$r.=', ';}}
    if($secs>=3600){$hours=floor($secs/3600);
    $secs=$secs%3600;
    $r.=$hours.' 时';
    if($hours<>1){$r.='';}
    if($secs>0){$r.=', ';}}
    if($secs>=60){$minutes=floor($secs/60);
    $secs=$secs%60;
    $r.=$minutes.' 分';
    if($minutes<>1){$r.='';}
    if($secs>0){$r.=', ';}}
    $r.=$secs.' 秒';
    if($secs<>1){$r.='';
    }
    return $r;
}


//时间格式化2
function formatTime($time){

    $t = time() - $time;
    $mon = (int) ($t / (86400 * 30));
	if($mon >= 12){
        return '一年前';
    }
	if($mon >= 6){
        return '半年前';
    }
	if($mon >= 3){
        return '三个月前';
    }
	if($mon >= 2){
        return '二个月前';
    }
    if($mon >= 1){
        return '一个月前';
    }
    $day = (int) ($t / 86400);
    if($day >= 1){
        return $day . '天前';
    }
    $h = (int)($t / 3600);
    if($h >= 1) {
        return $h . '小时前';
    }
    $min = (int)($t / 60);
    if($min >= 1){
        return $min . '分前';
    }
    return '刚刚';
}

//时间格式化2
function pincheTime($time){
	  $today  =  strtotime(date('Y-m-d')); //今天零点
      $here  =  (int)(($time - $today)/86400) ;
	  if($here==1){
		  return '明天';
	  }
	  if($here==2){
		  return '后天';
	  }
	  if($here>=3 && $here<7){
		  return $here.'天后';
	  }
	  if($here>=7 && $here<30){
		  return '一周后';
	  }
	  if($here>=30 && $here<365){
		  return '一个月后';
	  }
	  if($here>=365){
		  $r = (int)($here/365).'年后';
		  return   $r;
	  }
	 return '今天';
}

//时间格式化2
function eleWaitTime($time){
	if(!$time){
        return '未知错误';
    }
    $mon = (int) ($time / (86400 * 30));
	if($mon >= 12){
        return '一年前';
    }
	if($mon >= 6){
        return '半年前';
    }
	if($mon >= 3){
        return '三个月前';
    }
	if($mon >= 2){
        return '二个月前';
    }
    if($mon >= 1){
        return '一个月前';
    }
    $day = (int)($time / 86400);
    if($day >= 1){
        return $day . '天';
    }
    $h = (int) ($time / 3600);
    if($h >= 2){
        return $h . '小时';
    }
    $min =(int)($time / 60);
    if($min >= 1){
        return $min . '分钟';
    }
    return '0分钟';
}
/*
 * 经度纬度 转换成距离
 * $lat1 $lng1 是 数据的经度纬度
 * $lat2,$lng2 是获取定位的经度纬度
 */

function rad($d) {
    return $d * 3.1415926535898 / 180.0;
}

function getDistanceNone($lat1, $lng1, $lat2, $lng2) {
    $EARTH_RADIUS = 6378.137;
    $radLat1 = rad($lat1);
    //echo $radLat1;
    $radLat2 = rad($lat2);
    $a = $radLat1 - $radLat2;
    $b = rad($lng1) - rad($lng2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * $EARTH_RADIUS;
    $s = round($s * 10000);
    return $s;
}


//转换地图
function getDistance($lat1, $lng1, $lat2, $lng2){
	$config = SettingModel::config();
	if($config['config']['map']  == 1){
		//百度地图
		$s = getDistanceNone($lat1, $lng1, $lat2, $lng2);
		$s = $s / 10000;
		if($s < 1){
			$s = round($s * 1000);
			$s.='m';
		}else{
			$s = round($s, 2);
			$s.='km';
		}
		return $s;
	}else{
		//高德地图
	   $len_type = 1; //长度
	   $decimal = 2;//精度
	   $EARTH_RADIUS = 6378.137;
	   $earth = 6378.137;
       $pi = 3.1415926;
       $radLat1 = $lat1 * PI ()/ 180.0;   //PI()圆周率
       $radLat2 = $lat2 * PI() / 180.0;
       $a = $radLat1 - $radLat2;
       $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
       $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
       $s = $s * $EARTH_RADIUS;
       $s = round($s * 1000);
       if($len_type > 1){
           $s /= 1000;
       }
	   $mi = round($s,$decimal);
	   $mi = $mi > 1000? round($mi/1000,2).'km':$mi.'m'; //计算到商家的定位
       return $mi;
	}


}

function getDistanceCN($lat1, $lng1, $lat2, $lng2) {
    $s = getDistanceNone($lat1, $lng1, $lat2, $lng2);
    $s = $s / 10000;
    if ($s < 1) {
        $s = round($s * 1000);
        $s.='米';
    } else {
        $s = round($s, 2);
        $s.='千米';
    }
    return $s;
}




function arrayToObject($e) {
    if (gettype($e) != 'array')
        return;
    foreach ($e as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object')
            $e[$k] = (object) arrayToObject($v);
    }
    return (object) $e;
}

function objectToArray($e) {
    $e = (array) $e;
    foreach ($e as $k => $v) {
        if (gettype($v) == 'resource')
            return;
        if (gettype($v) == 'object' || gettype($v) == 'array')
            $e[$k] = (array) objectToArray($v);
    }
    return $e;
}


//获取链接

function LinkTo($ctl, $vars = array(),$var2=array()) {
    $vars = array_merge($vars,$var2);
    foreach ($vars as $k => $v) {
        if (empty($v))
            unset($vars[$k]);
    }
    return url($ctl, $vars);
}

//获取IP返回地址的函数,哈土豆重新构架
function IpToArea($_ip) {
    static $IpLocation;
    if(empty($IpLocation)){
       $IpLocation = new \IpLocation('UTFWry.dat');
    }
    $arr = $IpLocation->getlocation($_ip);
    return $arr['country'] . $arr['area'];
}

//分站授权
function BA($url = '', $vars = '', $title = '', $mini = "", $class = "", $width = '', $height = ''){
	$config = SettingModel::config();//调用全局
	static $admin;
    if(empty($admin)){
		$admin = session('admin');
		if(!$admin){
			$admin = cookie('admin');
			if($admin){
				$admin = @json_decode($admin,true);
			}
		}
		$admin['menu_list'] = model('RoleMaps')->getMenuIdsByRoleId($admin['role_id']);
    }
	if($admin['role_id'] != 1){
        $menu = model('Menu')->fetchAll();
        $menu_id = 0;
        foreach($menu as $k => $v){
            if(strtolower($v['menu_action']) == strtolower($url)){
                $menu_id = (int) $k;
            }
        }
        if(empty($menu_id) || !isset($admin['menu_list'][$menu_id])){


            $title = '未授权【'.$url.'】';
			$url = 'javascript:void(0);';
            $mini = '';
        }else{
            $url = url($url, $vars);
        }
    }else{
        $url = url($url, $vars);
    }

    //权限判断 暂时忽略，后面补充
    $m = $c = $h = $w = '';
    if(!empty($mini)){
        $m = ' mini="' . $mini . '"  ';
    }
    if(!empty($class)){
        $c = ' class="' . $class . ' " ';
    }
    if(!empty($width)){
        $w = ' w="' . $width . ' " ';
    }
    if(!empty($width)){
        $h = ' h="' . $height . ' " ';
    }
    return '<a data-menu-action="'.$menu_action.'"  data-menu-name="'.$menu_name.'" href="' . $url . '" ' . $m . $c . $w . $h . ' >' . $title . '</a>';
}

/**
 * 过滤不安全的HTML代码
 */
function SecurityEditorHtml($str) {
    $farr = array(
        "/\s+/", //过滤多余的空白
        "/<(\/?)(script|i?frame|style|html|body|title|link|meta|\?|\%)([^>]*?)>/isU",
        "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU"
    );
    $tarr = array(
        " ",
        "＜\\1\\2\\3＞",
        "\\1\\2",
    );
    $str = preg_replace($farr, $tarr, $str);
    return $str;
}

//检查是否为一个合法的时间格式
function isTime($time) {
    $pattern = '/[\d]{4}-[\d]{1,2}-[\d]{1,2}\s[\d]{1,2}:[\d]{1,2}:[\d]{1,2}/';

    return preg_match($pattern, $time);
}

//判断一个字符串是否是一个合法时间
function isDate($string){

	if($string == '0000-00-00'){
		return true;
	}

    if(preg_match('/^\d{4}-[0-9][0-9]-[0-9][0-9]$/', $string)) {
        $date_info = explode('-', $string);
        return checkdate(ltrim($date_info[1], '0'), ltrim($date_info[2], '0'), $date_info[0]);
    }
    if(preg_match('/^\d{8}$/', $string)) {
        return checkdate(ltrim(substr($string, 4, 2), '0'), ltrim(substr($string, 6, 2), '0'), substr($string, 0, 4));
    }
    return false;
}
//判断图片
function isImage($fileName) {
    $ext = explode('.', $fileName);
    $ext_seg_num = count($ext);
    if ($ext_seg_num <= 1)
        return false;

    $ext = strtolower($ext[$ext_seg_num - 1]);
    $nort = in_array($ext, array('jpeg', 'jpg', 'png', 'gif'));
    $hext = explode('?', $ext);
    $httt = in_array($hext[0], array('jpeg', 'jpg', 'png', 'gif'));
    if($nort || $httt){
        return true;
    }else{
        return false;
    }
}



//专门给含有HTML的字段
function cut_msubstr($str,$start,$length,$suffix = '1'){
	//p($str.'--'.$start.'===='.$length.'+++++'.$suffix);die;
   $str = preg_replace( "@<(.*?)>@is", "", $str);
   return   msubstr($str, $start, $length, 'utf-8', $suffix);
}

//截取只付
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
    if (function_exists("mb_substr"))
        $slice = @mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '' : $slice;
}

function rand_string($len = 6, $type = '', $addChars = '') {
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('123456789',3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {//位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
        // 中文随机字
        for ($i = 0; $i < $len; $i++) {
            $str.= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}

//检查字符串是否是UTF8编码
function is_utf8($string) {
    return preg_match('%^(?:
         [\x09\x0A\x0D\x20-\x7E]            # ASCII
       | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
       |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
       |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
       |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
       | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
       |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
    )*$%xs', $string);
}

function cleanhtml($str, $length = 0, $suffix = true) {
    $str = preg_replace("@<(.*?)>@is", "", $str);
    if ($length > 0) {
        $str = msubstr($str, 0, $length, 'utf-8', $suffix);
    }
    return $str;
}
//删除空格
function trimall($str){
    $qian=array(" ","　","\t","\n","\r");
    $hou=array("","","","","");
    return str_replace($qian,$hou,$str); 
}
//处理地址
function deleteHtml($str){
	$str = trim($str);
	$str = str_replace(PHP_EOL,'',$str);   
	$str = trimall($str);
	$str=@str_replace("#","",$str);
	$str=@str_replace("-","",$str);
	$str=@str_replace("_","",$str);
	$str=@str_replace("—","",$str);
	$str=strip_tags($str,"");
	$str=preg_replace("{\t}","",$str);
	$str=preg_replace("{\r\n}","",$str);
	$str=preg_replace("{\r}","",$str);
	$str=preg_replace("{\n}","",$str);
	$str=preg_replace("{ }","",$str);
	return $str;
}

// 随机生成一组字符串
function build_count_rand($number, $length = 4, $mode = 1) {
    if ($mode == 1 && $length < strlen($number)) {
        return false;
    }
    $rand = array();
    for ($i = 0; $i < $number; $i++) {
        $rand[] = rand_string($length, $mode);
    }
    $unqiue = array_unique($rand);
    if (count($unqiue) == count($rand)) {
        return $rand;
    }
    $count = count($rand) - count($unqiue);
    for ($i = 0; $i < $count * 3; $i++) {
        $rand[] = rand_string($length, $mode);
    }
    $rand = array_slice(array_unique($rand), 0, $number);
    return $rand;
}



/* 提取所有图片 */
function getImgs($content,$order='all'){
	$pattern='/<img.*?src=[\"|\']?(.*?)[\"|\']?\s.*?>/i';
	preg_match_all($pattern,$content,$match);
	if(isset($match[1])&&!empty($match[1])){
		if($order==='all'){
			return $match[1];
		}
		if(is_numeric($order)&&isset($match[1][$order])){
			return $match[1][$order];
		}
	}
	return '';
}


/*对象转换为数组*/
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
     } if(is_array($array)) {
         foreach($array as $key=>$value) {
             $array[$key] = object_array($value);
             }
     }
     return $array;
}

//重复数组
function a_array_unique($array){
   $out = array();
   foreach ($array as $key=>$value){
       if (!in_array($value, $out)){
           $out[$key] = $value;
       }
   }
   return $out;
}

//坐标范围
function returnSquarePoint($lng, $lat,$distance){
    $dlng =  2 * asin(sin($distance / (2 * 6378.2)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);
    $dlat = $distance/6378.2;
    $dlat = rad2deg($dlat);
    return array(
		'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
		'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
		'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
		'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
	);
}

//偏移换算
function placeToBaidu($lng,$lat){
	$p = 3.14159265358979324 * 6378.2 / 360.0;
	$x = $lng;
	$y = $lat;
	$z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $p);
	$theta = atan2($y, $x) + 0.000003 * cos($x * $p);
	$bd_lng = $z * cos($theta) + 0.0065;
	$bd_lat = $z * sin($theta) + 0.006;
	return array('lng' => $bd_lng ,'lat' => $bd_lat);
}
//carrot添加全局归递找父级
function get_all_parent ($array, $cate_id) {
	$arr = array();
	foreach ($array as $v) {
		if ($v['cate_id'] == $cate_id) {
			$arr[] = $v;
			$arr = array_merge($arr, get_all_parent($array, $v['parent_id']));
		}
	}
	return $arr;
}

/*数据库备份*/
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

//图片调用
function config_img($img){

	$config = SettingModel::config();

	if(strstr($img,"images4.5maiche.cn")){
		$img = str_replace("images4.5maiche.cn","images4.jintaocms.com",$img);
	}elseif(strstr($img,"images4.yanjiu007.com")){
		$img = str_replace("images4.yanjiu007.com","images4.jintaocms.com",$img);
	}elseif(strstr($img,"images4.jintao365.com")){
		$img = str_replace("images4.jintao365.com","images4.jintaocms.com",$img);
	}elseif(strstr($img,"http")){
		if(strstr($img,"+imghost+")){
			$img = str_replace('+imghost+',$config['site']['imghost'],$img);
		}else{
			$img = $img;
		}
	}elseif(empty($img)){
		$img = '/attachs/default.jpg';
	}else{
		if(strstr($img,'attachs')){
			$img = $img;
		}elseif(strstr($img,'static')){
			$img = $config['site']['host'] . ''.$img;
		}else{
			$img = '/attachs/'.$img;
		}
	}
	return  $img;
}


function config_weixin_img($img){
	$config = SettingModel::config();
	if(strstr($img,"images4.5maiche.cn")){
		$img = str_replace("images4.5maiche.cn","images4.jintaocms.com",$img);
	}elseif(strstr($img,"images4.yanjiu007.com")){
		$img = str_replace("images4.yanjiu007.com","images4.jintaocms.com",$img);
	}elseif(strstr($img,"images4.jintao365.com")){
		$img = str_replace("images4.jintao365.com","images4.jintaocms.com",$img);
	}elseif(strstr($img,"http")){
	 	$img = $img;
	}elseif(empty($img)){
		$img = $config['site']['host'] .'/attachs/default.jpg';
	}else{
		if(strstr($img,"attachs")){
			$img = $config['site']['host'] . ''.$img;
		}elseif(strstr($img,'static')){
			$img = $config['site']['host'] . ''.$img;
		}else{
			$img = $config['site']['host'] . '/attachs/'.$img;
		}
	}
	return  $img;
}




//返回完整的URL，1代表PC，2代表手机端
function config_navigation_url($url,$type){
	$config = SettingModel::config();
	if(strstr($url,"http")){
	 	$url = $url;
	}elseif(strstr($url,"https")){
		$url = $url;
	}else{
		if($type == 1){
			$url = $config['site']['host'] . '/home'. $url;
		}else{
			if(strstr($url,"wap")){
				$url = $config['site']['host'] . '/'.$url;
			}elseif(strstr($url,"user")){
				$url = $config['site']['host'] . '/'.$url;
			}else{
				$url = $config['site']['host'] . '/wap/'.$url;
			}
		}
	}
	return  $url;
}

function config_user_name($user_name){
	if(strstr($user_name,'@')){
	 	$user_name = substr_replace($user_name,'****',3,4);
	}elseif(preg_match("/1[3458]{1}\d{9}$/",$user_name)){
		$user_name = substr_replace($user_name,'******',3,6);
	}else{
		$user_name = $user_name;
	}
	return  $user_name;
}
//分割缩略图设置尺寸
function thumbSize($thumb = '200X200',$key = 0){
    if(is_array($thumb)){
        $thumb = $thumb['thumb'];
    }
    $array = explode('X',$thumb);
    return $array[$key];
}


//$val 返回$array 中的一个区间值
function compareArr($array,$val){
    $val = intval($val);
    if(is_array($array)){
        foreach($array as $k=>$v){
            if(isset($array[$k+1])){
                if($val >= $v and $val < $array[$k+1]){
                    return $k;
                }
            }else{
                if($val >= $v){
                    return $k;
                }
            }
        }
        return a;
    }else{
        return a;
    }
}



//数组去掉重复，按照键值
function second_array_unique_bykey($arr, $key){
    $tmp_arr = array();
    foreach($arr as $k => $v){
        if(in_array($v[$key], $tmp_arr)){
            unset($arr[$k]);
        }
        else{
            $tmp_arr[$k] = $v[$key];
        }
   }
   return $arr;
}





//两个数组的笛卡尔积
function combineArray($arr1,$arr2){
    $result = array();
    foreach ($arr1 as $item1){
        foreach ($arr2 as $item2){
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}
//将数据库中查出的列表以指定的 id 作为数组的键名
function convert_arr_key($arr, $key_name){
    $arr2 = array();
    foreach($arr as $key => $val){
        $arr2[$val[$key_name]] = $val;
    }
    return $arr2;
}

//所有数组的笛卡尔积
function combineDika(){
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();

    $arr1 = array_shift($data);
    foreach($arr1 as $key=>$item){
        $result[] = array($item);
    }
    foreach($data as $key=>$item){
        $result = combineArray($result,$item);
    }
    return $result;
}

//将数据库中查出的列表以指定的 id 作为数组的键名 数组指定列为元素 的一个
function get_id_val($arr, $key_name,$key_name2){
    $arr2 = array();
    foreach($arr as $key => $val){
        $arr2[$val[$key_name]] = $val[$key_name2];
    }
    return $arr2;
}

//比较数组
function array_comparison($v1, $v2) {
    if ($v1 === $v2) {
        return 0;
    }
    if ($v1 > $v2) {
        return 1;
    } else {
        return -1;
    }
}
//苹果手机下面不能保存cookie
function unescape($str){
	$ret = '';
	$len = strlen($str);
	for ($i = 0; $i < $len; $i ++){
		if ($str[$i] == '%' && $str[$i + 1] == 'u'){
			$val = hexdec(substr($str, $i + 2, 4));
			if ($val < 0x7f)
				$ret .= chr($val);
			else
				if ($val < 0x800)
					$ret .= chr(0xc0 | ($val >> 6)) .
					chr(0x80 | ($val & 0x3f));
				else
					$ret .= chr(0xe0 | ($val >> 12)) .
					chr(0x80 | (($val >> 6) & 0x3f)) .
					chr(0x80 | ($val & 0x3f));
					$i += 5;
			} else
				if ($str[$i] == '%'){
					$ret .= urldecode(substr($str, $i, 3));
					$i += 2;
				} else
				$ret .= $str[$i];
		}
	return $ret;
}
//配置证书时候用
function getcwdOL(){
   $total = $_SERVER[PHP_SELF];
   $file = explode("/", $total);
   $file = $file[sizeof($file) - 1];
  return substr($total, 0, strlen($total) - strlen($file) - 1);
}
//获取链接
function getSiteUrl(){
  $host = $_SERVER[SERVER_NAME];
  $port = ($_SERVER[SERVER_PORT] == "80") ? "" : ":$_SERVER[SERVER_PORT]";
  return "http://" . $host . $port . getcwdOL();
}
//毫秒时间戳
function msectime() {
  list($tmp1, $tmp2) = explode(' ', microtime());
  return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
}


//积分抵扣不带类型
function deduction($integral){
	$config = SettingModel::config();
	if($config['integral']['buy'] == 0){
	 	return  round($integral/100,2);
	}elseif($config['integral']['buy'] == 10){
		return  round($integral/10,2);
	}elseif($config['integral']['buy'] == 100){
		return  round($integral/1,2);
	}else{
		return  '配置错误';
	}
	return  '配置错误';
}

//积分抵扣带类型
function getUseIntegral($integral,$type,$id){
  	$config = SettingModel::config();
	if($config['integral']['buy'] == 0){
	 	return  round($integral/100,2);
	}elseif($config['integral']['buy'] == 10){
		return  round($integral/100,2);
	}elseif($config['integral']['buy'] == 100){
		return  round($integral/100,2);
	}else{
		return  '积分配置错误';
	}
	return  '积分配置错误';
}

//签到代码
function getSign($row,$user_id){
    $t = $row + 1;
    if($t > date('d')){
        $td = "<td style='background-color:lemonchiffon' valign='top'>
<div align='right' valign='top'><span style='position:relative;right:20px;'>" . $t . "</span>
</div><div align='left'> </div><div align='left'> </div></td>";
    }else{
        if(strlen($t) == 1){
            $day = "0" . $t;
        }else{
            $day = $t;
        }
        $t2 = strtotime(date("Y-m-" . $day . ""));
        $info = Db::name('user_sign')->field('user_id')->where("last_time = " . $t2 . " AND status = 0 AND user_id = " . $user_id . "")->find();
        if($info){
            $td = "<td style='background-color:navajowhite;navajowhite ;'>
<div align='right' valign='top'><span style='position:relative;right:20px;'>" . $t . "</span>
</div><div align='left'>
<img width='35px' height='35px' src='/static/default/wap/image/index/sign.gif' style='position:relative;left:10px;'> 已签到
</div></td>";
        }else{
            if($t == date('d')) {
                $td = "<td  class='today' onclick='signDay($(this))'>
<div align='right' valign='top'><span style='position:relative;right:20px;'>" . $t . "</span></div>
<div align='center'><a style='cursor:pointer;color:#ffffff;' >签到</a></div></td>";
            }else{
                $td = "<td style='background-color:#DCDCDC;'>
<div align='right' valign='top'><span style='position:relative;right:20px;'>" . $t . "</span>
</div><div align='left'style='height:47px'>
</div></td>";
            }
        }
    }
    return $td;
}




//php根据出生日期计算年龄
function birthday($birthday){
	 $age = strtotime($birthday);
	 if($age === false){
	  	return false;
	 }
	 list($y1,$m1,$d1) = explode("-",date("Y-m-d",$age));
	 $now = strtotime("now");
	 list($y2,$m2,$d2) = explode("-",date("Y-m-d",$now));
	 $age = $y2 - $y1;
	 if((int)($m2.$d2) < (int)($m1.$d1))
	 $age -= 1;
	 return $age;
}


//根据频道获取有多少信息
function getThreadCateCount($cate_id,$city_id){
	if(!$city_id){
		return  false;
	}
	$count = (int)Db::name('thread_post')->where(array('cate_id'=>$cate_id,'closed'=>'0','audit'=>'1','city_id'=>$city_id))->count();
	return $count;
}

//百度地图转换为高德地图
function getBaiduChangeMap($lat,$lng){
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng - 0.0065;
    $y = $lat - 0.006;
    $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta);
    $lat = $z * sin($theta);
	return $lat.','.$lng;
}

//高德地图转换为百度地图
function getMapChangeBaidu($lat,$lng){
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    $x = $lng;
    $y = $lat;
    $z =sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
    $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
    $lng = $z * cos($theta) + 0.0065;
    $lat = $z * sin($theta) + 0.006;
	return $lat.','.$lng;
}

function getMapChangeBaidu2($lat,$lng){
	$x_pi = 3.14159265358979324 * 3000.0 / 180.0;
	$x = $lng;
	$y = $lat;
	$z =sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
	$theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
	$lng = $z * cos($theta) + 0.0065;
	$lat = $z * sin($theta) + 0.006;
	return array('lat'=>$lat,'lng'=>$lng);
}


//获取当前域名
function curPageURL(){
  $pageURL = 'http';
  $_SERVER["HTTPS"] ='';
  if($_SERVER["HTTPS"] == "on"){
    $pageURL .= "s";
  }
  $pageURL .= "://";
  if($_SERVER["SERVER_PORT"] != "80"){
    $pageURL .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  } else{
    $pageURL .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  }
  return $pageURL;
}

//IP鉴权控制
function IpAuth($ip, $config){
    $ipArr = explode(".", $ip);
    for ( $i=0; $i<count($config); $i++ ){
        $ips = explode(".", $config[$i]['start']);
        $ipe = explode(".", $config[$i]['end']);
        for( $j=0; $j<4; $j++ ){
            if( $ips[$j]==$ipArr[$j] || $ipArr[$j]==$ipe[$j] ){
                if($j == 3){
                    return true;
                }else{
                    continue;
                }
            }else if( $ips[$j]<$ipArr[$j] && $ipArr[$j]<$ipe[$j] ){
                return true;
            }else{
                continue 2;
            }
        }
    }
    return false;
}



//百度测算距离和时间
function get_dist_info($sLat,$sLng,$eLat,$eLng){
    $url = 'http://api.map.baidu.com/direction/v1/routematrix?output=json&origins='.$sLat.','.$sLng.'&destinations='.$eLat.','.$eLng.'&ak=7b92b3afff29988b6d4dbf9a00698ed8';
    $map_info = file_get_contents($url);
    $map_info = json_decode($map_info,1);
    if($map_info['status']){
        $juli = getdistance($sLat, $sLng, $eLat, $eLng);
        $time = $juli*3;
        return array('juli'=>$juli,'time'=>$time);
    }else{
        $juli=$map_info['result']['elements'][0]['distance']['text'];
        $time=$map_info['result']['elements'][0]['duration']['text'];
        return array('juli'=>$juli, 'juli_value'=>$map_info['result']['elements'][0]['distance']['value'], 'time'=>$time, 'time_value'=>$map_info['result']['elements'][0]['duration']['value']);
    }
}



//时间转换
function timeChangeDay($time){
	$d = floor($time/(3600*24));
	$h = floor(($time%(3600*24))/3600);
	$m = floor((($time%(3600*24))%3600)/60);
	if($d>'0'){
		return $d.'天'.$h.'小时'.$m.'分';
	}else{
		if($h!='0'){
			return $h.'小时'.$m.'分';
		}else{
			return $m.'分';
		}
	}
}


//高德坐标系距离计算
function amapDistance($lot,$lat,$lng2,$lat2,$appoint_id){
	$config = getConfigKey('config');
	$info['status'] = '';
    $url = 'https://restapi.amap.com/v3/distance?origins='.$lot.','.$lat.'&destination='.$lng2.','.$lat2.'&output=JSON&key='.$config['amap_map_api'].'&type=1';
    $info = file_get_contents($url);
    $info = json_decode($info,1);

    if($info['status'] == 1){
		$juli = $info['results'][0]['distance'];
		$time = $info['results'][0]['duration'];
		$s = $juli/1000;
		if($s < 1){
			$s = round($s*1000);
			$s.='m';
		}else{
			$s = round($s,2);
			$s.='km';
		}
        return array('juli'=>$juli,'juliKm'=>$s,'time'=>$time,'times'=>timeChangeDay($time),'info'=> $info['results'][0]['info']);
    }else{
        return array('juli'=>'50000','juliKm'=>'','time'=>'','times'=>'');
    }
}



//从新计算配送价位
function update_logistics_price($dist,$city_id=0){
    $delivery_set = Db::name('delivery_set')->where(array('city_id'=>$city_id))->find();
    if(empty($delivery_set)){
        $delivery_set = Db::name('delivery_set')->where(array('city_id'=>0))->find();
    }
    $s_price = $delivery_set['s_price'];    //配送费起步价
    $s_dist = $delivery_set['s_dist'];      //起步配送距离
    $one_dist = $delivery_set['one_dist'];  //每公里价位
    if(strpos($dist,'公里') !== false){
        $dist = ceil(str_replace('公里', '', $dist));
        if($dist>2){
            $amount = ($dist-$s_dist)*$one_dist+$s_price;
        }else{
            $amount = $s_price;
        }
    }else{
        $dist = str_replace('米','',$dist);
        $dist = ceil($dist/1000);
        if($dist>2){
            $amount =($dist-$s_dist)*$one_dist+$s_price;
        }else{
            $amount = $s_price;
        }
    }
    return round($amount*100,2);
}

//万能表单表单转换为文字
function convertFormText($key,$value,$k){
	if($key == 'username'){
		$name = '姓名';
	}elseif($key == 'tel'){
		$name = '电话';
	}elseif($key == 'Handset'){
		$name = '手机';
	}elseif($key == 'QQ'){
		$name = 'QQ';
	}elseif($key == 'Mail'){
		$name = 'E-Mail';
	}elseif($key == 'other'){
		$name = '说明';
	}elseif($key == 'create_time'){
		$name = '时间';
	}elseif($key == 'create_ip'){
		$name = 'IP';
	}elseif($key == 'city_id'){
		$name = Db::name('city')->where(array('city_id'=>$value))->value('name');
	}elseif($key == 'area_id'){
		$name = Db::name('area')->where('area_id',$value)->value('area_name');
	}

	if($k == 'toloan'){
		if($key == 'buydate'){
			$name = '借款人';
		}elseif($key == 'address'){
			$name = '借贷类型';
		}elseif($key == 'Wang'){
			$name = '职业';
		}elseif($key == 'buypay'){
			$name = '性别';
		}elseif($key == 'buydate'){
			$name = '身份';
		}elseif($key == 'stylenum'){
			$name = '金额';
		}
	}elseif($k == 'joinus'){
		if($key == 'buydate'){
			$name = '是否已婚';
		}elseif($key == 'buypay'){
			$name = '预约时间';
		}elseif($key == 'Huozhi'){
			$name = '性别';
		}elseif($key == 'Wang'){
			$name = '学历';
		}elseif($key == 'address'){
			$name = '工作职位';
		}
	}elseif($k == 'jiaoyou'){
		if($key == 'buyPrice'){
			$name = '活动主题';
		}elseif($key == 'buypay'){
			$name = '性别';
		}elseif($key == 'stylenum'){
			$name = '工作';
		}elseif($key == 'Huozhi'){
			$name = '职业';
		}elseif($key == 'Wang'){
			$name = '职位';
		}elseif($key == 'address'){
			$name = '择友标准';
		}
	}
	return $name;
}

//数组排序
function array_sort($array, $keys, $type='asc' ){
   if(!isset($array) || !is_array($array) || empty($array)) return '';
   if(!isset($keys) || trim($keys) == '' ) return '';
   if(!isset($type) || $type == '' || !in_array( strtolower($type), array( 'asc', 'desc' ) ) ) return '';
   $keysvalue  = array();
   foreach($array as $key => $val){
       $val[$keys] = str_replace( '-', '', $val[$keys]);
       $val[$keys] = str_replace( ' ', '', $val[$keys]);
       $val[$keys] = str_replace( ':', '', $val[$keys]);
       $keysvalue[]  = $val[$keys];
    }
    asort($keysvalue);
    reset($keysvalue);
    foreach($keysvalue as $key => $vals)
        $keysort[] = $key;
    $keysvalue  = array();
    $count = count($keysort);
    if(strtolower($type) != 'asc' ){
        for($i = $count - 1; $i >= 0; $i-- )
        $keysvalue[] = $array[$keysort[$i]];
    }else{
        for($i = 0; $i < $count; $i++)
        $keysvalue[] = $array[ $keysort[$i]];
    }
    return $keysvalue;
}


function getRongKeySecret(){
    if(config('RONG_IS_DEV')){
        $key=config('RONG_DEV_APP_KEY');
        $secret=config('RONG_DEV_APP_SECRET');
    }else{
        $key=config('RONG_PRO_APP_KEY');
        $secret=config('RONG_PRO_APP_SECRET');
    }
    $data=array(
        'key'=>$key,
        'secret'=>$secret
        );
    return $data;
}


function getUrl($path){
    if(empty($path)){
        return '';
    }
    if(strpos($path, 'http://')!==false){
        return $path;
    }
}

function getToken($uid,$type){
	$map=array(
		'uid'=>$uid,
		'type'=>$type
		);
	$token=Db::name('users_oauth')->where($map)->value('access_token');
	return $token;
}


function get_rongcloud_token($uid){
    $token=Db::name('users_oauth')->where(array('type'=>1,'uid'=>$uid))->find();
    if(!empty($token['access_token'])){
        return $token;
    }
    $user_data=Db::name('users')->find($uid);
    if(empty($user_data)){
        return false;
    }
    $avatar=config_img($user_data['face']);
    $key_secret=getRongKeySecret();
    $rong_cloud=new \Rongcloud($key_secret['key'],$key_secret['secret']);
    $token_json=$rong_cloud->getToken($uid,$user_data['nickname'],$avatar);
    $token_array=json_decode($token_json,true);
    if($token_array['code']!=200){
        return false;
    }
    $token=$token_array['token'];
    $data=array(
        'uid'=>$uid,
        'type'=>1,
        'nickname'=>$user_data['nickname'],
        'head_img'=>$avatar,
        'access_token'=>$token
    );
    $result= Db::name('users_oauth')->insert($data);
    if($result){
        return $token;
    }else{
        return false;
    }
}



function refresh_rongcloud_token($uid){
    $user_data=Db::name('users')->find($uid);
    if(empty($user_data)){
        return false;
    }
    $avatar=getUrl($user_data['avatar']);
    $key_secret=getRongKeySecret();
    $rong_cloud=new \Rongcloud($key_secret['key'],$key_secret['secret']);
    $result_json=$rong_cloud->userRefresh($uid,$user_data['username'],$avatar);
    $result_array=json_decode($result_json,true);
    if($result_array['code']==200){
        return true;
    }else{
        return false;
    }
}


//外卖购物车数量
function cartEleNum($goods_id,$id){
	$cart = cookie('cart');
	$cart = unserialize($cart);
	if(is_array($cart)){
		foreach($cart as $k => $v){
			if($id != 0 && $v['option_id'] == $id){
				$num = $v['num'];
			}elseif($id == 0 && $v['goods_id'] == $goods_id){
				$num = $v['num'];
			}
		}
	}
	return $num;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc') {
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/*
 * 根据二维数组某个字段的值查找数组
*/
function filter_by_value ($array,$index,$value){
	if(is_array($array) && count($array)>0){
		foreach(array_keys($array) as $key){
			$temp[$key] = $array[$key][$index];
			if($temp[$key] == $value){
				$newarray[$key] = $array[$key];
			}
		}
	}
	return $newarray;
}

function arraySequence($array, $field, $sort = 'SORT_DESC'){
	 $arrSort = array();
	 foreach($array as $uniqid => $row){
		  foreach ($row as $key => $value){
		   $arrSort[$key][$uniqid] = $value;
		  }
	 }
	 array_multisort($arrSort[$field], constant($sort), $array);
	 return $array;
}


//接口正确输出
function json_success($data = '', $message = 'success'){
    header('Content-Type:application/json; charset=utf-8');
    $result['status'] = 1;
    $result['message'] = $message;
    $result['data'] = empty($data) ? [] : $data;
    exit(json_encode($result));
}

//接口错误输出
function json_error($message = 'error', $status = -1){
    header('Content-Type:application/json; charset=utf-8');
    $result['status'] = $status;
    $result['message'] = $message;
    exit(json_encode($result));
}

//获取客户端IP地址
function get_client_ip($type = 0){
    $type = $type ? 1 : 0;
    static $ip = null;
    if($ip !== null) return $ip[$type];
    if(isset($_SERVER['HTTP_X_REAL_IP'])){
        //nginx 代理模式下，获取客户端真实IP
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
        //客户端的ip
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //浏览当前页面的用户计算机的网关
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip = trim($arr[0]);
    }else{
        //浏览当前页面的用户计算机的ip地址
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    //IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
    return $ip[$type];
}


function getWeek($time = '', $format='Y-m-d'){
	$time = $time != '' ? $time : time();
    //组合数据
    $date = [];
    for ($i=0; $i<=7; $i++){
      $date[$i] = date($format ,strtotime( '+' . $i-1 .' days', $time));
    }
    return $date;
}

//字符转码
function gbk2utf8($data){
    if(is_array($data)){
        return array_map('gbk2utf8', $data);
    }
    return iconv('gbk', 'utf-8//IGNORE', $data);
}


function ihttp_request($url, $post = '', $extra = array(), $timeout = 60) {
		if (function_exists('curl_init') && function_exists('curl_exec') && $timeout > 0) {
		$ch = ihttp_build_curl($url, $post, $extra, $timeout);
		if (is_error($ch)) {
			return $ch;
		}
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if ($errno || empty($data)) {
			return error($errno, $error);
		} else {
			return ihttp_response_parse($data);
		}
	}
	$urlset = ihttp_parse_url($url, true);
	if (!empty($urlset['ip'])) {
		$urlset['host'] = $urlset['ip'];
	}

	$body = ihttp_build_httpbody($url, $post, $extra);

	if ('https' == $urlset['scheme']) {
		$fp = ihttp_socketopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
	} else {
		$fp = ihttp_socketopen($urlset['host'], $urlset['port'], $errno, $error);
	}
	stream_set_blocking($fp, $timeout > 0 ? true : false);
	stream_set_timeout($fp, ini_get('default_socket_timeout'));
	if (!$fp) {
		return error(1, $error);
	} else {
		fwrite($fp, $body);
		$content = '';
		if ($timeout > 0) {
			while (!feof($fp)) {
				$content .= fgets($fp, 512);
			}
		}
		fclose($fp);

		return ihttp_response_parse($content, true);
	}
}




function ihttp_get($url) {
	return ihttp_request($url);
}


function ihttp_post($url, $data) {
	$headers = array('Content-Type' => 'application/x-www-form-urlencoded');

	return ihttp_request($url, $data, $headers);
}


function ihttp_multi_request($urls, $posts = array(), $extra = array(), $timeout = 60) {
	if (!is_array($urls)) {
		return error(1, '请使用ihttp_request函数');
	}
	$curl_multi = curl_multi_init();
	$curl_client = $response = array();

	foreach ($urls as $i => $url) {
		if (isset($posts[$i]) && is_array($posts[$i])) {
			$post = $posts[$i];
		} else {
			$post = $posts;
		}
		if (!empty($url)) {
			$curl = ihttp_build_curl($url, $post, $extra, $timeout);
			if (is_error($curl)) {
				continue;
			}
			if (CURLM_OK === curl_multi_add_handle($curl_multi, $curl)) {
								$curl_client[] = $curl;
			}
		}
	}
	if (!empty($curl_client)) {
		$active = null;
		do {
			$mrc = curl_multi_exec($curl_multi, $active);
		} while (CURLM_CALL_MULTI_PERFORM == $mrc);

		while ($active && CURLM_OK == $mrc) {
			do {
				$mrc = curl_multi_exec($curl_multi, $active);
			} while (CURLM_CALL_MULTI_PERFORM == $mrc);
		}
	}

	foreach ($curl_client as $i => $curl) {
		$response[$i] = curl_multi_getcontent($curl);
		curl_multi_remove_handle($curl_multi, $curl);
	}
	curl_multi_close($curl_multi);

	return $response;
}

function ihttp_socketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if (function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif (function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif (function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname . ':' . $port, $errno, $errstr, $timeout);
	}

	return $fp;
}


function ihttp_response_parse($data, $chunked = false) {
	$rlt = array();

	$pos = strpos($data, "\r\n\r\n");
	$split1[0] = substr($data, 0, $pos);
	$split1[1] = substr($data, $pos + 4, strlen($data));

	$split2 = explode("\r\n", $split1[0], 2);
	preg_match('/^(\S+) (\S+) (.*)$/', $split2[0], $matches);
	$rlt['code'] = !empty($matches[2]) ? $matches[2] : 200;
	$rlt['status'] = !empty($matches[3]) ? $matches[3] : 'OK';
	$rlt['responseline'] = !empty($split2[0]) ? $split2[0] : '';
	$header = explode("\r\n", $split2[1]);
	$isgzip = false;
	$ischunk = false;
	foreach ($header as $v) {
		$pos = strpos($v, ':');
		$key = substr($v, 0, $pos);
		$value = trim(substr($v, $pos + 1));
		if (isset($rlt['headers'][$key]) && is_array($rlt['headers'][$key])) {
			$rlt['headers'][$key][] = $value;
		} elseif (!empty($rlt['headers'][$key])) {
			$temp = $rlt['headers'][$key];
			unset($rlt['headers'][$key]);
			$rlt['headers'][$key][] = $temp;
			$rlt['headers'][$key][] = $value;
		} else {
			$rlt['headers'][$key] = $value;
		}
		if (!$isgzip && 'content-encoding' == strtolower($key) && 'gzip' == strtolower($value)) {
			$isgzip = true;
		}
		if (!$ischunk && 'transfer-encoding' == strtolower($key) && 'chunked' == strtolower($value)) {
			$ischunk = true;
		}
	}
	if ($chunked && $ischunk) {
		$rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
	} else {
		$rlt['content'] = $split1[1];
	}
	if ($isgzip && function_exists('gzdecode')) {
		$rlt['content'] = gzdecode($rlt['content']);
	}

	$rlt['meta'] = $data;
	if ('100' == $rlt['code']) {
		return ihttp_response_parse($rlt['content']);
	}

	return $rlt;
}

function ihttp_response_parse_unchunk($str = null) {
	if (!is_string($str) or strlen($str) < 1) {
		return false;
	}
	$eol = "\r\n";
	$add = strlen($eol);
	$tmp = $str;
	$str = '';
	do {
		$tmp = ltrim($tmp);
		$pos = strpos($tmp, $eol);
		if (false === $pos) {
			return false;
		}
		$len = hexdec(substr($tmp, 0, $pos));
		if (!is_numeric($len) or $len < 0) {
			return false;
		}
		$str .= substr($tmp, ($pos + $add), $len);
		$tmp = substr($tmp, ($len + $pos + $add));
		$check = trim($tmp);
	} while (!empty($check));
	unset($tmp);

	return $str;
}

//判断是什么平台
function platform(){
    $platform='h5';
    $a_strtolower = strtolower($_SERVER['HTTP_USER_AGENT']);
    if(strpos($a_strtolower, "micromessenger")){
        if(strpos($a_strtolower, "miniprogram")){
            $platform = 'mpapp';
        }else{
            $platform = 'wxapp';
        }
    }elseif(strpos($a_strtolower, "uni-app") || strpos($a_strtolower, "Html5Plus")){
        $platform = 'app';
    }
    return $platform;
}


function ihttp_parse_url($url, $set_default_port = false) {
	if (empty($url)) {
		return error(1);
	}
	$urlset = parse_url($url);
	if (!empty($urlset['scheme']) && !in_array($urlset['scheme'], array('http', 'https'))) {
		return error(1, '只能使用 http 及 https 协议');
	}
	if (empty($urlset['path'])) {
		$urlset['path'] = '/';
	}
	if (!empty($urlset['query'])) {
		$urlset['query'] = "?{$urlset['query']}";
	}
	if (strexists($url, 'https://') && !extension_loaded('openssl')) {
		if (!extension_loaded('openssl')) {
			return error(1, '请开启您PHP环境的openssl', '');
		}
	}
	if (empty($urlset['host'])) {
		$current_url = parse_url($GLOBALS['_W']['siteroot']);
		$urlset['host'] = $current_url['host'];
		$urlset['scheme'] = $current_url['scheme'];
		$urlset['path'] = $current_url['path'] . 'web/' . str_replace('./', '', $urlset['path']);
		$urlset['ip'] = '127.0.0.1';
	} elseif (!ihttp_allow_host($urlset['host'])) {
		return error(1, 'host 非法');
	}

	if ($set_default_port && empty($urlset['port'])) {
		$urlset['port'] = 'https' == $urlset['scheme'] ? '443' : '80';
	}

	return $urlset;
}


function ihttp_allow_host($host) {
	global $_W;
	if (strexists($host, '@')) {
		return false;
	}
	$pattern = '/^(10|172|192|127)/';
	if (preg_match($pattern, $host) && isset($_W['setting']['ip_white_list'])) {
		$ip_white_list = $_W['setting']['ip_white_list'];
		if ($ip_white_list && isset($ip_white_list[$host]) && !$ip_white_list[$host]['status']) {
			return false;
		}
	}

	return true;
}


function ihttp_build_curl($url, $post, $extra, $timeout) {
	if (!function_exists('curl_init') || !function_exists('curl_exec')) {
		return error(1, 'curl扩展未开启');
	}

	$urlset = ihttp_parse_url($url);
	if (is_error($urlset)) {
		return $urlset;
	}

	if (!empty($urlset['ip'])) {
		$extra['ip'] = $urlset['ip'];
	}

	$ch = curl_init();
	if (!empty($extra['ip'])) {
		$extra['Host'] = $urlset['host'];
		$urlset['host'] = $extra['ip'];
		unset($extra['ip']);
	}
	curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . (empty($urlset['port']) || '80' == $urlset['port'] ? '' : ':' . $urlset['port']) . $urlset['path'] . (!empty($urlset['query']) ? $urlset['query'] : ''));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	@curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	if ($post) {
		if (is_array($post)) {
			$filepost = false;
						foreach ($post as $name => &$value) {
				if (version_compare(phpversion(), '5.5') >= 0 && is_string($value) && '@' == substr($value, 0, 1)) {
					$post[$name] = new CURLFile(ltrim($value, '@'));
				}
				if ((is_string($value) && '@' == substr($value, 0, 1)) || (class_exists('CURLFile') && $value instanceof CURLFile)) {
					$filepost = true;
				}
			}
			if (!$filepost) {
				$post = http_build_query($post);
			}
		}
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if (!empty($GLOBALS['_W']['config']['setting']['proxy'])) {
		$urls = parse_url($GLOBALS['_W']['config']['setting']['proxy']['host']);
		if (!empty($urls['host'])) {
			curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
			$proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
			if (!empty($urls['scheme']) && defined($proxytype)) {
				curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
			} else {
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
				curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
			}
			if (!empty($GLOBALS['_W']['config']['setting']['proxy']['auth'])) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['_W']['config']['setting']['proxy']['auth']);
			}
		}
	}
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	if (defined('CURL_SSLVERSION_TLSv1')) {
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
	if (!empty($extra) && is_array($extra)) {
		$headers = array();
		foreach ($extra as $opt => $value) {
			if (strexists($opt, 'CURLOPT_')) {
				curl_setopt($ch, constant($opt), $value);
			} elseif (is_numeric($opt)) {
				curl_setopt($ch, $opt, $value);
			} else {
				$headers[] = "{$opt}: {$value}";
			}
		}
		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
	}

	return $ch;
}

function ihttp_build_httpbody($url, $post, $extra) {
	$urlset = ihttp_parse_url($url, true);
	if (is_error($urlset)) {
		return $urlset;
	}

	if (!empty($urlset['ip'])) {
		$extra['ip'] = $urlset['ip'];
	}

	$body = '';
	if (!empty($post) && is_array($post)) {
		$filepost = false;
		$boundary = random(40);
		foreach ($post as $name => &$value) {
			if ((is_string($value) && '@' == substr($value, 0, 1)) && file_exists(ltrim($value, '@'))) {
				$filepost = true;
				$file = ltrim($value, '@');

				$body .= "--$boundary\r\n";
				$body .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . basename($file) . '"; Content-Type: application/octet-stream' . "\r\n\r\n";
				$body .= file_get_contents($file) . "\r\n";
			} else {
				$body .= "--$boundary\r\n";
				$body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
				$body .= $value . "\r\n";
			}
		}
		if (!$filepost) {
			$body = http_build_query($post, '', '&');
		} else {
			$body .= "--$boundary\r\n";
		}
	}

	$method = empty($post) ? 'GET' : 'POST';
	$fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
	$fdata .= "Accept: */*\r\n";
	$fdata .= "Accept-Language: zh-cn\r\n";
	if ('POST' == $method) {
		$fdata .= empty($filepost) ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data; boundary=$boundary\r\n";
	}
	$fdata .= "Host: {$urlset['host']}\r\n";
	$fdata .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1\r\n";
	if (function_exists('gzdecode')) {
		$fdata .= "Accept-Encoding: gzip, deflate\r\n";
	}
	$fdata .= "Connection: close\r\n";
	if (!empty($extra) && is_array($extra)) {
		foreach ($extra as $opt => $value) {
			if (!strexists($opt, 'CURLOPT_')) {
				$fdata .= "{$opt}: {$value}\r\n";
			}
		}
	}
	if ($body) {
		$fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
	} else {
		$fdata .= "\r\n";
	}

	return $fdata;
}

function strexists($string, $find) {
	return !(false === strpos($string, $find));
}


function iserializer($value){
	return serialize($value);
}

function is_serialized($data, $strict = true) {
	if (!is_string($data)) {
		return false;
	}
	$data = trim($data);
	if ('N;' == $data) {
		return true;
	}
	if (strlen($data) < 4) {
		return false;
	}
	if (':' !== $data[1]) {
		return false;
	}
	if ($strict) {
		$lastc = substr($data, -1);
		if (';' !== $lastc && '}' !== $lastc) {
			return false;
		}
	} else {
		$semicolon = strpos($data, ';');
		$brace = strpos($data, '}');
				if (false === $semicolon && false === $brace) {
			return false;
		}
				if (false !== $semicolon && $semicolon < 3) {
			return false;
		}
		if (false !== $brace && $brace < 4) {
			return false;
		}
	}
	$token = $data[0];
	switch ($token) {
		case 's':
			if ($strict) {
				if ('"' !== substr($data, -2, 1)) {
					return false;
				}
			} elseif (false === strpos($data, '"')) {
				return false;
			}
						case 'a':
			return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
		case 'O':
			return false;
		case 'b':
		case 'i':
		case 'd':
			$end = $strict ? '$' : '';

			return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
	}

	return false;
}

//反编译
function iunserializer($value){
	if(empty($value)){
		return array();
	}
	if (!is_serialized($value)){
		return $value;
	}
	if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
		$result = unserialize($value, array('allowed_classes' => false));
	} else {
		if (preg_match('/[oc]:[^:]*\d+:/i', $value)) {
			return array();
		}
		$result = unserialize($value);
	}
	if (false === $result) {
		$temp = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs) {
			return 's:' . strlen($matchs[2]) . ':"' . $matchs[2] . '";';
		}, $value);

		return unserialize($temp);
	} else {
		return $result;
	}
}

function error($errno, $message = '') {
	return array(
		'errno' => $errno,
		'message' => $message,
	);
}


function is_error($data) {
	if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && 0 == $data['errno'])) {
		return false;
	} else {
		return true;
	}
}


function changLang($lang = 'zh',$name,$name1,$str =0){
    if($lang =='zh'){
		$title = $name;
	}else{
		$title = $name1 ? $name1 : $name;
	}
	if($str > 0){
		return cut_msubstr($title,0,$str,false);
	}else{
		return $title;
	}

}

//currency货币转换
function currency($ctl,$act){
	$getConfigKey =  getConfigKey($key = 'site');

	if($ctl == 'ele' || $ctl == 'eleorder' || $ctl == 'eledianping'){
		$getConfigKey['currency'] ? $getConfigKey['currency'] : '¥';
	}
	return $getConfigKey['currency'] ? $getConfigKey['currency'] : '¥';
}
//currency货币转换
function currencyText($ctl,$act){
	$getConfigKey =  getConfigKey($key = 'site');
	return $getConfigKey['currencyText'] ? $getConfigKey['currencyText'] : '元';
}

function getIP() { 
	if (@$_SERVER["HTTP_X_FORWARDED_FOR"]) 
	$ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
	else if (@$_SERVER["HTTP_CLIENT_IP"]) 
	$ip = $_SERVER["HTTP_CLIENT_IP"]; 
	else if (@$_SERVER["REMOTE_ADDR"]) 
	$ip = $_SERVER["REMOTE_ADDR"]; 
	else if (@getenv("HTTP_X_FORWARDED_FOR"))
	$ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if (@getenv("HTTP_CLIENT_IP")) 
	$ip = getenv("HTTP_CLIENT_IP"); 
	else if (@getenv("REMOTE_ADDR")) 
	$ip = getenv("REMOTE_ADDR"); 
	else 
	$ip = "Unknown"; 
	return $ip; 
}


function getServerIp(){
    $server_ip = gethostbyname($_SERVER["SERVER_NAME"]);
    return $server_ip;
}


//格式化打印函数
function p($array){
	dump($array,1,'<pre style=font-size:18px;color:#00ae19;>',0);
}

function tpl_form_field_image($name, $value = '', $default = '',$callback='photo',$model='0',$options = array(),$fileName='上传图片',$type='1') {
    if (!empty($options['global'])) {
        $options['global'] = true;
    } else {
        $options['global'] = false;
    }
    if (empty($options['class_extra'])) {
        $options['class_extra'] = '';
    }
    if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
        if (!preg_match('/^\w+([\/]\w+)?$/i', $options['dest_dir'])) {
            exit('图片上传目录错误,只能指定最多两级目录,如: "upload","upload/d1"');
        }
    }
    $options['direct'] = true;
    $options['multiple'] = false;
    if (isset($options['thumb'])) {
        $options['thumb'] = !empty($options['thumb']);
    }

	
    $options['fileSizeLimit'] = 1024*1024*3;
    $s = '';
	$s .= '
		<div style="width:100px;height:auto;float:left;">
			<input type="hidden" name="'.$name.'" value="'.$value.'" id="data_'.$callback.'"/>
			<div id="fileToUpload-'.$callback.'">'.$fileName.'</div>
		</div>
		<div style="width:200px;height:auto; float:left;">
			<img style="width:180px; height:auto" id="'.$callback.'_img" src="'.$default.'"/>
		</div>';
	$s .= '
		<script type="text/javascript">
			var uploader = WebUploader.create({                             
				auto: true,                             
				swf: "/static/default/webuploader/Uploader.swf",                             
				server: "/app/upload/uploadify/model/'.$model.'",                             
				pick: "#fileToUpload-'.$callback.'",                             
				resize: true,  
			});
			uploader.on("uploadSuccess",function(file,resporse){                             
				$("#data_'.$callback.'").val(resporse.url);                             
				$("#'.$callback.'_img").attr("src",resporse.url).show();                         
			});                                                
			uploader.on("uploadError",function(file){                             
				alert("上传出错");                         
			});
		</script>';
    return $s;
}
