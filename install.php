<?php
// Вывод ошибок
error_reporting(E_ALL & ~E_NOTICE); // Уровень вывода ошибок (без нотисов)

// Абсолютный путь
$path = dirname(__FILE__) . '/';

// Подключение темплейт конфигов
include_once $path . 'admin/inc/db.inc.php';
include_once $path . 'admin/inc/funclibery.inc.php';
if(file_exists($path.'admin/inc/config.inc.php')){//если первый этап завершен
  //подключаем норм конфиг
  include_once $path . 'admin/inc/config.inc.php';
  //соединяемся с базой данных для всех исходящих запросов
  $objDB = GoToDB($config['connetion'], $config['user'], $config['password']);
  //проверяем есть ли данные про суперюзеру, если все есть -- переброс на индекс
  if(checkUserBy('signature',$config['SuperUser'], false)){
    header("Location: index.php");exit;
  }else{
    if($_GET['Steps'] != 'Second'){header("Location: install.php?Steps=Second");exit;}
  }
}else{
  include_once $path . 'admin/inc/config_template.inc.php';
}




// Заголовок кодировки
header('Content-type: text/html; charset=' . $config['encoding']);

 $strIcoButton1 = 'fa-caret-right'; //иконка по умолчанию для кнопки далее/завершить
 $strLinkButton1 = 'FirstStepData'; //ссылка по умолчанию для кнопки далее/завершить
 $strTextButton1 = 'Next Step'; //текст по умолчанию для кнопки далее/завершить

 $strIcoButton2 = 'fa-times'; //иконка по умолчанию для кнопки отмена
 $strLinkButton2 = 'reset'; //ссылка по умолчанию для кнопки отмена
 $strTextButton2 = 'Clear'; //текст по умолчанию для кнопки отмена

 $strError = ''; //переменная вывода ошибки. по умолчанию пуста

 // принимаем информацию на каком мы этапе
 if($_POST['strInnFromForm'] == 'FirstStepData'){ //этап 1 закончилм, вносим данные сепер админа, этап 2
   //проверка переданных с формы данных
   if(trim($_POST['SuperUser']) == ''){header("Location: install.php?strError=Empty");exit;}
   if(trim($_POST['DBName']) == ''){header("Location: install.php?strError=Empty");exit;}
   if(trim($_POST['DBUsername']) == ''){header("Location: install.php?strError=Empty");exit;}
   if(trim($_POST['DBPass']) == ''){header("Location: install.php?strError=Empty");exit;}
   if(trim($_POST['DBHost']) == ''){header("Location: install.php?strError=Empty");exit;}
   if(strripos($_POST['DBHost'], ':')){//если в хост передали порт через двоеточие
     $arrTmpArray = array();
     $arrTmpArray = explode(':',$_POST['DBHost']);
     $_POST['DBHost'] = "{$arrTmpArray[0]};port={$arrTmpArray[1]}";
   }
   //необязательные поля
   $_POST['EMHost'] = trim($_POST['EMHost']);
   $_POST['EmailName'] = trim($_POST['EmailName']);
   $_POST['EmailPass'] = trim($_POST['EmailPass']);
   $_POST['EmailEncript'] = trim($_POST['EmailEncript']);
   $_POST['EmailPort'] = trim($_POST['EmailPort']);
   //попытка подключения к базе данных по переданной инфо
   //подключение удалось, попытка создать файл конфиг с переданными данными
   if(GoToDB("mysql:host={$_POST['DBHost']};dbname={$_POST['DBName']}", $_POST['DBUsername'], $_POST['DBPass']) !== false){
     //файл создан - переходим к шагу 2
     if(crConfigFile($_POST['SubPrefix'], $_POST['SuperUser'], "mysql:host={$_POST['DBHost']};dbname={$_POST['DBName']}", $_POST['DBUsername'], $_POST['DBPass'], $path . 'admin/inc/', $_POST['EMHost'], $_POST['EmailName'], $_POST['EmailPass'], $_POST['EmailEncript'], $_POST['EmailPort'])){
       header("Location: install.php?Steps=Second");exit;
       //файл не создан - перебрасываем на первый шаг и выводим ошибку - через гет
     }else{header("Location: install.php?strError=FileCr");exit;}
     //подключение не удалось - перебрасываем на первый шаг и выводим ошибку - через гет
   }else{header("Location: install.php?strError=Connect");exit;}
 //этап 2 закончили, если все ок, установка завершена
 }elseif($_POST['strInnFromForm'] == 'SecondStepData'){
   // проверяем присланные данные
   if(trim($_POST['UserLogin']) == ''){header("Location: install.php?Steps=Second&strError=EmptyLogin");exit;}
   if(trim($_POST['UserEmail']) == '' or checkUserBy('email',trim($_POST['UserEmail']), false) or !validEmail(trim($_POST['UserEmail']))){header("Location: install.php?Steps=Second&strError=WrongEmail");exit;}
   if(trim($_POST['UserPass']) == '' or trim($_POST['UserPass_rep']) == '' or trim($_POST['UserPass']) != trim($_POST['UserPass_rep'])){header("Location: install.php?Steps=Second&strError=BadPass");exit;}

   //пробуем создать пользывателя, создался тру -- нет фолс
   if(addUser(trim($_POST['UserLogin']), trim($_POST['UserEmail']), trim($_POST['UserPass']), '1', $config['SuperUser'])){
     header("Location: index.php");exit;
   }else{header("Location: install.php?Steps=Second&strError=addUserError");exit;}
 }

 //выводим ошибки и фиксируем этапы установки используя гет строку
 if($_GET['Steps'] == 'Second'){
   //проверить есть ли конфиг, не хитрят ли юзеры пропуская первый шаг вручную
   if(!file_exists($path.'admin/inc/config.inc.php')){//если первый этап не завершен
     header("Location: install.php");exit;
   }
   //если нам на втором этапе вернулась ошибка - пустое поле логин
   if($_GET['strError'] == 'EmptyLogin'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Empty required field - login!</strong> Please add information at all fields marked with * which you see below.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   //если нам на первом этапе вернулась ошибка - нету, или не верный мейл
   }elseif($_GET['strError'] == 'WrongEmail'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Wrong data in required field - email!</strong> Please add information at all fields marked with * which you see below.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   //если нам на первом этапе вернулась ошибка - ошибка в переданном пароле
   }elseif($_GET['strError'] == 'BadPass'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Wrong data in required fields - password!</strong> Please add information at all fields marked with * which you see below.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   //если нам на первом этапе вернулась ошибка - не удалось создать пользователя
   }elseif($_GET['strError'] == 'addUserError'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>User adding problem!</strong> Please try again later.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   }

   $strTextButton1 = 'Open my Site'; //текст для кнопки далее/завершить
   $strLinkButton1 = 'SecondStepData'; //ссылка для кнопки далее/завершить

   $strPageTitle = 'Installation, last step<hr>';
   $strMessage = '<h3>Step 2</h3><p>Please, fill the form below and press "Open my Site" button.</p>
   '.$strError.'
   <!-- форма -->
   <form action="" method="post" autocomplete="off">
      <div class="form-row" style="padding-bottom: 15px;">
        <div class="col-lg-4 offset-lg-4">
          <label for="validationSuperUser">Global Admin Signature*</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-user-circle"></i></span>
            </div>
            <input type="text" class="form-control" id="validationSuperUser" name="SuperUser" value="'.$config['SuperUser'].'" aria-describedby="inputGroupPrepend" disabled>
          </div>
        </div>
      </div>
      <div class="form-row" style="padding-bottom: 15px;">
        <div class="col-lg-4 offset-lg-4">
          <label for="validationUserLogin">Name Surname*</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-user-o"></i></span>
            </div>
            <input type="text" class="form-control" id="validationUserLogin" name="UserLogin" placeholder="Ivanov Ivan" aria-describedby="inputGroupPrepend1" required>
          </div>
        </div>
      </div>
      <div class="form-row" style="padding-bottom: 15px;">
        <div class="col-lg-4 offset-lg-4">
          <label for="validationUserEmail">Email address*</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-at"></i></span>
            </div>
            <input type="email" class="form-control" id="validationUserEmail" name="UserEmail" placeholder="vasheimya@vashdomen.ru" aria-describedby="inputGroupPrepend2" required>
          </div>
        </div>
      </div>
      <div class="form-row" style="padding-bottom: 15px;">
        <div class="col-lg-4 offset-lg-4">
          <label for="validationUserPass">Password*</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="validationUserPass" name="UserPass" aria-describedby="inputGroupPrepend3" required>
          </div>
        </div>
      </div>
      <div class="form-row" style="padding-bottom: 15px;">
        <div class="col-lg-4 offset-lg-4">
          <label for="validationUserPass_rep">Repeat Password*</label>
          <div class="input-group">
            <div class="input-group-prepend">
              <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-lock"></i></span>
            </div>
            <input type="password" class="form-control" id="validationUserPass_rep" name="UserPass_rep" aria-describedby="inputGroupPrepend4" required>
          </div>
        </div>
      </div>

        <!-- кнопки -->
        <div class="container">
          <div class="row" style = "color:white;" >
            <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
              <button type="'.$strLinkButton2.'" class="btn btn-info btn-sm"><i class="fa '.$strIcoButton2.'"></i> '.$strTextButton2.'</button>
            </div>
            <div class="col-sm-3 col-lg-2">
              <button name="strInnFromForm" value='.$strLinkButton1.' id="" type="submit" class="btn btn-success btn-sm btnModal"><i class="fa '.$strIcoButton1.'"></i> '.$strTextButton1.'</button>
            </div>
          </div>
        </div>
        <!-- кнопки конец-->
    </form>
   <!-- форма конец-->';
 }else{ //первый этап - подставляем текст и форму для вноса инфо по базе данных
   //если нам на первом этапе вернулась ошибка - пустые поля
   if($_GET['strError'] == 'Empty'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Empty required field(s)!</strong> Please add information at all fields marked with * which you see below.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   //если нам на первом этапе вернулась ошибка - не создался файл для конфига
   }elseif($_GET['strError'] == 'FileCr'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Configuration file not created!</strong> It can be because of your server configuration, you can try to create it manually by copying file "config_template.inc.php" at admin/inc, rename it to "config.inc.php" and edit database and other params.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   //если нам на первом этапе вернулась ошибка - не удалось подключится к базе данных используя внесенные данные
   }elseif($_GET['strError'] == 'Connect'){
     $strError = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>MySQL connection problem!</strong> We can`t create the connection with your database using information which you send.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
   }

  $strPageTitle = 'Installation';
  $strMessage = '<h3>Step 1</h3><p>Please, fill the form below and press "next" button.</p>
  '.$strError.'
  <!-- форма -->
  <form action="" method="post" autocomplete="off">
     <div class="form-row" style="padding-bottom: 15px;">
       <div class="col-lg-4 offset-lg-4">
         <label for="validationSuperUser">Global Admin Login*</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-user-circle"></i></span>
           </div>
           <input type="text" class="form-control" id="validationSuperUser" name="SuperUser" placeholder="myLogin" aria-describedby="inputGroupPrepend" required>
         </div>
       </div>
     </div>
     <div class="form-row" style="padding-bottom: 15px;">
       <div class="col-lg-2 offset-lg-1">
         <label for="validationDBName">Database Name*</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-database"></i></span>
           </div>
           <input type="text" class="form-control" id="validationDBName" name="DBName" placeholder="your Database Name" aria-describedby="inputGroupPrepend1" required>
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationDBUsername">Username*</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-user-circle"></i></span>
           </div>
           <input type="text" class="form-control" id="validationDBUsername" name="DBUsername" placeholder="Username with Database Access" aria-describedby="inputGroupPrepend2" required>
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationDBPass">Password*</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-lock"></i></span>
           </div>
           <input type="password" class="form-control" id="validationDBPass" name="DBPass" placeholder="Password for Database User" aria-describedby="inputGroupPrepend3" required>
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationDBHost">Database Host*</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-arrows-alt"></i></span>
           </div>
           <input type="text" class="form-control" id="validationDBHost" name="DBHost" placeholder="localhost" aria-describedby="inputGroupPrepend4" required>
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationSubPrefix">Site Subfolder</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend5"><i class="fa fa-th"></i></span>
           </div>
           <input type="text" class="form-control" id="validationSubPrefix" name="SubPrefix" placeholder="Site Subfolder if exist" aria-describedby="inputGroupPrepend5">
         </div>
       </div>
     </div>
     <span><h5>Below fields with email sending configuration (not required)</h5></span>
     <div class="form-row" style="padding-bottom: 15px;">
       <div class="col-lg-2 offset-lg-1">
         <label for="validationEMHost">SMTP host</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend6"><i class="fa fa-database"></i></span>
           </div>
           <input type="text" class="form-control" id="validationEMHost" name="EMHost" placeholder="smtp.gmail.com" aria-describedby="inputGroupPrepend6">
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationEmailName">Email address</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend7"><i class="fa fa-at"></i></span>
           </div>
           <input type="email" class="form-control" id="validationEmailName" name="EmailName" placeholder="vasheimya@vashdomen.ru" aria-describedby="inputGroupPrepend7">
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationEmailPass">Email password</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend8"><i class="fa fa-lock"></i></span>
           </div>
           <input type="password" class="form-control" id="validationEmailPass" name="EmailPass" placeholder="Password for Email" aria-describedby="inputGroupPrepend8">
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationEmailEncript">Email encript</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend9"><i class="fa fa-database"></i></span>
           </div>
           <input type="text" class="form-control" id="validationEmailEncript" name="EmailEncript" placeholder="ssl" aria-describedby="inputGroupPrepend9">
         </div>
       </div>
       <div class="col-lg-2">
         <label for="validationEmailPort">SMTP port</label>
         <div class="input-group">
           <div class="input-group-prepend">
             <span class="input-group-text" id="inputGroupPrepend10"><i class="fa fa-database"></i></span>
           </div>
           <input type="text" class="form-control" id="validationEmailPort" name="EmailPort" placeholder="465" aria-describedby="inputGroupPrepend10">
         </div>
       </div>
     </div>
       <!-- кнопки -->
       <div class="container">
         <div class="row" style = "color:white;" >
           <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
             <button type="'.$strLinkButton2.'" class="btn btn-info btn-sm"><i class="fa '.$strIcoButton2.'"></i> '.$strTextButton2.'</button>
           </div>
           <div class="col-sm-3 col-lg-2">
             <button name="strInnFromForm" value='.$strLinkButton1.' id="" type="submit" class="btn btn-success btn-sm btnModal"><i class="fa '.$strIcoButton1.'"></i> '.$strTextButton1.'</button>
           </div>
         </div>
       </div>
       <!-- кнопки конец-->
   </form>
  <!-- форма конец-->';
 }

 ?>

<!DOCTYPE html>
<html>
 <head>
   <TITLE><?=$config['sitename']?> — <?=$strPageTitle?></TITLE>
   <base target="_top">
   <!-- Настройка favicon -->
   <link rel="shortcut icon" href="admin/images/favicon-32x32.png" type="image/png">
   <!-- Настройка viewport -->
   <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, user-scalable=no">
   <!-- Кодировка веб-страницы -->
   <meta charset="<?php echo $config['encoding']; ?>">
   <!-- Подключаем Bootstrap CSS -->
   <link rel="stylesheet" href="admin/lib/bootstrap-4.5.0-dist/css/bootstrap.min.css">
   <!-- Add icon library -->
   <link rel="stylesheet" href="admin/lib/font-awesome-4.7.0/css/font-awesome.min.css">
   <!-- Подключаем свой CSS -->
   <link rel="stylesheet" href="admin/css/Style.css">
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
                     <div class="col-sm-12"><img id='imageAnimation' height='55px' width = '55px' src='admin/images/SimplyLanding-ICO.png'></div>
                   </div>
                 </div>
                 <h3>Please don't do anything,</h3> <p> just wait until process will ends</p>
               </div>
             </div>
           </div>
           <!-- содержимое страницы -->
           <!-- базовый блок -->
           <img width='40%' src='admin/images/SimplyLanding-logo.png'>
           <H1 style="color:#F89633;"><?=$strPageTitle?></H1>
           <?=$strMessage?>


           <!-- содержимое страницы конец -->
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
               <img height='40px' src='admin/images/SimplyLanding-logo.png'>
             </td>
           </tr>
         </tbody>
       </table>
     </div>
   </div>



   <!-- Подключаем jQuery -->
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

   <!-- Подключаем Bootstrap JS -->
   <script src="admin/lib/bootstrap-4.5.0-dist/js/bootstrap.min.js"></script>

   <script>
       $(".btnModal").click(function() {
        <?php
          if($_GET['Steps'] == 'Second'){ //если мы на втором шаге то выводим окна для второго
            echo
              "if(document.getElementById('validationUserLogin').value != '' && document.getElementById('validationUserEmail').value != '' && document.getElementById('validationUserPass').value != '' && document.getElementById('validationUserPass_rep').value != ''){
                 $('#LoadingModal').modal('show');
              }";
          }else{ //если мы на первом шаге то выводим окна для первого
            echo
              "if(document.getElementById('validationSuperUser').value != '' && document.getElementById('validationDBName').value != '' && document.getElementById('validationDBUsername').value != '' && document.getElementById('validationDBPass').value != '' && document.getElementById('validationDBHost').value != '') {
                 $('#LoadingModal').modal('show');
              }";
          }
        ?>
       });
   </script>
 </body>
</html>
