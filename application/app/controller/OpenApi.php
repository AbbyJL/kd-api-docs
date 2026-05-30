
<?php
namespace app\app\controller;

use think\Db;
use think\Cache;

/**
 * 开放API控制器 - 供第三方平台调用
 */
class OpenApi extends Base
{
    // API版本
    const API_VERSION = 'v1';
    
    // 当前请求的应用信息
    private $appInfo = null;

    /**
     * 初始化 - 验证签名
     */
    public function _initialize()
    {
        parent::_initialize();
        
        $this-&gt;verifyAuth();
    }

    /**
     * 验证认证信息
     */
    private function verifyAuth()
    {
        $appKey = input('appKey', '');
        $timestamp = input('timestamp', 0);
        $nonce = input('nonce', '');
        $signature = input('signature', '');

        if (empty($appKey) || empty($timestamp) || empty($nonce) || empty($signature)) {
            $this-&gt;jsonError(4001, '缺少必要的认证参数');
        }

        $timeDiff = time() - $timestamp;
        if ($timeDiff &gt; 300 || $timeDiff &lt; -300) {
            $this-&gt;jsonError(4002, '请求已过期，请检查时间戳');
        }

        $appInfo = Db::name('open_apps')-&gt;where(['app_key' =&gt; $appKey, 'status' =&gt; 1])-&gt;find();
        if (empty($appInfo)) {
            $this-&gt;jsonError(4003, '应用不存在或已被禁用');
        }

        $this-&gt;appInfo = $appInfo;

        $expectedSign = md5($appKey . $timestamp . $nonce . $appInfo['app_secret']);
        if (strcasecmp($signature, $expectedSign) !== 0) {
            $this-&gt;jsonError(4004, '签名验证失败');
        }

        $this-&gt;logRequest();
    }

    /**
     * 记录请求日志
     */
    private function logRequest()
    {
        $logData = [
            'app_id' =&gt; $this-&gt;appInfo['id'],
            'api_url' =&gt; request()-&gt;url(),
            'request_method' =&gt; request()-&gt;method(),
            'request_params' =&gt; json_encode(input(), JSON_UNESCAPED_UNICODE),
            'ip' =&gt; request()-&gt;ip(),
            'create_time' =&gt; time()
        ];
        Db::name('open_api_logs')-&gt;insert($logData);
    }

    /**
     * 获取AccessToken
     */
    public function getAccessToken()
    {
        $cacheKey = 'openapi_access_token_' . $this-&gt;appInfo['id'];
        $cachedToken = Cache::get($cacheKey);
        
        if ($cachedToken) {
            $this-&gt;jsonSuccess([
                'accessToken' =&gt; $cachedToken,
                'expiresIn' =&gt; 7200
            ]);
        }

        $accessToken = md5($this-&gt;appInfo['app_key'] . time() . rand_string(16));
        Cache::set($cacheKey, $accessToken, 7200);

        $this-&gt;jsonSuccess([
            'accessToken' =&gt; $accessToken,
            'expiresIn' =&gt; 7200
        ]);
    }

    /**
     * 运费预估
     */
    public function estimate()
    {
        $senderProvince = input('senderProvince', '');
        $senderCity = input('senderCity', '');
        $senderDistrict = input('senderDistrict', '');
        $senderAddress = input('senderAddress', '');
        $receiveProvince = input('receiveProvince', '');
        $receiveCity = input('receiveCity', '');
        $receiveDistrict = input('receiveDistrict', '');
        $receiveAddress = input('receiveAddress', '');
        $weight = input('weight', 0);
        $length = input('length', 0);
        $width = input('width', 0);
        $height = input('height', 0);

        if (empty($senderProvince) || empty($senderCity) || empty($receiveProvince) || empty($receiveCity)) {
            $this-&gt;jsonError(5001, '寄件/收件地址信息不完整');
        }

        $expressList = model('Setting')-&gt;getExpressList([
            'senderProvince' =&gt; $senderProvince,
            'senderCity' =&gt; $senderCity,
            'senderDistrict' =&gt; $senderDistrict,
            'senderAddress' =&gt; $senderAddress,
            'recipientsProvince' =&gt; $receiveProvince,
            'recipientsCity' =&gt; $receiveCity,
            'recipientsDistrict' =&gt; $receiveDistrict,
            'recipientsAddress' =&gt; $receiveAddress,
            'weight' =&gt; $weight,
            'long' =&gt; $length,
            'width' =&gt; $width,
            'height' =&gt; $height,
            'uid' =&gt; $this-&gt;appInfo['user_id'] ?? 1
        ]);

        $result = [];
        foreach ($expressList as $item) {
            $result[] = [
                'expressName' =&gt; $item['name'],
                'type' =&gt; $item['type'],
                'price' =&gt; isset($item['sumMoneyYuan']) ? round($item['sumMoneyYuan'] / 100, 2) : 0,
                'discountPrice' =&gt; isset($item['preOrderFee']) ? round($item['preOrderFee'] / 100, 2) : 0,
                'firstPrice' =&gt; isset($item['firstPrice']) ? $item['firstPrice'] : 0,
                'isBest' =&gt; isset($item['isBest']) ? $item['isBest'] : false
            ];
        }

        $this-&gt;jsonSuccess([
            'list' =&gt; $result,
            'totalCount' =&gt; count($result)
        ]);
    }

    /**
     * 创建订单
     */
    public function createOrder()
    {
        $outOrderNo = input('outOrderNo', '');
        $senderName = input('senderName', '');
        $senderMobile = input('senderMobile', '');
        $senderProvince = input('senderProvince', '');
        $senderCity = input('senderCity', '');
        $senderDistrict = input('senderDistrict', '');
        $senderAddress = input('senderAddress', '');
        $receiveName = input('receiveName', '');
        $receiveMobile = input('receiveMobile', '');
        $receiveProvince = input('receiveProvince', '');
        $receiveCity = input('receiveCity', '');
        $receiveDistrict = input('receiveDistrict', '');
        $receiveAddress = input('receiveAddress', '');
        $expressType = input('expressType', 1);
        $weight = input('weight', 0);
        $length = input('length', 0);
        $width = input('width', 0);
        $height = input('height', 0);
        $goodsName = input('goodsName', '日用品');
        $remark = input('remark', '');

        if (empty($outOrderNo)) {
            $this-&gt;jsonError(6001, '外部订单号不能为空');
        }

        if (empty($senderName) || empty($senderMobile) || empty($receiveName) || empty($receiveMobile)) {
            $this-&gt;jsonError(6002, '寄件人或收件人信息不完整');
        }

        $existOrder = Db::name('express_order')-&gt;where([
            'app_id' =&gt; $this-&gt;appInfo['id'],
            'out_order_no' =&gt; $outOrderNo
        ])-&gt;find();
        
        if ($existOrder) {
            $this-&gt;jsonError(6003, '订单已存在');
        }

        $orderData = [
            'orderNo' =&gt; 'KF' . date('YmdHis') . rand(1000, 9999),
            'out_order_no' =&gt; $outOrderNo,
            'app_id' =&gt; $this-&gt;appInfo['id'],
            'sendName' =&gt; $senderName,
            'sendMobile' =&gt; $senderMobile,
            'sendProvince' =&gt; $senderProvince,
            'sendCity' =&gt; $senderCity,
            'sendArea' =&gt; $senderDistrict,
            'sendAddress' =&gt; $senderAddress,
            'receiveName' =&gt; $receiveName,
            'receiveMobile' =&gt; $receiveMobile,
            'receiveProvince' =&gt; $receiveProvince,
            'receiveCity' =&gt; $receiveCity,
            'receiveArea' =&gt; $receiveDistrict,
            'receiveAddress' =&gt; $receiveAddress,
            'type' =&gt; $expressType,
            'wight' =&gt; $weight,
            'length' =&gt; $length,
            'width' =&gt; $width,
            'height' =&gt; $height,
            'goodsName' =&gt; $goodsName,
            'remark' =&gt; $remark,
            'orderStatus' =&gt; 0,
            'orderStatusName' =&gt; '待付款',
            'user_id' =&gt; $this-&gt;appInfo['user_id'] ?? 1,
            'create_time' =&gt; time()
        ];

        $orderId = Db::name('express_order')-&gt;insertGetId($orderData);

        $this-&gt;jsonSuccess([
            'orderId' =&gt; $orderId,
            'orderNo' =&gt; $orderData['orderNo'],
            'outOrderNo' =&gt; $outOrderNo,
            'status' =&gt; 0,
            'statusName' =&gt; '待付款'
        ]);
    }

    /**
     * 查询订单
     */
    public function queryOrder()
    {
        $orderId = input('orderId', 0);
        $orderNo = input('orderNo', '');
        $outOrderNo = input('outOrderNo', '');

        if (empty($orderId) &amp;&amp; empty($orderNo) &amp;&amp; empty($outOrderNo)) {
            $this-&gt;jsonError(7001, '缺少查询参数');
        }

        $where = ['app_id' =&gt; $this-&gt;appInfo['id']];
        if ($orderId) {
            $where['id'] = $orderId;
        } elseif ($orderNo) {
            $where['orderNo'] = $orderNo;
        } else {
            $where['out_order_no'] = $outOrderNo;
        }

        $order = Db::name('express_order')-&gt;where($where)-&gt;find();
        if (empty($order)) {
            $this-&gt;jsonError(7002, '订单不存在');
        }

        $result = [
            'orderId' =&gt; $order['id'],
            'orderNo' =&gt; $order['orderNo'],
            'outOrderNo' =&gt; $order['out_order_no'],
            'status' =&gt; $order['orderStatus'],
            'statusName' =&gt; $order['orderStatusName'],
            'senderName' =&gt; $order['sendName'],
            'senderMobile' =&gt; $order['sendMobile'],
            'receiveName' =&gt; $order['receiveName'],
            'receiveMobile' =&gt; $order['receiveMobile'],
            'waybillNo' =&gt; $order['deliveryId'],
            'expressNo' =&gt; $order['expressNo'],
            'weight' =&gt; $order['wight'],
            'amount' =&gt; round(($order['sumMoneyYuan'] ?? 0) / 100, 2),
            'createTime' =&gt; date('Y-m-d H:i:s', $order['create_time'])
        ];

        $this-&gt;jsonSuccess($result);
    }

    /**
     * 查询物流轨迹
     */
    public function queryTrack()
    {
        $orderId = input('orderId', 0);
        $orderNo = input('orderNo', '');
        $waybillNo = input('waybillNo', '');

        if (empty($orderId) &amp;&amp; empty($orderNo) &amp;&amp; empty($waybillNo)) {
            $this-&gt;jsonError(8001, '缺少查询参数');
        }

        $where = ['app_id' =&gt; $this-&gt;appInfo['id']];
        if ($orderId) {
            $where['id'] = $orderId;
        } elseif ($orderNo) {
            $where['orderNo'] =&gt; $orderNo;
        } elseif ($waybillNo) {
            $where['deliveryId'] = $waybillNo;
        }

        $order = Db::name('express_order')-&gt;where($where)-&gt;find();
        if (empty($order)) {
            $this-&gt;jsonError(8002, '订单不存在');
        }

        $trackData = model('ExpressOrder')-&gt;logisticsInfo($order['id']);
        
        $result = [
            'orderId' =&gt; $order['id'],
            'orderNo' =&gt; $order['orderNo'],
            'waybillNo' =&gt; $order['deliveryId'],
            'status' =&gt; $order['orderStatus'],
            'statusName' =&gt; $order['orderStatusName'],
            'trackingList' =&gt; []
        ];

        if (isset($trackData['logistics_info']) &amp;&amp; is_array($trackData['logistics_info'])) {
            foreach ($trackData['logistics_info'] as $item) {
                $result['trackingList'][] = [
                    'trackTime' =&gt; $item['time'] ?? '',
                    'description' =&gt; $item['description'] ?? '',
                    'location' =&gt; $item['location'] ?? ''
                ];
            }
        }

        $this-&gt;jsonSuccess($result);
    }

    /**
     * 取消订单
     */
    public function cancelOrder()
    {
        $orderId = input('orderId', 0);
        $orderNo = input('orderNo', '');
        $outOrderNo = input('outOrderNo', '');
        $reason = input('reason', '');

        if (empty($orderId) &amp;&amp; empty($orderNo) &amp;&amp; empty($outOrderNo)) {
            $this-&gt;jsonError(9001, '缺少订单参数');
        }

        $where = ['app_id' =&gt; $this-&gt;appInfo['id']];
        if ($orderId) {
            $where['id'] = $orderId;
        } elseif ($orderNo) {
            $where['orderNo'] = $orderNo;
        } else {
            $where['out_order_no'] =&gt; $outOrderNo;
        }

        $order = Db::name('express_order')-&gt;where($where)-&gt;find();
        if (empty($order)) {
            $this-&gt;jsonError(9002, '订单不存在');
        }

        if (!in_array($order['orderStatus'], [0, 1, 2])) {
            $this-&gt;jsonError(9003, '当前订单状态不允许取消');
        }

        $result = model('ExpressOrder')-&gt;cancel($order, $order['id'], $reason ?: 'API取消订单', 0, 1);

        if ($result) {
            $this-&gt;jsonSuccess([
                'orderId' =&gt; $order['id'],
                'orderNo' =&gt; $order['orderNo'],
                'msg' =&gt; '取消成功'
            ]);
        } else {
            $this-&gt;jsonError(9004, '取消失败');
        }
    }

    /**
     * 获取快递公司列表
     */
    public function getExpressList()
    {
        $expressList = Db::name('express_cate')
            -&gt;where(['status' =&gt; 1])
            -&gt;order('orderby asc, cate_id desc')
            -&gt;select();

        $result = [];
        foreach ($expressList as $item) {
            $result[] = [
                'expressId' =&gt; $item['cate_id'],
                'expressName' =&gt; $item['cate_name'],
                'type' =&gt; $item['type'],
                'tag' =&gt; $item['tag'],
                'isBest' =&gt; $item['isBest'] == 1,
                'logo' =&gt; config_weixin_img($item['photo'])
            ];
        }

        $this-&gt;jsonSuccess([
            'list' =&gt; $result,
            'totalCount' =&gt; count($result)
        ]);
    }

    /**
     * 成功响应
     */
    private function jsonSuccess($data = [])
    {
        echo json_encode([
            'code' =&gt; 0,
            'msg' =&gt; 'success',
            'data' =&gt; $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 错误响应
     */
    private function jsonError($code, $msg)
    {
        echo json_encode([
            'code' =&gt; $code,
            'msg' =&gt; $msg,
            'data' =&gt; null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
