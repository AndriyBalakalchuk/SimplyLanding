<?php
//подключение к базе данных
function GoToDB($connetion, $user, $password){
  global $config;

  try {
  $baza = new PDO($connetion, $user, $password);
  }catch(PDOException $e){
    return false;
  }
  $baza->exec("set names utf8");

  return $baza;
}

//проверка есть ли значение в колонке таблици юзеров
function checkUserBy($strColumnName, $strFindIt, $boolNeedReturn, $arrWhatReturn=[]){
  global $config, $objDB;

  $create_table = $objDB->exec("CREATE TABLE IF NOT EXISTS
    `sl_users` (
      id MEDIUMINT(10) COLLATE utf8_general_ci NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
      login VARCHAR(400) COLLATE utf8_general_ci NOT NULL,
      email VARCHAR(150) COLLATE utf8_general_ci NOT NULL,
      password VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
      solt BIGINT(50) COLLATE utf8_general_ci NOT NULL,
      accesslevel BIGINT(50) COLLATE utf8_general_ci NOT NULL,
      signature VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
      edited BIGINT(50) COLLATE utf8_general_ci NOT NULL) DEFAULT CHARSET utf8;"
     ); /*создаем таблицу*/

     //если $boolNeedReturn фолс то нужно что бы функция вернула - есть строка в заданной колонке с таким признаком или нет
     //тру или фолс
   if(!$boolNeedReturn){
     $stmt = $objDB->prepare("SELECT count(*) FROM sl_users WHERE $strColumnName=:searchme");
     $stmt->bindValue(':searchme', $strFindIt);
     $stmt->execute();
     if ($stmt->fetchColumn()>0) {
       return true;
     }else{
       return false;
     }
   }else{
    //если $boolNeedReturn тру то нужно искать строку в обозначенной колонке и выводить данные из колонок запрошенных массивом
    //создаем строку-запрос какие колонки нам нужны
    $strColumns = '';
    $i = 0;
    foreach($arrWhatReturn as $strWhatReturn){
      if($i==0){//на первый круг пишем без зяпятой
        $strColumns = $strWhatReturn;
      }else{//все прочие дописываем с запятой
        $strColumns = $strColumns.','.$strWhatReturn;
      }
      $i++;
    }
    try{
      $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $stmt = $objDB->prepare("SELECT $strColumns FROM sl_users WHERE $strColumnName=:searchme");
      $stmt->execute(['searchme' => $strFindIt]);
      while ($row = $stmt->fetch()) {
        $arrUser[]=$row;
      }
    }catch(PDOException $e){return false;}
    return $arrUser;
  }
}

//добавить пользователя
function addUser($strLogin, $strEmail, $strPassword, $strAccesslevel, $strSignature){
  global $config, $objDB;
  //используем таймстамп и как соль пароля и как дату изменения
  $intSolt=time();

  //проверка не дублируется ли подпись юзера
  if(checkUserBy('signature',$strSignature, false)){return false;}

  $strPassword=sha1(md5($strPassword).$intSolt);

  try{
    $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $objDB->prepare('INSERT INTO sl_users (login, email, password, solt, accesslevel, signature,edited) VALUES(:login,:email,:password,:solt,:accesslevel,:signature,:edited)');
    $stmt->bindParam(':login', $strLogin);
    $stmt->bindParam(':email', $strEmail);
    $stmt->bindParam(':password', $strPassword);
    $stmt->bindParam(':solt', $intSolt);
    $stmt->bindParam(':accesslevel', $strAccesslevel);
    $stmt->bindParam(':signature', $strSignature);
    $stmt->bindParam(':edited', $intSolt);

    $stmt->execute();
  }catch(PDOException $e){return false;}
  return true;
}

//проверка есть ли логин такой и пароль
function tryToEnter($strSignat, $strPass){
  global $config, $objDB;

  try{
    $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $objDB->prepare("SELECT * FROM sl_users WHERE signature=:signature");
    $stmt->execute(['signature' => $strSignat]);
    $arrUser = $stmt->fetch();

    if($strSignat==$arrUser['signature']){
        $strPass=sha1(md5($strPass).$arrUser['solt']);

        if($strPass==$arrUser['password']){
          $arrUserData = array();
          $arrUserData[] = $arrUser['login'];
          $arrUserData[] = $arrUser['signature'];
          $arrUserData[] = $arrUser['email'];
          return $arrUserData;
        }else{return false;}
    }else{return false;}

  }catch(PDOException $e){return false;}
  return true;
}

//обновление информации в любой из таблиц
function updateTable($strTable, $arrFields, $strWhen, $strThis, $arrData){
  global $config, $objDB, $path;

  $strSet = '';
  $arrInsert = array();

  //формируем массив данных для вставки и строку для скл запроса
  $i = 0;
  foreach ($arrFields as $strField) {
    //формируем массив данных для вставки
    $arrInsert[$strField] = $arrData[$i];
    //формируем строку для скл запроса
    if($i==0){//на первый круг пишем без зяпятой
      $strSet = $strField.'=:'.$strField;
    }else{//все прочие дописываем с запятой
      $strSet = $strSet.','.$strField.'=:'.$strField;
    }
    $i = $i+1;
  }
  $arrInsert[$strWhen] = $strThis;

  try{
    $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt= $objDB->prepare("UPDATE $strTable SET $strSet WHERE $strWhen=:$strWhen");
    $stmt->execute($arrInsert);
  }catch(PDOException $e){return false;}
  return true;
}

//получение информации из любой из таблиц
function selectFromTable($strTable, $arrWhatReturn, $boolNeedFilter = false, $strWhen = '', $strThis = ''){
  global $config, $objDB;


  //создаем строку-запрос какие колонки нам нужны
  $strColumns = '';
  $i = 0;
  foreach($arrWhatReturn as $strWhatReturn){
    if($i==0){//на первый круг пишем без зяпятой
      $strColumns = $strWhatReturn;
    }else{//все прочие дописываем с запятой
      $strColumns = $strColumns.','.$strWhatReturn;
    }
    $i++;
  }
  try{
    $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if($boolNeedFilter){ //запросили фильтр
      $stmt = $objDB->prepare("SELECT $strColumns FROM $strTable WHERE $strWhen=:searchme");
      $stmt->execute(['searchme' => $strThis]);
    }else{//достать все, без фильтрования
      $stmt = $objDB->prepare("SELECT $strColumns FROM $strTable WHERE id IS NOT NULL");
      $stmt->execute();
    }




    while ($row = $stmt->fetch()) {
      $arrUsersData[]=$row;
    }
  }catch(PDOException $e){return false;}
  return $arrUsersData;
}

//удаляем строки в таблице и возвращаем тру или фолс
function removeFromTable($strTable, $strWhen, $strThis){
  global $config, $objDB;

  try{
    $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt= $objDB->prepare("DELETE FROM $strTable WHERE $strWhen=:searchme");
    $stmt->bindParam(':searchme', $strThis);
    $stmt->execute();
    if(!$stmt->rowCount()){return false;}

  }catch(PDOException $e){return false;}
  return true;
}

//проверка есть ли значение в колонке таблици Задач
function checkTaskBy($strColumnName, $strFindIt){
  global $config, $objDB;

  $create_table = $objDB->exec("CREATE TABLE IF NOT EXISTS
    `big_task_log` (
      id BIGINT(50) COLLATE utf8_general_ci NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
      date_added BIGINT(50) COLLATE utf8_general_ci NOT NULL,
      date_created BIGINT(50) COLLATE utf8_general_ci,
      complete VARCHAR(150) COLLATE utf8_general_ci,
      work_spreadsheet TEXT COLLATE utf8_general_ci,
      staff_initial VARCHAR(100) COLLATE utf8_general_ci,
      date_completed BIGINT(50) COLLATE utf8_general_ci,
      status INT(2) COLLATE utf8_general_ci,
      email_addresses VARCHAR(100) COLLATE utf8_general_ci,
      job_name VARCHAR(100) COLLATE utf8_general_ci,
      work_was_started VARCHAR(50) COLLATE utf8_general_ci,
      task_priority INT(2) COLLATE utf8_general_ci,
      canceled_in_date BIGINT(50) COLLATE utf8_general_ci,
      need_for_parts VARCHAR(50) COLLATE utf8_general_ci,
      customer_name VARCHAR(100) COLLATE utf8_general_ci,
      gsn_or_id_of_machine VARCHAR(100) COLLATE utf8_general_ci,
      managers_comment TEXT COLLATE utf8_general_ci,
      labour_plus_mileage VARCHAR(100) COLLATE utf8_general_ci,
      due_by VARCHAR(50) COLLATE utf8_general_ci,
      notes_or_more_detail TEXT COLLATE utf8_general_ci,
      site_address TEXT COLLATE utf8_general_ci,
      contact_phone_number_and_name TEXT COLLATE utf8_general_ci,
      date_started BIGINT(50) COLLATE utf8_general_ci) DEFAULT CHARSET utf8, AUTO_INCREMENT={$config['StartTaskID']};"
     ); /*создаем таблицу*/

   $stmt = $objDB->prepare("SELECT count(*) FROM big_task_log WHERE $strColumnName=:searchme");
   $stmt->bindValue(':searchme', $strFindIt);
   $stmt->execute();
   if ($stmt->fetchColumn()>0) {
     return true;
   }else{
     return false;
   }
}

//добавить строчку в любую таблицу
function addIntoTable($strTable, $arrFields, $arrData){
  global $config, $objDB;

  $strSet = ''; //строка "колонки для вставки"
  $strIt = ''; //строка "переменные для вставки"
  $arrInsert = array(); //массив для замены переменных

  //формируем массив данных для вставки и строку для скл запроса
  $i = 0;
  foreach ($arrFields as $strField) {
    //формируем массив данных для вставки
    $arrInsert[$strField] = $arrData[$i];
    //формируем строку для скл запроса
    if($i==0){//на первый круг пишем без зяпятой
      $strSet = $strField;
      $strIt = ':'.$strField;
    }else{//все прочие дописываем с запятой
      $strSet = $strSet.','.$strField;
      $strIt = $strIt.',:'.$strField;
    }
    $i = $i+1;
  }

  try{
    $objDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $objDB->prepare("INSERT INTO $strTable ($strSet) VALUES($strIt)");
    $stmt->execute($arrInsert);

  }catch(PDOException $e){echo $e; exit; return false;}
  return $objDB->lastInsertId();;
}

//проверка есть ли значение в колонке контактов 
function CheckData($strTableName, $strColumnName, $strFindIt, $boolGetAll=false){
  global $config, $objDB;

  //проверяем к какой таблице обращение, и создаем таблицу если ее нет (если такой таблици не планировалось, просто возвращаем фолс)
  switch ($strTableName) {
    case 'sl_contacts':
      $create_table = $objDB->exec("CREATE TABLE IF NOT EXISTS
        `sl_contacts` (
          id MEDIUMINT(10) COLLATE utf8_general_ci NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
          contact_for VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          contact VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          time VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          edit_by VARCHAR(50) COLLATE utf8_general_ci) DEFAULT CHARSET utf8;"
        ); /*создаем таблицу*/
    break;
    case 'sl_content':
      $create_table = $objDB->exec("CREATE TABLE IF NOT EXISTS
        `sl_content` (
          id MEDIUMINT(10) COLLATE utf8_general_ci NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
          content_for VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          text_big VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          text_small VARCHAR(2000) COLLATE utf8_general_ci NOT NULL,
          text_big_en VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          text_small_en VARCHAR(2000) COLLATE utf8_general_ci NOT NULL,
          time VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          edit_by VARCHAR(50) COLLATE utf8_general_ci) DEFAULT CHARSET utf8;"
        ); /*создаем таблицу*/
    break;
    case 'sl_images':
      $create_table = $objDB->exec("CREATE TABLE IF NOT EXISTS
        `sl_images` (
          id MEDIUMINT(10) COLLATE utf8_general_ci NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
          image_for VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          image_name VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          time VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          edit_by VARCHAR(50) COLLATE utf8_general_ci) DEFAULT CHARSET utf8;"
        ); /*создаем таблицу*/
    break;
    case 'sl_portfolio':
      $create_table = $objDB->exec("CREATE TABLE IF NOT EXISTS
        `sl_portfolio` (
          id MEDIUMINT(10) COLLATE utf8_general_ci NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
          item_for VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          images TEXT COLLATE utf8_general_ci NOT NULL,
          text_big VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
          text_small TEXT COLLATE utf8_general_ci NOT NULL,
          text_big_ua VARCHAR(100) COLLATE utf8_general_ci NOT NULL,
          text_small_ua TEXT COLLATE utf8_general_ci NOT NULL,
          time VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          edit_by VARCHAR(50) COLLATE utf8_general_ci) DEFAULT CHARSET utf8;"
        ); /*создаем таблицу*/
    break;

    default:
      return false;
      break;
  }

  //выбор SQL запроса - нужно отфильтровать данные или нет
  if($boolGetAll){
    $stmt = $objDB->prepare("SELECT count(*) FROM $strTableName");
  }else{
    $stmt = $objDB->prepare("SELECT count(*) FROM $strTableName WHERE $strColumnName=:searchme");
  }

   $stmt->bindValue(':searchme', $strFindIt);
   $stmt->execute();
   if ($stmt->fetchColumn()>0) {
     return true;
   }else{
     return false;
   }
}
