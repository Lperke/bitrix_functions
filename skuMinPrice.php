
<div class="pricebl"> 
<? 
$IBlockID = 4; 
$productID = 33;

//get sku array
$mxResult = CCatalogSKU::GetInfoByProductIBlock( 
	$IBlockID 
);

if (is_array($mxResult)) 
{ 
	$rsOffers = CIBlockElement::GetList(
	  array("PRICE"=>"DESC"),
	  array(
	  	'IBLOCK_ID' => $mxResult['IBLOCK_ID'], 
		'PROPERTY_'.$mxResult['SKU_PROPERTY_ID'] => $productID //CML2_LINK PROP
		),
	  false,
	  Array("nPageSize" => 1)
	); 
	while ($arOffer = $rsOffers->GetNext()) 
	{ 
		$ar_price = GetCatalogProductPrice($arOffer["ID"], 1);
		$price = "от ".CurrencyFormat($ar_price['PRICE'], $arPrice["CURRENCY"]) ;
	} 
} 
?> 
</div> 
