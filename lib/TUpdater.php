<?php

namespace TDAuto\Price;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class TUpdater {

    //Экземпляр класса
    private static $instance = NULL;

    private $MODULE_ID = "tdauto.price";

    private $priceListDir     = "/upload/tdauto/price/";      //Папка где храниться файл цен
    private $priceFilePattern = "/^price_for_([\\d]+).dat/i"; //Шаблон имен для файлов цен

    private $userListDir     = "/upload/tdauto/user/";  //Папка где храниться файл пользователей
    private $userFilePattern = "/^contragents.xml$/i";  //Шаблон имен для файлов пользователей

    private $connection = NULL;
    private $currentUserID = NULL;

    private $report = array();

    private static $debug = true;
    private static $filePrefix = "";

    public function __construct() {

        $this->connection = Application::getConnection();

        $this->priceListDir = $_SERVER["DOCUMENT_ROOT"] . "/" . Option::get("main", "upload_dir", "upload") . "/tdauto/price/";
        $this->userListDir  = $_SERVER["DOCUMENT_ROOT"] . "/" . Option::get("main", "upload_dir", "upload") . "/tdauto/user/";;

    }

    /**
     * Обновление/Добавление цен
     * из файлов в БД
     * @return string
     */
    public static function runPrice($skip = false) {

        if (!self::$instance)
            self::$instance = new self();

        if (!self::$instance->connection->isConnected())
            return __METHOD__ . "();";

		if (!$skip) {
			$isUpdate = (int)Option::get(self::$instance->MODULE_ID, "update_price", 1);
			if ($isUpdate !== 1) return __METHOD__ . "();";
			Option::set(self::$instance->MODULE_ID, "update_price", 0);
		}

		@ignore_user_abort(true);
		@set_time_limit(0);

        self::$filePrefix = "price";
		
        self::_log("Начало обработка цен ...");

        $fileList = array();

        //Список всех файлов
        $dh  = opendir(self::$instance->priceListDir);
        while (false !== ($filename = readdir($dh))) {

            preg_match(self::$instance->priceFilePattern, $filename, $matches);

            //имя Файла соответствует правилам файлов цен
            if (!empty($matches) && count($matches) == 2 && (int)$matches[1] > 0) {
                $userID = (int)$matches[1];
                if ($userID < 0) continue;
                $fileList[$userID] = $filename;
            }

        }

        self::_log("Найдено: файлов - " . count($fileList));

        //Обработка файла
        foreach ($fileList as $userID => $filename) {

            $userID = (int) $userID;
            self::$instance->currentUserID = $userID;

            self::$instance->report[self::$instance->currentUserID] = array(
                "PRICE_UPDATED" => 0
            );

            self::_log("[{$userID}] - Пользователь");

            //Получить список цен из файлов
            $filePriceList = self::$instance->_getPriceListFromFile(self::$instance->priceListDir . $filename);
            self::_log(" [" . count($filePriceList) . "] - записей в файле");

            //Получить список цен из БД (если они есть)
            $dbPriceList = self::$instance->_getPriceListFromDB($userID);
            self::_log(" [" . count($dbPriceList) . "] - записей в БД");

            //Деактивируем все цены перед обновление
            self::$instance->_disableStatus($userID);

            //Обновляем цены
            self::$instance->_updatePrice($userID, $filePriceList, $dbPriceList);

            if (count($filePriceList) == self::$instance->report[self::$instance->currentUserID]["PRICE_UPDATED"]) {

                //Файл успешно обработан
                self::_log(" [100%] - успешно обработан");

            } else {//Есть не обработанные данные

                self::_log(" [" . self::$instance->report[self::$instance->currentUserID]["PRICE_UPDATED"] . "] - успешно обработан");
                self::_log(" [" . (count($filePriceList) - self::$instance->report[self::$instance->currentUserID]["PRICE_UPDATED"]) . "] - не удалось обработать");

            }

            //Удаляем из БД не обновленные (которые нет в файле) цены
            self::$instance->_removeDisabledStatus($userID);

        }

        self::_log("Конец." . PHP_EOL);

        return __METHOD__ . "();";

    }

    /**
     * Деактивируем всех цен для выбранного пользователя
     * @param $userID
     */
    private function _disableStatus($userID) {

        $userID = (int) $userID;

        if ($userID > 0)
            self::$instance->connection->queryExecute('UPDATE `' . UserPriceTable::getTableName() . '` SET STATUS = 0 WHERE USER_ID = ' . $userID);

    }

    /**
     * Получить всех цен из БД для выбранного пользователя
     * @param $userID
     * @return array
     */
    private function _getPriceListFromDB($userID) {

        $userID = (int) $userID;

        $priceList = array();

        if ($userID > 0) {

            $dbUserPrice = self::$instance->connection->query('
                SELECT 
                    AIRUS_CODE, ID 
                FROM 
                    `' . UserPriceTable::getTableName() . '`
                WHERE 
                    USER_ID = ' . $userID . ' 
                ORDER BY 
                    AIRUS_CODE;
            ');
            while ($arUserPrice = $dbUserPrice->Fetch()) {
                $priceList[$arUserPrice["AIRUS_CODE"]] = $arUserPrice["ID"];
            }

        }

        return $priceList;

    }

    /**
     * Получит список цен из файла
     * @param $fullFilePath
     * @return array
     */
    private function _getPriceListFromFile($fullFilePath) {

        $priceList = array();

        $handle = @fopen($fullFilePath, "r");

        if ($handle) {

            $lineList = array();
            while (($line = fgets($handle, 4096)) !== false) {
                $lineList[] = $line;
            }

            foreach ($lineList as $line) {

                list($airusCode, $price) = explode("=", trim($line));

                $airusCode = (int) $airusCode;
                $price = str_replace(",", ".", $price);
                $price = (double) $price;

                if ($airusCode > 0 && $price > 0) {
                    $priceList[$airusCode] = $price;
                } else {
                    self::_log(" ERROR: Строка не является ценой [" . $line . "]");
                }

            }

            if (!feof($handle)) {
                self::_log(" ERROR: Не удалось прочитать файл fgets [" . $fullFilePath . "]");
            }

            fclose($handle);

        } else {

            self::_log(" ERROR: Не удалось открыт файл для чтение fopen [" . $fullFilePath . "]");

        }

        return $priceList;

    }

    /**
     * Добавить/Обновить цен в БД для выбранного пользователя
     * @param $userID
     * @param $filePriceList
     * @param $dbPriceList
     */
    private function _updatePrice($userID, $filePriceList, $dbPriceList) {

        $sqlQuery = "";

        foreach ($filePriceList as $airusCode => $price) {

            try {

                if ($dbPriceList[$airusCode]) {

                    $sqlQuery = "
                        UPDATE 
                            `" . UserPriceTable::getTableName() . "`
                        SET
                            `USER_ID` = '" . $userID . "',
                            `AIRUS_CODE` = '" . $airusCode . "',
                            `PRICE` = '" . $price . "',
                            `STATUS` = '1'
                        WHERE 
                            `ID` = '" . $dbPriceList[$airusCode] . "';
                    ";
                    self::$instance->connection->query($sqlQuery);

                } else {

                    $sqlQuery = "
                        INSERT INTO `" . UserPriceTable::getTableName() . "` (
                            `USER_ID`, 
                            `AIRUS_CODE`, 
                            `PRICE`, 
                            `STATUS`
                        ) VALUES (
                            '" . $userID . "',
                            '" . $airusCode . "', 
                            '" . $price ."', 
                            '1'
                        );
                    ";
                    self::$instance->connection->query($sqlQuery);

                }

                self::$instance->report[self::$instance->currentUserID]["PRICE_UPDATED"]++;

            } catch (Exception $e) {

                self::_log(" ERROR: " . $e->getMessage());

            }

        }

    }

    /**
     * Удалить не обновленные цены для
     * выбранного пользователя
     * @param $userID
     */
    private function _removeDisabledStatus($userID) {

        $userID = (int) $userID;

        if ($userID > 0) {

            $sqlQuery = "
                DELETE FROM 
                    `" . UserPriceTable::getTableName() . "`
                WHERE 
                    `USER_ID` = '" . $userID . "' AND STATUS = '0';
            ";
            self::$instance->connection->query($sqlQuery);

        }

    }


    public static function runUser($skip = false) {

        if (!self::$instance)
            self::$instance = new self();

        if (!Loader::includeModule("iblock"))
            return __METHOD__ . "();";

		if (!$skip) {
			$isUpdate = (int)Option::get(self::$instance->MODULE_ID, "update_user", 1);
			if ($isUpdate !== 1) return __METHOD__ . "();";
			Option::set(self::$instance->MODULE_ID, "update_user", 0);
		}

		@ignore_user_abort(true);
		@set_time_limit(0);
		
        self::$filePrefix = "user";

        self::_log("Начало обработка пользователей ...");

        $fileList = array();

        //Список всех файлов
        $dh  = opendir(self::$instance->userListDir);
        while (false !== ($filename = readdir($dh))) {

            preg_match(self::$instance->userFilePattern, $filename, $matches);

            //имя Файла соответствует правилам файлов пользователя
            if (!empty($matches)) {
                $fileList[] = $filename;
            }

        }

        self::_log("Найдено: файлов - " . count($fileList));

        //Обработка файла
        foreach ($fileList as $filename) {

            $ABS_FILE_NAME = self::$instance->userListDir . $filename;

            $xmlData = new \CDataXML();
            $xmlData->Load($ABS_FILE_NAME);

            $arResult = $xmlData->GetArray();

            foreach ($arResult["contragents"]["#"]["contragent"] as $contragent) {

                $contragent = $contragent["#"];

                $userID = (int) $contragent["idINET"][0]["#"];
                if ($userID <= 0) continue;

                foreach ($contragent["LClients"][0]["#"]["clients"][0]["#"]["client"] as $id => $client) {

                    $client = $client["#"];

                    $name = $client["FORMA"][0]["#"] . " \"" . trim($client["NAME"][0]["#"]) . "\"";

                    $addressList = [];
                    foreach($client["phisaddresses"][0]["#"]["phisaddress"] as $address){
                        $addressList[] = [
                            "VALUE"       => trim($address["#"]["NAME"][0]["#"]),
                            "DESCRIPTION" => trim($address["#"]["CODE"][0]["#"])
                        ];
                    }

                    $INN = trim($client["INN"][0]["#"]);
                    $legalAddress = trim($client["JURADDR"][0]["#"]);

                    $managerID = trim($client["MANAGERCODE"][0]["#"]);
                    $managerNAME = trim($client["MANAGERNAME"][0]["#"]);

                    $objectID = trim($client["object"][0]["#"]);

                    $arCompanyFields = [
                        "USER_ID"       => $userID,
                        "NAME"          => $name,
                        "CODE"          => $client["Code"][0]["#"],
                        "SORT"          => 100 * ($id + 1),
                        "LEGAL_ADDRESS" => $legalAddress,
                        "ADDRESS"       => $addressList,
                        "INN"           => $INN,
                        "MANAGER_ID"    => $managerID,
                        "MANAGER_NAME"  => $managerNAME,
                        "OBJECT_ID"     => $objectID
                    ];

                    $ID = TUser::addCompany($arCompanyFields);

                }

                /*if (!$result)
                    self::_log("  ERROR: Не удалось обновить пользователя ID={$userID}. " . $user->LAST_ERROR);*/

            }

        }

        self::_log("Конец." . PHP_EOL);

        return __METHOD__ . "();";

    }

    /**
     * Лог
     * @param $msg
     */
    private static function _log($msg) {

        if (self::$debug === true) {

            if (!self::$instance)
                self::$instance = new self();

			if(!$_SERVER["DOCUMENT_ROOT"])
				$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__) . "/../../../../";

            $logDir = defined("LOG_DIR") ? LOG_DIR : $_SERVER["DOCUMENT_ROOT"]."/logs/";
            $logFullDir = $logDir . self::$instance->MODULE_ID . "/";

            CheckDirPath($logFullDir);

            $timeMark = '['. date('d.m.Y H:i:s') .']';
            file_put_contents($logFullDir.date("Ym").(self::$filePrefix?("_".self::$filePrefix):"").".log", $timeMark .' '. $msg . PHP_EOL, FILE_APPEND);

        }

    }

}
