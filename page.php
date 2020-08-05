<?php
  // Вывод ошибок
  error_reporting(E_ALL & ~E_NOTICE); // Уровень вывода ошибок (без нотисов)

  // Абсолютный путь
  $path = dirname(__FILE__) . '/';

  // Подключение конфига баз данных и скриптов
  include_once $path . 'admin/inc/db.inc.php';
  include_once $path . 'admin/inc/funclibery.inc.php';
  include_once $path . 'admin/inc/getData.inc.php';



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

  //определяем язык для пользователя
  if(!isset($_COOKIE["Language"]) or $_COOKIE["Language"] == ''){
	$strLanguage = '';
  }else{
	$strLanguage = $_COOKIE["Language"];
  }

  //провеяем есть ли айди переданный в гет
  if(!CheckData('sl_portfolio', 'id', $_GET['item'])){//нету айди -- отправить на 404
	header("Location: error.php");exit;
  }

  //получаем все переменные - детали ----→ Readme
  $arrAllData = getVariables(basename(__FILE__),$strLanguage);
  
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?=$arrAllData['sitetitle']['header']?></title>
    <!-- Настройка favicon -->
    <link rel="shortcut icon" href="<?=$arrAllData['FavIco']['image']?>" type="image/png">
    <!-- Настройка viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, user-scalable=no">
    <!-- Кодировка веб-страницы -->
    <meta charset="<?php echo $config['encoding']; ?>">
    <!-- Подключаем Bootstrap CSS -->
    <link rel="stylesheet" href="<?=$config['sitelink']?>admin/lib/bootstrap-4.5.0-dist/css/bootstrap.min.css">
    <!-- Add icon library -->
    <link rel="stylesheet" href="<?=$config['sitelink']?>admin/lib/font-awesome-4.7.0/css/font-awesome.min.css">
    <!-- Подключаем свой CSS -->
    <link rel="stylesheet" href="<?=$config['sitelink']?>main.css?v=09-05-20V1">
    <style>
      .page-portfolio-top {
			  background-image: url("<?=getSVGword(2000, 212, $arrAllData['portfolio']['headers'][0])?>");
	    }
      @media (max-width: 992px) { /* применить стили если размер экрана меньше 992px */
	      .navbar-brand::after {
          content: "<?=$arrAllData['sitetitle']['header']?>";
        }
	    }
      .contact-me .contact-info li > a:hover {
        color: <?=$arrAllData['prColor']['header']?>;
      }
      .footer a:hover {
	      color: <?=$arrAllData['prColor']['header']?>;
      }
      .footer i {
        color: <?=$arrAllData['prColor']['header']?>;
      }
      .progress-bar {
	      background-color: <?=$arrAllData['prColor']['header']?>;
      }
      .page-portfolio-description i {
	      color: <?=$arrAllData['prColor']['header']?>;
      }
      .hr-for-h1 {
        border: 5px solid <?=$arrAllData['prColor']['header']?>;
      }
      .btn-primary {
        background-color: <?=$arrAllData['prColor']['header']?>;
        border-color: <?=$arrAllData['prColor']['header']?>;
      }
      .btn-primary:hover {
        background-color: <?=alter_brightness($arrAllData['prColor']['header'], 6, 'up')?> !important;
        border-color: <?=alter_brightness($arrAllData['prColor']['header'], 6, 'up')?> !important;
      }
      .btn-primary:focus {
        background-color: <?=alter_brightness($arrAllData['prColor']['header'], 10, 'down')?> !important;
        border-color: <?=alter_brightness($arrAllData['prColor']['header'], 10, 'down')?> !important;
      }
      .btn-primary:active {
        background-color: <?=alter_brightness($arrAllData['prColor']['header'], 10, 'down')?> !important;
        border-color: <?=alter_brightness($arrAllData['prColor']['header'], 10, 'down')?> !important;
      }
      .navbar-nav a.active {
        color: <?=$arrAllData['prColor']['header']?> !important;
      }
      .navbar-nav span { /* кнопка выбора языка */
        background-color:<?=$arrAllData['prColor']['header']?>;
      }
      .fa-upwork {
        background-image: url("<?=getSVGupwork($arrAllData['prColor']['header'])?>");
      }
      body {
        font-family: <?=$arrAllData['sitefont']['header']?>;
      }
    </style>
  </head>
  <body data-spy="scroll" data-target=".navbar" data-offset="57">
<!-- меню -->
		<nav class="navbar navbar-expand-lg navbar-light fixed-top" data-offset-top="57">
			<div class="container">
				<a class="navbar-brand" href="#"></a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
				<div class="navbar-nav">
					<?php for ($i=0, $strAct='active'; $i < count($arrAllData['Navbar']); $i++) { if($i>0){$strAct='';}?>
						<a class="nav-item nav-link <?=$strAct?>" href="<?=$arrAllData['Navbar'][$i][2]?>"><?=$arrAllData['Navbar'][$i][0]?></a>
					<?php }?>
				</div>
				</div>
			</div>
		</nav>
<!-- /меню -->
<!-- html from Databaze -->
		<?=$arrAllData['portfolio']['texts'][0]?>
<!-- /html from Databaze -->
<!-- footer -->
		<div class="footer" id="footer">
			<div class="container">
				<div class="row">
					<div class="col-md">
						<h4><?=$arrAllData['сontacts_sec_header']['header']?></h4>
						<hr>
						<ul>
							<li><i class="fa fa-phone" aria-hidden="true"></i><a href="tel:<?=$arrAllData['phone']['header']?>"><?=$arrAllData['phone']['header']?></a></li>
							<li><i class="fa fa-envelope" aria-hidden="true"></i><a href="mailto:<?=$arrAllData['Email']['header']?>"><?=$arrAllData['Email']['header']?></a></li>
							<li><i class="fa fa-map-marker" aria-hidden="true"></i><?=$arrAllData['location']['header']?></li>
						</ul>
					</div>
					<div class="col-md">
						<h4><?=$arrAllData['footer']['navigation']?></h4>
						<hr>
						<ul>
							<?php for ($i=0; $i < count($arrAllData['Navbar'])-1; $i++) {?>
								<li><i class="fa fa-angle-right" aria-hidden="true"></i><a class="" href="<?=$arrAllData['Navbar'][$i][2]?>"><?=$arrAllData['Navbar'][$i][0]?></a></li>
							<?php }?>
						</ul>
					</div>
					<div class="col-md">
						<h4><?=$arrAllData['footer']['follow']?></h4>
						<hr>
						<?php if($arrAllData['Facebook']['header']!='' and $arrAllData['Facebook']['header'] !=' ' and $arrAllData['Facebook']['header'] != 'Lorem ipsum'){?>
							<a href="<?=$arrAllData['Facebook']['header']?>" target="_blank"><i class="fa fa-facebook-square" aria-hidden="true"></i></a>&nbsp;
						<?php }?>
						<?php if($arrAllData['youtube']['header']!='' and $arrAllData['youtube']['header'] !=' ' and $arrAllData['youtube']['header'] != 'Lorem ipsum'){?>
							<a href="<?=$arrAllData['youtube']['header']?>" target="_blank"><i class="fa fa-youtube-square" aria-hidden="true"></i></a>&nbsp;
						<?php }?>
						<?php if($arrAllData['instagram']['header']!='' and $arrAllData['instagram']['header'] !=' ' and $arrAllData['instagram']['header'] != 'Lorem ipsum'){?>
							<a href="<?=$arrAllData['instagram']['header']?>" target="_blank"><i class="fa fa-instagram" aria-hidden="true"></i></a>&nbsp;
						<?php }?>
						<?php if($arrAllData['upwork']['header']!='' and $arrAllData['upwork']['header'] !=' ' and $arrAllData['upwork']['header'] != 'Lorem ipsum'){?>
							<a href="<?=$arrAllData['upwork']['header']?>" target="_blank"><i class="fa fa-upwork" aria-hidden="false"></i></a>&nbsp;
						<?php }?>
						<?php if($arrAllData['linkedin']['header']!='' and $arrAllData['linkedin']['header'] !=' ' and $arrAllData['linkedin']['header'] != 'Lorem ipsum'){?>
							<a href="<?=$arrAllData['linkedin']['header']?>" target="_blank"><i class="fa fa-linkedin-square" aria-hidden="false"></i></a>&nbsp;
						<?php }?>
						<br>
						<?=$arrAllData['footer']['followHTML']?>
					</div>
				</div>
				<div class="row">
					<div class="col-md">
						<p>
              <?=$arrAllData['hardskill_header']['header']?>:
              <?php for ($i=0, $strDecider=','; $i < count($arrAllData['SkillHard']['headers']); $i++){if($i==count($arrAllData['SkillHard']['headers'])-1){$strDecider='.';}?>
                <?=$arrAllData['SkillHard']['headers'][$i]?><?=$strDecider?>
              <?php }?>
              <br>
              <?=$arrAllData['softskill_header']['header']?>:
              <?php for ($i=0, $strDecider=','; $i < count($arrAllData['SkillSoft']['headers']); $i++){if($i==count($arrAllData['SkillSoft']['headers'])-1){$strDecider='.';}?>
                <?=$arrAllData['SkillSoft']['headers'][$i]?><?=$strDecider?>
              <?php }?>
              <br>
              <?=$arrAllData['Categories']['header']?>: 
              <?php for ($i=0, $strDecider=','; $i < count($arrAllData['categories']['names']); $i++){if($i==count($arrAllData['categories']['names'])-1){$strDecider='.';}?>
                <?=$arrAllData['categories']['names'][$i]?><?=$strDecider?>
              <?php }?>
						</p>
					</div>
				</div>
			</div>
		</div>
<!-- /footer -->
<!-- copyright -->
		<div class="copyright">
			<div class="container">
				<div class="row">
					<div class="col-md"><?=$config['Copyright']?></div>
				</div>
			</div>
		</div>
<!-- /copyright -->



    <!-- Подключаем jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Подключаем Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <!-- Подключаем Bootstrap JS -->
    <script src="<?=$config['sitelink']?>admin/lib/bootstrap-4.5.0-dist/js/bootstrap.min.js"></script>

    <script>
			// Example starter JavaScript for disabling form submissions if there are invalid fields
			(function() {
				'use strict';
				window.addEventListener('load', function() {
				// Fetch all the forms we want to apply custom Bootstrap validation styles to
				var forms = document.getElementsByClassName('needs-validation');
				// Loop over them and prevent submission
				var validation = Array.prototype.filter.call(forms, function(form) {
					form.addEventListener('submit', function(event) {
					if (form.checkValidity() === false) {
						event.preventDefault();
						event.stopPropagation();
					}
					form.classList.add('was-validated');
					}, false);
				});
				}, false);
			})();

			// Add scrollspy to <body>
			$('body').scrollspy({target: ".navbar", offset: 57});

			// Add smooth scrolling on all links inside the navbar
			$(".to-id").on('click', function(event) {

			// Make sure this.hash has a value before overriding default behavior
			if (this.hash !== "") {

				// Prevent default anchor click behavior
				event.preventDefault();

				// Store hash
				var hash = this.hash;

				// Using jQuery's animate() method to add smooth page scroll
				// The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
				$('html, body').animate({
					scrollTop: $(hash).offset().top
				}, 900, function(){

					// Add hash (#) to URL when done scrolling (default click behavior)
					window.location.hash = hash;
				});

			} // End if

			});
    </script>
    <?=$arrAllData['addScripts']['description']?>
  </body>
</html>
