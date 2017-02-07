//init.php
//log file init
define('LOG_FILENAME',  $_SERVER['DOCUMENT_ROOT'].'/logs_init.txt');


AddEventHandler("iblock", "OnAfterIBlockElementUpdate","OnAfterIBlockElementUpdateHandler");

function addProductDiscount ($name, $prod_id, $discount, $type = 'S' ) {

    $res = CCatalogDiscount::Add(
        array(
            "SITE_ID" =>"s1",
            "NAME" => $name,
            "CURRENCY" =>"RUB",
            "ACTIVE"=>"Y",
            "VALUE_TYPE"=> $type,
            "VALUE"=> $discount,
            "LAST_DISCOUNT" => "N",
            "PRODUCT_IDS"=> array(
                $prod_id,
            ),
        )
    );

    return $res;
}

function deleteProductDiscount($ID) {

    $res = CCatalogDiscount::Delete(
        $ID
    );

    return $res;
}

function updateProductDiscount ($discount_id, $arDiscount) {

    $res = CCatalogDiscount::Update(
        $discount_id,
        $arDiscount
    );

    return $res;
}

function OnAfterIBlockElementUpdateHandler(&$arFields)
{

    if($arFields["RESULT"]) {
        //if not 1c_catalog
        if ($arFields["IBLOCK_ID"] != 14) return;

        global $USER;

        $ID = $arFields["ID"];
        CModule::IncludeModule('iblock');
        CModule::IncludeModule("catalog");
        CModule::IncludeModule("sale");

            $dbEl = CIBlockElement::GetList(Array(), Array("IBLOCK_ID"=>14, "ID"=>$ID),false,false , Array("IBLOCK_ID", "ID", 'PROPERTY_SALE', 'PROPERTY_SALE_TYPE'));

            if($arItem = $dbEl->GetNextElement())
            {
                $property = $arItem->GetProperties(
                    array(),
                    array(
                      // 'CODE' => array('PROPERTY_SALE', 'PROPERTY_SALE_TYPE')//can't use array?
                       'ID' => array(168, 169)//'PROPERTY_SALE', 'PROPERTY_SALE_TYPE' IDs
                    )
                );

            }
       // AddMessage2Log("property ". print_r($property, true).".");
            //get discountsv
            $arDiscounts = CCatalogDiscount::GetDiscountByProduct(
                $arFields['ID'],
                $USER->GetUserGroupArray(),
                "N",
                1,
                s1
            );

        //Обновление элемента (добавление скидки) update/add
        if (is_numeric($ID) and !empty($property) and !empty($property['SALE']['VALUE']) and !empty($property['SALE_TYPE']['VALUE'])) {
            $no_prod_discount = true;

            foreach ($arDiscounts as $discount) {

                if ($discount['NAME'] == $arFields['NAME'] ) {
                    $no_prod_discount = false;

                    //prop not empty and discount already exist
                    if ($discount['VALUE'] == $property['SALE']['VALUE'] && $discount['VALUE_TYPE'] == $property['SALE_TYPE']['VALUE']) {
                        //prop not changed
                        AddMessage2Log("Запись ".$arFields["ID"].". Property was not changed");
                        return;
                    } else {
                        //prop changed
                        $arDiscount = array();
                        $arDiscount = CCatalogDiscount::GetByID($discount['ID']);
                        $arDiscount["VALUE"] = $property['SALE']['VALUE'];
                        $arDiscount["VALUE_TYPE"] = $property['SALE_TYPE']['VALUE'];
                        updateProductDiscount ( $discount['ID'], $arDiscount);
                        AddMessage2Log("Запись ".$arFields["ID"].". Discount has been changed id=".$discount['ID'].", name=". $arFields['NAME']);
                    }
                }
            }

            //prop not empty and no such discount
            if ($no_prod_discount) {

                addProductDiscount($arFields['NAME'], $ID, $property['SALE']['VALUE'], $property['SALE_TYPE']['VALUE']);
                AddMessage2Log("Запись ".$arFields["ID"].". Discount ".$arFields['NAME'].'/'.$property['SALE']['VALUE']." has been added.");
            }

        }

        //prop  empty
        if (!empty($property) and (empty($property['SALE']['VALUE']) || empty($property['SALE_TYPE']['VALUE']))) {
            foreach ($arDiscounts as $discount) {
                if ($discount['NAME'] == $arFields['NAME']) {
                    deleteProductDiscount($discount['ID']);
                    AddMessage2Log("Запись ".$arFields["ID"].". Discount ".$discount['ID'].'/'.$arFields['NAME']." has been deleted.");
                }
            }
        }
    }
    else {
        AddMessage2Log("Ошибка изменения записи ".$arFields["ID"]." (".$arFields["RESULT_MESSAGE"].").");
    }

}
