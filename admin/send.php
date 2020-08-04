<?php
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

// Заголовок кодировки
header('Content-type: text/html; charset='.$config['encoding']);

//проверяем переменные
if($_POST['inner'] == 'form'){
    $boolVariables = true;
    if(!isset($_POST['name'])){$boolVariables = false;}
    if(!isset($_POST['email'])){$boolVariables = false;}
    if(!isset($_POST['phone'])){$boolVariables = false;}
    if(!isset($_POST['text'])){$boolVariables = false;}
    if($boolVariables){

        // емайл получателя данных из формы
        $to = selectFromTable('sl_content', array('content_for', 'text_big', 'text_big_en', 'time','edit_by'), true, 'content_for', 'Email')[0]['text_big'];
        $tema = "Feadback From - ".sprintf("%s://%s%s",isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',$_SERVER['SERVER_NAME'],$_SERVER['REQUEST_URI']); // тема полученного емайла
        $message = "Name: ".$_POST['name']."<br>";//присвоить переменной значение, полученное из формы name=name
        $message .= "E-mail: ".$_POST['email']."<br>"; //полученное из формы name=email
        $message .= "Phone: ".$_POST['phone']."<br>"; //полученное из формы name=phone
        $message .= "Text message: ".$_POST['text']."<br>"; //полученное из формы name=message

        //проверить есть ли данные для SMTP
        if($email_config['Username'] == '#EmailName'){
            $headers  = 'MIME-Version: 1.0' . "\r\n"; // заголовок соответствует формату плюс символ перевода строки
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n"; // указывает на тип посылаемого контента
            if(!mail($to, $tema, $message, $headers)){$strMess = 'error';}else{$strMess = 'done';} //отправляет получателю на емайл значения переменных
        }else{
            if(!send_to_user($to, $tema, $message, $path)){$strMess = 'error';}else{$strMess = 'done';}
        }
    }else{$strMess = 'error';}

    header("Location: send.php?strMess=".$strMess);exit;
}elseif ($_POST['inner'] == 'rss') {
    if(!isset($_POST['mail'])){
        $strMess = 'error';
    }else{
        CheckData('sl_collected_mails', 'id', '', true);
        if(addIntoTable('sl_collected_mails', array('email','time'), array($_POST['mail'],time()))){
            $strMess = 'done';
        }else{
            $strMess = 'error';
        }
    }
    header("Location: send.php?strMess=".$strMess);exit;
}

$strIcoButton = 'fa-undo'; //иконка по умолчанию для кнопки под ошибкой
$strLinkButton = $config['sitelink']; //ссылка по умолчанию для кнопки под ошибкой
$strTextButton = 'Back'; //текст по умолчанию для кнопки под ошибкой

if($_GET['strMess']=='error'){
    $strErrorTitle = 'ERROR';
    $strErrorMessage = 'We are sorry but an error occurs.';
}elseif($_GET['strMess']=='done'){
    $strErrorTitle = 'Done';
    $strErrorMessage = 'All was done, thank you.';
}else{
    header("Location: ../index.php");exit;
}


?>

<!DOCTYPE html>
<html>
  <head>
    <TITLE><?=$config['sitename']?> — <?=$strErrorTitle?></TITLE>
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
              <H1 style="color:#f83533;"><strong><?=$strErrorTitle?></strong></H1>
              <p><?=$strErrorMessage?></p>
              <hr/>
              <table border='0'>
                <td>
                  <tr>
                    <a id="" class="btn btn-info btn-sm btnModal" href='<?=$strLinkButton?>'><i class="fa <?=$strIcoButton?>" aria-hidden="true"></i> <?=$strTextButton?></a>
                  </tr>
                </td>
              </table>
              <div id="content" style="display:none;">
                <img height='50px' src='<?=$config['WaitingGIF']?>'>
              </div>
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
  </body>
</html>
