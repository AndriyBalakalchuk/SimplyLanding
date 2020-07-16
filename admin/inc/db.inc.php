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
function updateTable($strTable, $arrFields, $strWhen, $strThis, $arrData, $boolUpdStatus=true){
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
  //--------------------------------------------------------------
  // проверяем работали ли с большой общей таблице и если да -- проверяем и корректируем статус задачи
  //--------------------------------------------------------------
  if($strTable == 'big_task_log' and $boolUpdStatus){
    //получаем нужные данные
    $arrThisJobData = selectFromTable('big_task_log', array('canceled_in_date',
                                                             'date_created',
                                                             'work_spreadsheet',
                                                             'work_was_started',
                                                             'date_completed'), true, $strWhen, $strThis)[0];
    $arrThisJobData['work_spreadsheet'] = unserialize($arrThisJobData['work_spreadsheet'])['submitEmpl'];

    if($arrThisJobData['canceled_in_date'] == '' or $arrThisJobData['canceled_in_date'] == 0){//если дата нет даты закрытия  проверяем дальше
      if($arrThisJobData['date_created'] == '' or $arrThisJobData['date_created'] == 0){//если нет даты создания ставим единицу
        $intStatus = 1;
      }else{//если дата создания есть проверяем дальше
        if($arrThisJobData['work_was_started'] == '' or $arrThisJobData['work_was_started'] == 'false'){//если работник не начал работать ставим 2
          $intStatus = 2;
        }else{//если работник начал работать проверяем дальше
          if($arrThisJobData['work_spreadsheet'] == '' or $arrThisJobData['work_spreadsheet'] == 'false'){//если работник не подтвердил работу как завершенную ставим 3
            $intStatus = 3;
          }else{//если работник подтвердил работу как завершенную проверяем дальше
            if($arrThisJobData['date_completed'] == '' or $arrThisJobData['date_completed'] == 0){//если даты завершения нет то ставим статус 4
              $intStatus = 4;
            }else{//если дата завершения есть то ставим статус 5
              $intStatus = 5;
              //создаем пдф счет для клиента
              try{
               $strCliePDFname = getPdfClientCost($strThis, $path);
              }catch (Throwable $e){
                return false;
              }
              //создаем пдф счет для бухгалтерии
              try{
               $strFlatPDFname = getPdfFlaternCost($strThis, $path);
              }catch (Throwable $e){
                return false;
              }
              //проверяем создались ли файлы
              if($strCliePDFname == false or $strFlatPDFname == false){
                return false;
              }
              //отправляем счета письмом
              if(!send_pdf_to_user($_SESSION['new']['email'], 'Job #'.$strThis, 'Job #'.$strThis."\n".'job name'.' - done', $path, array($strCliePDFname,$strFlatPDFname))){
                return false;
              }
              //вносим данные в гугл таблици
              if(!addToSpreadsheet($strThis, $path)){
                return false;
              }
            }
          }
        }
      }
    }else{//если дата закрытия есть ставим статус 6
      $intStatus = 6;
    }

    //обновляем статус
    if(!updateTable('big_task_log', array('status'),$strWhen,$strThis,array($intStatus), false)){
       return false;
     }
  }
  //--------------------------------------------------------------

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
function CheckData($strTableName, $strColumnName, $strFindIt){
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
          text_big_ua VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          text_small_ua VARCHAR(2000) COLLATE utf8_general_ci NOT NULL,
          time VARCHAR(50) COLLATE utf8_general_ci NOT NULL,
          edit_by VARCHAR(50) COLLATE utf8_general_ci) DEFAULT CHARSET utf8;"
        ); /*создаем таблицу*/
    break;
    
    default:
      return false;
      break;
  }



   $stmt = $objDB->prepare("SELECT count(*) FROM $strTableName WHERE $strColumnName=:searchme");
   $stmt->bindValue(':searchme', $strFindIt);
   $stmt->execute();
   if ($stmt->fetchColumn()>0) {
     return true;
   }else{
     return false;
   }
}
