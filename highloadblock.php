       
       // подключаем пространство имен класса HighloadBlockTable и даём ему псевдоним HLBT для удобной работы
        use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
        
 $ID = 2; //highloadblock id we use
 
CModule::IncludeModule('highloadblock');

//function to get class data by HL block id
function GetEntityDataClass($HlBlockId) {
    if (empty($HlBlockId) || $HlBlockId < 1)
    {
        return false;
    }
    //get info from db
    $hlblock = HLBT::getById($HlBlockId)->fetch();   
    //initialise entity class
    $entity = HLBT::compileEntity($hlblock);
    //get class data
    $entity_data_class = $entity->getDataClass();
    
    return $entity_data_class;
}

        $entity_data_class = GetEntityDataClass($ID);
        
        //get count of elems in HL block
        $count = $entity_data_class::getCount();
        
        //add new elem in  HL block
        $add = $entity_data_class::add(array(
          'UF_NAME'         => 'Yellow color',
          'UF_CODE'         => 'YELLOW',
          'UF_VALUE'         => '#ffff00',
          'UF_ACTIVE'   => '1'
       ));
       
       $delete = $entity_data_class::delete($idForDelete);
      
       $update = $entity_data_class::update($idForUpdate, array(
          'UF_NAME'         => 'Фиолетовый',
          'UF_CODE'         => 'PURPLE',
          'UF_VALUE'         => '#5A009D',
          'UF_ACTIVE'   => '1'
        ));

        $arResult = $entity_data_class::getList(array(
              'select' => array('ID', 'UF_NAME', 'UF_FILE', 'UF_XML_ID'),// HL block fields code ir '*' all
              'order' => array('ID' => 'ASC'),
              'limit' => '10', //ограничиваем выборку 10-ю элементами
              'filter' => array(
                      'UF_ACTIVE' => '1', //if select  yes/no 1/0
                      Array(
                        "LOGIC"=>"AND",
                        Array(
                           "UF_NAME"=>'y%' //start from Y
                        ),
                        Array(
                           "!UF_NAME"=>'' //and not empty
                        )
                     )
                ) 
        ));

        while ($res = $arResult->fetch())
        {
           $rsFile = CFile::GetPath($res["UF_FILE"]); //file prop
     
        }
        

//highloadblock events
http://g-rain-design.ru/blog/posts/highloadblock-events/
