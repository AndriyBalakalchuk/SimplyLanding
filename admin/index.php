<?php
session_start();//старт сессии

// Вывод ошибок
error_reporting(E_ALL & ~E_NOTICE); // Уровень вывода ошибок (без нотисов)

// Абсолютный путь
$path = dirname(__FILE__) . '/';

// Подключение конфига баз данных и скриптов
include_once $path . 'inc/db.inc.php';
include_once $path . 'inc/funclibery.inc.php';



//нету файла конфиг -- не установлено, значит запускаем установку
//проверяем есть ли данные про суперюзеру, если все есть -- ок, нету значит установка не закончена
if(file_exists($path.'inc/config.inc.php')){
  // Подключение конфига
  include_once $path.'inc/config.inc.php';
  //соединяемся с базой данных для всех исходящих запросов
  $objDB = GoToDB($config['connetion'], $config['user'], $config['password']);
  //проверяем есть ли соединение и есть ли наш суперадмин
  if(!$objDB or !checkUserBy('signature',$config['SuperUser'], false)){
    //нету отправляем на установку
    header("Location: ../install.php");exit;
  }
//файла конфиг нету, отправляем на установку
}else{header("Location: ../install.php");exit;}


if(!$_SESSION['new']['Logined']){//пользователь не вошел, переброс на вход
  header("Location: LogIN.php");exit;
}
//получаем уровень доступа пользователя
$intUserPermis = checkUserBy('signature', $_SESSION['new']['signature'], true, array('accesslevel'))[0]['accesslevel']*1;


// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);


// принимаем информацию пришедшую из форм
//--------------------------------------------------------------------------------
$strCurCuery=clearMyQuery($_SERVER['QUERY_STRING'],'strError');
if($_POST['strInnFromForm'] == 'ChanPassword'){ //пришел запрос на изменение пароля
  if(trim($_POST['UserPass']) == '' or trim($_POST['UserPass_rep']) == '' or trim($_POST['UserPass']) != trim($_POST['UserPass_rep'])){header("Location: index.php?strError=BadPass&".$strCurCuery);exit;}
  //проверяем есть ли права на действие
  if($_POST['UserSign']==$_SESSION['new']['signature'] or checkUserBy('signature', $_POST['UserSign'], true, array('accesslevel'))[0]['accesslevel']*1>$intUserPermis or $_SESSION['new']['signature']==$config['SuperUser']){
    //хешируем пароль
    $intSolt=time();
    $strPassword=sha1(md5(trim($_POST['UserPass'])).$intSolt);
   //пробуем изменить пароль для юзера
    if(updateTable('sl_users', array('password','solt','edited'), 'signature', $_POST['UserSign'], array($strPassword,$intSolt,$intSolt))){
      header("Location: index.php?strError=passOK&".$strCurCuery);exit;
      //если по какой-то причине пользователь не обновлен
    }else{header("Location: index.php?strError=BadAcces&".$strCurCuery);exit;}
  }else{header("Location: index.php?strError=BadAcces&".$strCurCuery);exit;}
//--------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'ChanEmail'){//пришел запрос на изменение адреса почты
  //проверяем переданные переменные
  if(trim($_POST['UserEmail']) == '' || checkUserBy('email',trim($_POST['UserEmail']), false)==1 || !validEmail(trim($_POST['UserEmail']))){header("Location: index.php?strError=Empty&".$strCurCuery);exit;}
  //проверяем есть ли права на действие
  if($_POST['UserSign']==$_SESSION['new']['signature'] or checkUserBy('signature', $_POST['UserSign'], true, array('accesslevel'))[0]['accesslevel']*1>$intUserPermis or $_SESSION['new']['signature']==$config['SuperUser']){
    //пробуем изменить мейл для юзера
     if(updateTable('sl_users', array('email','edited'), 'signature', $_POST['UserSign'], array(trim($_POST['UserEmail']),time()))){
       //если меняли для себя то перезаписать текущие данніе сессии
       if($_POST['UserSign']==$_SESSION['new']['signature']){$_SESSION['new']['email'] = trim($_POST['UserEmail']);}
       header("Location: index.php?strError=passOK&".$strCurCuery);exit;
       //если по какой-то причине пользователь не обновлен
     }else{header("Location: index.php?strError=BadAcces&".$strCurCuery);exit;}
   }else{header("Location: index.php?strError=BadAcces&".$strCurCuery);exit;}
//--------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'ChanName'){//пришел запрос на изменение имени юзера
  //проверяем переданные переменные
  if(trim($_POST['UserName']) == ''){header("Location: index.php?strError=Empty&".$strCurCuery);exit;}
  //проверяем есть ли права на действие
  if($_POST['UserSign']==$_SESSION['new']['signature'] or checkUserBy('signature', $_POST['UserSign'], true, array('accesslevel'))[0]['accesslevel']*1>$intUserPermis or $_SESSION['new']['signature']==$config['SuperUser']){
    //пробуем изменить Имя для юзера
     if(updateTable('sl_users', array('login','edited'), 'signature', $_POST['UserSign'], array(trim($_POST['UserName']),time()))){
       //если меняли для себя то перезаписать текущие данніе сессии
       if($_POST['UserSign']==$_SESSION['new']['signature']){$_SESSION['new']['login'] = trim($_POST['UserName']);}
       header("Location: index.php?strError=passOK&".$strCurCuery);exit;
       //если по какой-то причине пользователь не обновлен
     }else{header("Location: index.php?strError=BadAcces&".$strCurCuery);exit;}
   }else{header("Location: index.php?strError=BadAcces&".$strCurCuery);exit;}
   //--------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'remove'){//пришел запрос на удаление юзера
  //проверяем есть ли права на действие
  if($_POST['UserSign']!=$_SESSION['new']['signature'] and (checkUserBy('signature', $_POST['UserSign'], true, array('accesslevel'))[0]['accesslevel']*1>$intUserPermis or $_SESSION['new']['signature']==$config['SuperUser'])){
    //пробуем удалить юзера
     if(removeFromTable('sl_users', 'signature', $_POST['UserSign'])){
       header("Location: index.php?strPage=moders&strError=passOK");exit;
       //если по какой-то причине пользователь не обновлен
     }else{header("Location: index.php?strPage=moders&ChangeMod=remove&strError=BadAcces&strUsSign=".$_POST['UserSign']);exit;}
   }else{header("Location: index.php?strPage=moders&ChangeMod=remove&strError=BadAcces&strUsSign=".$_POST['UserSign']);exit;}
   //--------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'accesslevel'){//пришел запрос на изменение прав доступа юзера
  //проверяем переданные переменные
  if(trim($_POST['UserAccess']) != 'Employee' and trim($_POST['UserAccess']) != 'Manager'){
    header("Location: index.php?strPage=moders&ChangeMod=accesslevel&strError=Empty&strUsSign=".$_POST['UserSign']);exit;
  }else{//преобразуем уровень доступа из строки в числовое значение
    if($_SESSION['new']['signature']==$config['SuperUser']){
      if(trim($_POST['UserAccess'])== 'Manager'){
        $intUserAccess = 1;
      }else{
        $intUserAccess = 2;
      }
    }else{
      header("Location: index.php?strPage=moders&ChangeMod=accesslevel&strError=BadAcces&strUsSign=".$_POST['UserSign']);exit;
    }
  }
  //проверяем есть ли права на действие
  if($_POST['UserSign']!=$_SESSION['new']['signature']){
    //пробуем изменить Имя для юзера
     if(updateTable('sl_users', array('accesslevel','edited'), 'signature', $_POST['UserSign'], array($intUserAccess,time()))){
       header("Location: index.php?strPage=moders&strError=passOK");exit;
       //если по какой-то причине пользователь не обновлен
     }else{header("Location: index.php?strPage=moders&ChangeMod=accesslevel&strError=BadAcces&strUsSign=".$_POST['UserSign']);exit;}
   }else{header("Location: index.php?strPage=moders&ChangeMod=accesslevel&strError=BadAcces&strUsSign=".$_POST['UserSign']);exit;}
   //--------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'addUser'){//пришел запрос на добавление пользователя
  //проверяем переданные переменные
  if(trim($_POST['SuperUser']) == '' or checkUserBy('signature',trim($_POST['SuperUser']), false)){header("Location: index.php?strPage=moders&strError=Empty");exit;}
  if(trim($_POST['UserLogin']) == ''){header("Location: index.php?strPage=moders&strError=Empty");exit;}
  if(trim($_POST['UserEmail']) == '' or checkUserBy('email',trim($_POST['UserEmail']), false) or !validEmail(trim($_POST['UserEmail']))){header("Location: index.php?strPage=moders&strError=Empty");exit;}
  if(trim($_POST['UserPass']) == '' or trim($_POST['UserPass_rep']) == '' or trim($_POST['UserPass']) != trim($_POST['UserPass_rep'])){header("Location: index.php?strPage=moders&strError=Empty");exit;}
  if(trim($_POST['UserAccess']) != 'Employee' and trim($_POST['UserAccess']) != 'Manager'){
    header("Location: index.php?strPage=moders&strError=Empty");exit;
  }else{//преобразуем уровень доступа из строки в числовое значение
    if($_SESSION['new']['signature']==$config['SuperUser'] and trim($_POST['UserAccess'])== 'Manager'){
      $intUserAccess = 1;
    }else{
      $intUserAccess = 2;
    }
  }
  //проверяем есть ли права на действие
  if($intUserPermis==1){
    //пробуем создать пользывателя, создался тру -- нет фолс
    if(addUser(trim($_POST['UserLogin']), trim($_POST['UserEmail']), trim($_POST['UserPass']), $intUserAccess, trim($_POST['SuperUser']))){
      header("Location: index.php?strPage=moders&strError=passOK");exit;
    }else{header("Location: index.php?strPage=moders&strError=BadAcces");exit;}
  }else{header("Location: index.php?strPage=moders&strError=BadAcces");exit;}
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'addINTO'){//пришел запрос на добавление в таблицу
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
  //определяем таблицу для добавления
  if(isset($_POST['strContact_for'])){//определена таблица Контактов
    if(isset($_POST[$_POST['strContact_for']]) and $_POST[$_POST['strContact_for']] != ''){//проверка что данные пришли
      if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
        if(addIntoTable('sl_contacts', array('contact_for','contact','time','edit_by'), array($_POST['strContact_for'],$_POST[$_POST['strContact_for']],time(),$_SESSION['new']['signature']))){
          header("Location: index.php?strError=passOK&".$strCurCuery);exit;
        }else{
          header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
        }
      }else{//пользователь не может менять данные
        header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
      }
    }else{//данные не пришли
      header("Location: index.php?strError=Empty&".$strCurCuery);exit;
    }
  }elseif(isset($_POST['strContent_for'])){//определена таблица Контента
    if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
      if(addIntoTable('sl_content', array('content_for','text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), array($_POST['strContent_for'],$_POST['header'],$_POST['text_block'],$_POST['header_en'],$_POST['text_block_en'],time(),$_SESSION['new']['signature']))){
        header("Location: index.php?strError=passOK&".$strCurCuery);exit;
      }else{
        header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
      }
    }else{//пользователь не может менять данные
      header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
    }
  }elseif(isset($_POST['strImage_for'])){//определена таблица Изображений
    if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
      $newImageName = uploadImage($_FILES['new_image'],$_POST['strImage_for'], 570, 600);
      if($newImageName!==false){ //загрузка изображения - ок
        if(addIntoTable('sl_images', array('image_for','image_name', 'time','edit_by'), array($_POST['strImage_for'],$newImageName,time(),$_SESSION['new']['signature']))){
          header("Location: index.php?strError=passOK&".$strCurCuery);exit;
        }else{
          header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
        }
      }else{//загрузка изображения - ОШИБКА 
        header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
      }
    }else{//пользователь не может менять данные
      header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
    }
  }else{//не подобрали таблицу - отмена действий
    header("Location: index.php?strError=wrongReq&".$strCurCuery);exit;
  } 
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'updIN'){//пришел запрос на обновление ячеек таблици
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
  //определяем таблицу для вноса обновений
  if(isset($_POST['strContact_for'])){//определена таблица Контактов
    if(isset($_POST[$_POST['strContact_for']]) and $_POST[$_POST['strContact_for']] != ''){//проверка что данные пришли
      if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
        if(updateTable('sl_contacts', array('contact','time','edit_by'), 'contact_for', $_POST['strContact_for'], array($_POST[$_POST['strContact_for']],time(),$_SESSION['new']['signature']))){
          header("Location: index.php?strError=passOK&".$strCurCuery);exit;
        }else{
          header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
        }
      }else{//пользователь не может менять данные
        header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
      }
    }else{//данные не пришли
      header("Location: index.php?strError=Empty&".$strCurCuery);exit;
    }
  }elseif(isset($_POST['strContent_for'])){//определена таблица Контента
    if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
      if(updateTable('sl_content', array('text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), 'content_for', $_POST['strContent_for'], array($_POST['header'],$_POST['text_block'],$_POST['header_en'],$_POST['text_block_en'],time(),$_SESSION['new']['signature']))){
        header("Location: index.php?strError=passOK&".$strCurCuery);exit;
      }else{
        header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
      }
    }else{//пользователь не может менять данные
      header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
    }
  }elseif(isset($_POST['intIDinCont'])){//определена таблица Контента и обновление строки по ИД
    if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
      if(updateTable('sl_content', array('text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), 'id', $_POST['intIDinCont'], array($_POST['header'],$_POST['text_block'],$_POST['header_en'],$_POST['text_block_en'],time(),$_SESSION['new']['signature']))){
        header("Location: index.php?strError=passOK&".$strCurCuery);exit;
      }else{
        header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
      }
    }else{//пользователь не может менять данные
      header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
    }
  }else{//не подобрали таблицу - отмена действий
    header("Location: index.php?strError=wrongReq&".$strCurCuery);exit;
  }
  
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
}elseif($_POST['strInnFromForm'] == 'RemoveIMG'){//пришел запрос на удаление изображения
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
  //определяем таблицу для вноса обновений
  if(isset($_POST['strImage_for'])){//определена таблица изображений
    if($_POST['boolINDB'] == 1){removeFromTable('sl_images', 'image_name', $_POST['image_name']);}
    if($_POST['boolINFiles'] == 1){unlink("images/{$_POST['strImage_for']}/{$_POST['image_name']}");}
    if($_POST['boolINFilesMini'] == 1){unlink("images/{$_POST['strImage_for']}/mini/{$_POST['image_name']}");}
    header("Location: index.php?strPage={$_POST['strImage_for']}&strError=passOK");exit;     
  }
}elseif($_POST['strInnFromForm'] == 'RemoveRow'){//пришел запрос на удаление строки в базе данных
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
  //определяем таблицу для вноса обновений
  if(isset($_POST['strContent_for'])){//определена таблица контента
    if(!removeFromTable('sl_content', 'id', $_POST['id'])){
      header("Location: index.php?strError=wrongReq&".$strCurCuery);exit;
    }
    header("Location: index.php?strPage={$_POST['strPage']}&strError=passOK");exit;     
  }
}elseif($_POST['strInnFromForm'] == 'addINTOPort'){//пришел запрос на добавление в таблицу ПОРФОЛИО
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
  if($intUserPermis == 1 or $intUserPermis == 2){ //проверяем что пользователь может менять данные
    $newImageName = '';
    foreach ($_FILES['new_image'] as $arrImage) {
      $newImageName .= uploadImage($arrImage, 570, 600).'Ω';
    }
    
    if($newImageName!==false and $newImageName!=''){ //загрузка изображения - ок
      if(addIntoTable('sl_portfolio', array('item_category','item_category_en','images','text_big','text_small','text_big_en','text_small_en', 'time','edit_by'), array($_POST['item_category'],$_POST['item_category_en'],$newImageName,$_POST['header'],$_POST['text_block'],$_POST['header_en'],$_POST['text_block_en'],time(),$_SESSION['new']['signature']))){
        header("Location: index.php?strError=passOK&".$strCurCuery);exit;
      }else{
        header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
      }
    }else{//загрузка изображения - ОШИБКА 
      header("Location: index.php?strError=BadDBAcces&".$strCurCuery);exit;
    }
  }else{//пользователь не может менять данные
    header("Location: index.php?strError=BadUserAcces&".$strCurCuery);exit;
  }
  //------------------------------------------------------------------------------------------
  //------------------------------------------------------------------------------------------
}


// принимаем информацию которую будет выводить ошибка или другая строка гет запроса
if($_GET['strError'] == 'BadPass'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required fields - password!</strong> Please add information at all fields marked with * which you see below.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'wrongReq'){
    $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                   <strong>Wrong Request detected!</strong> Please try to edit only existing datasheets.
                   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                   </button>
                 </div>';
}elseif($_GET['strError'] == 'BadAcces'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Access denied</strong> Please try to edit only existing users and users with less than your permission.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'BadDBAcces'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Databaze error</strong> Please try to edit this after time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'BadUserAcces'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Access denied</strong> Please try to edit only allowed for your permission data.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'Empty'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field!</strong> Please add information at all fields marked with * which you see below.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'passOK'){ // сообщение если все прошло хорошо
  $strError = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> All done successfully.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}else{
  $strError = '';
}

//выводим на страницу по умолчанию
$strPageTitle = 'Hello, '.$_SESSION['new']['login'];





//действия переданные в гет запросе
if($_GET['strDo'] == 'Remove'){ //запрос на отмену задачи
  if(isset($_GET['strId']) and $_GET['strId']!='' and is_array(selectFromTable('big_task_log', array('id'), true, 'id', $_GET['strId']))){//если задача есть
    if($intUserPermis==1){//если уровень доступа позволяет
      if(updateTable('big_task_log', array('canceled_in_date'),'id',$_GET['strId'],array(time()))){//продуем отменить задачу
        header("Location: index.php?strError=remOK");exit;
      }else{header("Location: index.php?strError=remNon");exit;}//отмена не удалась
    }else{header("Location: index.php?strError=Access");exit;}//уровеь не позволяет
  }else{header("Location: index.php?strError=NotExist");exit;}//задачи тнет
}elseif($_GET['strDo'] == 'Redim'){ //запрос на возврат отмененной задачи
  if(isset($_GET['strId']) and $_GET['strId']!='' and is_array(selectFromTable('big_task_log', array('id'), true, 'id', $_GET['strId']))){//если задача есть
    if($intUserPermis==1){//если уровень доступа позволяет
      if(updateTable('big_task_log', array('canceled_in_date'),'id',$_GET['strId'],array(0))){//продуем вернуть задачу
        header("Location: index.php?strError=redOK");exit;
      }else{header("Location: index.php?strError=redNon");exit;}//возврат не удалась
    }else{header("Location: index.php?strError=Access");exit;}//уровеь не позволяет
  }else{header("Location: index.php?strError=NotExist");exit;}//задачи тнет
}elseif($_GET['strDo'] == 'Start'){ //запрос на начать работу над заявкой быстро
  if(isset($_GET['strId']) and $_GET['strId']!='' and is_array(selectFromTable('big_task_log', array('id'), true, 'id', $_GET['strId']))){//если задача есть
    if($intUserPermis==1 or $intUserPermis==2){//если уровень доступа позволяет
      //получаем текущие данные
      $arrTasksRows = unserialize(selectFromTable('big_task_log', array('work_spreadsheet'), true, 'id', $_GET['strId'])[0]['work_spreadsheet']);
      //если значение дискрипшина и даты пустое то вносим
      if($arrTasksRows['Description'][0]=='' and $arrTasksRows['arrDate'][0]=='' and $arrTasksRows['arrDate'][0]==0){
        $arrTasksRows['Description'][0] = '-';
        $arrTasksRows['arrDate'][0] = time();
      }else{header("Location: index.php?strError=NotStatus");exit;}//задача уже содержит дату и описание (на другой стадии она)
      if(updateTable('big_task_log', array('work_spreadsheet', 'work_was_started'),'id',$_GET['strId'],array(serialize($arrTasksRows), time()))){//продуем внести данные
        header("Location: index.php?strError=startOK");exit;
      }else{header("Location: index.php?strError=startNon");exit;}//данные внести не удалась
    }else{header("Location: index.php?strError=Access");exit;}//уровеь не позволяет
  }else{header("Location: index.php?strError=NotExist");exit;}//задачи тнет
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

            <!-- шапка навбар -->
            <div class='row myTopper'>
                <p class="leftstr myTopTextL"><?=$strPageTitle?></p>
                <p class="rightstr myTopTextR">
                  <a href='<?=$config['sitelink']?>' target='_blank' style='padding-right:5px;border-right: 1px solid black;'>VIEW SITE</a>
                  <a href='<?=$config['sitelink']?>admin/LogIN.php'>EXIT</a>
                </p>
                <div style="clear: left"></div>
            </div>
            <!-- шапка навбар -->


            <!-- большой блок -->
            <div class="container-fluid">
              <div class="row">
                <!-- меню и контент -->
                <div class="col-sm-4 col-md-2 myMenu">
                    <div class='myMenuListStable'>MENU</div>
                    <?php menu($_GET['strPage']); ?> <!-- выводит меню -->
                </div>
                <div class="col-sm-8 col-md-10 myContent">
                    <?=$strError?>
                    <?php Content($_GET['strPage'], $intUserPermis); ?> <!-- выводит контент -->
                </div>
                <!-- меню и контент -->
              </div>
            </div>
            <!-- большой блок -->

            
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
    </script>
    <!-- скрипты по запросу страницы -->
    <?=getScript($_GET['strPage'])?>
  </body>
</html>
