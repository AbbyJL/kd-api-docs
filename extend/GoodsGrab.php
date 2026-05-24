<?php

/**
 * 商品抓取
 * Created by PhpStorm.
 * User: lirui
 * Date: 2020/1/4
 * Time: 上午10:55
 */






use Symfony\Component\DomCrawler\Crawler;


class GoodsGrab
{
    /**
     * 支持的抓取
     * @var array
     */
    public static $supports = [
        'taobao' => 'taobao.com',
        'tmall' => 'tmall.com',
        'jd' => 'jd.com',
        '1688' => '1688.com',
    ];

    /**
     * 抓取商品
     * @param $link
     */
    public static function grab($link){
		
		
		
        $result = [];
        if (!$link) {
            return $result;
        }

        $type = '';

        foreach (self::$supports as $supportKey => $support) {
            if (strpos($link, $support) === false) {
                continue;
            }
            $type = $supportKey;
        }

        if (!$type) {
            return $result;
        }

        if ($type == '1688') {
            $pattern = "/(\d+)\.html/is";
            preg_match($pattern, $link, $match);
            $itemId = $match[1];
            $link = "https://m.1688.com/offer/{$itemId}.html";
        }
		
		
		$encoding = true;
		if($type == 'jd'){
			$encoding = false;
		}

        $html = self::curlGet($link, 25,$encoding);

        if(!$html){
            return $result;
        }

        $html = self::Utf8String($html);

        $method = 'grab' . ucfirst($type);
        return self::$method($html);
    }

    /**
     * 抓取淘宝链接商品
     * @param $html
     */
    public static function grabTaobao($html)
    {
        $result = [];
		
        $crawler = new Crawler($html);
		
        //标题
        preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
        $result['title'] = isset($title['1']) ? str_replace(['-淘宝网', '-tmall.com天猫', ' - 阿里巴巴', ' ', '-', '【图片价格品牌报价】京东', '京东', '【行情报价价格评测】'], '', trim($title['1'])) : '';
        $result['subtitle'] = $result['title'];

        //价格
        $priceDom = $crawler->filter('#J_StrPrice .tb-rmb-num');

        $result['productprice'] = $priceDom->count() > 0 ? (float)$priceDom->text() : 0.00;
        $result['marketprice'] = $priceDom->count() > 0 ? (float)$priceDom->text() : 0.00;
        $result['costprice'] = $priceDom->count() > 0 ? (float)$priceDom->text() : 0.00;

        //获取轮播图
        $images = self::getTaobaoImg($html);
        if ($images) {
            $images = array_merge($images);
            $result['thumbs'] = isset($images['gaoqing']) ? $images['gaoqing'] : (array)$images;
            $result['thumb'] = is_array($result['thumbs']) && isset($result['thumbs'][0]) ? $result['thumbs'][0] : '';
        }

        //获取产品详情
        $result['description'] = self::getTaobaoDesc($html);
		
		//p($result);die;

        return $result;
    }

    /**
     * 获取淘宝图片
     * @param $html
     */
    private static function getTaobaoImg($html)
    {
        preg_match('/auctionImages([^<>]*)"]/', $html, $imgarr);
        if (!isset($imgarr[1])) return '';
        $arr = explode(',', $imgarr[1]);
        foreach ($arr as $k => &$v) {
            $str = trim($v);
            $str = str_replace(['"', ' ', '', ':['], '', $str);
            if (strpos($str, '?')) {
                $_tarr = explode('?', $str);
                $str = trim($_tarr[0]);
            }
            $_i_url = strpos($str, 'http') ? $str : 'http:' . $str;
            if (self::_img_exists($_i_url)) {
                $v = $_i_url;
            } else {
                unset($arr[$k]);
            }
        }
        return array_unique($arr);
    }

    /**
     * 获取淘宝描述
     * @param string $html
     * @return mixed|string
     */
    public static function getTaobaoDesc($html)
    {
		$HttpService = new \HttpService();
        preg_match('/descUrl([^<>]*)counterApi/', $html, $descarr);
        if (!isset($descarr[1])) {
            return '';
        }
        $arr = explode(':', $descarr[1]);
        $url = [];
        foreach ($arr as $k => $v) {
            if (strpos($v, '//')) {
                $str = str_replace(['\'', ',', ' ', '?', ':'], '', $v);
                $url[] = trim($str);
            }
        }
        if ($url) {
            $apiUrl = strpos($url[0], 'http') ? $url[0] : 'http:' . $url[0];
        }
		
        //获取请求内容
        $descJson =$HttpService->getRequest($apiUrl);
        //转换字符集
        $descJson = self::Utf8String($descJson);

        if (strpos($descJson, 'desc=') !== false) {
            $descJson = str_replace('var desc=\'', '', $descJson);
            $descJson = str_replace(["\n", "\t", "\r", "\\\\"], '', $descJson);
            return substr($descJson, 0, -2);
        } else {
            $pattern = "/punishPath\=\{\[\'(.*?)\'\]/is";
            preg_match($pattern, $descJson, $match);
            $apiUrl = $match[1];
            //获取请求内容
            $descJson = $HttpService->getRequest($apiUrl);
            //转换字符集
            $descJson = self::Utf8String($descJson);
            if (strpos($descJson, 'desc=') === false) {
                return "自动抓取失败，请在新窗口打开链接: {$apiUrl}，复制单引号内容,编辑器点击Html按钮，粘贴";
            }

            $descJson = str_replace('var desc=\'', '', $descJson);
            $descJson = str_replace(["\n", "\t", "\r", "\\\\"], '', $descJson);
            return substr($descJson, 0, -2);
        }
    }

    /**
     * 获取天猫商品详情
     * @param $html
     */
    public static function getTianMaoDesc($html)
    {
		$HttpService = new \HttpService();
        preg_match('/descUrl":"([^<>]*)","httpsDescUrl":"/', $html, $descarr);

        if (!isset($descarr[1])) {
            preg_match('/httpsDescUrl":"([^<>]*)","fetchDcUrl/', $html, $descarr);
            if (!isset($descarr[1])) {
                return '';
            }
        }

        $link = strpos($descarr[1], 'http') ? $descarr[1] : 'http:' . $descarr[1];
        //获取请求内容
        $descJson = HttpService::getRequest($link);
        //转换字符集
        $descJson = self::Utf8String($descJson);

        if (strpos($descJson, 'desc=') !== false) {
            $descJson = str_replace('var desc=\'', '', $descJson);
            $descJson = str_replace(["\n", "\t", "\r", "\\\\"], '', $descJson);
            return substr($descJson, 0, -2);
        } else {
            $pattern = "/punishPath\=\{\[\'(.*?)\'\]/is";
            preg_match($pattern, $descJson, $match);
            $apiUrl = $match[1];
            //获取请求内容
            $descJson = $HttpService->getRequest($apiUrl);
            //转换字符集
            $descJson = self::Utf8String($descJson);
            if (strpos($descJson, 'desc=') === false) {
                return "自动抓取失败，请在新窗口打开链接: {$apiUrl}，复制单引号内容,编辑器点击Html按钮，粘贴";
            }

            $descJson = str_replace('var desc=\'', '', $descJson);
            $descJson = str_replace(["\n", "\t", "\r", "\\\\"], '', $descJson);
            return substr($descJson, 0, -2);
        }
    }

    /**
     * 获取京东商品详情
     */
    public static function getJdDesc($html)
    {
		$HttpService = new \HttpService();
        preg_match('/,(.*?)desc:([^<>]*)\',/i', $html, $descarr);
        if (!isset($descarr[1]) && !isset($descarr[2])) return '';
        $tmpArr = explode(',', $descarr[2]);
        if (count($tmpArr) > 0) {
            $descarr[2] = trim($tmpArr[0]);
        }
        $replace_arr = ['\'', '\',', ' ', ',', '/*', '*/'];
        if (isset($descarr[2])) {
            $d_url = str_replace($replace_arr, '', $descarr[2]);
            $apiUrl = self::formatDescUrl(strpos($d_url, 'http') ? $d_url : 'http:' . $d_url);
        } else {
            $d_url = str_replace($replace_arr, '', $descarr[1]);
            $d_url = self::formatDescUrl($d_url);
            $d_url = rtrim(rtrim($d_url, "?"), "&");
            $apiUrl = substr($d_url, 0, 4) == 'http' ? $d_url : 'http:' . $d_url;
        }

        //获取请求内容
        $descJson = $HttpService->getRequest($apiUrl);
		
        //转换字符集
        $descJson = self::Utf8String($descJson);
        //截取掉多余字符
        if (substr($descJson, 0, 8) == 'showdesc') {
            $descJson = str_replace('showdesc', '', $descJson);
        }

        $descJson = str_replace('data-lazyload=', 'src=', $descJson);
        $descArray = json_decode($descJson, true);
        if (!$descArray) {
            $descArray = ['content' => ''];
        }


        return $descArray['content'];
    }

    /**
     * 京东商品描述网址
     * @param string $url
     * @return string
     */
    public static function formatDescUrl($url = '')
    {
        if (!$url) return '';
        $url = substr($url, 0, 4) == 'http' ? $url : 'http:' . $url;
        if (!strpos($url, '&')) {
            $_arr = explode('?', $url);
            if (!is_array($_arr) || count($_arr) <= 0) return $url;
            return trim($_arr[0]);
        } else {
            $_arr = explode('&', $url);
        }
        if (!is_array($_arr) || count($_arr) <= 0) return $url;
        unset($_arr[count($_arr) - 1]);
        $new_url = '';
        foreach ($_arr as $k => $v) {
            $new_url .= $v . '&';
        }
        return !$new_url ? $url : $new_url;
    }

    /**
     * 获取1688商品描述
     * @param string $html
     * @return mixed|string
     */
    public static function get1688Desc($html)
    {
		$HttpService = new \HttpService();
        preg_match('/\"detailUrl\"\s*:\s*\"(.*?)\"/is', $html, $descarr);

        if (!isset($descarr[1])) {
            return '';
        }

        $apiUrl = $descarr[1];

        //获取请求内容
        $descJson = $HttpService->getRequest($apiUrl);
        //转换字符集
        $descJson = self::Utf8String($descJson);
        //截取掉多余字符
        $descJson = str_replace('var offer_details=', '', $descJson);
        $descJson = str_replace(["\n", "\t", "\r"], '', $descJson);
        $descJson = substr($descJson, 0, -1);
        $descArray = json_decode($descJson, true);

        if (!isset($descArray['content'])) {
            $descArray['content'] = '';
        }

        return $descArray['content'];
    }

    /**
     * 抓取天猫链接商品
     * @param $html
     */
    public static function grabTmall($html)
    {
        $result = [];

        //标题
        preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
        $result['title'] = isset($title['1']) ? str_replace(['-淘宝网', '-tmall.com天猫', ' - 阿里巴巴', ' ', '-', '【图片价格品牌报价】京东', '京东', '【行情报价价格评测】'], '', trim($title['1'])) : '';
        $result['subtitle'] = $result['title'];

        //价格
        $pattern = "/\"price\"\:\s*\"(.+?)\"/is";
        preg_match($pattern, $html, $match);

        $result['productprice'] = (float)$match[1];
        $result['marketprice'] = (float)$match[1];
        $result['costprice'] = (float)$match[1];
        //获取轮播图
        $images = self::getTianMaoImg($html);
        if ($images) {
            $images = array_merge($images);
            $result['thumbs'] = (array)$images;
            $result['thumb'] = is_array($result['thumbs']) && isset($result['thumbs'][0]) ? $result['thumbs'][0] : '';
        }

        //获取产品详情
        $result['description'] = self::getTianMaoDesc($html);

        return $result;
    }

    /**
     * 抓取京东链接商品
     * @param $html
     */
    public static function grabJd($html)
    {
		
		$HttpService = new \HttpService();
        $result = [];
		
		//p($html);
        $crawler = new Crawler($html);
		//p($crawler);die;
		

        //标题
        preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
		
		
		
        $result['title'] = isset($title['1']) ? str_replace(['-淘宝网', '-tmall.com天猫', ' - 阿里巴巴', ' ', '-', '【图片价格品牌报价】京东', '京东', '【行情报价价格评测】'], '', trim($title['1'])) : '';
        $result['subtitle'] = $result['title'];
	

        $pattern = "/\/product\/(\d+)\.html/is";
        preg_match($pattern, $html, $match);
        $itemId = (int)$match[1];

        //价格
        $url = "https://pe.3.cn/prices/mgets?skuids={$itemId}";
        $price = $HttpService->request($url);

        if ($price) {
            $price = json_decode($price, true);
            $result['productprice'] = (float)$price[0]['op'];
            $result['marketprice'] = (float)$price[0]['p'];
            $result['costprice'] = (float)$price[0]['op'];
        }


        //获取轮播图
        $images = self::getJdImg($html);
        if ($images) {
            $images = array_merge($images);
            $result['thumbs'] = (array)$images;
            $result['thumb'] = is_array($result['thumbs']) && isset($result['thumbs'][0]) ? $result['thumbs'][0] : '';
        }
		
		
		
		

        //获取产品详情
        $result['description'] = self::getJdDesc($html);

        return $result;
    }

    /**
     * 抓取1688链接商品
     * @param $html
     */
    public static function grab1688($html)
    {
        $result = [];

        //标题
        preg_match('/<title>([^<>]*)<\/title>/', $html, $title);
        $result['title'] = isset($title['1']) ? str_replace(['-淘宝网', '-tmall.com天猫', ' - 阿里巴巴', ' ', '-', '【图片价格品牌报价】京东', '京东', '【行情报价价格评测】'], '', trim($title['1'])) : '';
        $result['subtitle'] = $result['title'];

        //价格
        $pattern = "/\"price\"\:\s*\"(.+?)\"/is";
        preg_match($pattern, $html, $match);

        $result['productprice'] = (float)$match[1];
        $result['marketprice'] = (float)$match[1];
        $result['costprice'] = (float)$match[1];
        //获取轮播图
        $images = self::get1688Img($html);

        if ($images) {
            if ($images['gaoqing']) {
                $result['thumbs'] = $images['thumb'];
            } else {
                $result['thumbs'] = $images['gaoqing'];
            }
            $result['thumb'] = is_array($result['thumbs']) && isset($result['thumbs'][0]) ? $result['thumbs'][0] : '';
        }

        //获取产品详情
        $result['description'] = self::get1688Desc($html);

        return $result;
    }

    /**
     * 获取商品描述中的所有图片
     * @param string $desc
     * @return array|string
     */
    public static function decodedesc($desc = '')
    {
        $desc = trim($desc);
        if (!$desc) return '';
        preg_match_all('/<img[^>]*?src="([^"]*?)"[^>]*?>/i', $desc, $match);
        if (!isset($match[1]) || count($match[1]) <= 0) {
            preg_match_all('/:url(([^"]*?));/i', $desc, $match);
            if (!isset($match[1]) || count($match[1]) <= 0) return $desc;
        } else {
            preg_match_all('/:url(([^"]*?));/i', $desc, $newmatch);
            if (isset($newmatch[1]) && count($newmatch[1]) > 0) $match[1] = array_merge($match[1], $newmatch[1]);
        }
        $match[1] = array_unique($match[1]); //去掉重复
        foreach ($match[1] as $k => &$v) {
            $_tmp_img = str_replace([')', '(', ';'], '', $v);
            $_tmp_img = strpos($_tmp_img, 'http') ? $_tmp_img : 'http:' . $_tmp_img;
            if (strpos($v, '?')) {
                $_tarr = explode('?', $v);
                $_tmp_img = trim($_tarr[0]);
            }
            $_urls = str_replace(['\'', '"'], '', $_tmp_img);
            if (self::_img_exists($_urls)) $v = $_urls;
        }
        return $match[1];
    }

    /**
     * GET 请求
     * @param string $url
     */
    public static function curlGet($url = '', $time_out = 25 ,$encoding = false)
    {
        if (!$url) {
            return '';
        }
        $agent = $_SERVER['HTTP_USER_AGENT'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: yanshi.jiaodudesign.com'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('user-agent:' . $agent));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);

        $response = curl_exec($ch);

        if ($error = curl_error($ch)) {
            return false;
        }
		if($encoding){
			return mb_convert_encoding($response, 'utf-8', 'GB2312');
		}else{
			return $response;
		}
    }
	
	

    /**
     * 获取天猫商品图片
     * @param string $html
     * @return array|string
     */
    public static function getTianMaoImg($html = '')
    {
        $pic_size = '430';
        preg_match('/<img[^>]*id="J_ImgBooth"[^r]*rc=\"([^"]*)\"[^>]*>/', $html, $img);
        if (isset($img[1])) {
            $_arr = explode('x', $img[1]);
            $filename = $_arr[count($_arr) - 1];
            $pic_size = intval(substr($filename, 0, 3));
        }
        preg_match('|<ul id="J_UlThumb" class="tb-thumb tm-clear">(.*)</ul>|isU', $html, $match);
        preg_match_all('/<img src="(.*?)" \//', $match[1], $images);
        if (!isset($images[1])) return '';
        foreach ($images[1] as $k => &$v) {
            $tmp_v = trim($v);
            $_arr = explode('x', $tmp_v);
            $_fname = $_arr[count($_arr) - 1];
            $_size = intval(substr($_fname, 0, 3));
            if (strpos($tmp_v, '://')) {
                $_arr = explode(':', $tmp_v);
                $r_url = trim($_arr[1]);
            } else {
                $r_url = $tmp_v;
            }
            $str = str_replace($_size, $pic_size, $r_url);
            if (strpos($str, '?')) {
                $_tarr = explode('?', $str);
                $str = trim($_tarr[0]);
            }
            $_i_url = strpos($str, 'http') ? $str : 'http:' . $str;
            if (self::_img_exists($_i_url)) {
                $v = $_i_url;
            } else {
                unset($images[1][$k]);
            }
        }
        return array_unique($images[1]);
    }

    /**
     * 获取京东商品图片
     * @param $html
     */
    public static function getJdImg($html)
    {
        //获取图片服务器网址
        preg_match('/<img(.*?)id="spec-img"(.*?)data-origin=\"(.*?)\"[^>]*>/', $html, $img);
        if (!isset($img[3])) return '';
        $info = parse_url(trim($img[3]));
        if (!$info['host']) return '';
        if (!$info['path']) return '';
        $_tmparr = explode('/', trim($info['path']));
        $url = 'http://' . $info['host'] . '/' . $_tmparr[1] . '/' . str_replace(['jfs', ' '], '', trim($_tmparr[2]));
        preg_match('/imageList:(.*?)"],/is', $html, $img);
        if (!isset($img[1])) {
            return '';
        }
        $_arr = explode(',', $img[1]);
        foreach ($_arr as $k => &$v) {
            $_str = $url . str_replace(['"', '[', ']', ' '], '', trim($v));
            if (strpos($_str, '?')) {
                $_tarr = explode('?', $_str);
                $_str = trim($_tarr[0]);
            }
            if (self::_img_exists($_str)) {
                $v = $_str;
            } else {
                unset($_arr[$k]);
            }
        }
        return array_unique($_arr);
    }

    /**
     * 获取1688商品图片
     * @param $html
     */
    public static function get1688Img($html)
    {
        preg_match('/<ul class=\"nav nav-tabs fd-clr\">(.*?)<\/ul>/is', $html, $img);
        if (!isset($img[0])) {
            return '';
        }
        preg_match_all('/preview":"(.*?)\"\}\'>/is', $img[0], $arrb);
        if (!isset($arrb[1]) || count($arrb[1]) <= 0) {
            return '';
        }

        $thumb = [];
        $gaoqing = [];
        $res = ['thumb' => '', 'gaoqing' => ''];  //缩略图片和高清图片
        foreach ($arrb[1] as $k => $v) {
            $_str = str_replace(['","original":"'], '*', $v);
            $_arr = explode('*', $_str);
            if (is_array($_arr) && isset($_arr[0]) && isset($_arr[1])) {
                if (strpos($_arr[0], '?')) {
                    $_tarr = explode('?', $_arr[0]);
                    $_arr[0] = trim($_tarr[0]);
                }
                if (strpos($_arr[1], '?')) {
                    $_tarr = explode('?', $_arr[1]);
                    $_arr[1] = trim($_tarr[0]);
                }
                if (self::_img_exists($_arr[0])) $thumb[] = trim($_arr[0]);
                if (self::_img_exists($_arr[1])) $gaoqing[] = trim($_arr[1]);
            }
        }
        $res = ['thumb' => array_unique($thumb), 'gaoqing' => array_unique($gaoqing)];  //缩略图片和高清图片
        return $res;
    }

    /*
     * 设置字符串字符集
     * @param string $str 需要设置字符集的字符串
     * @return string
     * */
    public static function Utf8String($str)
    {
        $encode = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        if (strtoupper($encode) != 'UTF-8') $str = mb_convert_encoding($str, 'utf-8', $encode);
        return $str;
    }

    /**
     * 检测远程文件是否存在
     * @param string $url
     * @return bool
     */
    public static function _img_exists($url = '')
    {
        ini_set("max_execution_time", 0);
        $str = @file_get_contents($url, 0, null, 0, 1);

        if (strlen($str) <= 0) {
            return false;
        }
        if ($str) {
            return true;
        } else {
            return false;
        }
    }

}