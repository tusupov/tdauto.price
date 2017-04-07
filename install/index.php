<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use TDAuto\Price\UserTable;

Loc::loadMessages(__FILE__);

if (class_exists("tdauto_price")) {
    return;
}

class tdauto_price extends CModule {
	
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $MODULE_GROUP_RIGHTS;

    public function __construct() {

        $this->MODULE_ID = 'tdauto.price';
        $this->MODULE_VERSION = '0.1.0';
        $this->MODULE_VERSION_DATE = '2016-11-25 00:00:00';
        $this->MODULE_NAME = Loc::getMessage('TDAUTO_PRICE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('TDAUTO_PRICE_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage("TDAUTO_PRICE_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("TDAUTO_PRICE_PARTNER_URI");
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->MODULE_SORT = 1000;
        $this->COMPANY_IBLOCK_CODE = "company";
        $this->COMPANY_IBLOCK_ID = 0;
        $this->COMPANY_IBLOCK_TYPE_ID = "services";

    }

    public function doInstall() {

        $this->installDB();
        $this->addCompanyIBlock();
        
		ModuleManager::registerModule($this->MODULE_ID);
        
		$this->registerModuleEvents();
        $this->addModuleAgents();

    }

    public function doUninstall() {

        $this->uninstallDB();
        $this->deleteCompanyIBlock();
        $this->unRegisterModuleEvents();
        $this->deleteModuleAgents();

        ModuleManager::unregisterModule($this->MODULE_ID);

    }

    public function installDB() {

        global $DB, $APPLICATION;

        $errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.strtolower($DB->type).'/install.sql');

        if ($errors !== false) {
            $APPLICATION->throwException(implode('', $this->errors));
            return false;
        }

    }

    private function addCompanyIBlock() {

        if (Loader::includeModule("iblock")) {

            $obIblock = new \CIBlock;

            $arIblockFields = [
                "ACTIVE"         => "Y",
                "NAME"           => "Компании",
                "CODE"           => $this->COMPANY_IBLOCK_CODE,
                "SITE_ID"        => "s1",
                "IBLOCK_TYPE_ID" => $this->COMPANY_IBLOCK_TYPE_ID,
                "INDEX_ELEMENT"  => "N",
                "SORT"           => "1000",
                "FIELDS"         => [
                    "CODE" => [
                        "IS_REQUIRED" => "Y",
                        "DEFAULT_VALUE" => [
                            "UNIQUE" => "Y"
                        ]
                    ]
                ],
                "GROUP_ID" => [
                    "2" => "R"
                ]
            ];

            $this->COMPANY_IBLOCK_ID = $this->getIblockIDbyCode($this->COMPANY_IBLOCK_CODE);
            if ($this->COMPANY_IBLOCK_ID > 0) {
                $obIblock->Update(
                    $this->COMPANY_IBLOCK_ID,
                    $arIblockFields
                );
            } else {
                $this->COMPANY_IBLOCK_ID = $obIblock->Add(
                    $arIblockFields
                );
            }

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"          => "USER",
                    "NAME"          => "Пользователь",
                    "SORT"          => 100,
                    "PROPERTY_TYPE" => "S",
                    "USER_TYPE"     => "UserID"
                ]
            );

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"     => "LEGAL_ADDRESS",
                    "NAME"     => "Юридический адрес",
                    "SORT"     => 200,
                ]
            );

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"     => "INN",
                    "NAME"     => "ИНН",
                    "SORT"     => 300,
                ]
            );

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"             => "ADDRESS",
                    "NAME"             => "Адрес",
                    "SORT"             => 400,
                    "MULTIPLE"         => "Y",
                    "WITH_DESCRIPTION" => "Y"
                ]
            );

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"          => "MANAGER_ID",
                    "NAME"          => "ID менеджера",
                    "SORT"          => 500,
                    "PROPERTY_TYPE" => "N"
                ]
            );

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"     => "MANAGER_NAME",
                    "NAME"     => "Менеджер",
                    "SORT"     => 600
                ]
            );

            $this->addIblockProperty(
                $this->COMPANY_IBLOCK_ID,
                [
                    "CODE"          => "OBJECT_ID",
                    "NAME"          => "ID склада",
                    "SORT"          => 700,
                    "PROPERTY_TYPE" => "N"
                ]
            );

        }

    }

    private function addIblockProperty($iblockID, $arFields) {

        $ib = new \CIBlockProperty;

        $default = array(
            'IBLOCK_ID' => $iblockID,
            'NAME'      => '',
            'ACTIVE'    => 'Y',
            'SORT'      => '500',
            'CODE'      => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE'  => 'N',
            'USER_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'FILTRABLE'   => 'N',
            'LINK_IBLOCK_ID' => 0
        );

        $arFields = array_merge($default, $arFields);

        $propertyID = $this->getIblockPropertyID($iblockID, $arFields["CODE"]);

        if ($propertyID) {
            $ib->Update(
                $propertyID,
                $arFields
            );
        } else {
            $ib->Add(
                $arFields
            );
        }

    }

    private function getIblockPropertyID($iblockID, $propertyCode) {

        $arProperty = \CIBlockProperty::GetList(
            [
                "SORT" => "ASC"
            ], [
                "IBLOCK_ID" => $iblockID,
                "CODE"      => $propertyCode
            ]
        )->Fetch();

        return ($arProperty && $arProperty["ID"]) ? $arProperty : NULL;

    }


	private function registerModuleEvents(){

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler('sale', 'OnBeforeSaleBasketItemSetField', 'tdauto.price', '\TDAuto\Price\TPrice', 'OnBeforeSaleBasketItemSetField');

    }

    private function addModuleAgents() {

        \CAgent::AddAgent(
            "\\TDAuto\\Price\\TUpdater::runPrice();",
            $this->MODULE_ID,
            "N",
            15 * 60,
            date("d.m.Y") . " 00:00:00",
            "Y",
            "",
            100
        );

        \CAgent::AddAgent(
            "\\TDAuto\\Price\\TUpdater::runUser();",
            $this->MODULE_ID,
            "N",
            15 * 60,
            date("d.m.Y") . " 00:00:00",
            "Y",
            "",
            200
        );

    }

    public function uninstallDB() {

        global $DB, $APPLICATION;

        $errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/db/'.strtolower($DB->type).'/uninstall.sql');

        if ($errors !== false) {
            $APPLICATION->throwException(implode('', $this->errors));
            return false;
        }

    }

    private function deleteCompanyIBlock() {

        if (Loader::includeModule("iblock")) {

            $this->COMPANY_IBLOCK_ID = $this->getIblockIDbyCode($this->COMPANY_IBLOCK_CODE);

            if ($this->COMPANY_IBLOCK_ID > 0) {
                $obIblock = new \CIBlock;
                $obIblock->Delete($this->COMPANY_IBLOCK_ID);
            }

        }

    }

    private function unRegisterModuleEvents(){

        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler('sale', 'OnBeforeSaleBasketItemSetField', 'tdauto.price', '\TDAuto\Price\TPrice', 'OnBeforeSaleBasketItemSetField');

    }

    private function deleteModuleAgents() {

        \CAgent::RemoveAgent(
            "TDAuto\\Price\\TUpdater::runPrice();",
            $this->MODULE_ID
        );
        \CAgent::RemoveAgent(
            "TDAuto\\Price\\TUpdater::runUser();",
            $this->MODULE_ID
        );

    }

    private function getIblockIDbyCode($code) {

        if (Loader::includeModule("iblock")) {

            $arIblock = \CIBlock::GetList(
                array(
                    "SORT" => "ASC"
                ),
                array(
                    "CODE"              => $code,
                    "IBLOCK_TYPE_ID"    => $this->COMPANY_IBLOCK_TYPE_ID
                )
            )->Fetch();
            if ($arIblock["ID"] > 0) return $arIblock["ID"];

        }

        return NULL;

    }

}
