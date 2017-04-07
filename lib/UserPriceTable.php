<?php

namespace TDAuto\Price;

use Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UserPriceTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> AIRUS_CODE int mandatory
 * <li> PRICE double mandatory
 * <li> STATUS int optional default 1
 * </ul>
 *
 * @package TDAuto\Price
 **/

class UserPriceTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'tdauto_price_user';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('USER_ENTITY_ID_FIELD'),
            ),
            'USER_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_USER_ID_FIELD'),
            ),
            'AIRUS_CODE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_AIRUS_CODE_FIELD'),
            ),
            'PRICE' => array(
                'data_type' => 'float',
                'required' => true,
                'title' => Loc::getMessage('USER_ENTITY_PRICE_FIELD'),
            ),
            'STATUS' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('USER_ENTITY_STATUS_FIELD'),
            ),
        );
    }


    public static function onBeforeAdd(Main\Entity\Event $event) {

        $result = new Main\Entity\EventResult;
        $data = $event->getParameter("fields");

        if (!isset($data["STATUS"])) {
            $result->modifyFields(array("STATUS" => 1));
        }

        return $result;
    }

    public static function onBeforeUpdate(Main\Entity\Event $event) {

        $result = new Main\Entity\EventResult;
        $data = $event->getParameter("fields");

        if (!isset($data["STATUS"])) {
            $result->modifyFields(array("STATUS" => 1));
        }

        return $result;
    }

}
