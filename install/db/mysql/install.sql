
DROP TABLE IF EXISTS `tdauto_price_user`;

CREATE TABLE `tdauto_price_user` (
  `ID` int(18) NOT NULL AUTO_INCREMENT,
  `USER_ID` int(18) unsigned NOT NULL,
  `AIRUS_CODE` int(18) unsigned NOT NULL,
  `PRICE` decimal(12,2) NOT NULL,
  `STATUS` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UNIQUE_USER_ID_AIRUS_CODE` (`USER_ID`,`AIRUS_CODE`),
  KEY `INDEX_USER_ID_AIRUS_CODE` (`USER_ID`,`AIRUS_CODE`)
  /*KEY `INDEX_USER_ID` (`USER_ID`),
  KEY `INDEX_STATUS` (`STATUS`)*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `tdauto_price_discount`;

CREATE TABLE `tdauto_price_discount` (
  `USER_ID` int(18) NOT NULL,
  `ACTIVE` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'N',
  `SHOW_TYPE` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'P',
  `BT_0_100` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_100_500` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_500_1000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_1000_3000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_3000_5000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_5000_10000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_10000_15000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_15000_50000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_50000` decimal(10,2) NOT NULL DEFAULT '0.00',
  `BT_0_100_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_100_500_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_500_1000_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_1000_3000_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_3000_5000_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_5000_10000_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_10000_15000_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_15000_50000_TYPE` char(1) NOT NULL DEFAULT 'P',
  `BT_50000_TYPE` char(1) NOT NULL DEFAULT 'P',
  PRIMARY KEY (`USER_ID`),
  UNIQUE KEY `UNIQUE_USER_ID` (`USER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;