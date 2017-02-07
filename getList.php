   <? $date_to = date('d.m.Y');
    $previous_4_week = date('d.m.Y', strtotime("-8 week"));
    
    $arFilter =  array(
            "IBLOCK_ID" => array($iblock_id),
            "ACTIVE" => "Y",
            '>=DATE_CREATE' => $previous_4_week, 
            '<=DATE_CREATE' => $date_to,
             "!PROPERTY_SKIDKA" => false, //or
             "PROPERTY_SKIDKA_VALUE" => "да"
         );
         
    $arSelect = Array("ID", "IBLOCK_ID",'DATE_CREATE', 'CATALOG_GROUP_1', "NAME","DETAIL_PICTURE","PREVIEW_PICTURE",'PREVIEW_TEXT', "DETAIL_PAGE_URL", 'IBLOCK_SECTION_ID', 'MEASURE','PROPERTY_CML2_ARTICLE' ); // or '*' all

    $res = CIBlockElement::GetList(
         array('RAND' => 'ASC', 'SORT' => 'DESC'), //for random sort
         arFilter,
         false,
         Array("nPageSize" => 3), //element limit on page
         $arSelect
    );

    $res->NavStart();
    $el_count = $res->NavRecordCount; //elements count
    
    while ($ar_elem = $res->GetNextElement()) {
            $arFields = $ar_elem->GetFields();
            $prod_id = $arFields['ID'];
            $arProps = $ar_elem->GetProperties();
            
            $img_path = CFile::GetPath($arFields["DETAIL_PICTURE"]); //image path
            $href = $arFields['DETAIL_PAGE_URL'];
            $arPrice = CCatalogProduct::GetOptimalPrice($prod_id, 1, $USER->GetUserGroupArray()); //price
            
            if (!$arPrice || count($arPrice) <= 0)
            {
                if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($prod_id, 1, $USER->GetUserGroupArray()))
                {
                    $quantity = $nearestQuantity;
                    $arPrice = CCatalogProduct::GetOptimalPrice($prod_id, 1, $USER->GetUserGroupArray());//"N"
                }
            }

            $is_discount_price = !empty( $arPrice["DISCOUNT"]);
            $arCurFormat = CCurrencyLang::GetCurrencyFormat('RUB');
            
             if ($is_discount_price) {
                                echo ' <span class="old_price">' . CurrencyFormat($arPrice["PRICE"]["PRICE"], $arPrice["PRICE"]["CURRENCY"]) . '</span>';
                                echo '<strong>' . CurrencyFormat($arPrice["DISCOUNT_PRICE"], $arPrice["PRICE"]["CURRENCY"]) . (!empty($measure)? '<span>/ 1'. $measure. '</span>' : ''). '</strong>';
                                $prod_price = $arPrice["DISCOUNT_PRICE"];
                            } else {
                                echo '<strong>' . CurrencyFormat($arPrice["PRICE"]["PRICE"], $arPrice["PRICE"]["CURRENCY"]) . (!empty($measure)? '<span>/ 1'. $measure. '</span>' : '').'</strong>';
                                $prod_price = $arPrice["PRICE"]["PRICE"];
                            }
                            
             if ($is_discount_price) :?>
                 <div class="sale-item">
                                <?
                       $f = str_replace('#', $arPrice['DISCOUNT']['VALUE'], $arCurFormat['FORMAT_STRING']); // fixed discount
                       $s =   floor(100 - (($arPrice['DISCOUNT']['VALUE']/ $arPrice["PRICE"]["PRICE"]) * 100)). '%';//fixed price
                       $p =   $arPrice['DISCOUNT']['VALUE'] . '%'; //percent

                                if ($arPrice['DISCOUNT']['VALUE_TYPE'] == 'F') {
                                    echo $f;
                                } else if ($arPrice['DISCOUNT']['VALUE_TYPE'] == 'S') {
                                    echo $s;
                                } else {
                                    echo $p;
                                }
                                ?>
                     </div>
              <? endif;?>
              <div class="buttons">
              <!-- add to cart url; action variable ADD2BASKET, qty variable - quantity -->
                  <a href="javascript:void(0)" data-add="<?=$href;?>?action=ADD2BASKET&id=<?=$prod_id?>&quantity=1" rel="nofollow" class="custom-button">в корзину</a>
                    </div>
              <?
            
        }
