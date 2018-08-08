
-- merchant表添加判断试点商户的字段
ALTER TABLE merchants ADD is_pilot TINYINT NOT NULL DEFAULT 0 COMMENT '是否是试点商户 0普通商户 1试点商户';

