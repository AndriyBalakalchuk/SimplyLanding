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
      .hello {
        background-image: url("<?=getSVGword(1400, 212, $arrAllData['Slider']['header'])?>");
      }
      .hello .container {
        background-image: url("<?=$arrAllData['SliderIMG']['images'][0]?>");
      }
      .my-skills {
      	background-image: url("<?=getSVGword(1400, 212, $arrAllData['Skills']['header'])?>");
      }
      <?php for ($i=0; $i < count($arrAllData['categories']['images']); $i++) {?>
        #port<?=$i?>{
          background-image: url("<?=$arrAllData['categories']['images'][$i]?>");
        }
      <?php }?>
      .latest-works {
        background-image: url("<?=getSVGword(1400, 212, $arrAllData['portfolio_header']['header'])?>");
      }
      .contact-me {
        background-image: url("<?=getSVGword(1400, 212, $arrAllData['сontacts_header']['header'])?>");
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
						<a class="nav-item nav-link <?=$strAct?>" href="<?=$arrAllData['Navbar'][$i][1]?>"><?=$arrAllData['Navbar'][$i][0]?></a>
					<?php }?>
				</div>
				</div>
			</div>
		</nav>
<!-- /меню -->
<!-- hello -->
		<div class="hello" id="hello">
			<div class="container">
				<div class="row">
					<div class="col-md-6">
						<h1><?=$arrAllData['Slider']['header']?></h1>
						<p><?=$arrAllData['Slider']['description']?></p>
					</div>
					<div class="col-md-5">
					</div>
				</div>
			</div>
		</div>
<!-- /hello -->
<!-- about-me -->
		<div class="about-me" id="about-me">
			<div class="container">
				<div class="row">
					<div class="col">
						<h1><?=$arrAllData['About']['header']?></h1>
						<hr class="hr-for-h1">
					</div>
				</div>
				<div class="row">
					<div class="col-md-5 text-center">
          <img src="<?=$arrAllData['SliderIMG']['images'][1]?>" class="img-fluid" alt="">
					</div>
					<div class="col-md-7 d-flex">
						<div class="align-self-center">
							<p><?=$arrAllData['About']['description']?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
<!-- /about-me -->
<!-- my-skills -->
		<div class="my-skills" id="my-skills">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<h1><?=$arrAllData['Skills']['header']?></h1>
						<hr class="hr-for-h1">
						<p><?=$arrAllData['Skills']['description']?></p>
					</div>
        </div>
        <?php for ($i=0; $i < count($arrAllData['SkillHard']['headers']); $i++) {?>
          <?php if($i%2==0){echo '<div class="row">';}?>     
            <div class="col-md-6">
              <div class="my-skills-description"><span><?=$arrAllData['SkillHard']['headers'][$i]?></span><span><?=$arrAllData['SkillHard']['descriptions'][$i]?>%</span></div>
              <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?=$arrAllData['SkillHard']['descriptions'][$i]?>%" aria-valuenow="<?=$arrAllData['SkillHard']['descriptions'][$i]?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          <?php if($i%2==1){echo '</div>';}?> 
        <?php }?>  
        <?php for ($j=$i; $j < count($arrAllData['SkillSoft']['headers'])+$i; $j++) {?>
          <?php if($j%2==0){echo '<div class="row">';}?>     
            <div class="col-md-6">
              <div class="my-skills-description"><span><?=$arrAllData['SkillSoft']['headers'][$j-$i]?></span><span><?=$arrAllData['SkillSoft']['descriptions'][$j-$i]?>%</span></div>
              <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?=$arrAllData['SkillSoft']['descriptions'][$j-$i]?>%" aria-valuenow="<?=$arrAllData['SkillSoft']['descriptions'][$j-$i]?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          <?php if($j%2==1){echo '</div>';}?> 
        <?php }?>  
			</div>
		</div>
<!-- /my-skills -->
<!-- my-services -->
		<div class="my-services" id="my-services">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<h1><?=$arrAllData['Categories']['header']?></h1>
						<hr class="hr-for-h1">
						<p><?=$arrAllData['Categories']['description']?></p>
					</div>
				</div>
				<div class="row row-cols-1 row-cols-md-2">
          <?php for ($i=0; $i < count($arrAllData['categories']['names']); $i++) {?>
            <a class="card-a-100" href='<?=$config['sitelink']?>category.php?category=<?=$arrAllData['categories']['names'][$i]?>'>
              <div class="col mb-4" style='height:370px;'>
                <div class="card h-100" id="port<?=$i?>">
                  <div class="darker">
                    <div class="card-img-overlay">
                      <h2 class="card-title"><?=$arrAllData['categories']['names'][$i]?></h2>
                    </div>
                  </div>
                </div>
            </div>
          </a>
          <?php }?>
				</div>
			</div>
		</div>
<!-- /my-services -->
<!-- latest-works -->
		<div class="latest-works" id="latest-works">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<h1><?=$arrAllData['portfolio_header']['header']?></h1>
						<hr class="hr-for-h1">
						<p><?=$arrAllData['portfolio_header']['description']?></p>
					</div>
				</div>
				<div class="row row-cols-1 row-cols-md-3">
          <?php for ($i=0; $i < count($arrAllData['portfolio']['headers']); $i++) { if($i==3){break;}if(mb_strlen($arrAllData['portfolio']['images'][count($arrAllData['portfolio']['images'])-1-$i][0])<100){$strAddressImage = $config['sitelink'].'admin/images/Portfolio/';}?>
            <div class="col mb-4">
				      <a class="card-a-100" href='<?=$config['sitelink']?>page.php?item=<?=$arrAllData['portfolio']['ids'][count($arrAllData['portfolio']['images'])-1-$i]?>'>
					      <div class="card h-100">
						      <img src="<?=$strAddressImage?><?=$arrAllData['portfolio']['images'][count($arrAllData['portfolio']['images'])-1-$i][0]?>" class="card-img-top" alt="...">
						      <div class="card-body">
						        <h5 class="card-title"><?=$arrAllData['portfolio']['headers'][count($arrAllData['portfolio']['images'])-1-$i]?></h5>
						        <p class="card-text"><?=$arrAllData['portfolio']['texts'][count($arrAllData['portfolio']['images'])-1-$i]?></p>
						      </div>
					      </div>
		  		    </a>
            </div>
          <?php }?>
				</div>
			</div>
		</div>
<!-- /latest-works -->
<!-- reviews -->
		<div class="reviews">
			<div class="container">
				<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
					<ol class="carousel-indicators">
            <?php for ($i=0, $strAct='active'; $i < count($arrAllData['feedbacks']['names']); $i++) { if($i>0){$strAct='';}?>
              <li data-target="#carouselExampleIndicators" data-slide-to="<?=$i?>" class="<?=$strAct?>"></li>
            <?php }?>
          </ol>
					<div class="carousel-inner">
            <?php for ($i=0, $strAct='active'; $i < count($arrAllData['feedbacks']['names']); $i++) { if($i>0){$strAct='';}?>
              <div class="carousel-item <?=$strAct?>">
                <div class="d-flex carousel-max-height">
                  <div class="align-self-center">
                    <div class="col-md-8 offset-md-2 reviews-text">
                      <h3><?=$arrAllData['feedbacks']['names'][$i]?></h3>
                      <p>
                        "<?=$arrAllData['feedbacks']['texts'][$i]?>"
                      </p>
                      <p><?=$arrAllData['feedbacks']['positions'][$i]?></p>
                    </div>
                    <div class="reviews-stars">
                      <?=$arrAllData['feedbacks']['images'][$i]?>
                    </div>
                  </div>
                </div>
              </div>
            <?php }?>
					</div>
					<a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
					</a>
					<a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
					</a>
				</div>
			</div>
		</div>
<!-- /reviews -->
<!-- contact-me -->
		<div class="contact-me" id="contact-me">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<h1><?=$arrAllData['сontacts_header']['header']?></h1>
						<hr class="hr-for-h1">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<h5><?=$arrAllData['сontacts_sec_header']['header']?></h5>
						<ul class="contact-info">
							<li><i class="fa fa-phone" aria-hidden="true"></i><a href="tel:<?=$arrAllData['phone']['header']?>"><?=$arrAllData['phone']['header']?></a></li>
							<li><i class="fa fa-envelope" aria-hidden="true"></i><a href="mailto:<?=$arrAllData['Email']['header']?>"><?=$arrAllData['Email']['header']?></a></li>
							<li><i class="fa fa-map-marker" aria-hidden="true"></i><?=$arrAllData['location']['header']?></li>
						</ul>
						<h5><?=$arrAllData['footer']['follow']?></h5>
						<ul class="follow-me">
							<?php if($arrAllData['Facebook']['header']!='' and $arrAllData['Facebook']['header'] !=' ' and $arrAllData['Facebook']['header'] != 'Lorem ipsum'){?>
								<li><a href="<?=$arrAllData['Facebook']['header']?>" target="_blank"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></li>
							<?php }?>
							<?php if($arrAllData['youtube']['header']!='' and $arrAllData['youtube']['header'] !=' ' and $arrAllData['youtube']['header'] != 'Lorem ipsum'){?>
								<li><a href="<?=$arrAllData['youtube']['header']?>" target="_blank"><i class="fa fa-youtube-square" aria-hidden="true"></i></a></li>
							<?php }?>
							<?php if($arrAllData['instagram']['header']!='' and $arrAllData['instagram']['header'] !=' ' and $arrAllData['instagram']['header'] != 'Lorem ipsum'){?>
								<li><a href="<?=$arrAllData['instagram']['header']?>" target="_blank"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
							<?php }?>
							<?php if($arrAllData['upwork']['header']!='' and $arrAllData['upwork']['header'] !=' ' and $arrAllData['upwork']['header'] != 'Lorem ipsum'){?>
								<li><a href="<?=$arrAllData['upwork']['header']?>" target="_blank"><i class="fa fa-upwork" aria-hidden="false"></i></a></li>
							<?php }?>
							<?php if($arrAllData['linkedin']['header']!='' and $arrAllData['linkedin']['header'] !=' ' and $arrAllData['linkedin']['header'] != 'Lorem ipsum'){?>
								<li><a href="<?=$arrAllData['linkedin']['header']?>" target="_blank"><i class="fa fa-linkedin-square" aria-hidden="false"></i></a></li>
							<?php }?>
						</ul>
					</div>
					<div class="col-md-8">
						<hr style="display: none;">
						<form class="needs-validation" method='post' action='admin/send.php' novalidate>
							<div class="form-group">
								<label for="formInputName"><?=$arrAllData['feedbackForm']['name']?></label>
								<input type="text" name="name" class="form-control form-control-lg" id="formInputName" placeholder="<?=$arrAllData['feedbackForm']['pl_name']?>" required>
								<div class="valid-feedback"><?=$arrAllData['feedbackForm']['Ok']?></div>
								<div class="invalid-feedback"><?=$arrAllData['feedbackForm']['pl_name']?></div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="formInputPhone"><?=$arrAllData['feedbackForm']['phone']?></label>
									<input type="text" name="phone" class="form-control form-control-lg" id="formInputPhone" placeholder="<?=$arrAllData['feedbackForm']['pl_phone']?>" pattern="[\s-+0-9]*" required>
									<div class="valid-feedback"><?=$arrAllData['feedbackForm']['Ok']?></div>
									<div class="invalid-feedback"><?=$arrAllData['feedbackForm']['pl_phone']?></div>
								</div>
								<div class="form-group col-md-6">
									<label for="formInputEmail"><?=$arrAllData['feedbackForm']['mail']?></label>
									<input type="text" name="email" class="form-control form-control-lg" id="formInputEmail" placeholder="<?=$arrAllData['feedbackForm']['pl_mail']?>" pattern="([-_\.\w]+@[-_\.a-zA-Z_]+?\.[a-zA-Z]{2,6})" required>
									<div class="valid-feedback"><?=$arrAllData['feedbackForm']['Ok']?></div>
									<div class="invalid-feedback"><?=$arrAllData['feedbackForm']['pl_mail']?></div>
								</div>
							</div>
							<div class="form-group">
								<label for="formInputMessage"><?=$arrAllData['feedbackForm']['message']?></label>
								<textarea name="text" class="form-control form-control-lg" id="formInputMessage" rows="5" placeholder="<?=$arrAllData['feedbackForm']['pl_message']?>" required></textarea>
								<div class="valid-feedback"><?=$arrAllData['feedbackForm']['Ok']?></div>
								<div class="invalid-feedback"><?=$arrAllData['feedbackForm']['pl_message']?></div>
							</div>
							<div>
								<button type="submit" name="inner" value="form" class="btn btn-primary btn-lg"><?=$arrAllData['feedbackForm']['Send']?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
<!-- /contact-me -->
<!-- footer -->
		<div class="footer">
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
								<li><i class="fa fa-angle-right" aria-hidden="true"></i><a class="to-id" href="<?=$arrAllData['Navbar'][$i][1]?>"><?=$arrAllData['Navbar'][$i][0]?></a></li>
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
			$(".nav-link, .to-id").on('click', function(event) {

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

			

			// меняем высоту блока отзывов в зависимости от размера максимального большого отзыва
			// если страница загрузилась
			document.addEventListener('DOMContentLoaded', myCheckMaxHeightReview);
			// если размеры окна поменялись, выполнить
			window.addEventListener('resize', function(){
				// сбрасываем предыдущую высоту
				var myСarouselHeight = document.querySelectorAll('.carousel-max-height');
				for (let i = 0; i < myСarouselHeight.length; i++) {
					myСarouselHeight[i].removeAttribute('style');
				}
				myCheckMaxHeightReview();
			});
			// получаем максимальную высоту из всех отзывов в блоке reviews
			function myCheckMaxHeightReview() {
				// получаем элементы блоков с отзывами
				var myReviews = document.getElementsByClassName('carousel-item');
				// перебераем и находим самый высокий
				var myMaxHeightReview = 0;
				for (let i = 0; i < myReviews.length; i++) {
					// active есть только у первого элемента, меряем
					if (i == 0) {
						myReviews[i].classList.add('active');
						myMaxHeightReview = myReviews[i].clientHeight;
						continue;
					}
					// активируем что бы помереть высоту
					myReviews[i].classList.add('active');
					// если предыдущая высота меньше, то заносим новое максимальное значение
					if (myMaxHeightReview < myReviews[i].clientHeight) myMaxHeightReview = myReviews[i].clientHeight;
					// убираем active, оставляем только для первого элемента
					myReviews[i].classList.remove('active');
				}

				// применяем максимальный размер ко всем слайдам
				var myСarouselHeight = document.querySelectorAll('.carousel-max-height');
				for (let i = 0; i < myСarouselHeight.length; i++) {
					myСarouselHeight[i].style.height = myMaxHeightReview + 'px';
				}
			}
    </script>
    <?=$arrAllData['addScripts']['description']?>
  </body>
</html>
