-- 顧客テーブルの作成
DROP TABLE IF EXISTS user;
CREATE TABLE user(
`user_code` varchar(50) NOT NULL UNIQUE COMMENT '顧客コード',
`user_name` varchar(128) NOT NULL COMMENT '顧客名',
`user_post` varchar(7) NOT NULL COMMENT '郵便番号',
`user_address` varchar(255) NOT NULL COMMENT '住所',
`user_tel` varchar(15) NOT NULL COMMENT '電話番号',
`user_email` varchar(255) NOT NULL COMMENT 'email',
`created` datetime NOT NULL COMMENT '作成日時',
`updated` datetime NOT NULL COMMENT '修正日時',
PRIMARY KEY(`user_code`)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB, COMMENT='1レコードが1管理者を意味するテーブル';