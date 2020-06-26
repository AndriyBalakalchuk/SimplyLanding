<?php
session_start();//старт сессии

// Вывод ошибок
error_reporting(E_ALL & ~E_NOTICE); // Уровень вывода ошибок (без нотисов)

// Абсолютный путь
$path = dirname(__FILE__) . '/';

// Подключение конфига баз данных и скриптов
include_once $path . 'admin/inc/db.inc.php';
include_once $path . 'admin/inc/funclibery.inc.php';


//нету файла конфиг -- не установлено, значит запускаем установку
//проверяем есть ли данные про суперюзеру, если все есть -- ок, нету значит установка не закончена
if(file_exists($path.'admin/inc/config.inc.php')){
  // Подключение конфига
  include_once $path.'admin/inc/config.inc.php';
  //соединяемся с базой данных для всех исходящих запросов
  $objDB = GoToDB($config['connetion'], $config['user'], $config['password']);
  //проверяем есть ли соединение и есть ли наш суперадмин
  if(!$objDB or !checkUserBy('signature',$config['SuperUser'], false)){
    //нету отправляем на установку
    header("Location: install.php");exit;
  }
//файла конфиг нету, отправляем на установку
}else{header("Location: install.php");exit;}

//пользователь не вошел, редиректим
if(!$_SESSION['new']['Logined']){
  header("Location: index.php");exit;
}

//получаем уровень доступа текущего юзера
$intUserPermis = checkUserBy('signature', $_SESSION['new']['signature'], true, array('accesslevel'))[0]['accesslevel']*1;
// у него нет доступа тут быть - редиректим
if($intUserPermis!=1 and $intUserPermis!=2){
  header("Location: index.php?strError=Access");exit;
}

//если передан сесуществующий ай-ди задачи, или айди нету
if(!isset($_GET['strId']) or $_GET['strId']=='' or !is_array(selectFromTable('big_task_log', array('id'), true, 'id', $_GET['strId']))){
  header("Location: index.php?strError=NotExist");exit;
}


// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);

$strIcoButton1 = 'fa-floppy-o'; //иконка по умолчанию для кнопки войти/выйти
$strLinkButton1 = "SaveJobSheet"; //ссылка по умолчанию для кнопки войти/выйти
$strTextButton1 = 'Save'; //текст по умолчанию для кнопки войти/выйти

$strIcoButton2 = 'fa-undo'; //иконка по умолчанию для кнопки подать задачу
$strLinkButton2 = $config['sitelink']; //ссылка по умолчанию для кнопки подать задачу
$strTextButton2 = 'Back'; //текст по умолчанию для кнопки подать задачу

//получаем имеющиеся данные для вывода
$arrThisJobData = selectFromTable('big_task_log', array('staff_initial',
                                                         'job_name',
                                                         'work_spreadsheet',
                                                         'customer_name',
                                                         'due_by',
                                                         'notes_or_more_detail',
                                                         'managers_comment',
                                                         'site_address',
                                                         'status',
                                                         'date_completed',
                                                         'contact_phone_number_and_name'), true, 'id', $_GET['strId'])[0];
 //получаем внутретабличные данные для вывода
$arrWorksheet = unserialize($arrThisJobData['work_spreadsheet']);

// принимаем информацию пришедшую из форм
//--------------------------------------------------------------------------------
if($_POST['strInnFromForm'] == 'SaveJobSheet'){ //пришел запрос на обновление данных задачи

  $strCurCuery=clearMyQuery($_SERVER['QUERY_STRING'],'strError');
    /*
    проверки переданных данных*/

    //регулируем заливку ячеек в базу так что бы заливать только то что можно данному юзеру на данной стадии
    //если юзер попытался передать неположенные ему на данной стадии данные мы их просто заменяем уже переданными ранее
    //если пришел сотрудник
    if($intUserPermis == 2 and $arrThisJobData['staff_initial']==$_SESSION['new']['signature']){
      if($arrThisJobData['status']==1 or $arrThisJobData['status']==2 or $arrThisJobData['status']==3){//стадия  1 и 2 и 3
        $_POST['submitManag'] = $arrWorksheet['submitManag'];
        $_POST['HourRate'] = $arrWorksheet['Hourly applied rate'];
        $_POST['MilRate'] = $arrWorksheet['Mileage rate/mile'];
        $_POST['PartsMarkup'] = $arrWorksheet['Parts markup'];
        for($i=0;$i<12; $i++){
          $_POST['apprPart'][$i] = $arrWorksheet['Ok'][$i];
        }
      }elseif($arrThisJobData['status']==4 or $arrThisJobData['status']==5 or $arrThisJobData['status']==6){//стадия 4 и 5 и 6
        $_POST['submitManag'] = $arrWorksheet['submitManag'];
        $_POST['HourRate'] = $arrWorksheet['Hourly applied rate'];
        $_POST['MilRate'] = $arrWorksheet['Mileage rate/mile'];
        $_POST['PartsMarkup'] = $arrWorksheet['Parts markup'];
        $_POST['Travelouttime'] = $arrWorksheet['Travel out time'];
        $_POST['TrRetTime'] = $arrWorksheet['Travel return time'];
        $_POST['StaffMileage'] = $arrWorksheet['Staff mileage'];
        $_POST['MacHours'] = $arrWorksheet['Machine hours'];
        $_POST['Make'] = $arrWorksheet['Make'];
        $_POST['Model'] = $arrWorksheet['Model'];
        $_POST['Serial'] = $arrWorksheet['Serial No'];
        $_POST['CuSign'] = $arrWorksheet['Customer Sign'];
        $_POST['CuPO'] = $arrWorksheet['Customer PO#'];
        $_POST['Date'] = getBrowserDate($arrWorksheet['Date']);
        $_POST['RegNum'] = $arrWorksheet['Registration number &/or Plant#'];

        for ($i=0; $i<12 ; $i++) {
          $_POST['Parts'][$i] = $arrWorksheet['Parts'][$i];
          $_POST['Supplier'][$i] = $arrWorksheet['Supplier'][$i];
          $_POST['Price'][$i] = $arrWorksheet['Price'][$i];
          $_POST['apprPart'][$i] = $arrWorksheet['Ok'][$i];
        }
        for ($i=0; $i<15 ; $i++) {
          $_POST['Descripton'][$i] = $arrWorksheet['Description'][$i];
          if($arrWorksheet['arrDate'][$i]!=''){$_POST['arrDate'][$i] = getBrowserDate($arrWorksheet['arrDate'][$i]);}
          $_POST['HHmM'][$i] = $arrWorksheet['Hours'][$i];
        }
      }

      if($arrThisJobData['status']==5 or $arrThisJobData['status']==6){//стадия 5 еще + одно поле
        $_POST['submitEmpl'] = $arrWorksheet['submitEmpl'];
      }
    }elseif($intUserPermis == 1){ //если пришел менеджер
     if($arrThisJobData['status']==5 or $arrThisJobData['status']==6){//стадия 5 и 6
       $_POST['submitEmpl'] = $arrWorksheet['submitEmpl'];
       $_POST['HourRate'] = $arrWorksheet['Hourly applied rate'];
       $_POST['MilRate'] = $arrWorksheet['Mileage rate/mile'];
       $_POST['PartsMarkup'] = $arrWorksheet['Parts markup'];
       $_POST['Travelouttime'] = $arrWorksheet['Travel out time'];
       $_POST['TrRetTime'] = $arrWorksheet['Travel return time'];
       $_POST['StaffMileage'] = $arrWorksheet['Staff mileage'];
       $_POST['MacHours'] = $arrWorksheet['Machine hours'];
       $_POST['Make'] = $arrWorksheet['Make'];
       $_POST['Model'] = $arrWorksheet['Model'];
       $_POST['Serial'] = $arrWorksheet['Serial No'];
       $_POST['CuSign'] = $arrWorksheet['Customer Sign'];
       $_POST['CuPO'] = $arrWorksheet['Customer PO#'];
       $_POST['Date'] = getBrowserDate($arrWorksheet['Date']);
       $_POST['RegNum'] = $arrWorksheet['Registration number &/or Plant#'];

       for ($i=0; $i<12 ; $i++) {
         $_POST['Parts'][$i] = $arrWorksheet['Parts'][$i];
         $_POST['Supplier'][$i] = $arrWorksheet['Supplier'][$i];
         $_POST['Price'][$i] = $arrWorksheet['Price'][$i];
         $_POST['apprPart'][$i] = $arrWorksheet['Ok'][$i];
       }
       for ($i=0; $i<15 ; $i++) {
         $_POST['Descripton'][$i] = $arrWorksheet['Description'][$i];
         if($arrWorksheet['arrDate'][$i]!=''){$_POST['arrDate'][$i] = getBrowserDate($arrWorksheet['arrDate'][$i]);}
         $_POST['HHmM'][$i] = $arrWorksheet['Hours'][$i];
       }
     }
     if($arrThisJobData['status']==6){//стадия 6 еще + одно поле
       $_POST['submitManag'] = $arrWorksheet['submitManag'];
     }
    }else{header("Location: jobsheet.php?strError=Access&$strCurCuery");exit;}

    //продолжаем проверки и отслеживание ошибок

    // $_POST['submitManag'] - пусто или тру (нужно хранить как checked или пусто) - только менеджер усли передано то проверить есть ли галочка от сотрудника игалочки напротив деталей, если нет то проставить
    $complete = 'false';
    $date_completed = '';
    if($_POST['submitManag']!=''){
      //так как значение отфильтровано выше то убираем проверку доступа
      //if($intUserPermis!=1){header("Location: jobsheet.php?strError=Access&$strCurCuery");exit;}
      for($i=0;$i<count($_POST['Parts']);$i++){
        if($_POST['Parts'][$i]!='' and $_POST['apprPart'][$i]==''){
          $_POST['apprPart'][$i]='checked';
        }
      }
      $_POST['submitManag'] = 'checked';
      $_POST['submitEmpl'] = 'true';
      //если пришла галочка значит и в базу вносим что все завершено и дату завершения
      $complete = 'true';
      if($arrThisJobData['date_completed'] == 0 or $arrThisJobData['date_completed'] == ''){
        $date_completed = time();
      }else{
        $date_completed = $arrThisJobData['date_completed'];
      }
    }
    // $_POST['submitEmpl'] - пусто или тру (нужно хранить как checked или пусто) - если передано то проверить есть ли дата начала работ, если нету то вписать текущую
  if($_POST['submitEmpl']!=''){
    $boolDateExist = false;
    for($i=0;$i<count($_POST['arrDate']);$i++){
      if($_POST['arrDate'][$i]!=''){
        $boolDateExist = true;
      }
    }
    if(!$boolDateExist and $_POST['arrDate'][0]=='' and $arrWorksheet['arrDate'][0]==''){$_POST['arrDate'][0]=getBrowserDate(time());}
    if(!$boolDateExist and $_POST['Descripton'][0]=='' and $arrWorksheet['Description'][0]==''){$_POST['Descripton'][0]='-';}
    $_POST['submitEmpl'] = 'checked';
  }

    // $_POST['strTaskId'] - не редактируемое - игнорируем
    // $_POST['Initials'] - отключено - игнорируем для всех
    // $_POST['JobName'] - отключено - игнорируем для всех
    // $_POST['Travelouttime'] - проверить что бы было HH:MM
    if($_POST['Travelouttime'] != ''){
      $_POST['Travelouttime'] = getCleaTime($_POST['Travelouttime']);
    }
    // $_POST['TrRetTime'] - проверить что бы было HH:MM
    if($_POST['TrRetTime'] != ''){
      $_POST['TrRetTime'] = getCleaTime($_POST['TrRetTime']);
    }
    // $_POST['StaffMileage'] - должно быть число (возможна дробь)
    if($_POST['StaffMileage'] != ''){
      $_POST['StaffMileage'] = getCleaFloat($_POST['StaffMileage']);
    }
    // $_POST['MacHours'] - должно быть число (возможна дробь)
    if($_POST['MacHours'] != ''){
      $_POST['MacHours'] = getCleaFloat($_POST['MacHours']);
    }
    // $_POST['Make'] - без проверок
    // $_POST['Model'] - без проверок
    // $_POST['Serial'] - без проверок
    // $_POST['CuName'] - отключено - игнорируем для всех
    // $_POST['CuSign'] - без проверок
    // $_POST['CuPO'] - без проверок
    // $_POST['FiName'] - отключено - игнорируем для всех
    // $_POST['FiSign'] - отключено - игнорируем для всех
    // $_POST['Date'] - должно быть датой
    if($_POST['Date'] != ''){
      //если дата прислана то проверяем верна ли она
      if(trim($_POST['Date']) != ''){
        switch (checkDate_DD_MM_YYYY(trim($_POST['Date']), true)) {
          case 'In Past':
            $_POST['Date'] = trim($_POST['Date']);
            break;
          case 'Wrong Format':
            header("Location: jobsheet.php?strError=WrongFormat&$strCurCuery");exit;
          default:
            $_POST['Date'] = trim($_POST['Date']);
        }
      }else{//если дата не прислана то просто пишем что даты нет
          $_POST['Date'] = '';
      }
    }
    // $_POST['RegNum'] - без проверок
    // $_POST['HourRate']- только менеджер / дробь
    if($_POST['HourRate']!=''){
      //так как значение отфильтровано выше то убираем проверку доступа
      //if($intUserPermis!=1){header("Location: jobsheet.php?strError=Access&$strCurCuery");exit;}
      $_POST['HourRate'] == getCleaFloat($_POST['HourRate']);
    }
    // $_POST['MilRate']- только менеджер/ дробь
    if($_POST['MilRate']!=''){
      //так как значение отфильтровано выше то убираем проверку доступа
      //if($intUserPermis!=1){header("Location: jobsheet.php?strError=Access&$strCurCuery");exit;}
      $_POST['MilRate'] == getCleaFloat($_POST['MilRate']);
    }
    // $_POST['PartsMarkup']- только менеджер
    if($_POST['PartsMarkup']!=''){
      //так как значение отфильтровано выше то убираем проверку доступа
      //if($intUserPermis!=1){header("Location: jobsheet.php?strError=Access&$strCurCuery");exit;}
      $_POST['PartsMarkup'] == getCleaFloat(substr(trim($_POST['PartsMarkup']), 0, -1)).'%';
    }
    // $_POST['Parts'] - массив 0-12
    // $_POST['Supplier']- массив 0-12
    // $_POST['Price']- массив 0-12
    $need_for_parts = 'false';
    if(is_array($_POST['Price'])){
      for($i=0;$i<count($_POST['Price']);$i++){
        if($_POST['Price'][$i] != ''){
          $_POST['Price'][$i]=getCleaFloat($_POST['Price'][$i]);
          if($_POST['apprPart'][$i]==''){$need_for_parts='true';}
        }
      }
    }elseif(!isset($_POST['Price']) or $_POST['Price']==''){ //если пришла пустота и уже есть данные в массиве то вносим те же данные
      if(is_array($arrWorksheet['Price'])){
        for($i=0;$i<count($arrWorksheet['Price']);$i++){
          if($arrWorksheet['Price'][$i] != ''){
            $_POST['Price'][$i]=$arrWorksheet['Price'][$i];
            if($_POST['apprPart'][$i]=='' and $arrWorksheet['Ok'][$i]==''){$need_for_parts='true';}
          }
        }
      }
    }
    // $_POST['apprPart']- массив 0-12 пусто или тру (нужно хранить как checked или пусто) - только менеджер  - блокирует и заставляет игнорить свою строчку для сотрудника если стоит галка.
    if(is_array($_POST['apprPart'])){
      for($i=0;$i<count($_POST['apprPart']);$i++){
        if($_POST['apprPart'][$i] != ''){$_POST['apprPart'][$i]='checked';}
      }
    }
    // $_POST['Descripton']- массив 0-15
    $work_was_started = 'false';
    if(is_array($_POST['Descripton'])){
      for($i=0;$i<count($_POST['Descripton']);$i++){
        if($_POST['Descripton'][$i] != ''){
          $work_was_started = 'true';
        }
      }
    }elseif(!isset($_POST['Descripton']) or $_POST['Descripton']==''){ //если пришла пустота и уже есть данные в массиве то вносим те же данные
      if(is_array($arrWorksheet['Description'])){
        for($i=0;$i<count($arrWorksheet['Description']);$i++){
          if($arrWorksheet['Description'][$i] != ''){
            $_POST['Descripton'][$i]=$arrWorksheet['Description'][$i];
            $work_was_started = 'true';
          }
        }
      }
    }
    // $_POST['arrDate']- массив 0-15
    $date_is_started = false;
    $date_started = '';
    if(is_array($_POST['arrDate'])){
      for($i=0;$i<15;$i++){
        if(trim($_POST['arrDate'][$i]) != ''){
          switch (checkDate_DD_MM_YYYY(trim($_POST['arrDate'][$i]), true)) {
            case 'In Past':
              $_POST['arrDate'][$i] = trim($_POST['arrDate'][$i]);
              $date_is_started = true;
              if($date_is_started and $date_started == ''){$date_started = checkDate_DD_MM_YYYY(trim($_POST['arrDate'][$i]), false);}
              break;
            case 'Wrong Format':
              header("Location: jobsheet.php?strError=WrongFormat&$strCurCuery");exit;
            default:
              $_POST['arrDate'][$i] = trim($_POST['arrDate'][$i]);
              $date_is_started = true;
              if($date_is_started and $date_started == ''){$date_started = checkDate_DD_MM_YYYY(trim($_POST['arrDate'][$i]), false);}
          }
        }else{//если дата не прислана то просто пишем что даты нет
            $_POST['arrDate'][$i] = '';
        }
      }
    }else{
      if(!isset($_POST['arrDate']) or $_POST['arrDate']==''){ //если пришла пустота и уже есть данные в массиве то вносим те же данные
        if(is_array($arrWorksheet['arrDate'])){
          for($i=0;$i<count($arrWorksheet['arrDate']);$i++){
            if($arrWorksheet['arrDate'][$i] != ''){
              $_POST['arrDate'][$i]=getBrowserDate($arrWorksheet['arrDate'][$i]);
            }else{
              $_POST['arrDate'][$i] = '';
            }
          }
        }else{
          for($i=0;$i<15;$i++){
            $_POST['arrDate'][$i] = '';
          }
        }
      }
    }
    // $_POST['HHmM']- массив 0-15
    $intTotalHours = '0:00';
    if(is_array($_POST['HHmM'])){
      for($i=0;$i<15;$i++){
        if($_POST['HHmM'][$i] != ''){
          $_POST['HHmM'][$i]=getCleaTime($_POST['HHmM'][$i]);
        }
        if(!empty(strpos($_POST['HHmM'][$i],":"))){
          $intTotalHours = getSunTime($intTotalHours,$_POST['HHmM'][$i]);
        }
      }
    }elseif(!isset($_POST['HHmM']) or $_POST['HHmM']==''){ //если пришла пустота и уже есть данные в массиве то вносим те же данные
      if(is_array($arrWorksheet['Hours'])){
        for($i=0;$i<count($arrWorksheet['Hours']);$i++){
          if($arrWorksheet['Hours'][$i] != ''){
            $_POST['HHmM'][$i]=$arrWorksheet['Hours'][$i];
            $work_was_started = 'true';
          }
          if(!empty(strpos($_POST['HHmM'][$i],":"))){
            $intTotalHours = getSunTime($intTotalHours,$_POST['HHmM'][$i]);
          }
        }
      }
    }


    //поля могут быть отредактированы только менеджером или назначенным сотрудником
    if($arrThisJobData['staff_initial']!=$_SESSION['new']['signature'] and $intUserPermis!=1){
      header("Location: jobsheet.php?strError=Access&$strCurCuery");exit;
    }

    //переписываем значения в нашем массиве - если после проверки данные есть — вносим
    // $_POST['submitEmpl'] - $arrSpreadsheetData['submitEmpl']
    if(trim($_POST['submitEmpl'])!=''){$arrWorksheet['submitEmpl']=trim($_POST['submitEmpl']);}else{$arrWorksheet['submitEmpl']='';}
    // $_POST['submitManag'] - $arrSpreadsheetData['submitManag']
    if(trim($_POST['submitManag'])!=''){$arrWorksheet['submitManag']=trim($_POST['submitManag']);}else{$arrWorksheet['submitManag']='';}
    // $_POST['Travelouttime'] - $arrSpreadsheetData['Travel out time']
    if(trim($_POST['Travelouttime'])!=''){$arrWorksheet['Travel out time']=trim($_POST['Travelouttime']);}
    // $_POST['TrRetTime'] - $arrSpreadsheetData['Travel return time']
    if(trim($_POST['TrRetTime'])!=''){$arrWorksheet['Travel return time']=trim($_POST['TrRetTime']);}
    // $_POST['StaffMileage'] - $arrSpreadsheetData['Staff mileage']
    if(trim($_POST['StaffMileage'])!=''){$arrWorksheet['Staff mileage']=trim($_POST['StaffMileage']);}
    // $_POST['MacHours'] - $arrSpreadsheetData['Machine hours']
    if(trim($_POST['MacHours'])!=''){$arrWorksheet['Machine hours']=trim($_POST['MacHours']);}
    // $_POST['Make'] - $arrSpreadsheetData['Make']
    if(trim($_POST['Make'])!=''){$arrWorksheet['Make']=trim($_POST['Make']);}
    // $_POST['Model'] - $arrSpreadsheetData['Model']
    if(trim($_POST['Model'])!=''){$arrWorksheet['Model']=trim($_POST['Model']);}
    // $_POST['Serial'] - $arrSpreadsheetData['Serial No']
    if(trim($_POST['Serial'])!=''){$arrWorksheet['Serial No']=trim($_POST['Serial']);}
    // $_POST['CuSign'] - $arrSpreadsheetData['Customer Sign']
    if(trim($_POST['CuSign'])!=''){$arrWorksheet['Customer Sign']=trim($_POST['CuSign']);}
    // $_POST['CuPO'] - $arrSpreadsheetData['Customer PO#']
    if(trim($_POST['CuPO'])!=''){$arrWorksheet['Customer PO#']=trim($_POST['CuPO']);}
    // $_POST['Date'] - $arrSpreadsheetData['Date']
    if(trim($_POST['Date'])!=''){$arrWorksheet['Date']=checkDate_DD_MM_YYYY(trim($_POST['Date']), false);}
    // $_POST['RegNum'] - $arrSpreadsheetData['Registration number &/or Plant#']
    if(trim($_POST['RegNum'])!=''){$arrWorksheet['Registration number &/or Plant#']=trim($_POST['RegNum']);}
    // $_POST['HourRate'] - $arrSpreadsheetData['Hourly applied rate']
    if(trim($_POST['HourRate'])!=''){$arrWorksheet['Hourly applied rate']=trim($_POST['HourRate']);}
    // $_POST['MilRate'] - $arrSpreadsheetData['Mileage rate/mile']
    if(trim($_POST['MilRate'])!=''){$arrWorksheet['Mileage rate/mile']=trim($_POST['MilRate']);}
    // $_POST['PartsMarkup'] - $arrSpreadsheetData['Parts markup']
    if(trim($_POST['PartsMarkup'])!=''){$arrWorksheet['Parts markup']=trim($_POST['PartsMarkup']);}
    // $_POST['Parts'] - массив 0-12 - $arrSpreadsheetData['Parts']
    for($i=0;$i<count($_POST['Parts']);$i++){
      if($_POST['Parts'][$i] != ''){
        $arrWorksheet['Parts'][$i]=$_POST['Parts'][$i];
      }else{
        $arrWorksheet['Parts'][$i]='';
      }
    }

    // $_POST['Supplier']- массив 0-12 - $arrSpreadsheetData['Supplier']
    for($i=0;$i<count($_POST['Supplier']);$i++){
      if($_POST['Supplier'][$i] != ''){
        $arrWorksheet['Supplier'][$i]=$_POST['Supplier'][$i];
      }else{
        $arrWorksheet['Supplier'][$i]='';
      }
    }

    // $_POST['Price']- массив 0-12 - $arrSpreadsheetData['Price']
    for($i=0;$i<count($_POST['Price']);$i++){
      if($_POST['Price'][$i] != ''){
        $arrWorksheet['Price'][$i]=$_POST['Price'][$i];
      }else{
        $arrWorksheet['Price'][$i]='';
      }
    }

    // $_POST['apprPart']- массив 0-12 - $arrSpreadsheetData['Ok']
    for($i=0;$i<12;$i++){
      if($_POST['apprPart'][$i] != ''){
        $arrWorksheet['Ok'][$i]='checked';
      }else{
        $arrWorksheet['Ok'][$i]='';
      }
    }



    // $_POST['Descripton']- массив 0-15 - $arrSpreadsheetData['Description']
    for($i=0;$i<count($_POST['Descripton']);$i++){
      if($_POST['Descripton'][$i] != ''){
        $arrWorksheet['Description'][$i]=$_POST['Descripton'][$i];
      }else{
        $arrWorksheet['Description'][$i]='';
      }
    }
    // $_POST['arrDate']- массив 0-15 - $arrSpreadsheetData['arrDate']
    for($i=0;$i<15;$i++){
      if($_POST['arrDate'][$i] != ''){
        $arrWorksheet['arrDate'][$i]=checkDate_DD_MM_YYYY(trim($_POST['arrDate'][$i]), false);
      }else{
        $arrWorksheet['arrDate'][$i]='';
      }
    }

    // $_POST['HHmM'] - $arrSpreadsheetData['Hours']
    for($i=0;$i<15;$i++){
      if($_POST['HHmM'][$i] != ''){
        $arrWorksheet['Hours'][$i]=$_POST['HHmM'][$i];
      }else{
        $arrWorksheet['Hours'][$i]='';
      }
    }


    // сериализируем массив
    $strWorksheetData = serialize($arrWorksheet);

    // затраченное время
      //трудовое время(суммируем весь массив аррННмМ)
      //офисное время(время приезда минус время отезда)
    //умножить на стоимость часа (60 по умолчанию)
    $intLabour = getNumberFromTime(getSunTime($intTotalHours,getDifTime($arrWorksheet['Travel return time'],$arrWorksheet['Travel out time'])))*$arrWorksheet['Hourly applied rate'];
    //количество миль умножить на стоимость мили
    $intMilage = $arrWorksheet['Staff mileage']*$arrWorksheet['Mileage rate/mile'];

    //пробуем обновить данные в задаче (доп поля) $complete  $date_completed $date_started $work_was_started $need_for_parts
    $strIDofUpdTask=updateTable('big_task_log', array('complete',
                                                      'date_completed',
                                                      'work_spreadsheet',
                                                      'work_was_started',
                                                      'date_started',
                                                      'need_for_parts',
                                                      'labour_plus_mileage'),
                                                      'id',$_POST['strTaskId'],
                                                array($complete,
                                                      $date_completed*1,
                                                      $strWorksheetData,
                                                      $work_was_started,
                                                      $date_started*1,
                                                      $need_for_parts,
                                                      $intLabour+$intMilage));





    if($strIDofUpdTask!==false){
        header("Location: jobsheet.php?strError=SaveOK&strId={$_POST['strTaskId']}");exit;
    }else{header("Location: jobsheet.php?strError=dbAddingErr&$strCurCuery");exit;}
//--------------------------------------------------------------------------------
}


// принимаем информацию которую будет выводить ошибка или другая строка гет запроса
if($_GET['strError'] == 'Access'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Access denied!</strong> Your access level lower than needed.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'SaveOK'){
  $strError = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> Task successfully saved.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'dbAddingErr'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>En error in adding to database  process!</strong> Sorry, please try again later.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'InPast'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Date!</strong> Sorry, but you are trying to add data but it is in past, you must choose date in future, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'WrongFormat'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Date!</strong> Sorry, but you are trying to add data but it`s wrong data format or non existing data, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}

 //выводим на страницу по умолчанию
 $strPageTitle = 'Jobsheet #'.$_GET['strId'];
 $strMessage = $strError;

//если заявка оппределена на другого юзера а текущий не менеджер то скрываем редактирование по умолчанию
if($arrThisJobData['staff_initial']!=$_SESSION['new']['signature'] and $intUserPermis!=1){
  $strAutoDisabler = 'disabled';
}else{
  $strAutoDisabler = '';
}

 $arrDisabled = array();
 $arrDisabled['submitEmpl'] = $strAutoDisabler;
 $arrDisabled['submitManag'] = $strAutoDisabler;
 $arrDisabled['Travel out time'] = $strAutoDisabler;
 $arrDisabled['Travel return time'] = $strAutoDisabler;
 $arrDisabled['Staff mileage'] = $strAutoDisabler;
 $arrDisabled['Machine hours'] = $strAutoDisabler;
 $arrDisabled['Make'] = $strAutoDisabler;
 $arrDisabled['Model'] = $strAutoDisabler;
 $arrDisabled['Serial No'] = $strAutoDisabler;
 $arrDisabled['Customer Sign'] = $strAutoDisabler;
 $arrDisabled['Customer PO#'] = $strAutoDisabler;
 $arrDisabled['Date'] = $strAutoDisabler;
 $arrDisabled['Registration number &/or Plant#'] = $strAutoDisabler;
 $arrDisabled['Hourly applied rate'] = $strAutoDisabler;
 $arrDisabled['Mileage rate/mile'] = $strAutoDisabler;
 $arrDisabled['Parts markup'] = $strAutoDisabler;
 $arrDisabled['Parts'] = array();
 $arrDisabled['Supplier'] = array();
 $arrDisabled['Price'] = array();
 $arrDisabled['Ok'] = array();
 for ($i=0; $i<12 ; $i++) {
   $arrDisabled['Parts'][$i] = $strAutoDisabler;
   $arrDisabled['Supplier'][$i] = $strAutoDisabler;
   $arrDisabled['Price'][$i] = $strAutoDisabler;
   $arrDisabled['Ok'][$i] = $strAutoDisabler;
 }
 $arrDisabled['Description'] = array();
 $arrDisabled['arrDate'] = array();
 $arrDisabled['Hours'] = array();
 for ($i=0; $i<15 ; $i++) {
   $arrDisabled['Description'][$i] = $strAutoDisabler;
   $arrDisabled['arrDate'][$i] = $strAutoDisabler;
   $arrDisabled['Hours'][$i] = $strAutoDisabler;
 }

 //отключаем поля для сотрудника
if($intUserPermis == 2){
   if($arrThisJobData['status']==1 or $arrThisJobData['status']==2 or $arrThisJobData['status']==3){//стадия  1 и 2 и 3
     $arrDisabled['submitManag'] = 'disabled';
     for ($i=0;$i<12; $i++) {
       $arrDisabled['Ok'][$i] = 'disabled';
     }
     $arrDisabled['Hourly applied rate'] = 'disabled';
     $arrDisabled['Mileage rate/mile'] = 'disabled';
     $arrDisabled['Parts markup'] = 'disabled';
   }elseif($arrThisJobData['status']==4 or $arrThisJobData['status']==5 or $arrThisJobData['status']==6){//стадия 4 и 5 и 6
     $arrDisabled['submitManag'] = 'disabled';
     $arrDisabled['Travel out time'] = 'disabled';
     $arrDisabled['Travel return time'] = 'disabled';
     $arrDisabled['Staff mileage'] = 'disabled';
     $arrDisabled['Machine hours'] = 'disabled';
     $arrDisabled['Make'] = 'disabled';
     $arrDisabled['Model'] = 'disabled';
     $arrDisabled['Serial No'] = 'disabled';
     $arrDisabled['Customer Sign'] = 'disabled';
     $arrDisabled['Customer PO#'] = 'disabled';
     $arrDisabled['Date'] = 'disabled';
     $arrDisabled['Registration number &/or Plant#'] = 'disabled';
     $arrDisabled['Hourly applied rate'] = 'disabled';
     $arrDisabled['Mileage rate/mile'] = 'disabled';
     $arrDisabled['Parts markup'] = 'disabled';
     for ($i=0; $i<12 ; $i++) {
       $arrDisabled['Parts'][$i] = 'disabled';
       $arrDisabled['Supplier'][$i] = 'disabled';
       $arrDisabled['Price'][$i] = 'disabled';
       $arrDisabled['Ok'][$i] = 'disabled';
     }
     for ($i=0; $i<15 ; $i++) {
       $arrDisabled['Description'][$i] = 'disabled';
       $arrDisabled['arrDate'][$i] = 'disabled';
       $arrDisabled['Hours'][$i] = 'disabled';
     }
   }

   if($arrThisJobData['status']==5 or $arrThisJobData['status']==6){//стадия 5 еще + одно поле
     $arrDisabled['submitEmpl'] = 'disabled';
   }
}else{ //отключаем поля для менеджера
  if($arrThisJobData['status']==5 or $arrThisJobData['status']==6){//стадия 5 и 6
    $arrDisabled['submitEmpl'] = 'disabled';
    $arrDisabled['Travel out time'] = 'disabled';
    $arrDisabled['Travel return time'] = 'disabled';
    $arrDisabled['Staff mileage'] = 'disabled';
    $arrDisabled['Machine hours'] = 'disabled';
    $arrDisabled['Make'] = 'disabled';
    $arrDisabled['Model'] = 'disabled';
    $arrDisabled['Serial No'] = 'disabled';
    $arrDisabled['Customer Sign'] = 'disabled';
    $arrDisabled['Customer PO#'] = 'disabled';
    $arrDisabled['Date'] = 'disabled';
    $arrDisabled['Registration number &/or Plant#'] = 'disabled';
    $arrDisabled['Hourly applied rate'] = 'disabled';
    $arrDisabled['Mileage rate/mile'] = 'disabled';
    $arrDisabled['Parts markup'] = 'disabled';
    for ($i=0; $i<12 ; $i++) {
      $arrDisabled['Parts'][$i] = 'disabled';
      $arrDisabled['Supplier'][$i] = 'disabled';
      $arrDisabled['Price'][$i] = 'disabled';
      $arrDisabled['Ok'][$i] = 'disabled';
    }
    for ($i=0; $i<15 ; $i++) {
      $arrDisabled['Description'][$i] = 'disabled';
      $arrDisabled['arrDate'][$i] = 'disabled';
      $arrDisabled['Hours'][$i] = 'disabled';
    }
  }
  if($arrThisJobData['status']==6){//стадия 6 еще + одно поле
    $arrDisabled['submitManag'] = 'disabled';
  }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <TITLE><?=$config['sitename']?> — <?=$strPageTitle?></TITLE>
    <base target="_top">
    <!-- Настройка favicon -->
    <link rel="shortcut icon" href="<?=$config['Favicon']?>" type="image/png">
    <!-- Настройка viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, user-scalable=no">
    <!-- Кодировка веб-страницы -->
    <meta charset="<?php echo $config['encoding']; ?>">
    <!-- Подключаем Bootstrap CSS -->
    <link rel="stylesheet" href="<?=$config['sitelink']?>admin/lib/bootstrap-4.5.0-dist/css/bootstrap.min.css">
    <!-- Add icon library -->
    <link rel="stylesheet" href="<?=$config['sitelink']?>admin/lib/font-awesome-4.7.0/css/font-awesome.min.css">
    <!-- Подключаем свой CSS -->
    <link rel="stylesheet" href="<?=$config['sitelink']?>admin/css/Style.css">
  </head>
  <body style="background:#f5f5f5;font-family: <?=$config['font-family']?>, sans-serif;">
    <div style='background:#f5f5f5;padding: 1px 1px 1px 1px;text-align:center;'>
      <div style='margin: 30px 1% 50px 1%;padding: 24px 32px 24px 32px;background: #fff;border-right: 1px solid #eaeaea;border-left: 1px solid #eaeaea;'>
        <table border='0'>
          <div>
            <div class="modal fade bd-example-modal-sm" id="LoadingModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content" style = 'text-align:center;'>
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-sm-12"><img height='25px' width = '55px' src='<?=$config['WaitingGIF']?>'></div>
                    </div>
                  </div>
                  <h3>Please don't do anything,</h3> <p> just wait until process will ends</p>
                </div>
              </div>
            </div>

            <p><?=$strMessage?></p>

            <!-- форма старт -->
            <form action='' method='post'>
              <!-- шапка -->
              <div class="container">
                <div class="form-row">
                  <div class="col-sm-9 alert alert-info">
                    <H5>Please, don't forget to Save changes after the data adding process.</H5>
                  </div>
                  <div class="col-sm-3 ">
                    <div class="form-check">
                      <input class="form-check-input" name="submitEmpl" type="checkbox" value="true" id="defaultCheck1" <?=$arrWorksheet['submitEmpl']?> <?=$arrDisabled['submitEmpl']?>>
                      <label class="form-check-label" for="defaultCheck1">Employee submit</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" name="submitManag" type="checkbox" value="true" id="defaultCheck2" <?=$arrWorksheet['submitManag']?> <?=$arrDisabled['submitManag']?>>
                      <label class="form-check-label" for="defaultCheck2">Manager submit</label>
                    </div>
                  </div>
                </div>
              </div>
              <input id="" name="strTaskId" type="hidden" value="<?=$_GET['strId']?>">
              <!-- первый блок -->
              <div class="container">
                <div class="form-row">
                  <div class="col-sm-6 headerDiv">
                    Request
                  </div>
                  <div class="col-sm-6 headerDiv">
                    Data
                  </div>
                </div>
                <div class="form-row">
                  <div class="col-sm-5">
                    <div class="TaskDiv">
                      <strong>Date dye:</strong><br>
                      <?=getNormalDate($arrThisJobData['due_by'])?>
                    </div>
                    <div class="TaskDiv">
                      <strong>Customer comment:</strong><br>
                      <?=$arrThisJobData['notes_or_more_detail']?>
                    </div>
                    <div class="TaskDiv">
                      <strong>Manager comment:</strong><br>
                      <?=$arrThisJobData['managers_comment']?>
                    </div>
                    <div class="TaskDiv">
                      <strong>Customer site / address (where to go):</strong><br>
                      <?=$arrThisJobData['site_address']?>
                    </div>
                    <div class="TaskDiv">
                      <strong>Who to contact on site:</strong><br>
                      <?=$arrThisJobData['contact_phone_number_and_name']?>
                    </div>
                  </div>
                  <div class="col-sm-7">
                    <div class="form-group row TaskDiv">
                      <label for="valInitials" class="col-sm-4 col-form-label">Initials</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Initials" name="Initials" value='<?=$arrThisJobData['staff_initial']?>' id="valInitials" disabled>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valJobName" class="col-sm-4 col-form-label">Job Name</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Job Name" name="JobName" value='<?=$arrThisJobData['job_name']?>' id="valJobName" disabled>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valTravelouttime" class="col-sm-4 col-form-label">Travel out time</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Travel out time" name="Travelouttime" value='<?=$arrWorksheet['Travel out time']?>' id="valTravelouttime" <?=$arrDisabled['Travel out time']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valTrRetTime" class="col-sm-4 col-form-label">Travel return time</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Travel return time" name="TrRetTime" value='<?=$arrWorksheet['Travel return time']?>' id="valTrRetTime" <?=$arrDisabled['Travel return time']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valStaffMileage" class="col-sm-4 col-form-label">Staff mileage</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Staff mileage" name="StaffMileage" value='<?=$arrWorksheet['Staff mileage']?>' id="valStaffMileage" <?=$arrDisabled['Staff mileage']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valMacHours" class="col-sm-4 col-form-label">Machine hours</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Machine hours" name="MacHours" value='<?=$arrWorksheet['Machine hours']?>' id="valMacHours" <?=$arrDisabled['Machine hours']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valMake" class="col-sm-4 col-form-label">Make</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Make" name="Make" value='<?=$arrWorksheet['Make']?>' id="valMake" <?=$arrDisabled['Make']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valModel" class="col-sm-4 col-form-label">Model</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Model" name="Model" value='<?=$arrWorksheet['Model']?>' id="valModel" <?=$arrDisabled['Model']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valSerial" class="col-sm-4 col-form-label">Serial No</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Serial No" name="Serial" value='<?=$arrWorksheet['Serial No']?>' id="valSerial" <?=$arrDisabled['Serial No']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valCuName" class="col-sm-4 col-form-label">Customer name</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Customer name" name="CuName" value='<?=$arrThisJobData['customer_name']?>' id="valCuName" disabled>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valCuSign" class="col-sm-4 col-form-label">Customer Sign</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Customer Sign" name="CuSign" value='<?=$arrWorksheet['Customer Sign']?>' id="valCuSign" <?=$arrDisabled['Customer Sign']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valCuPO" class="col-sm-4 col-form-label">Customer PO#</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Customer PO#" name="CuPO" value='<?=$arrWorksheet['Customer PO#']?>' id="valCuPO" <?=$arrDisabled['Customer PO#']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valFiName" class="col-sm-4 col-form-label">Fitter name</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Fitter name" name="FiName" value='<?=checkUserBy('signature', $arrThisJobData['staff_initial'], true, array('login'))[0]['login']?>' id="valFiName" disabled>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valFiSign" class="col-sm-4 col-form-label">Fitter sign</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Fitter sign" name="FiSign" value='<?=$arrThisJobData['staff_initial']?>' id="valFiSign" disabled>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valDate" class="col-sm-4 col-form-label">Date</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="date" placeholder="Date" name="Date" value='<?=getBrowserDate($arrWorksheet['Date'])?>' id="valDate" <?=$arrDisabled['Date']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valRegNum" class="col-sm-4 col-form-label">Registration number &amp;/or Plant #</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Registration number" name="RegNum" value='<?=$arrWorksheet['Registration number &/or Plant#']?>' id="valRegNum" <?=$arrDisabled['Registration number &/or Plant#']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valHourRate" class="col-sm-4 col-form-label">Hourly applied rate</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Hourly applied rate" name="HourRate" value='<?=$arrWorksheet['Hourly applied rate']?>' id="valHourRate" <?=$arrDisabled['Hourly applied rate']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valMilRate" class="col-sm-4 col-form-label">Mileage Rate/mile</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Mileage Rate/mile" name="MilRate" value='<?=$arrWorksheet['Mileage rate/mile']?>' id="valMilRate" <?=$arrDisabled['Mileage rate/mile']?>>
                      </div>
                    </div>
                    <div class="form-group row TaskDiv">
                      <label for="valPartsMarkup" class="col-sm-4 col-form-label">Parts markup</label>
                      <div class="col-sm-8">
                        <input class="form-control form-control-sm" type="text" placeholder="Parts markup" name="PartsMarkup" value='<?=$arrWorksheet['Parts markup']?>' id="valPartsMarkup" <?=$arrDisabled['Parts markup']?>>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <!-- последний блок -->
              <div class="container">
                <div class="form-row">
                  <div class="col-sm-7 headerDiv">
                    Parts used
                  </div>
                  <div class="col-sm-3 headerDiv">
                    Supplier name
                  </div>
                  <div class="col-sm-1 headerDiv">
                    Price
                  </div>
                  <div class="col-sm-1 headerDiv">
                    Ok?
                  </div>
                </div>
                <?php
                  for($i=0;$i<12;$i++){
                ?>
                <div class="form-row">
                  <div class="col-sm-7 TaskDiv row-job">
                    <input class="form-control form-control-sm" type="text" placeholder="Parts" name="Parts[<?=$i?>]" value='<?=$arrWorksheet['Parts'][$i]?>' <?=$arrDisabled['Parts'][$i]?>>
                  </div>
                  <div class="col-sm-3 TaskDiv row-job">
                    <input class="form-control form-control-sm" type="text" placeholder="Supplier" name="Supplier[<?=$i?>]" value='<?=$arrWorksheet['Supplier'][$i]?>' <?=$arrDisabled['Supplier'][$i]?>>
                  </div>
                  <div class="col-sm-1 TaskDiv row-job">
                    <input class="form-control form-control-sm" type="text" placeholder="Price" name="Price[<?=$i?>]" value='<?=$arrWorksheet['Price'][$i]?>' <?=$arrDisabled['Price'][$i]?>>
                  </div>
                  <div class="col-sm-1 TaskDiv row-job">
                    <input class="form-check-input" id="aprPart<?=$i?>" name="apprPart[<?=$i?>]" type="checkbox" value="true" <?=$arrWorksheet['Ok'][$i]?> <?=$arrDisabled['Ok'][$i]?>>
                  </div>
                </div>
                <?php
                  }
                ?>
                <div class="form-row">
                  <div class="col-sm-7 headerDiv">
                    Descripton of work carried out
                  </div>
                  <div class="col-sm-3 headerDiv">
                    Date
                  </div>
                  <div class="col-sm-2 headerDiv">
                    HH:MM
                  </div>
                </div>
                <?php
                  for($i=0;$i<15;$i++){
                ?>
                <div class="form-row">
                  <div class="col-sm-7 TaskDiv row-job">
                    <input class="form-control form-control-sm" type="text" placeholder="Descripton" name="Descripton[<?=$i?>]" value='<?=$arrWorksheet['Description'][$i]?>' <?=$arrDisabled['Description'][$i]?>>
                  </div>
                  <div class="col-sm-3 TaskDiv row-job">
                    <input class="form-control form-control-sm" type="date" placeholder="Date" name="arrDate[<?=$i?>]" value='<?php if($arrWorksheet['arrDate'][$i]!='' and $arrWorksheet['arrDate'][$i] != 0){echo getBrowserDate($arrWorksheet['arrDate'][$i]);} ?>' <?=$arrDisabled['arrDate'][$i]?>>
                  </div>
                  <div class="col-sm-2 TaskDiv row-job">
                    <input class="form-control form-control-sm" type="text" placeholder="HH:MM" name="HHmM[<?=$i?>]" value='<?=$arrWorksheet['Hours'][$i]?>' <?=$arrDisabled['Hours'][$i]?>>
                  </div>
                </div>
                <?php
                  }
                ?>
              </div>


                <div class="container">
                  <div class="row" style = 'color:white;' >
                    <div class="col-xl-2 offset-xl-4 col-lg-3 offset-lg-3 col-md-3 offset-md-3 col-sm-4 offset-sm-2">
                      <button name="strInnFromForm" value='<?=$strLinkButton1?>' id="" type="submit" class="btn btn-success btn-sm btnModal"><i class="fa <?=$strIcoButton1?>"></i> <?=$strTextButton1?></button>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 ">
                      <a id="" class="btn btn-info btn-sm btnModal" href='<?=$strLinkButton2?>'><i class="fa <?=$strIcoButton2?>"></i> <?=$strTextButton2?></a>
                    </div>
                  </div>
                </div>
            </form>
            <!-- форма конец -->
          </div>
        </table>
      </div>
      <div style='padding: 0px 0px 0px 54px;'>
        <table border='0' style='width: 100%; margin-bottom: 20px;'>
          <tbody>
            <tr>
              <td style='width: 80%;text-align: right;'>
                <div style='font-size:8pt;padding-left:10px;text-align: left;'>
                  <?=$config['Copyright']?>
                </div>
              </td>
              <td style='text-align: right;padding-right: 54px;'>
                <img height='40px' src='<?=$config['Logo']?>'>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>



    <!-- Подключаем jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Подключаем Bootstrap JS -->
    <script src="<?=$config['sitelink']?>admin/lib/bootstrap-4.5.0-dist/js/bootstrap.min.js"></script>

    <script>
        $(".btnModal").click(function() {
          $("#LoadingModal").modal('show');
        });
        $("#defaultCheck1").click(function() {
          if($("#defaultCheck1 option:checked")){
            $("#defaultCheck1").removeAttr('checked');
          }
        });
        $("#defaultCheck2").click(function() {
          if($("#defaultCheck2 option:checked")){
            $("#defaultCheck2").removeAttr('checked');
          }
        });
        <?php
          for($i=0;$i<12;$i++){
         ?>
         $("#aprPart<?=$i?>").click(function() {
           if($("#aprPart<?=$i?> option:checked")){
             $("#aprPart<?=$i?>").removeAttr('checked');
           }
         });
         <?php
           }
          ?>
    </script>
  </body>
</html>
