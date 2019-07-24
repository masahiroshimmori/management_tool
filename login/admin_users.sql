-- 管理画面（管理者）はユーザと違ってメールアドレスを登録することはしないはず。
-- また、管理も煩雑ではないためadmin_idは文字列でauto_incrementもしていない。
-- 管理者テーブルの作成
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users(
`admin_id` varbinary(255) NOT NULL UNIQUE COMMENT '識別するためのID',
`name` varchar(128) NOT NULL COMMENT '表示用の名前',
`pass` varbinary(255) NOT NULL COMMENT 'パスワード：password_hash()関数利用',
`role` tinyint unsigned default 0 COMMENT 'ユーザ権限：0/閲覧のみ 1/通常操作のみ 2/管理者を管理できる',
`created` datetime NOT NULL COMMENT '作成日時',
`updated` datetime NOT NULL COMMENT '修正日時',
PRIMARY KEY(`admin_id`)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB, COMMENT='1レコードが1管理者を意味するテーブル';


-- 管理者ユーザロックテーブルの作成
DROP TABLE IF EXISTS admin_user_login_lock;
CREATE TABLE admin_user_login_lock(
`admin_id` varbinary(255) NOT NULL COMMENT '識別するためのID',
`error_count` tinyint unsigned NOT NULL COMMENT 'ログインエラー回数(ログイン成功したらリセット)',
`lock_time` datetime NOT NULL COMMENT 'ロック時間。0000-00-00 00:00:00ならロックされていない。',
PRIMARY KEY(`admin_id`)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB, COMMENT='1レコードが1ユーザのロック状態を意味するテーブル';


-- 一件「全権限管理者」を作成作成
-- パスのxxxx部はmake_pass.phpから取得して入力(コピペ)すること
INSERT INTO admin_users(admin_id, name, pass, role, created, updated)VALUES('root', '管理者', '$2y$10$MbgsqJnSh1G.idB4hfouf.6bMCQbm8.uKMKgXrnLdrGBiRCrcpzvW', 2, now(), now());