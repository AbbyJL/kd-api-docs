
# 开放API文档

## 概述

本文档描述了快递系统开放API的使用方法，包括认证方式、接口说明等。

## 基础信息

- 协议：HTTPS
- 数据格式：JSON
- 字符编码：UTF-8

## 认证方式
z
### 签名机制

所有API请求都需要通过签名验证，签名生成方式如下：

```
signature = md5(appKey + timestamp + nonce + appSecret)
```

### 请求参数

所有API请求都需要包含以下公共参数：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| appKey | string | 是 | 应用Key |
| timestamp | int | 是 | 当前时间戳（秒），误差不超过5分钟 |
| nonce | string | 是 | 随机字符串 |
| signature | string | 是 | 签名 |

### 响应格式

成功响应：
```json
{
    "code": 0,
    "msg": "success",
    "data": { ... }
}
```

失败响应：
```json
{
    "code": 4001,
    "msg": "错误信息",
    "data": null
}
```

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 0 | 成功 |
| 4001 | 缺少必要的认证参数 |
| 4002 | 请求已过期 |
| 4003 | 应用不存在或已被禁用 |
| 4004 | 签名验证失败 |
| 5001 | 寄件/收件地址信息不完整 |
| 6001 | 外部订单号不能为空 |
| 6002 | 寄件人或收件人信息不完整 |
| 6003 | 订单已存在 |
| 7001 | 缺少查询参数 |
| 7002 | 订单不存在 |
| 8001 | 缺少查询参数 |
| 8002 | 订单不存在 |
| 9001 | 缺少订单参数 |
| 9002 | 订单不存在 |
| 9003 | 当前订单状态不允许取消 |
| 9004 | 取消失败 |

## API接口

### 1. 获取AccessToken

**接口地址**：`/openapi/getAccessToken`

**请求方式**：POST

**请求参数**：仅公共参数

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "accessToken": "xxx",
        "expiresIn": 7200
    }
}
```

### 2. 运费预估

**接口地址**：`/openapi/estimate`

**请求方式**：POST

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| senderProvince | string | 是 | 寄件省份 |
| senderCity | string | 是 | 寄件城市 |
| senderDistrict | string | 否 | 寄件区县 |
| senderAddress | string | 否 | 寄件详细地址 |
| receiveProvince | string | 是 | 收件省份 |
| receiveCity | string | 是 | 收件城市 |
| receiveDistrict | string | 否 | 收件区县 |
| receiveAddress | string | 否 | 收件详细地址 |
| weight | float | 是 | 重量(kg) |
| length | float | 否 | 长(cm) |
| width | float | 否 | 宽(cm) |
| height | float | 否 | 高(cm) |

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "list": [
            {
                "expressName": "顺丰",
                "type": 1,
                "price": 15.00,
                "discountPrice": 12.00,
                "firstPrice": 12,
                "isBest": true
            }
        ],
        "totalCount": 1
    }
}
```

### 3. 创建订单

**接口地址**：`/openapi/createOrder`

**请求方式**：POST

**请求参数**：

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| outOrderNo | string | 是 | 外部订单号 |
| senderName | string | 是 | 寄件人姓名 |
| senderMobile | string | 是 | 寄件人电话 |
| senderProvince | string | 是 | 寄件省份 |
| senderCity | string | 是 | 寄件城市 |
| senderDistrict | string | 否 | 寄件区县 |
| senderAddress | string | 是 | 寄件详细地址 |
| receiveName | string | 是 | 收件人姓名 |
| receiveMobile | string | 是 | 收件人电话 |
| receiveProvince | string | 是 | 收件省份 |
| receiveCity | string | 是 | 收件城市 |
| receiveDistrict | string | 否 | 收件区县 |
| receiveAddress | string | 是 | 收件详细地址 |
| expressType | int | 否 | 快递类型 |
| weight | float | 是 | 重量(kg) |
| length | float | 否 | 长(cm) |
| width | float | 否 | 宽(cm) |
| height | float | 否 | 高(cm) |
| goodsName | string | 否 | 物品名称 |
| remark | string | 否 | 备注 |

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "orderId": 123,
        "orderNo": "KF202401011200001",
        "outOrderNo": "TEST123",
        "status": 0,
        "statusName": "待付款"
    }
}
```

### 4. 查询订单

**接口地址**：`/openapi/queryOrder`

**请求方式**：POST

**请求参数**：（以下三选一即可）

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| orderId | int | 否 | 订单ID |
| orderNo | string | 否 | 订单号 |
| outOrderNo | string | 否 | 外部订单号 |

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "orderId": 123,
        "orderNo": "KF202401011200001",
        "outOrderNo": "TEST123",
        "status": 2,
        "statusName": "已接单",
        "senderName": "张三",
        "senderMobile": "13800138000",
        "receiveName": "李四",
        "receiveMobile": "13900139000",
        "waybillNo": "SF1234567890",
        "expressNo": "",
        "weight": 2.5,
        "amount": 15.00,
        "createTime": "2024-01-01 12:00:00"
    }
}
```

### 5. 查询物流轨迹

**接口地址**：`/openapi/queryTrack`

**请求方式**：POST

**请求参数**：（以下三选一即可）

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| orderId | int | 否 | 订单ID |
| orderNo | string | 否 | 订单号 |
| waybillNo | string | 否 | 运单号 |

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "orderId": 123,
        "orderNo": "KF202401011200001",
        "waybillNo": "SF1234567890",
        "status": 3,
        "statusName": "已取件",
        "trackingList": [
            {
                "trackTime": "2024-01-01 14:30:00",
                "description": "快件已在【深圳】揽收",
                "location": "深圳"
            }
        ]
    }
}
```

### 6. 取消订单

**接口地址**：`/openapi/cancelOrder`

**请求方式**：POST

**请求参数**：（以下三选一即可）

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| orderId | int | 否 | 订单ID |
| orderNo | string | 否 | 订单号 |
| outOrderNo | string | 否 | 外部订单号 |
| reason | string | 否 | 取消原因 |

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "orderId": 123,
        "orderNo": "KF202401011200001",
        "msg": "取消成功"
    }
}
```

### 7. 获取快递公司列表

**接口地址**：`/openapi/getExpressList`

**请求方式**：POST

**请求参数**：仅公共参数

**响应示例**：
```json
{
    "code": 0,
    "msg": "success",
    "data": {
        "list": [
            {
                "expressId": 1,
                "expressName": "顺丰",
                "type": 1,
                "tag": "特快",
                "isBest": true,
                "logo": "https://..."
            }
        ],
        "totalCount": 1
    }
}
```

## PHP SDK使用示例

```php
&lt;?php
require 'KdApiSdk.php';

// 初始化SDK
$sdk = new KdApiSdk(
    'https://yourdomain.com',
    '你的APP Key',
    '你的APP Secret'
);

// 创建订单
try {
    $result = $sdk-&gt;createOrder([
        'outOrderNo' =&gt; 'TEST' . time(),
        'senderName' =&gt; '张三',
        'senderMobile' =&gt; '13800138000',
        'senderProvince' =&gt; '广东省',
        'senderCity' =&gt; '深圳市',
        'senderAddress' =&gt; '科技园路1号',
        'receiveName' =&gt; '李四',
        'receiveMobile' =&gt; '13900139000',
        'receiveProvince' =&gt; '北京市',
        'receiveCity' =&gt; '北京市',
        'receiveAddress' =&gt; '望京SOHO',
        'weight' =&gt; 2,
        'goodsName' =&gt; '文件'
    ]);
    
    if ($result['code'] == 0) {
        echo "订单创建成功，订单号：" . $result['data']['orderNo'];
    }
} catch (Exception $e) {
    echo "错误：" . $e-&gt;getMessage();
}
?&gt;
```

## 配置路由

在 `application/route.php` 中添加：

```php
return [
    // 开放API路由
    'openapi/getAccessToken' =&gt; 'app/OpenApi/getAccessToken',
    'openapi/estimate' =&gt; 'app/OpenApi/estimate',
    'openapi/createOrder' =&gt; 'app/OpenApi/createOrder',
    'openapi/queryOrder' =&gt; 'app/OpenApi/queryOrder',
    'openapi/queryTrack' =&gt; 'app/OpenApi/queryTrack',
    'openapi/cancelOrder' =&gt; 'app/OpenApi/cancelOrder',
    'openapi/getExpressList' =&gt; 'app/OpenApi/getExpressList',
];
```

或者直接使用完整路径访问：
- `/index.php/app/open_api/getAccessToken`
- `/index.php/app/open_api/estimate`
- 等等...
