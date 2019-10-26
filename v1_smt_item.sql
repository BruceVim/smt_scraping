/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100408
 Source Host           : localhost:3306
 Source Schema         : test

 Target Server Type    : MySQL
 Target Server Version : 100408
 File Encoding         : 65001

 Date: 16/10/2019 21:08:35
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for v1_smt_item
-- ----------------------------
DROP TABLE IF EXISTS `v1_smt_item`;
CREATE TABLE `v1_smt_item`  (
  `si_id` int(11) NOT NULL AUTO_INCREMENT,
  `si_product_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
  `si_product_desc` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
  `si_product_msrp` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0.00' COMMENT '零售价区间',
  `si_product_msrp_min` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '零售价最低',
  `si_product_msrp_max` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '零售价最高',
  `si_product_cost_price` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '0.00' COMMENT '原价区间',
  `si_product_cost_price_min` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '原价最低',
  `si_product_cost_price_max` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '原价最高',
  `si_product_stock` int(6) NOT NULL DEFAULT 0 COMMENT '库存',
  `si_product_shipping_date` int(3) NOT NULL DEFAULT 0 COMMENT '运送时间',
  `si_product_attr_json` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT '产品属性',
  `si_create_time` int(10) NOT NULL,
  `si_update_time` int(10) NOT NULL,
  PRIMARY KEY (`si_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for v1_smt_item_variations
-- ----------------------------
DROP TABLE IF EXISTS `v1_smt_item_variations`;
CREATE TABLE `v1_smt_item_variations`  (
  `siv_id` int(11) NOT NULL AUTO_INCREMENT,
  `siv_si_id` int(11) NOT NULL,
  `siv_sku_name` varchar(32) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'sku名称',
  `siv_sku_stock` int(8) NOT NULL,
  `siv_sku_price` decimal(10, 2) NOT NULL,
  `siv_sku_img` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `siv_create_at` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  PRIMARY KEY (`siv_id`) USING BTREE,
  INDEX ```siv_si_id```(`siv_si_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 211 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
