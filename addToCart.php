<?
$res = CIBlockElement::GetList(
         array('RAND' => 'ASC', 'SORT' => 'DESC'),
         array("IBLOCK_ID" => array($iblock_id), "ACTIVE" => "Y"),
         false,
         Array("nPageSize" => 3),
         Array("ID", "IBLOCK_ID", "NAME","DETAIL_PICTURE","DETAIL_PAGE_URL" )
    );
while ($ar_elem = $res->GetNextElement()) {
    $arFields = $ar_elem->GetFields();      
    $prod_id = $arFields['ID'];                 
    $href = $arFields["DETAIL_PAGE_URL"];
    $img_path = CFile::GetPath($arFields["DETAIL_PICTURE"]);
?>
    <div class="product-item" data-id="<?=$prod_id;?>">
      <a href="<? echo $href; ?>" class="name"><?= $arFields["NAME"] ?></a>
      <div class="image">
          <a href="<?  echo $href;  ?>"> <?
                echo '<img src="' . $img_path . '" alt="' . $arFields['NAME'] . '"/>';
          ?> </a>
       </div>
      <a href="javascript:void(0)"  data-add="<?=$href;?>?action=ADD2BASKET&id=<?=$prod_id?>&quantity=1" rel="nofollow" class="buy-btn">в корзину</a>
    </div>
<?
}
?>
<?
/**
 * cart rules free-delivery discount (getDiscountCartList)
 */

global $discountListCart;

if (empty($discountListCart)) :
  $USER_ID = $GLOBALS["USER"]->GetID();
  $discountListCart = getDiscountCartList($arResult["ITEMS"]["AnDelCanBuy"], $USER_ID,
  $arResult['allSum'] + $arResult['DISCOUNT_PRICE_ALL'], $arResult['allWeight']);

  $discountLogic = $discountListCart[0];
  $discountPrice = $discountListCart[1];

  if (is_array($discountLogic)) {
  $js_discountPrice1 = json_encode($discountPrice[1]);
  $js_discountPrice2 = json_encode($discountLogic[1]);
  $js_discountLogic1 = json_encode($discountPrice[0]);
  $js_discountLogic2 = json_encode($discountLogic[0]);
  ?>
  <script>
      var discountPrice = [<?=$js_discountPrice1; ?>, <?=$js_discountPrice2;?>];
      var discountLogic = [<?=$js_discountLogic1; ?>, <?=$js_discountLogic2;?>];
  </script> <?
  } else {
  ?>
  <script>
      var discountPrice = <?=$discountPrice; ?>;
      var discountLogic = '<?=$discountLogic; ?>';
  </script> <?
  }
endif;
?>
<script>
/**
         * add to cart from getList
         * */

        $(document).on('click','.buy-btn',function () {
            var _this = $(this);
            var prod_add = _this.attr('data-add');
            var prod_id = _this.closest('.product-item').attr('data-id');
            var name = _this.closest('.product-item').find('.name').html();
            var image = _this.closest('.product-item').find('.image a').html();

            $.ajax({
                async: true,
                type: "GET",
                url: prod_add,
                dataType: "html",
                success: function (data) {
                    var addAnswer = new BX.PopupWindow(
                        "added_" + prod_id,
                        null,
                        {
                            content: '<div class="popup-custom popup-window popup-window-content-white"><div class="access-title-bar popup-window-titlebar-text">Товар '
                            + name + ' добавлен в корзину</div><div class="image">' + image + '</div></div>',//BX( 'ajax-add-answer'+prod_id),
                            closeIcon: {right: "20px", top: "10px"},
                            titleBar: '',
                            zIndex: 0,
                            offsetLeft: 0,
                            offsetTop: 0,
                            draggable: {restrict: false},
                            overlay: {backgroundColor: 'black', opacity: '50'}, /* затемнение фона */
                            buttons: [
                                new BX.PopupWindowButton({
                                    text: "Перейти в корзину",
                                    className: " custom-button",
                                    events: {
                                        click: function () {
                                            location.href = location + '/personal/cart';
                                        }
                                    }
                                }),
                                new BX.PopupWindowButton({
                                    text: "Вернуться",
                                    className: "webform-button-link-cancel custom-button",
                                    events: {
                                        click: function () {
                                            this.popupWindow.close();
                                        }
                                    }
                                })
                            ]
                        });

                    addAnswer.show();
                    openDeliveryPopup(data);
                },
                error: function (request, status, error) {
                    console.log('error', request.responseText, status, error);
                }

            });

            return false;
        });

/**
         * free shipping popup
         * depence from Cart discounts 
         * */
      var openDeliveryPopup = function(data) {
          var popupDelivery = $('.popup-delivery');
            var totalCart = parseInt($(data).find('.bx-basket-block .total-cart').text().replace(/\s+/g, ''), 10);
        

            if (discountPrice) {
                var free = false;
                var noSale = false;
                var forFreeSum = discountPrice - totalCart;
                if (typeof discountPrice != 'object') {
                    if (discountPrice <= totalCart ) free = true;
                } else {
                    if (discountLogic[0] == '<=' && discountLogic[1] == '>='){
                        if (discountPrice[0] >= totalCart && totalCart >= discountPrice[1]) free = true;
                        forFreeSum =  discountPrice[1] - totalCart;
                        if (totalCart >= discountPrice[0]) noSale = true;
                    } else if (discountLogic[0] == '>=' && discountLogic[1] == '<=') {
                        if (discountPrice[0] <= totalCart && totalCart <= discountPrice[1]) free = true;
                        forFreeSum = discountPrice[0] - totalCart;
                        if (totalCart >= discountPrice[1]) noSale = true;
                    }
                }

                popupDelivery.html('');

                if (free === false && noSale == false) {
                    popupDelivery.prepend('<div class="delivery-block">До бесплатной доставки осталось <span>' + forFreeSum + '</span> руб.!</div>' );
                } else if (noSale === true) {
                    return;
                } else if (free === true) {
                    popupDelivery.addClass('free').prepend('<div class="delivery-block">Бесплатная доставка для Вашего заказа!</div>');
                }
            }

            popupDelivery.fadeIn();
            popupDelivery.delay(6000).fadeOut(400);
        }
        
        </script>
