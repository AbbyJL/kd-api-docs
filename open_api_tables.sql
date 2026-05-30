
-- 开放API应用表
CREATE TABLE IF NOT EXISTS `open_apps` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `app_name` varchar(100) NOT NULL DEFAULT '' COMMENT '应用名称',
  `app_key` varchar(50) NOT NULL DEFAULT '' COMMENT 'APP Key',
  `app_secret` varchar(100) NOT NULL DEFAULT '' COMMENT 'APP Secret',
  `user_id` int(11) DEFAULT 1 COMMENT '关联用户ID',
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人姓名',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '联系电话',
  `remark` text COMMENT '备注',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态 0禁用 1启用',
  `create_time` int(11) DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_app_key` (`app_key`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='开放API应用表';

-- API请求日志表
CREATE TABLE IF NOT EXISTS `open_api_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `app_id` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '应用ID',
  `api_url` varchar(255) NOT NULL DEFAULT '' COMMENT 'API地址',
  `request_method` varchar(10) NOT NULL DEFAULT '' COMMENT '请求方式',
  `request_params` text COMMENT '请求参数',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `create_time` int(11) DEFAULT 0 COMMENT '请求时间',
  PRIMARY KEY (`id`),
  KEY `idx_app_id` (`app_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='API请求日志表';

-- 为订单表添加应用ID字段
ALTER TABLE `express_order` ADD COLUMN `app_id` int(11) unsigned DEFAULT 0 COMMENT '应用ID' AFTER `id`;
ALTER TABLE `express_order` ADD COLUMN `out_order_no` varchar(50) DEFAULT '' COMMENT '外部订单号' AFTER `orderNo`;
ALTER TABLE `express_order` ADD INDEX `idx_app_id` (`app_id`);
ALTER TABLE `express_order` ADD INDEX `idx_out_order_no` (`out_order_no`);
