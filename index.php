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

  //получаем все переменные - детали ----→ Readme
  $arrAllData = getVariables(basename(__FILE__),$strLanguage);
  
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title><?=$config['sitename']?> — <?=$strPageTitle?></title>
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
    <link rel="stylesheet" href="<?=$config['sitelink']?>main.css?v=09-05-20V1">
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
					<a class="nav-item nav-link active" href="#hello">Home</a>
					<a class="nav-item nav-link" href="#about-me">About Me</a>
					<a class="nav-item nav-link" href="#my-skills">My Skills</a>
					<a class="nav-item nav-link" href="#my-services">Services</a>
					<a class="nav-item nav-link" href="#latest-works">Portfolio</a>
					<a class="nav-item nav-link" href="#">News</a>
					<a class="nav-item nav-link" href="#contact-me">Contact</a>
					<a class="nav-item nav-link" href="#"><span>RU</span></a>
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
						<h1>I’m Andriy</h1>
						<p>Web developer. Sites, applications and G Suite add-ons it's my specialization.</p>
						<a class="btn btn-primary btn-lg to-id" href="#contact-me" role="button">Contact me</a>
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
						<h1>About Me</h1>
						<hr class="hr-for-h1">
					</div>
				</div>
				<div class="row">
					<div class="col-md-5 text-center">
						<img src="img/ad78d195987dbc704337530370b214d5.png" class="img-fluid" alt="">
					</div>
					<div class="col-md-7 d-flex">
						<div class="align-self-center">
							<p>As you read before — I am a web developer. My skills were accumulated by the years of practice and learning.</p>
							<p>I can help you with: sites with beautiful view in any device (landings, corporate sites, blogs, sites with admin panel or without, etc), web applications for automating processes (digitalization and manual work removing), G Suite applications and add-ons. I work in a team with professional print and web designers, that's why we can start work from your idea point.</p>
							<p>Just scroll this page and take a look at my portfolio. Don't hesitate to contact me if you have any further questions.</p>
							<p>Thanks for your time spent on reading this text :)</p>
							<a class="btn btn-primary btn-lg to-id" href="#latest-works" role="button">My portfolio</a>
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
						<h1>My Skills</h1>
						<hr class="hr-for-h1">
						<p>Below you can look at some of my skills levels, it's situation when I rate myself and I am recommend you scroll little down and look at my works.</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="my-skills-description"><span>HTML & CSS</span><span>95%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="my-skills-description"><span>Unyson</span><span>95%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="my-skills-description"><span>JavaScript</span><span>85%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="my-skills-description"><span>Bootstrap</span><span>95%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="my-skills-description"><span>PHP</span><span>95%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="my-skills-description"><span>Photoshop / Illustrator / InDesign</span><span>85%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="my-skills-description"><span>Google Script</span><span>95%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="my-skills-description"><span>WordPress</span><span>95%</span></div>
						<div class="progress">
							<div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
<!-- /my-skills -->
<!-- my-services -->
		<div class="my-services" id="my-services">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<h1>My Services</h1>
						<hr class="hr-for-h1">
						<p>Here are a few categories which will sort my portfolio for your comfort.</p>
					</div>
				</div>
				<div class="row row-cols-1 row-cols-md-2">
					<div class="col mb-4">
						<div class="card h-100">
							<img src="img/service-1.jpg" class="card-img-top" alt="...">
							<div class="card-img-overlay">
								<h2 class="card-title">Sites&WordPress</h2>
								<p class="card-text">Web projects for WordPress CMS and other web sites projects.</p>
							</div>
						</div>
					</div>
					<div class="col mb-4">
						<div class="card h-100">
							<img src="img/service-2.jpg" class="card-img-top" alt="...">
							<div class="card-img-overlay">
								<h2 class="card-title">Web Applications</h2>
								<p class="card-text">Websites which developed for corporate internal usage to automate teamwork processes and better effectivity.</p>
							</div>
						</div>
					</div>
					<div class="col mb-4">
						<div class="card h-100">
							<img src="img/service-3.jpg" class="card-img-top" alt="...">
							<div class="card-img-overlay">
								<h2 class="card-title">G Suite</h2>
								<p class="card-text">Web applications and addons developed on the google script language for automating processes or creating new tools.</p>
							</div>
						</div>
					</div>
					<div class="col mb-4">
						<div class="card h-100">
							<img src="img/service-4.jpg" class="card-img-top" alt="...">
							<div class="card-img-overlay">
								<h2 class="card-title">Print Automation</h2>
								<p class="card-text">Scripting for Adobe Photoshop, Illustrator, PDF, and other tools for automatization manual work process or giving new possibilities.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
<!-- /my-services -->
<!-- latest-works -->
		<div class="latest-works" id="latest-works">
			<div class="container">
				<div class="row">
					<div class="col-md-6 offset-md-3">
						<h1>Latest Works</h1>
						<hr class="hr-for-h1">
						<p>Take a look at my portfolio and don't hesitate to contact me if you have any further questions.</p>
					</div>
				</div>
				<div class="row row-cols-1 row-cols-md-3">
					<div class="col mb-4">
					<div class="card h-100">
						<img src="img/portfolio-1.jpg" class="card-img-top" alt="...">
						<div class="card-body">
							<h5 class="card-title">Variable Email</h5>
							<p class="card-text">Variable Email - is tool which can help you to work with personalized email sendings, also it can be helpful at automated personalized email notification (using google triggers), it's easy to setup, and you don't need js knowledge to use it. Just try it!</p>
						</div>
					</div>
					</div>
					<div class="col mb-4">
					<div class="card h-100">
						<img src="img/portfolio-2.jpg" class="card-img-top" alt="...">
						<div class="card-body">
							<h5 class="card-title">Light landing-site with CMS </h5>
							<p class="card-text">Landing page easy to install and run at your hosting. Inbuilt CMS is very light and you don't need any specific knowledge to create your beautiful, quick and easy web site with double language. This site type was created for a personal web page (as human presentation), but it can be used as a product presentation site or company presentation site too.</p>
						</div>
					</div>
					</div>
					<div class="col mb-4">
					<div class="card h-100">
						<img src="img/portfolio-3.jpg" class="card-img-top" alt="...">
						<div class="card-body">
							<h5 class="card-title">Price-tags generator </h5>
							<p class="card-text">Web application called to remove manual price-tag creation for middle and big size of retail businesses. Generates ready to print pdf files with price tags in any format and size which was approved on the store.</p>
						</div>
					</div>
					</div>
				</div>
			</div>
		</div>
<!-- /latest-works -->
<!-- reviews -->
		<div class="reviews">
			<div class="container">
				<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
					<ol class="carousel-indicators">
					<li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
					<li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
					<li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
					</ol>
					<div class="carousel-inner">
						<div class="carousel-item active">
							<div class="d-flex carousel-max-height">
								<div class="align-self-center">
									<div class="col-md-8 offset-md-2 reviews-text">
										<h3>Lorem ipsum dolor first</h3>
										<p>
											"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lacus sed neque adipiscing nisl ornare euismod sed iaculis neque. Elementum et pulvinar urna sit lacus praesent. Posuere platea non, convallis dolor mi, interdum imperdiet. Neque quis erat commodo, pellentesque diam nulla sed et convallis dolor mi."
										</p>
										<p>- Upwork Client</p>
									</div>
									<div class="reviews-stars">
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star-half-o" aria-hidden="true"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="carousel-item">
							<div class="d-flex carousel-max-height">
								<div class="align-self-center">
									<div class="col-md-8 offset-md-2 reviews-text">
										<h3>Lorem ipsum dolor second</h3>
										<p>
											"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lacus sed neque adipiscing nisl ornare euismod sed iaculis neque."
										</p>
										<p>- Upwork Client</p>
									</div>
									<div class="reviews-stars">
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star-half-o" aria-hidden="true"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="carousel-item">
							<div class="d-flex carousel-max-height">
								<div class="align-self-center">
									<div class="col-md-8 offset-md-2 reviews-text">
										<h3>Lorem ipsum dolor third</h3>
										<p>
											"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lacus sed neque adipiscing nisl ornare euismod sed iaculis neque. Elementum et pulvinar urna sit lacus praesent. Posuere platea non, convallis dolor mi, interdum imperdiet. Neque quis erat commodo, pellentesque diam nulla sed et convallis dolor mi. Elementum et pulvinar urna sit lacus praesent. Posuere platea non, convallis dolor mi, interdum imperdiet. Neque quis erat commodo, pellentesque diam nulla sed et convallis dolor mi."
										</p>
										<p>- Upwork Client</p>
									</div>
									<div class="reviews-stars">
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
										<i class="fa fa-star" aria-hidden="true"></i>
									</div>
								</div>
							</div>
						</div>
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
						<h1>Contact Me</h1>
						<hr class="hr-for-h1">
					</div>
				</div>
				<div class="row">
					<div class="col-md-4">
						<h5>Contact info</h5>
						<ul class="contact-info">
							<li><i class="fa fa-phone" aria-hidden="true"></i><a href="tel:+380665001716">+38 (066) 500-17-16</a></li>
							<li><i class="fa fa-envelope" aria-hidden="true"></i><a href="mailto:andriy@balakalchuk.com">andriy@balakalchuk.com</a></li>
							<li><i class="fa fa-map-marker" aria-hidden="true"></i>Kiev, Ukraine</li>
						</ul>
						<h5>Follow me</h5>
						<ul class="follow-me">
							<li><a href="#"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-youtube-square" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
							<li><a href="#"><i class="fa fa-upwork" aria-hidden="false"></i></a></li>
						</ul>
					</div>
					<div class="col-md-8">
						<hr style="display: none;">
						<form class="needs-validation" novalidate>
							<div class="form-group">
								<label for="formInputName">Name</label>
								<input type="text" class="form-control form-control-lg" id="formInputName" placeholder="Type your name" required>
								<div class="valid-feedback">Looks good!</div>
								<div class="invalid-feedback">Please type your name.</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-6">
									<label for="formInputPhone">Phone number</label>
									<input type="text" class="form-control form-control-lg" id="formInputPhone" placeholder="Type your phone number" pattern="[\s-+0-9]*" required>
									<div class="valid-feedback">Looks good!</div>
									<div class="invalid-feedback">Please type your phone number.</div>
								</div>
								<div class="form-group col-md-6">
									<label for="formInputEmail">E-mail</label>
									<input type="text" class="form-control form-control-lg" id="formInputEmail" placeholder="Type your e-mail address" pattern="([-_\.\w]+@[-_\.a-zA-Z_]+?\.[a-zA-Z]{2,6})" required>
									<div class="valid-feedback">Looks good!</div>
									<div class="invalid-feedback">Please type your e-mail address.</div>
								</div>
							</div>
							<div class="form-group">
								<label for="formInputMessage">Your message</label>
								<textarea class="form-control form-control-lg" id="formInputMessage" rows="5" placeholder="Type your message here" required></textarea>
								<div class="valid-feedback">Looks good!</div>
								<div class="invalid-feedback">Please type your message here.</div>
							</div>
							<div>
								<button type="submit" class="btn btn-primary btn-lg">Send message</button>
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
						<h4>Contact info</h4>
						<hr>
						<ul>
							<li><i class="fa fa-phone" aria-hidden="true"></i><a href="tel:+380665001716">+38 (066) 500-17-16</a></li>
							<li><i class="fa fa-envelope" aria-hidden="true"></i><a href="mailto:andriy@balakalchuk.com">andriy@balakalchuk.com</a></li>
							<li><i class="fa fa-map-marker" aria-hidden="true"></i>Kiev, Ukraine</li>
						</ul>
					</div>
					<div class="col-md">
						<h4>Navigation</h4>
						<hr>
						<ul>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">Home</a></li>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">About Me</a></li>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">My Skills</a></li>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">Services</a></li>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">Portfolio</a></li>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">News</a></li>
							<li><i class="fa fa-angle-right" aria-hidden="true"></i><a href="#">Contact</a></li>
						</ul>
					</div>
					<div class="col-md">
						<h4>Follow me</h4>
						<hr>
						<a href="#"><i class="fa fa-facebook-square" aria-hidden="true"></i></a>&nbsp;
						<a href="#"><i class="fa fa-youtube-square" aria-hidden="true"></i></a>&nbsp;
						<a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>&nbsp;
						<a href="#"><i class="fa fa-upwork" aria-hidden="false"></i></a>&nbsp;
						<br>
						<form class="needs-validation" novalidate>
							<label for="E-Mail">Get latest updates and offers.</label>
							<div class="input-group mb-3">
								<input id="E-Mail" type="text" class="form-control" placeholder="E-Mail" pattern="([-_\.\w]+@[-_\.a-zA-Z_]+?\.[a-zA-Z]{2,6})" aria-describedby="button-addon2" required>
								<div class="input-group-append">
									<button class="btn btn-primary" style="border-bottom-right-radius:.25rem;border-top-right-radius:.25rem;" type="submit" id="button-addon2">Send</button>
								</div>
								<div class="valid-feedback">Looks good!</div>
								<div class="invalid-feedback">The email is not a valid email.</div>
							</div>
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-md">
						<h6>Tags</h6>
						<p>
							Skills: HTML, CSS, Bootstrap, Google Spreadsheets, Google Script, Google API<br>
							Category: g suite, Web Applications
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
					<div class="col-md">Copyright © 2020 Andriy Balakalchuk</div>
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
  </body>
</html>
