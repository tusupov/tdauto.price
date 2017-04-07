<?php

namespace TDAuto\Price;

use Bitrix\Main\Application;

class TDiscount {

    private static $instance = null;

    private $settings = null;

    private $dbConnection = null,
            $userID = 0,
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

    public function __construct() {

        $this->dbConnection = Application::getConnection();

        global $USER;
        $this->userID = (int) $USER->GetID();

    }

    public static function active() {

        if (!self::$instance)
            self::$instance = new self();

        self::$instance->settings = null;

        if (self::$instance->userID > 0) {

            $dbDiscount = UserDiscountTable::getList([
                "filter" => [
                    "USER_ID" => self::$instance->userID
                ],
                "select" => [
                    "*"
                ]
            ]);

            if ($arDiscount = $dbDiscount->fetch()) {

                UserDiscountTable::update(
                    $arDiscount["USER_ID"],
                    [
                        "ACTIVE" => "Y"
                    ]
                );

            } else {

                $arFields = [
                    "USER_ID"   => self::$instance->userID,
                    "ACTIVE"    => "Y",
                    "SHOW_TYPE" => "P",
                ];

                foreach (self::$instance->defaultValues as $id) {
                    $arFields[$id] = 0;
                    $arFields[$id . "_TYPE"] = "P";
                }

                UserDiscountTable::add($arFields);

            }

        }

    }

    public static function deactive() {

        if (!self::$instance)
            self::$instance = new self();

        self::$instance->settings = null;

        if (self::$instance->userID > 0) {

            $dbDiscount = UserDiscountTable::getList([
                "filter" => [
                    "USER_ID" => self::$instance->userID
                ],
                "select" => [
                    "*"
                ]
            ]);

            if ($arDiscount = $dbDiscount->fetch()) {
                UserDiscountTable::update(
                    $arDiscount["USER_ID"],
                    [
                        "ACTIVE" => "N"
                    ]
                );
            }

        }

    }

    public static function getSettings() {

        if (!self::$instance)
            self::$instance = new self();

        if (self::$instance->settings)
            return self::$instance->settings;

        if (self::$instance->userID > 0) {

            $dbDiscount = UserDiscountTable::getList([
                "filter" => [
                    "ACTIVE"  => "Y",
                    "USER_ID" => self::$instance->userID
                ],
                "select" => [
                    "*"
                ]
            ]);

            if ($arDiscount = $dbDiscount->fetch()) {

                foreach (self::$instance->defaultValues as $bt)
                    $arDiscount[$bt] = str_replace(".", ",", (float)$arDiscount[$bt]);

                self::$instance->settings = $arDiscount;

            }

        }

        if (!self::$instance->settings)
            self::$instance->settings = [
                "ACTIVE" => "N"
            ];

        return self::$instance->settings;

    }

    public static function updateSettings($POST) {

        if (!self::$instance)
            self::$instance = new self();

        self::$instance->settings = null;

        if (self::$instance->userID > 0) {

            $arFields = [
                "USER_ID"   => self::$instance->userID,
                "ACTIVE"    => "Y",
                "SHOW_TYPE" => "P",
            ];

            if ($POST["SHOW_TYPE"] && in_array($POST["SHOW_TYPE"], ["P", "D", "B"]))
                $arFields["SHOW_TYPE"] = $POST["SHOW_TYPE"];

            foreach (self::$instance->defaultValues as $id) {

                $arFields[$id] = 0;

                if ($POST[$id])
                    $arFields[$id] = (float)str_replace(",", ".", $POST[$id]);

                $arFields[$id."_TYPE"] = "P";

                if ($POST[$id."_TYPE"] && in_array($POST[$id."_TYPE"], ["P", "R"]))
                    $arFields[$id."_TYPE"] = $POST[$id."_TYPE"];

            }

            UserDiscountTable::update(
                self::$instance->userID,
                $arFields
            );

        }

    }

    public static function calculate($price) {

        if (!self::$instance)
            self::$instance = new self();

        if (!self::$instance->settings)
            self::$instance->settings = self::getSettings();

        if (self::$instance->settings["ACTIVE"] != "Y") return $price;

        $price = (float) $price;

        switch (true) {

            case ($price <= 100):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_0_100_TYPE"], self::$instance->settings["BT_0_100"]);
                break;

            case ($price <= 500):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_100_500_TYPE"], self::$instance->settings["BT_100_500"]);
                break;

            case ($price <= 1000):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_500_1000_TYPE"], self::$instance->settings["BT_500_1000"]);
                break;

            case ($price <= 3000):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_1000_3000_TYPE"], self::$instance->settings["BT_1000_3000"]);
                break;

            case ($price <= 5000):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_3000_5000_TYPE"], self::$instance->settings["BT_3000_5000"]);
                break;

            case ($price <= 10000):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_5000_10000_TYPE"], self::$instance->settings["BT_5000_10000"]);
                break;

            case ($price <= 15000):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_10000_15000_TYPE"], self::$instance->settings["BT_10000_15000"]);
                break;

            case ($price <= 50000):
                $price = self::getWithDiscount($price, self::$instance->settings["BT_15000_50000_TYPE"], self::$instance->settings["BT_15000_50000"]);
                break;

            default:
                $price = self::getWithDiscount($price, self::$instance->settings["BT_50000_TYPE"], self::$instance->settings["BT_50000"]);
                break;

        }

        return $price;

    }

    private static function getWithDiscount($price, $type, $value) {

        $price = (float) $price;
        $value = (float) $value;

        return $price + (
                    $type == "R" ?
                        $value :
                        $price * ($value / 100.0)
                );

    }

}
