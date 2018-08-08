
-- merchant表添加判断试点商户的字段
ALTER TABLE merchants ADD is_pilot TINYINT NOT NULL DEFAULT 0 COMMENT '是否是试点商户 0普通商户 1试点商户';

-- user表添加微信昵称和微信头像字段
ALTER TABLE `users` ADD COLUMN `wx_nick_name` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '微信昵称' AFTER `status`;
ALTER TABLE `users` ADD COLUMN `wx_avatar_url` VARCHAR(191) NOT NULL DEFAULT '' COMMENT '微信头像' AFTER `wx_nick_name`;

-- merchants,merchant_drafts表添加法人身份证号码字段
ALTER TABLE merchants ADD legal_id_card_num varchar(100) COMMENT '法人身份证号码' AFTER legal_id_card_pic_b;
ALTER TABLE merchant_drafts ADD legal_id_card_num varchar(100) COMMENT '法人身份证号码' AFTER legal_id_card_pic_b;