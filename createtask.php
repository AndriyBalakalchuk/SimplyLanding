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




// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);

 $strIcoButton1 = 'fa-floppy-o'; //иконка по умолчанию для кнопки войти/выйти
 $strLinkButton1 = "addNewTask"; //ссылка по умолчанию для кнопки войти/выйти
 $strTextButton1 = 'Save task'; //текст по умолчанию для кнопки войти/выйти

 $strIcoButton2 = 'fa-undo'; //иконка по умолчанию для кнопки подать задачу
 $strLinkButton2 = $config['sitelink']; //ссылка по умолчанию для кнопки подать задачу
 $strTextButton2 = 'Back'; //текст по умолчанию для кнопки подать задачу


 // принимаем информацию пришедшую из форм
 //--------------------------------------------------------------------------------
 if($_POST['strInnFromForm'] == 'addNewTask'){ //пришел запрос на внос новой задачи в базу данных
     /*
      проверки переданных данных
    */
    if(trim($_POST['UserMail']) == ''){header("Location: createtask.php?strError=Email");exit;}

    if(!validEmail(trim($_POST['UserMail']))){header("Location: createtask.php?strError=Email2");exit;}

    if(trim($_POST['TaskName']) == ''){header("Location: createtask.php?strError=TaskName");exit;}

    if(trim($_POST['taskPriority']) == ''){header("Location: createtask.php?strError=taskPriority");exit;}
    //если выбрали что заявка не внутенняя то нужно проверить внесено ли имя (оно для внешних обязательно)
    if(trim($_POST['SwitchFromWho']) != 'internal'){
      if(trim($_POST['CustumerName']) == ''){header("Location: createtask.php?strError=CustumerName");exit;}

      //если это внешняя заявка то проверяем адрес и контактный телефон на наличие информации
      if(trim($_POST['AddressName']) == ''){header("Location: createtask.php?strError=AddressName");exit;}
      if(trim($_POST['UserTel']) == ''){header("Location: createtask.php?strError=UserTel");exit;}
    }else{//внутренняя заявка проверяем есть ли такое имя и если такого имени нет - чистим переданое в ноль
      //если в колонке с инициалами нет такого инициала то ставим "внутренняя задача" в переменную
      if(!checkUserBy('signature',trim($_POST['CustumerName']), false)){
        $_POST['CustumerName']="Internal job";
      }
    }

    //проверка есть ли такой приоритет в перичне приоритетов
    if(trim($_POST['taskPriority'])*1 < 1 || trim($_POST['taskPriority'])*1 > 4){header("Location: createtask.php?strError=taskPriority");exit;}

    if(trim($_POST['MachineID']) == ''){
      $_POST['MachineID'] = '';
    }

    //если дата прислана то проверяем верна ли она
    if(trim($_POST['UserData']) != ''){
      switch (checkDate_DD_MM_YYYY(trim($_POST['UserData']), true)) {
        case 'In Past':
          header("Location: createtask.php?strError=InPast");exit;
          break;
        case 'Wrong Format':
          header("Location: createtask.php?strError=WrongFormat");exit;
        default:
          $_POST['UserData'] = trim($_POST['UserData']);
      }
      }else{//если дата не прислана то просто пишем что даты нет
        $_POST['UserData'] = '';
      }


    /*
      проверки переданных данных конец
    */
    //
    //проверка нет ли айди 5 в задачах (такого айди точно нет и функция просто создаст таблицу и вернет фолс)
    if(checkTaskBy('id', 5)){header("Location: createtask.php?strError=dublicateID");exit;}
    //пробуем создать новую задачу по переданным данным
    $strIDofAddedTask=addIntoTable('big_task_log', array('date_added',
                                          'status',
                                          'email_addresses',
                                          'job_name',
                                          'customer_name',
                                          'gsn_or_id_of_machine',
                                          'task_priority',
                                          'due_by',
                                          'notes_or_more_detail',
                                          'site_address',
                                          'contact_phone_number_and_name'),
                                    array(time(),
                                          1,
                                          trim($_POST['UserMail']),
                                          trim($_POST['TaskName']),
                                          trim($_POST['CustumerName']),
                                          trim($_POST['MachineID']),
                                          trim($_POST['taskPriority'])*1,
                                          checkDate_DD_MM_YYYY(trim($_POST['UserData']), false),
                                          trim($_POST['NotifUser']),
                                          trim($_POST['AddressName']),
                                          trim($_POST['UserTel'])));
    if($strIDofAddedTask!==false){
      //получить все мейлы админов через запятую
      $arrMails = checkUserBy('accesslevel', 1, true, array('email'));
      if(is_array($arrMails)){
        $strEmail = '';
        $i = 0;
        foreach($arrMails as $strMail){
          if($i==0){//на первый круг пишем без зяпятой
            $strEmail = $strMail['email'];
          }else{//все прочие дописываем с запятой
            $strEmail = $strEmail.','.$strMail['email'];
          }
          $i++;
        }
        //если что-то пошло не так
      }else{header("Location: createtask.php?strError=mailError");exit;}
      //пробуем отправить всем с доступом 1 уведомление о новой записи
      if(sendNotification($strEmail, "New work added - $strIDofAddedTask", "You can use this <a href='{$config['sitelink']}' target='_blank'>→WJM tool←</a> for find and start working with job.</br> Job name: ".trim($_POST['TaskName'])."</br> Date due: ".trim($_POST['UserData'])."</br> Costumer comment: ".trim($_POST['NotifUser']))){
        header("Location: index.php?strError=addOK");exit;
        //отпавка провалилась, ошибку пишем
      }else{header("Location: createtask.php?strError=mailError");exit;}
      //если по какой-то причине данные в базу не попали
    }else{header("Location: createtask.php?strError=dbAddingErr");exit;}

 //--------------------------------------------------------------------------------
 }




// принимаем информацию которую будет выводить ошибка или другая строка гет запроса
if($_GET['strError'] == 'mailError'){
  $strError = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                 <strong>Task created but email notification was not send!</strong> Sorry, but due to some mail troubles on your hosting, we can`t send the notification.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'Email'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Email!</strong> Sorry, but you are trying to add empty email address, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'dublicateID'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Duplicate Id detected!</strong> Sorry, but your task wants to have already existing ID.
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
}elseif($_GET['strError'] == 'Email2'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Email!</strong> Sorry, but email address is looks like inkorrect, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'TaskName'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Task Name!</strong> Sorry, but you are trying to add empty task name, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'taskPriority'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Task Priority!</strong> Sorry, but you are trying to add empty Priority Level, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'CustumerName'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Custumer Name!</strong> Sorry, but you are trying to add empty customer name, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'AddressName'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - Address Name!</strong> Sorry, but you are trying to add empty address, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'UserTel'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - User Tel!</strong> Sorry, but you are trying to add empty contact phone number, please press back and write information one`s more time.
                 <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                   <span aria-hidden="true">&times;</span>
                 </button>
               </div>';
}elseif($_GET['strError'] == 'taskPriority'){
  $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                 <strong>Wrong data in required field - task Priority!</strong> Sorry, but you are trying to add unregistered Priority Level, please press back and write information one`s more time.
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
 $strPageTitle = 'Creating a new task for workshop';
 $strMessage = 'Please fill input rows in the form below and press "save task" button.';
 $strInternal = ''; //по умолчанию для невошедших юзеров флажок внутренняя задача снят
 $strUserSign = ''; //по умолчанию в поле имя заказчика ничего не вписано
 $strActivName = ''; //по умолчанию поле имя активно
 $strMyEmail = ''; //по умолчанию мейл поле пустое
 $strHiddenRow = ''; //по умолчанию строка с куда ехать и кому звонить показана
}

if($_SESSION['new']['Logined']){//пользователь вошел, меняем для него переменные
  $strInternal = 'checked'; //по умолчанию для вошедших юзеров флажок внутренняя задача поставлен
  $strUserSign = $_SESSION['new']['signature']; //если вошел в поле имя заказчика вписываем себя
  $strActivName = 'disabled'; //если вошел то поле имя не активно
  $strMyEmail = $_SESSION['new']['email']; //если вошли то вносим свой мейл
  $strHiddenRow = 'hidden'; //если вошел то строка с куда ехать и кому звонить скрыта
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
            <img width='40%' src='<?=$config['Logo']?>'>
            <H1 style="color:#F89633;"><?=$strPageTitle?></H1>
            <p><?=$strMessage?><?=$strError?></p>
            <hr/>

            <!-- форма старт -->
            <form action='' method='post'>
              <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="customSwitch1" name="SwitchFromWho" value='internal' <?=$strInternal?>>
                <label class="custom-control-label" for="customSwitch1">Internal job (this option only for workshop staff)</label>
              </div>
              <div class="form-row" style='padding-bottom: 15px;'>
                <div class="col-lg-4">
                  <label for="validationCustumerName">Customer name*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-user-circle"></i></span>
                    </div>
                    <input type="text" required class="form-control" id="validationCustumerName" name="CustumerName" placeholder="James Bond" aria-describedby="inputGroupPrepend" value="<?=$strUserSign?>" <?=$strActivName?>>
                  </div>
                </div>
                <div class="col-lg-4">
                  <label for="validationUsermail">Orderer email address*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-at"></i></span>
                    </div>
                    <input type="text" required class="form-control" id="validationUsermail" name="UserMail" placeholder="vip_client@company.com" value="<?=$strMyEmail?>" aria-describedby="inputGroupPrepend1" >
                  </div>
                </div>
                <div class="col-lg-4">
                  <label for="validationPriority">Choose job-priority*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend0"><i class="fa fa-arrows-v"></i></span>
                    </div>
                    <select id="validationPriority" class="form-control" name="taskPriority" aria-describedby="inputGroupPrepend0">
                      <option value='4'>4 hospital</option>
                      <option value='3'>3 low</option>
                      <option value='2'>2 medium</option>
                      <option value='1'>1 High</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="form-row" style='padding-bottom: 15px;'>
                <div class="col-lg-4">
                  <label for="validationTaskName">Task name (for easyest task searching)*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-cog"></i></span>
                    </div>
                    <input type="text" required class="form-control" id="validationTaskName" name="TaskName" placeholder="Repair car (skoda 1452) replace mirrors" aria-describedby="inputGroupPrepend2" >
                  </div>
                </div>
                <div class="col-lg-4">
                  <label for="validationUserData">Must be ready on ...</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-calendar-check-o"></i></span>
                    </div>
                    <input type="date" class="form-control" id="validationUserData" name="UserData" aria-describedby="inputGroupPrepend4" >
                  </div>
                </div>
                <div class="col-lg-4">
                  <label for="validationMachineID">GSN or ID of the machine</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-exclamation-circle"></i></span>
                    </div>
                    <input type="text" class="form-control" id="validationMachineID" name="MachineID" placeholder="GSN 8494389" aria-describedby="inputGroupPrepend3" >
                  </div>
                </div>
              </div>
              <div class="form-row" id="HidRow" style='padding-bottom: 15px;' <?=$strHiddenRow?>>
                <div class="col-lg-6">
                  <label for="validationAddressName">Site/address*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend5"><i class="fa fa-map-marker"></i></span>
                    </div>
                    <input type="text" class="form-control" id="validationAddressName" name="AddressName" placeholder="where to go?" aria-describedby="inputGroupPrepend5" >
                  </div>
                </div>
                <div class="col-lg-6">
                  <label for="validationUserTel">Contact phone number (and name)*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend6"><i class="fa fa-mobile"></i></span>
                    </div>
                    <input type="text" class="form-control" id="validationUserTel" name="UserTel" placeholder="Who to contact on site" aria-describedby="inputGroupPrepend6" >
                  </div>
                </div>
              </div>
              <div class="form-row" style='padding-bottom: 15px;'>
                <div class="form-group col-sm-12">
                  <label for="NotifTextarea">Job Details</label>
                  <textarea class="form-control" id="NotifTextarea" rows="3" name="NotifUser"></textarea>
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
          if(document.getElementById('validationUsermail').value != '' && /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(document.getElementById('validationUsermail').value) && document.getElementById('validationCustumerName').value != '' &&  document.getElementById('validationPriority').value != '' && document.getElementById('validationTaskName').value != ''){
            $("#LoadingModal").modal('show');
          }
        });

        $("#customSwitch1").click(function() {
          if($("#customSwitch1").prop("checked")){
            $("#validationCustumerName").prop('disabled', true);
            $("#HidRow").prop('hidden', true);
          }else{
            $("#validationCustumerName").prop('disabled', false);
            $("#HidRow").prop('hidden', false);
          }
        });
    </script>
  </body>
</html>
