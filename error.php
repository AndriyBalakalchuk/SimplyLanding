<?php
// Вывод ошибок
error_reporting(E_ALL & ~E_NOTICE); // Уровень вывода ошибок (без нотисов)

// Абсолютный путь
$path = dirname(__FILE__) . '/';

//нету файла конфиг -- не установлено, значит просим вернутся на главную и продолжить установку
if(!file_exists($path.'admin/inc/config.inc.php')){
  echo 'Please go the main page and do install before';exit;
}

// Подключение конфигов
include_once $path . 'admin/inc/config.inc.php';

// Заголовок кодировки
header('Content-type: text/html; charset=' . $config['encoding']);

 $strIcoButton = 'fa-undo'; //иконка по умолчанию для кнопки под ошибкой
 $strLinkButton = $config['sitelink']; //ссылка по умолчанию для кнопки под ошибкой
 $strTextButton = 'Back'; //текст по умолчанию для кнопки под ошибкой

// принимаем информацию которую будет выводить ошибка
if($_GET['strError'] != ''){


}else{ //непредусмотренная ошибка или серверные ошибки (400,401,403,404,500)
 $strErrorTitle = 'ERROR';
 $strErrorMessage = 'We are sorry but an error occurs, almost all the cases this error due to trying open non existing page.<br> Please, use buttons below to go back at the main page and continue work with the tool.';
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
