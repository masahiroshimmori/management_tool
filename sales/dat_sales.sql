-- 注文情報テーブル
DROP TABLE IF EXISTS dat_sales;
CREATE TABLE dat_sales(
sales_id int(50) unsigned AUTO_INCREMENT NOT NULL COMMENT '注文id',
order_id varchar(50) COMMENT '注文番号(任意)',
sales_date date NOT NULL COMMENT '登録日',
user_code varchar(50) NOT NULL COMMENT '顧客コード',
user_name varchar(50) NOT NULL COMMENT '顧客名',
total_calc bigint NOT NULL COMMENT '小計',
tax8 int(50) NOT NULL COMMENT '消費税8％',
tax10 int(50) NOT NULL COMMENT '消費税10％',
total_sum bigint NOT NULL COMMENT '合計',
created datetime NOT NULL COMMENT '作成日時',
updated datetime NOT NULL COMMENT '修正日時',
PRIMARY KEY(sales_id)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB;


-- 注文明細
DROP TABLE IF EXISTS dat_user_item;
CREATE TABLE dat_user_item(
user_sales_id int(50) unsigned AUTO_INCREMENT NOT NULL COMMENT '注文明細id',
user_item_dat int(50) unsigned NOT NULL COMMENT '注文明細通し番号',
item_code varchar(50) NOT NULL COMMENT '商品コード',
item_name varchar(50) NOT NULL COMMENT '商品名',
item_price int(50) NOT NULL COMMENT '単価',
item_mount int(50) NOT NULL COMMENT '数量',
item_tax int(50) unsigned NOT NULL COMMENT '消費税',
PRIMARY KEY(user_sales_id)
)CHARACTER SET 'utf8mb4', ENGINE = InnoDB;