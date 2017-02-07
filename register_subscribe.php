<?$APPLICATION->IncludeComponent(
"bitrix:main.register",
"custom",
array(
"AUTH" => "Y",
"REQUIRED_FIELDS" => array(
    0 => "EMAIL",
    1 => "NAME",
    2 => "PERSONAL_PHONE",
),
"SET_TITLE" => "Y",
"SHOW_FIELDS" => array(
    0 => "EMAIL",
    1 => "NAME",
    2 => "LAST_NAME",
    3 => "PERSONAL_PHONE",
),
"SUCCESS_PAGE" => "/login/",
"USER_PROPERTY" => array(
    0 => "UF_SUBSCRIBE", // custom field
),
"USER_PROPERTY_NAME" => "",
"USE_BACKURL" => "Y",
"COMPONENT_TEMPLATE" => "custom"
),
false
);?>
