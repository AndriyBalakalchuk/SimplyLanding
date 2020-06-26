<?php
session_start();//старт сессии

$_SESSION['new']['Logined']=FALSE;
$_SESSION['new']['login']='';

# Вывод ошибок
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


// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);

 $strPageTitle = 'Sign in';

if($_SERVER['REQUEST_METHOD'] == 'POST'){  //с формы что-то пришло.
    if(isset($_POST['login'])){ //логин пришел
        if(checkUserBy('signature',$_POST['login'], false)){//логин существует
          $varResult = tryToEnter($_POST['login'], $_POST['pass']);
            if(is_array($varResult)){//связка логин-пароль подошла
                $_SESSION['new']['Logined']=true;
                $_SESSION['new']['login']=$varResult[0];
                $_SESSION['new']['signature']= $varResult[1];
                $_SESSION['new']['email']= $varResult[2];
            }else{header("refresh:0; url={$config['sitelink']}admin/LogIN.php?ErrNO=1");exit;}//связка логин-пароль не подошла
        }else{header("refresh:0; url={$config['sitelink']}admin/LogIN.php?ErrNO=1");exit;}//такого логина нету
    }else{header("refresh:0; url={$config['sitelink']}admin/LogIN.php?ErrNO=1");exit;}//логина нет
}




if($_SESSION['new']['Logined']){//вошел
  header("Location: ".$config['sitelink']."admin/index.php");
  exit;
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
  <body style="font-family: <?=$config['font-family']?>; background-color:#7d9ca0;">


    <div class="container" style="padding-top:50px;">
        <div class="row">
            <div class="col-md-4 offset-md-7">
                <div style='border-color: #dddddd;'>
                    <div style="color: #333333;background-color: #f5f5f5;border-color: #dddddd;padding: 10px 15px;border-bottom: 1px solid transparent;border-top-right-radius: 3px;border-top-left-radius: 3px;">
                        <span class="fa fa-lock"></span> <?=$strPageTitle?></div>
                    <div style="padding: 15px;font-size: 14px;line-height: 1.428571429;color: #333333;background-color: #ffffff;">
                        <form class="form-horizontal" role="form" action="" method="post">
                          <div class="form-group">
                            <label for="login" class="col-sm-3 control-label">Signature</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="login" id="login" placeholder="Your user signature" required="">
                            </div>
                          </div>
                          <div class="form-group">
                              <label for="pass" class="col-sm-3 control-label">Password</label>
                              <div class="col-sm-12">
                                  <input class="form-control" type="password" name="pass" id="pass" placeholder="Your user password" required="">
                              </div>
                          </div>
                          <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9">
                              <?php
                              if($_SERVER['QUERY_STRING']!=''){
                              if($_GET['ErrNO']==1){//если ошибка в данных
                              echo "<div style='color:red;'>
                                        <label>
                                            Wrong user signature or password!
                                        </label>
                                    </div>";
                              }}?>
                            </div>
                          </div>
                        <div class="form-group last">
                            <div class="offset-sm-3 col-sm-9">
                                <button type="submit" name="sendet" class="btn btn-success btn-sm">Enter</button>
                                <a href="<?=$config['sitelink']?>" class="btn btn-info btn-sm">Back</a>
                            </div>
                        </div>
                        </form>
                    </div>
                    <div style="padding: 10px 15px;background-color: #f5f5f5;border-top: 1px solid #dddddd;border-bottom-right-radius: 3px;border-bottom-left-radius: 3px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
    <!-- Подключаем Bootstrap JS -->
    <script src="<?=$config['sitelink']?>admin/lib/bootstrap-4.5.0-dist/js/bootstrap.min.js"></script>
  </body>
</html>
