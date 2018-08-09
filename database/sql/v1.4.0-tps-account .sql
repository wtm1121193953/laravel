CREATE TABLE `tps_binds` (
  `tps_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键',
  `tps_bind_type` tinyint(1) DEFAULT '0' COMMENT '绑定类型，1：用户，2：商户，3：运营中心。',
  `tps_bind_account` varchar(50) DEFAULT NULL COMMENT '绑定TPS账号，运营中心使用邮箱账号，商户使用手机号',
  `tps_create_time` int(11) DEFAULT '0' COMMENT '创建时间戳',
  `tps_create_account` varchar(50) DEFAULT NULL COMMENT '创建账号',
  PRIMARY KEY (`tps_id`),
  UNIQUE KEY `tps_bind_account_UNIQUE` (`tps_bind_account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='TPS账号绑定表';


CREATE TABLE `tps_binds_verifyings` (
  `ver_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键。',
  `ver_code` varchar(20) DEFAULT NULL COMMENT '验证码，手机验证码或者邮箱验证码。',
  `ver_validtime` int(11) DEFAULT '0' COMMENT '验证码的有效时间戳，此时间以内生效。',
  `ver_account` varchar(50) DEFAULT NULL COMMENT '生成验证码的关联账号。',
  PRIMARY KEY (`ver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='TPS账号绑定校验表，主要用于存储临时发送的手机验证码和邮箱验证码，指定时间内验证其有效性。';
