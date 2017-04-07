<?php

namespace TDAuto\Price;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UserDiscountTable
 * 
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> ACTIVE bool optional default 'N'
 * <li> SHOW_TYPE string(1) mandatory default 'P'
 * <li> BT_0_100 double mandatory default 0.00
 * <li> BT_100_500 double mandatory default 0.00
 * <li> BT_500_1000 double mandatory default 0.00
 * <li> BT_1000_3000 double mandatory default 0.00
 * <li> BT_3000_5000 double mandatory default 0.00
 * <li> BT_5000_10000 double mandatory default 0.00
 * <li> BT_10000_15000 double mandatory default 0.00
 * <li> BT_15000_50000 double mandatory default 0.00
 * <li> BT_50000 double mandatory default 0.00
 * <li> BT_0_100_TYPE string(1) mandatory default 'P'
 * <li> BT_100_500_TYPE string(1) mandatory default 'P'
 * <li> BT_500_1000_TYPE string(1) mandatory default 'P'
 * <li> BT_1000_3000_TYPE string(1) mandatory default 'P'
 * <li> BT_3000_5000_TYPE string(1) mandatory default 'P'
 * <li> BT_5000_10000_TYPE string(1) mandatory default 'P'
 * <li> BT_10000_15000_TYPE string(1) mandatory default 'P'
 * <li> BT_15000_50000_TYPE string(1) mandatory default 'P'
 * <li> BT_50000_TYPE string(1) mandatory default 'P'
 * </ul>
 *
 * @package Bitrix\Price
 **/

class UserDiscountTable extends Main\Entity\DataManager {
	
	/**
	 * Returns DB table name for entity.
	 * @return string
	 */
	public static function getTableName() {
		return 'tdauto_price_discount';
	}

	/**
	 * Returns entity map definition.
	 * @return array
	 */
	public static function getMap() {
		
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_USER_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_ACTIVE_FIELD'),
			),
			'SHOW_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateShowType'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_SHOW_TYPE_FIELD'),
			),
			'BT_0_100' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_0_100_FIELD'),
			),
			'BT_100_500' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_100_500_FIELD'),
			),
			'BT_500_1000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_500_1000_FIELD'),
			),
			'BT_1000_3000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_1000_3000_FIELD'),
			),
			'BT_3000_5000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_3000_5000_FIELD'),
			),
			'BT_5000_10000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_5000_10000_FIELD'),
			),
			'BT_10000_15000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_10000_15000_FIELD'),
			),
			'BT_15000_50000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_15000_50000_FIELD'),
			),
			'BT_50000' => array(
				'data_type' => 'float',
				'required' => true,
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_50000_FIELD'),
			),
			'BT_0_100_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt0100Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_0_100_TYPE_FIELD'),
			),
			'BT_100_500_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt100500Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_100_500_TYPE_FIELD'),
			),
			'BT_500_1000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt5001000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_500_1000_TYPE_FIELD'),
			),
			'BT_1000_3000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt10003000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_1000_3000_TYPE_FIELD'),
			),
			'BT_3000_5000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt30005000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_3000_5000_TYPE_FIELD'),
			),
			'BT_5000_10000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt500010000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_5000_10000_TYPE_FIELD'),
			),
			'BT_10000_15000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt1000015000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_10000_15000_TYPE_FIELD'),
			),
			'BT_15000_50000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt1500050000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_15000_50000_TYPE_FIELD'),
			),
			'BT_50000_TYPE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBt50000Type'),
				'title' => Loc::getMessage('DISCOUNT_ENTITY_BT_50000_TYPE_FIELD'),
			),
		);
		
	}
	
	/**
	 * Returns validators for SHOW_TYPE field.
	 * @return array
	 */
	public static function validateShowType() {
		
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	
	/**
	 * Returns validators for BT_0_100_TYPE field.
	 * @return array
	 */
	public static function validateBt0100Type()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	/**
	 * Returns validators for BT_100_500_TYPE field.
	 * @return array
	 */
	public static function validateBt100500Type() {
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_500_1000_TYPE field.
	 * @return array
	 */
	public static function validateBt5001000Type() {
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_1000_3000_TYPE field.
	 * @return array
	 */
	public static function validateBt10003000Type()	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_3000_5000_TYPE field.
	 * @return array
	 */
	public static function validateBt30005000Type()	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_5000_10000_TYPE field.
	 * @return array
	 */
	public static function validateBt500010000Type() {
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_10000_15000_TYPE field.
	 * @return array
	 */
	public static function validateBt1000015000Type() {
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_15000_50000_TYPE field.
	 * @return array
	 */
	public static function validateBt1500050000Type() {
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BT_50000_TYPE field.
	 * @return array
	 */
	public static function validateBt50000Type() {
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	public static function onBeforeUpdate(Main\Entity\Event $event) {
		
		$result = new Main\Entity\EventResult;
		$data = $event->getParameter("fields");

        $modifyFieldList = [];

        if (isset($data["ACTIVE"])) {
            if ($data["ACTIVE"] != "Y" && $data["SHOW_TYPE"] != "N")
                $modifyFieldList["ACTIVE"] = "N";
        }

        if (isset($data["SHOW_TYPE"])) {
            if ($data["SHOW_TYPE"] != "P" && $data["SHOW_TYPE"] != "D" && $data["SHOW_TYPE"] != "B")
                $modifyFieldList["SHOW_TYPE"] = "P";
        }

        $defaultValues = [
            "BT_0_100",
            "BT_100_500",
            "BT_500_1000",
            "BT_1000_3000",
            "BT_3000_5000",
            "BT_5000_10000",
            "BT_10000_15000",
            "BT_15000_50000",
            "BT_50000"
        ];

        foreach ($defaultValues as $bt) {
            if (isset($data[$bt]))
                $modifyFieldList[$bt] = (float) $data[$bt];
        }

        foreach ($defaultValues as $bt) {
            if (isset($data[$bt."_TYPE"])) {
                if ($data[$bt."_TYPE"] != "P" && $data[$bt."_TYPE"] != "R")
                    $modifyFieldList[$bt."_TYPE"] = "P";
            }
        }

		if (!empty($modifyFieldList))
			$result->modifyFields($modifyFieldList);
		unset($modifyFieldList);

		return $result;

	}
	
}
