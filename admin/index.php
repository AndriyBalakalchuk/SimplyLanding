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



// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);

 $strIcoButton1 = 'fa-address-card'; //иконка по умолчанию для кнопки войти/выйти
 $strLinkButton1 = $config['sitelink']."admin/LogIN.php"; //ссылка по умолчанию для кнопки войти/выйти
 $strTextButton1 = 'Logout'; //текст по умолчанию для кнопки войти/выйти

 $strIcoButton2 = 'fa-plus-circle'; //иконка по умолчанию для кнопки подать задачу
 $strLinkButton2 = $config['sitelink'].'createtask.php'; //ссылка по умолчанию для кнопки подать задачу
 $strTextButton2 = 'Create new job'; //текст по умолчанию для кнопки подать задачу

 $intUserPermis = 0; //по умолчанию у пользователя доступ 0 - но он подтягивается из базы




// принимаем информацию которую будет выводить ошибка или другая строка гет запроса
if($_GET['strError'] == 'addOK'){
  $strError = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> Your task successfully added into workshop log.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'updOK'){
  $strError = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> Task successfully defined.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'remOK'){
  $strError = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> Task successfully canceled.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'remNon'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Error!</strong> Task can`t be removed.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'redOK'){
  $strError = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> Task successfully redeemed.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'redNon'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Error!</strong> Task can`t be redeemed.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'startOK'){
  $strError = '<div class="alert alert-info alert-dismissible fade show" role="alert">
                 <strong>Done!</strong> Task successfully started.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'startNon'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Error!</strong> Task can`t be started.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'Access'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Access denied!</strong> Your access level lower than needed.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'NotStatus'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Already started!</strong> Your trying to start work at already started task.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'NotExist'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>An ID don`t Exist!</strong> You`re trying to work with non existing Id.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}

//выводим на страницу по умолчанию
$strPageTitle = 'Hello, '.$_SESSION['new']['login'];
$strMessage = $strError.'You can use workshop job management (WJM) tool to manage jobs in your own access level using interface below, also you will find total lists of all jobs at the bottom of the screen.<br>
                        <div class="row">
                          <div class="col-sm-12">
                            <a id="" class="btn btn-warning btn-sm btnModal " href="'.$config['sitelink'].'usmanage.php"><i class="fa fa-id-card"></i> User data</a>
                          </div>
                        </div>';




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
                  <a href='<?=$strLinkButton1?>'><?=$strTextButton1?></a>
                </p>
                <div style="clear: left"></div>
            </div>
            <!-- шапка навбар -->


            <!-- #bigModalHTML - переменная для вставки модальных окон с деталями  -->
            <?=$arrMyTasks[7]?>
            <!-- #bigModalHTML - переменная для вставки модальных окон с деталями  -->
            <img width='40%' src='<?=$config['Logo']?>'>
            <H1 style="color:#F89633;"><?=$strPageTitle?></H1>
            <p><?=$strMessage?></p>
            <hr/>
            <div class="container">
              <div class="row">
                <div class="col-xl-2 offset-xl-4 col-lg-3 offset-lg-3 col-md-3 offset-md-3 col-sm-4 offset-sm-2">
                  <a id="" class="btn btn-info btn-sm btnModal " href='<?=$strLinkButton1?>'><i class="fa <?=$strIcoButton1?>"></i> <?=$strTextButton1?></a>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-3 col-sm-4 ">
                  <a id="" class="btn btn-success btn-sm btnModal" href='<?=$strLinkButton2?>'><i class="fa <?=$strIcoButton2?>"></i> <?=$strTextButton2?></a>
                </div>
              </div>
            </div>
            <hr/>
            <!--тут блок текущих задач текущему пользователю-->
            <!-- #genTaskLisForMe - переменная для задач текущему пользователю -->
            
            <!--конец блока текущих задач текущему пользователю-->
            
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
        <!-- #modalsScript - переменная для скриптов модальных окон -->
    </script>
  </body>
</html>
