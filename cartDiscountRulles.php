<?php

// достаем список скидок, применяемых к корзине из "Правила работы с корзиной"
//достаем бесплатную доставку id 5

if (!function_exists("getDiscountCartList")) {
    function getDiscountCartList($arOrder, $USER_ID = null, $ORDER_PRICE = 0, $ORDER_WEIGHT = 0)
    {
        $discountList = array();
        $arIDS = array(5);// Discount id

        $groupDiscountIterator = Bitrix\Sale\Internals\DiscountGroupTable::getList(array(
            'select' => array('DISCOUNT_ID'),
            'filter' => array('@GROUP_ID' => CUser::GetUserGroup($USER_ID), '=ACTIVE' => 'Y')
        ));

        while ($groupDiscount = $groupDiscountIterator->fetch()) {

            $groupDiscount['DISCOUNT_ID'] = (int)$groupDiscount['DISCOUNT_ID'];
            if ($groupDiscount['DISCOUNT_ID'] > 0)
                $arIDS[$groupDiscount['DISCOUNT_ID']] = true;
        }

        if ($arIDS) {
          
            $discountIterator = Bitrix\Sale\Internals\DiscountTable::getList(array(
                'select' => array(
                    "*","ID", "NAME", "PRIORITY", "SORT", "LAST_DISCOUNT", "UNPACK", "APPLICATION", "USE_COUPONS"
                ),
                'filter' => array(
                    '@ID' => $arIDS // Discounts id
                ),
                'order' => array(
                    "PRIORITY" => "DESC",
                    "SORT" => "ASC",
                    "ID" => "ASC"
                )
            ));

          
            // Discount DiscountZero Extra // Perc  Cur
            
            foreach ($arOrder as &$order) {
                $order['PRICE'] = $order['FULL_PRICE'];
            }
            $arOrderAll = array(
                'SITE_ID' => SITE_ID,
                'USER_ID' => $USER_ID,
                'ORDER_PRICE' => $ORDER_PRICE,
                'ORDER_WEIGHT' => $ORDER_WEIGHT,
                'BASKET_ITEMS' => $arOrder
            );

            while ($discount = $discountIterator->fetch()) {
               
                $condition_data = $discount['CONDITIONS_LIST']['CHILDREN'][0]['DATA'];
                if (count($discount['CONDITIONS_LIST']['CHILDREN']) > 1) {
                    $condition_data = array();
                    foreach ($discount['CONDITIONS_LIST']['CHILDREN'] as $discount_child)   
                      array_push($condition_data,$discount_child['DATA'] );
                }

                $action_data = $discount['ACTIONS_LIST']['CHILDREN'][0]['DATA'];
                $action_type = $action_data['Type'];
                $action_val = $action_data['Value'];
                $action_unit = $action_data['Unit'];

                $resDiscount = array();

                if ( $action_type == 'Discount' &&  $action_unit == 'Perc' && $action_val == 100 ) {

                    if (count($discount['CONDITIONS_LIST']['CHILDREN']) == 1) {
                        $cond_logic = $condition_data['logic'];
                   
                        $logic = logicType($cond_logic);//EqGr EqLs Equal Not Great Less >= <= = != > <
                        $cond_val = $condition_data['Value'];

                        array_push($resDiscount, $logic, $cond_val);
                    } else {
                        foreach ($condition_data as $condition_item) {
                        
                            $cond_logic = $condition_item['logic'];
                            $cond_val = $condition_item['Value']; 
                            $logic = logicType($cond_logic); //EqGr EqLs Equal Not Great Less >= <= = != > <
                            $item = array();
                            array_push($item, $logic, $cond_val);
                            array_push($resDiscount, $item);
                        }
                    }
                }



                /*$checkOrder = null;
                $strUnpack = $discount['UNPACK'];
                if (empty($strUnpack))
                    continue;
                eval('$checkOrder=' . $strUnpack . ';');
                if (!is_callable($checkOrder))
                    continue;
                $boolRes = $checkOrder($arOrderAll);
                unset($checkOrder);

                if ($boolRes) {
                    $discountList[] = $discount;
                }*/
            }
        }
        return $resDiscount;
    }
}
