<?php

namespace TDAuto\Price;

class TUser {

    private static $companyIblockCode = "company";
    private static $companyiblockType = "services";
    private static $companyiblockID   = NULL;

    public static function addCompany($arFields) {

        $ob = new \CIBlockElement;
        $ID = false;

        $company = self::getCompanyByCode($arFields["USER_ID"], $arFields["CODE"]);

        if ($company) {

            $ID = $company["ID"];
            $ob->Update(
                $company["ID"],
                [
                    "IBLOCK_ID"         => self::getCompanyIBlockID(),
                    "ACTIVE"            => "Y",
                    "IBLOCK_SECTION_ID" => false,
                    "NAME"              => $arFields["NAME"],
                    "CODE"              => $arFields["CODE"],
                    "SORT"              => $arFields["SORT"] ?: "500",
                    "PROPERTY_VALUES"   => [
                        "USER"          => $arFields["USER_ID"],
                        "INN"           => $arFields["INN"],
                        "LEGAL_ADDRESS" => $arFields["LEGAL_ADDRESS"],
                        "ADDRESS"       => $arFields["ADDRESS"],
                        "MANAGER_ID"    => $arFields["MANAGER_ID"],
                        "MANAGER_NAME"  => $arFields["MANAGER_NAME"],
                        "OBJECT_ID"     => $arFields["OBJECT_ID"]
                    ]
                ]
            );

        } else {

            $ID = $ob->Add([
                "IBLOCK_ID"         => self::getCompanyIBlockID(),
                "ACTIVE"            => "Y",
                "IBLOCK_SECTION_ID" => false,
                "NAME"              => $arFields["NAME"],
                "CODE"              => $arFields["CODE"],
                "SORT"              => $arFields["SORT"] ?: "500",
                "PROPERTY_VALUES"   => [
                    "USER"          => $arFields["USER_ID"],
                    "INN"           => $arFields["INN"],
                    "LEGAL_ADDRESS" => $arFields["LEGAL_ADDRESS"],
                    "ADDRESS"       => $arFields["ADDRESS"],
                    "MANAGER_ID"    => $arFields["MANAGER_ID"],
                    "MANAGER_NAME"  => $arFields["MANAGER_NAME"],
                    "OBJECT_ID"     => $arFields["OBJECT_ID"]
                ]
            ]);

        }

        return $ID;

    }

    /**
     * Список компании для пользователя
     * @param $userID
     * @return array|null
     */
    public static function getCompanyList($userID) {

        $userID = (int)$userID;

        if (empty($userID) || $userID <= 0) return NULL;

        $arResult = [];

        $ob = new \CIBlockElement;

        $dbCompany = $ob->GetList(
            [
                "SORT" => "ASC",
                "NAME" => "ASC"
            ],
            [
                "ACTIVE"            => "Y",
                "GLOBAL_ACTIVE"     => "Y",
                "IBLOCK_ID"         => self::getCompanyIBlockID(),
                "IBLOCK_SECTION_ID" => false,
                "PROPERTY_USER"     => $userID,
            ]
        );
        while($obCompany = $dbCompany->GetNextElement()) {

            $arCompany    = $obCompany->GetFields();
            $propsCompany = $obCompany->GetProperties();

            $arResult[$arCompany["ID"]] = [
                "ID"               => $arCompany["ID"],
                "NAME"             => $arCompany["NAME"],
                "CODE"             => $arCompany["CODE"],
                "INN"              => $propsCompany["INN"]["VALUE"],
                "LEGAL_ADDRESS"    => $propsCompany["LEGAL_ADDRESS"]["VALUE"],
                "MANAGER_ID"       => $propsCompany["MANAGER_ID"]["VALUE"],
                "MANAGER_NAME"     => $propsCompany["MANAGER_NAME"]["VALUE"],
                "OBJECT_ID"        => $propsCompany["OBJECT_ID"]["VALUE"],
            ];

            foreach ($propsCompany["ADDRESS"]["VALUE"] as $key => $value) {
                if(!$arResult[$arCompany["ID"]]["ADDRESS"])
                    $arResult[$arCompany["ID"]]["ADDRESS"] = [];
                $arResult[$arCompany["ID"]]["ADDRESS"][$propsCompany["ADDRESS"]["DESCRIPTION"][$key]] = $value;
            }

        }

        if ($arResult)
            return $arResult;

        return NULL;

    }

    public static function getCompanyByCode($userID, $code) {

        if (!$userID || !$code) return NULL;

        $ob = new \CIBlockElement;

        return $ob->GetList(
            [
                "SORT" => "ASC"
            ], [
                "CODE"          => $code,
                "PROPERTY_USER" => $userID,
            ]
        )->Fetch();

    }

    public static function getCompanyIBlockID() {

        if (!self::$companyIblockCode || !self::$companyiblockType) return NULL;

        if (self::$companyiblockID) return self::$companyiblockID;

        $ob = new \CIblock;
        $arIblock = $ob->GetList(
            [
                "ID" => "DESC"
            ], [
                "CODE" => self::$companyIblockCode,
                "TYPE" => self::$companyiblockType,
            ]
        )->Fetch();

        if ($arIblock && $arIblock["ID"])
            self::$companyiblockID = $arIblock["ID"];

        return self::$companyiblockID;

    }

}
