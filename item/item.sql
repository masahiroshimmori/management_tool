-- 商品テーブルの作成
DROP TABLE IF EXISTS item;
CREATE TABLE item(
`item_code` varbinary(50) NOT NULL UNIQUE COMMENT '商品コード',
`item_name` varchar(128) NOT NULL COMMENT '商品名',
`item_price` int unsigned default 0 COMMENT '売価',
`item_cost` int unsigned default 0 COMMENT '原価',
`item_tax` tinyint unsigned default 0 COMMENT '消費税8%or10%',
`created` datetime NOT NULL COMMENT '作成日時',
`updated` datetime NOT NULL COMMENT '修正日時',
PRIMARY KEY(`item_code`)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB, COMMENT='1レコードが1管理者を意味するテーブル';