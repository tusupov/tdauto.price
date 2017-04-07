<?defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('tdauto.price', array(
    'TDAuto\Price\UserPriceTable'    => 'lib/UserPriceTable.php',
    'TDAuto\Price\UserDiscountTable' => 'lib/UserDiscountTable.php',
    'TDAuto\Price\TUpdater'          => 'lib/TUpdater.php',
    'TDAuto\Price\TPrice'            => 'lib/TPrice.php',
    'TDAuto\Price\TUser'             => 'lib/TUser.php',
    'TDAuto\Price\TDiscount'         => 'lib/TDiscount.php',
));
