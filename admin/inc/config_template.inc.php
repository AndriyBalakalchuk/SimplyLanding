<?php
# Общие настройки
$config							= array();
$config['sitelink']				= 'http://'.$_SERVER['HTTP_HOST'].'/'.'#Subfolder'; # URL сайта, со слэшем на конце
$config['sitename']				= '[SL]'; # Заголовок сайта
$config['encoding']				= 'utf-8'; # Кодировка
$config['font-family']			= 'Open Sans'; # шрифт на сайте
$config['Version']              = 'v: 0.1';  #версия ПО
$config['SuperUser']            = '#SuperUser';  #главный админ(доп возможности)
$config['WaitingGIF']           = $config['sitelink'].'/admin/images/SimplyLanding-ICO.png';  #анимация для ожиданий
$config['Logo']                 = $config['sitelink'].'/admin/images/SimplyLanding-logo.png';  #лого для сайта
$config['Favicon']              = $config['sitelink'].'/admin/images/favicon-32x32.png';  #фавикон для сайта
$config['Copyright']            = 'Created by BVStudio in 2020.';  #мелкий текст внизу интерфейса

# Настройки меню навигации
$menu                           = array();
$menu['MyData']	       	    = 'Edit Profile';
$menu['moders']	           	= 'Moderators';
$menu['About']	            = 'About';
$menu['Slider']	      	    = 'Header';
$menu['Skills']	         	= 'Skills';
$menu['Portfolio']	      	= 'Portfolio';
$menu['Feedback']	      	= 'Feedback';
$menu['Partners']	      	= 'Partners';
$menu['Contacts']	      	= 'Contacts';
$menu['Theme']	            = 'Theme settings';

# Настройки почты - если требуется отправки писем
$email_config                   = array();
$email_config['Host']           = '#EmailHost'; // SMTP сервера вашей почты
$email_config['Username']       = '#EmailName'; // Логин на почте
$email_config['Password']       = '#EmailPass'; // Пароль на почте
$email_config['SMTPSecure']     = '#EmailEncript';
$email_config['Port']           = #EmailPort;

# Настройки доступа в базу данных MySQL
$config['connetion']	        = '#Connection';
$config['user']		            = '#User';
$config['password']	            = '#Password';
