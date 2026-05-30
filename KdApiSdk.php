
&lt;?php
/**
 * 快递API SDK
 * 用于调用开放平台API
 */
class KdApiSdk
{
    private $apiUrl;
    private $appKey;
    private $appSecret;
    private $accessToken;
    
    public function __construct($apiUrl, $appKey, $appSecret)
    {
        $this-&gt;apiUrl = rtrim($apiUrl, '/');
        $this-&gt;appKey = $appKey;
        $this-&gt;appSecret = $appSecret;
    }
    
    /**
     * 获取AccessToken
     */
    public function getAccessToken()
    {
        if ($this-&gt;accessToken) {
            return $this-&gt;accessToken;
        }
        
        $result = $this-&gt;request('/openapi/getAccessToken', []);
        if ($result['code'] == 0) {
            $this-&gt;accessToken = $result['data']['accessToken'];
            return $this-&gt;accessToken;
        }
        throw new Exception('获取AccessToken失败: ' . $result['msg']);
    }
    
    /**
     * 运费预估
     */
    public function estimate($params)
    {
        return $this-&gt;request('/openapi/estimate', $params);
    }
    
    /**
     * 创建订单
     */
    public function createOrder($params)
    {
        return $this-&gt;request('/openapi/createOrder', $params);
    }
    
    /**
     * 查询订单
     */
    public function queryOrder($params)
    {
        return $this-&gt;request('/openapi/queryOrder', $params);
    }
    
    /**
     * 查询轨迹
     */
    public function queryTrack($params)
    {
        return $this-&gt;request('/openapi/queryTrack', $params);
    }
    
    /**
     * 取消订单
     */
    public function cancelOrder($params)
    {
        return $this-&gt;request('/openapi/cancelOrder', $params);
    }
    
    /**
     * 获取快递公司列表
     */
    public function getExpressList()
    {
        return $this-&gt;request('/openapi/getExpressList', []);
    }
    
    /**
     * 发送请求
     */
    private function request($path, $params)
    {
        $url = $this-&gt;apiUrl . $path;
        $timestamp = time();
        $nonce = $this-&gt;generateNonce();
        $signature = md5($this-&gt;appKey . $timestamp . $nonce . $this-&gt;appSecret);
        
        $params['appKey'] = $this-&gt;appKey;
        $params['timestamp'] = $timestamp;
        $params['nonce'] = $nonce;
        $params['signature'] = $signature;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            throw new Exception('HTTP请求失败: ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception('JSON解析失败');
        }
        
        return $result;
    }
    
    /**
     * 生成随机字符串
     */
    private function generateNonce()
    {
        return md5(uniqid(mt_rand(), true));
    }
}

// ============================================
// 使用示例
// ============================================

/*
// 初始化SDK
$sdk = new KdApiSdk(
    'https://yourdomain.com',
    '你的APP Key',
    '你的APP Secret'
);

// 1. 获取AccessToken
try {
    $token = $sdk-&gt;getAccessToken();
    echo "AccessToken: " . $token . "\n";
} catch (Exception $e) {
    echo "错误: " . $e-&gt;getMessage() . "\n";
}

// 2. 运费预估
$estimateParams = [
    'senderProvince' =&gt; '广东省',
    'senderCity' =&gt; '深圳市',
    'senderDistrict' =&gt; '南山区',
    'senderAddress' =&gt; '科技园路1号',
    'receiveProvince' =&gt; '北京市',
    'receiveCity' =&gt; '北京市',
    'receiveDistrict' =&gt; '朝阳区',
    'receiveAddress' =&gt; '望京SOHO',
    'weight' =&gt; 2,
    'length' =&gt; 20,
    'width' =&gt; 15,
    'height' =&gt; 10
];

$estimateResult = $sdk-&gt;estimate($estimateParams);
if ($estimateResult['code'] == 0) {
    print_r($estimateResult['data']);
}

// 3. 创建订单
$orderParams = [
    'outOrderNo' =&gt; 'TEST' . time(),
    'senderName' =&gt; '张三',
    'senderMobile' =&gt; '13800138000',
    'senderProvince' =&gt; '广东省',
    'senderCity' =&gt; '深圳市',
    'senderDistrict' =&gt; '南山区',
    'senderAddress' =&gt; '科技园路1号',
    'receiveName' =&gt; '李四',
    'receiveMobile' =&gt; '13900139000',
    'receiveProvince' =&gt; '北京市',
    'receiveCity' =&gt; '北京市',
    'receiveDistrict' =&gt; '朝阳区',
    'receiveAddress' =&gt; '望京SOHO',
    'expressType' =&gt; 1,
    'weight' =&gt; 2,
    'length' =&gt; 20,
    'width' =&gt; 15,
    'height' =&gt; 10,
    'goodsName' =&gt; '文件',
    'remark' =&gt; '测试订单'
];

$createResult = $sdk-&gt;createOrder($orderParams);
if ($createResult['code'] == 0) {
    echo "订单创建成功: " . $createResult['data']['orderNo'] . "\n";
}

// 4. 查询订单
$queryParams = ['outOrderNo' =&gt; 'TEST123456'];
$queryResult = $sdk-&gt;queryOrder($queryParams);
if ($queryResult['code'] == 0) {
    print_r($queryResult['data']);
}

// 5. 查询轨迹
$trackParams = ['outOrderNo' =&gt; 'TEST123456'];
$trackResult = $sdk-&gt;queryTrack($trackParams);
if ($trackResult['code'] == 0) {
    print_r($trackResult['data']);
}

// 6. 取消订单
$cancelParams = ['outOrderNo' =&gt; 'TEST123456', 'reason' =&gt; '测试取消'];
$cancelResult = $sdk-&gt;cancelOrder($cancelParams);
if ($cancelResult['code'] == 0) {
    echo "订单取消成功\n";
}

// 7. 获取快递公司列表
$expressResult = $sdk-&gt;getExpressList();
if ($expressResult['code'] == 0) {
    print_r($expressResult['data']);
}
*/
