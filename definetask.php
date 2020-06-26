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
  header("Location: index.php?strError=Access");exit;
}

$intUserPermis = checkUserBy('signature', $_SESSION['new']['signature'], true, array('accesslevel'))[0]['accesslevel']*1;
// у него нет доступа распределять задачи, редиректим
if($intUserPermis!=1){
  header("Location: index.php?strError=Access");exit;
}


// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);

 $strIcoButton1 = 'fa-floppy-o'; //иконка по умолчанию для кнопки войти/выйти
 $strLinkButton1 = "DefNewTask"; //ссылка по умолчанию для кнопки войти/выйти
 $strTextButton1 = 'Send task'; //текст по умолчанию для кнопки войти/выйти

 $strIcoButton2 = 'fa-undo'; //иконка по умолчанию для кнопки подать задачу
 $strLinkButton2 = $config['sitelink']; //ссылка по умолчанию для кнопки подать задачу
 $strTextButton2 = 'Back'; //текст по умолчанию для кнопки подать задачу


 // принимаем информацию пришедшую из форм
 //--------------------------------------------------------------------------------
 if($_POST['strInnFromForm'] == 'DefNewTask'){ //пришел запрос на распределение задачи
   /*
     проверки переданных данных
     */
     $strCurCuery=clearMyQuery($_SERVER['QUERY_STRING'],'strError');

     if($_POST['taskPriority'] == ''){header("Location: definetask.php?strError=Prior&$strCurCuery");exit;}
     if($_POST['WoInitials'] != ''){
       if(!checkUserBy('signature',$_POST['WoInitials'], false) or checkUserBy('signature', $_POST['WoInitials'], true, array('accesslevel'))[0]['accesslevel']*1!=2){header("Location: definetask.php?strError=Worker&$strCurCuery");exit;}
     }
     if($_POST['strTaskId'] == '' or !is_array(selectFromTable('big_task_log', array('id'), true, 'id', $_POST['strTaskId']))){header("Location: definetask.php?strError=Task&$strCurCuery");exit;}

     //если дата прислана то проверяем верна ли она
     if(trim($_POST['UserData']) != ''){
       switch (checkDate_DD_MM_YYYY(trim($_POST['UserData']), true)) {
         case 'In Past':
           header("Location: definetask.php?strError=InPast&$strCurCuery");exit;
           break;
         case 'Wrong Format':
           header("Location: definetask.php?strError=WrongFormat&$strCurCuery");exit;
         default:
           $_POST['UserData'] = trim($_POST['UserData']);
       }
     }else{//если дата не прислана то просто пишем что даты нет
         $_POST['UserData'] = '';
     }

     //проверка есть ли такой приоритет в перичне приоритетов
     if(trim($_POST['taskPriority'])*1 < 1 || trim($_POST['taskPriority'])*1 > 4){header("Location: definetask.php?strError=Prior&$strCurCuery");exit;}

     //получаем массив задачи из ячейки work_spreadsheet и обновляем его если он пуст или изменен
     $arrSpreadsheetData = selectFromTable('big_task_log', array('work_spreadsheet'), true, 'id', $_POST['strTaskId'])[0]['work_spreadsheet'];
     //данных нет
     if($arrSpreadsheetData == ''){
       //вносим данные и сериализируем массив
        $arrSpreadsheetData = serialize(array('Travel out time'=>'',
                                              'submitEmpl'=>'',
                                              'submitManag'=>'',
                                              'Travel return time'=>'',
                                              'Staff mileage'=>'',
                                              'Machine hours'=>'',
                                              'Make'=>'',
                                              'Model'=>'',
                                              'Serial No'=>'',
                                              'Customer Sign'=>'',
                                              'Customer PO#'=>'',
                                              'Date'=>'',
                                              'Parts'=>array(),
                                              'Supplier'=>array(),
                                              'Price'=>array(),
                                              'Ok'=>array(),
                                              'Description'=>array(),
                                              'arrDate'=>array(),
                                              'Hours'=>array(),
                                              'Registration number &/or Plant#'=>'',
                                              'Hourly applied rate'=>'60',
                                              'Mileage rate/mile'=>'1.2',
                                              'Parts markup'=>'20%'));
     }

     //если назначили сотрудника то сохраняем дату в базу
     if($_POST['WoInitials'] != ''){
       $dateCreated = time();
     }else{//убрали сотрудника, убираем дату
       $dateCreated = 0;
     }

     //пробуем обновить данные в задаче
     $strIDofUpdTask=updateTable('big_task_log', array('complete',
                                                       'date_created',
                                                       'staff_initial',
                                                       'work_spreadsheet',
                                                       'need_for_parts',
                                                       'work_was_started',
                                                       'task_priority',
                                                       'managers_comment',
                                                       'labour_plus_mileage',
                                                       'due_by'),
                                                       'id',$_POST['strTaskId'],
                                                 array('false',
                                                       $dateCreated,
                                                       $_POST['WoInitials'],
                                                       $arrSpreadsheetData,
                                                       "true",
                                                       "false",
                                                       $_POST['taskPriority'],
                                                       $_POST['managerComment'],
                                                       '0',
                                                       checkDate_DD_MM_YYYY(trim($_POST['UserData']), false)));

     if($strIDofUpdTask!==false){
       //если назначили сотрудника шлем почту
       if($_POST['WoInitials'] != ''){
         //получить  мейл работника
         $arrMails = checkUserBy('signature', $_POST['WoInitials'], true, array('email'));
         if(is_array($arrMails)){
           $strEmail = $arrMails[0]['email'];
           //если что-то пошло не так
         }else{header("Location: definetask.php?strError=mailError&$strCurCuery");exit;}
         //пробуем отправить  уведомление о новой записи
         if(sendNotification($strEmail, "New work defined to you - {$_POST['strTaskId']}", "You can use this <a href='{$config['sitelink']}' target='_blank'>→WJM tool←</a> for find and start working with job.</br> Date due: ".trim($_POST['UserData'])."</br> Costumer comment: ".trim($_POST['managerComment']))){
           header("Location: index.php?strError=updOK");exit;
           //отпавка провалилась, ошибку пишем
         }else{header("Location: definetask.php?strError=mailError&$strCurCuery");exit;}
       }else{
         header("Location: index.php?strError=updOK");exit;
       }
       //если по какой-то причине данные в базу не попали
     }else{header("Location: definetask.php?strError=dbAddingErr&$strCurCuery");exit;}

     /*
     проверки переданных данных конец
     */




 //--------------------------------------------------------------------------------
 }


 $strPageTitle = 'Defining task '.$_GET['strId'];
 $strMessage = 'Please fill input rows in the form below and press "send task" button';

// формируем опции для выбора приоритета
 $strOptionPrior ="<option value='{$_GET['strPrior']}'>{$config_priority[$_GET['strPrior']]}</option>";
 for($i=1;$i<=count($config_priority);$i++){
   if($i != $_GET['strPrior']){
     $strOptionPrior .= "<option value='$i'>{$config_priority[$i]}</option>";
   }
 }
$arrDate = explode('.',$_GET['strDueBy']); // разбиваем дату для отображения


//получаем коммент менеджера к текущей задаче (если есть) и инициалы текщего юзера, и данные для деталей по задаче
$arrManComment = selectFromTable('big_task_log', array('managers_comment','staff_initial','job_name','customer_name','gsn_or_id_of_machine','task_priority','due_by','site_address','contact_phone_number_and_name','notes_or_more_detail','managers_comment'), true, 'id', $_GET['strId']);

//получаем список сотрудников и формируем опции для выпадающего меню
$arrEmployeers = selectFromTable('Users', array('signature','login'), true, 'accesslevel', '2');
$strSelectEmpl = '';
if(is_array($arrEmployeers)){
 $strSelectEmpl = '<option value="'.$arrManComment[0]['staff_initial'].'">Current</option>';
 foreach($arrEmployeers as $arrEmployeer){
   if($arrManComment[0]['staff_initial']!=$arrEmployeer['signature']){
     $strSelectEmpl .= '<option value="'.$arrEmployeer['signature'].'">'.$arrEmployeer['login'].'</option>';
   }
  }
 $strSelectEmpl .= '<option value="">Undefined</option>';
}else{
  $strSelectEmpl = '<option value="">Undefined</option>';
}

// принимаем информацию которую будет выводить ошибка или другая строка гет запроса
if($_GET['strError'] == 'Prior'){
  $strError = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                 <strong>Wrong priority!</strong> Sorry, but you are trying to add empty Priority Level, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'mailError'){
  $strError = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                 <strong>Task defined but email notification was not send!</strong> Sorry, but due to some mail troubles on your hosting, we can`t send the notification.
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
}elseif($_GET['strError'] == 'Worker'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong worker selected!</strong> Sorry, but you are trying to add wrong worker, please write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'Task'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong task selected!</strong> Sorry, but you are trying to work with undefined task, please write information one`s more time.
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
}else{ //выводим на страницу по умолчанию

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
            <div class="modal fade bd-example-modal-sm" id="ModalInfo1" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                  <strong>Job name:</strong> <?=$arrManComment[0]['job_name']?><br>
                  <strong>Custumer name:</strong> <?=$arrManComment[0]['customer_name']?><br>
                  <strong>Machine ID:</strong> <?=$arrManComment[0]['gsn_or_id_of_machine']?><br>
                  <strong>Priority:</strong> <?=$config_priority[$arrManComment[0]['task_priority']]?><br>
                  <strong>Date when all must be done:</strong> <?=getNormalDate($arrManComment[0]['due_by'])?><br>
                  <strong>Site/address:</strong> <?=$arrManComment[0]['site_address']?><br>
                  <strong>Contact phone number (and name):</strong> <?=$arrManComment[0]['contact_phone_number_and_name']?><br>
                  <strong>Job Details:</strong> <?=$arrManComment[0]['notes_or_more_detail']?><br>
                  <strong>Manager comment:</strong> <?=$arrManComment[0]['managers_comment']?><br>
                </div>
              </div>
            </div>
            <img width='40%' src='<?=$config['Logo']?>'>
            <H1 style="color:#F89633;"><?=$strPageTitle?></H1>
            <p><?=$strMessage?><?=$strError?></p>
            <hr/>
              <a class='btn btn-info btn-sm' style="color:white;" id='ModalInfoAct1' title='Details'><i class='fa fa-info-circle'></i> Job details</a>
            <hr/>
            <!-- форма старт -->
            <form action='' method='post'>
              <div class="form-row" style='padding-bottom: 15px;'>
                <div class="col-lg-4">
                  <label for="validationPriority">Choose job-priority</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-arrows-v"></i></span>
                    </div>
                    <select id="validationPriority" class="form-control" name="taskPriority" aria-describedby="inputGroupPrepend1">
                      <?=$strOptionPrior?>
                    </select>
                  </div>
                </div>
                <div class="col-lg-4">
                  <label for="validationUserData">Must be ready on ...</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-calendar-check-o"></i></span>
                    </div>
                    <input type="date" class="form-control" value="<?=$arrDate[2].'-'.$arrDate[1].'-'.$arrDate[0]?>" id="validationUserData" name="UserData" aria-describedby="inputGroupPrepend4" >
                  </div>
                </div>
                <input id="" name="strTaskId" type="hidden" value="<?=$_GET['strId']?>">
                <div class="col-lg-4">
                  <label for="validationWoInitials">Choose worker to work with job</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-user-circle"></i></span>
                    </div>
                    <select id="validationWoInitials" class="form-control" name="WoInitials" aria-describedby="inputGroupPrepend2">
                      <?=$strSelectEmpl?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-sm-12">
                  <label for="CommentTextarea">Manager Comment</label>
                  <textarea class="form-control" id="CommentTextarea" rows="3" name="managerComment"><?=$arrManComment[0]['managers_comment']?></textarea>
                </div>
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
        $("#ModalInfoAct1").click(function() {
          $("#ModalInfo1").modal('show');
        });
    </script>
  </body>
</html>
