<?php

namespace TDAuto\Price;

use Bitrix\Iblock\ElementTable,
    Bitrix\Main;

class TPrice {

    public static function get($arProductCode) {

        global $USER;
        if (empty($arProductCode) || !$USER->IsAuthorized()) return NULL;

        $dbResults = UserPriceTable::getList(array(
            "filter" => array(
                "USER_ID"    => $USER->GetID(),
                "AIRUS_CODE" => $arProductCode,
            ),
            "select" => array(
                "ID", "AIRUS_CODE", "PRICE"
            )
        ));

        $arResult = array();
        while($arItem = $dbResults->fetch()) {
            $arItem["PRICE"] = (double) $arItem["PRICE"];
            if ($arItem["PRICE"] > 0)
                $arResult[(int)$arItem["AIRUS_CODE"]] = $arItem["PRICE"];
        }

        if (empty($arResult))
            return NULL;

        if (!is_array($arProductCode))
            return $arResult[$arProductCode];

        return $arResult;

    }

    /**
     * Цена для текущего пользователя
     * @param $arFields
     */
    public static function OnBeforeSaleBasketItemSetField(Main\Event $event) {


        $basketItem = $event->getParameter("ENTITY");
        $name       = $event->getParameter("NAME");
        $value      = $event->getParameter("VALUE");
		
		$module      = $basketItem->getField("MODULE");
		$customPrice = $basketItem->getField("CUSTOM_PRICE");
		        
        if ($name == "PRICE" && /*$customPrice != "Y" &&*/ $module == "catalog") {

            $arElement = ElementTable::getList(array(
                "filter" => array(
                    "ID" => (int) $basketItem->getField("PRODUCT_ID")
                ),
                "select" => array(
                    "CODE"
                )
            ))->fetch();

            if ($arElement["CODE"] && ($userPrice = self::get($arElement["CODE"])) && (float)$userPrice > 0) {
				
                $event->addResult(
                    new Main\EventResult(
                        Main\EventResult::SUCCESS, array("VALUE" => (float)$userPrice)
                    )
                );

				$basketItem->setField("CUSTOM_PRICE", "Y");
				$basketItem->save();

            }

        }

    }

}
