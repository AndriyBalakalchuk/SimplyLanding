<?php
# Общие настройки
$config							= array();
$config['sitelink']				= 'http://'.$_SERVER['HTTP_HOST'].'/'.'#Subfolder'; # URL сайта, со слэшем на конце
$config['sitename']				= '[SL]'; # Заголовок сайта
$config['encoding']				= 'utf-8'; # Кодировка
$config['font-family']			= 'Open Sans'; # шрифт на сайте
$config['Version']              = 'v: 0.1';  #версия ПО
$config['SuperUser']            = '#SuperUser';  #главный админ(доп возможности)
$config['WaitingGIF']           = $config['sitelink'].'/admin/images/load.svg';  #анимация для ожиданий
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
$menu['Categories']	      	= 'Categories'; 
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


//---настройки и переводы для темы оформления
# Настройки навигационной панели
$navbarDef                      = array();
$navbarDef_en                   = array();
$navbarDef['Home']              = array('Главная','#hello');
$navbarDef_en['Home']           = array('Home','#hello');
$navbarDef['About Me']          = array('Обо мне','#about-me');
$navbarDef_en['About Me']       = array('About Me','#about-me');
$navbarDef['My Skills']         = array('Навыки','#my-skills');
$navbarDef_en['My Skills']      = array('My Skills','#my-skills');
$navbarDef['Services']          = array('Услуги','#my-services');
$navbarDef_en['Services']       = array('Services','#my-services');
$navbarDef['Portfolio']         = array('Работы','#latest-works');
$navbarDef_en['Portfolio']      = array('Portfolio','#latest-works');
$navbarDef['Contact']           = array('Контакты','#contact-me');
$navbarDef_en['Contact']        = array('Contact','#contact-me');
$navbarDef['LanguageSwich']     = array('<span>RU</span>',$config['sitelink'].'admin/change_lang.php');
$navbarDef_en['LanguageSwich']  = array('<span>EN</span>',$config['sitelink'].'admin/change_lang.php');

# Переводы формы обратной связи
$feedbackDef                        = array();
$feedbackDef['form']                = array();
$feedbackDef['form_en']             = array();
$feedbackDef['form']['name']        = 'Имя';
$feedbackDef['form_en']['name']     = 'Name';
$feedbackDef['form']['pl_name']     = 'Ваше имя';
$feedbackDef['form_en']['pl_name']  = 'Type your name';
$feedbackDef['form']['phone']       = 'Номер телефона';
$feedbackDef['form_en']['phone']    = 'Phone number';
$feedbackDef['form']['pl_phone']    = 'Внесите Ваш номер телефона';
$feedbackDef['form_en']['pl_phone'] = 'Type your phone number';
$feedbackDef['form']['mail']        = 'E-mail адрес';
$feedbackDef['form_en']['mail']     = 'E-mail';
$feedbackDef['form']['pl_mail']     = 'Внесите Ваш E-mail адрес';
$feedbackDef['form_en']['pl_mail']  = 'Type your e-mail address';
$feedbackDef['form']['message']     = 'Ваше сообщение';
$feedbackDef['form_en']['message']  = 'Your message';
$feedbackDef['form']['pl_message']  = 'Внесите Ваше сообщение';
$feedbackDef['form_en']['pl_message']= 'Type your message here';
$feedbackDef['form']['Send']        = 'Отправить сообщение';
$feedbackDef['form_en']['Send']     = 'Send message';
$feedbackDef['form']['Ok']          = 'Ок';
$feedbackDef['form_en']['Ok']       = 'Looks good!';

# Переводы для футера
$footerDef                          = array();
$footerDef['lang']                  = array();
$footerDef['lang_en']               = array();
$footerDef['lang']['follow']        = 'Подписаться';
$footerDef['lang_en']['follow']     = 'Follow me';
$footerDef['lang']['navigation']    = 'Навигация';
$footerDef['lang_en']['navigation'] = 'Navigation';
//форма подписки в футере-- закомментировать что бы убрать из отображения 
/*
$footerDef['lang']['followHTML']    = '<form method="post" action="admin/send.php" class="needs-validation" novalidate>
                                            <label for="E-Mail">Подписатся на новости</label>
                                            <div class="input-group mb-3">
                                                <input id="E-Mail" name="mail" type="text" class="form-control" placeholder="E-Mail" pattern="([-_\.\w]+@[-_\.a-zA-Z_]+?\.[a-zA-Z]{2,6})" aria-describedby="button-addon2" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" style="border-bottom-right-radius:.25rem;border-top-right-radius:.25rem;" name="inner" value="rss" type="submit" id="button-addon2">Отправить</button>
                                                </div>
                                                <div class="valid-feedback">Ок!</div>
                                                <div class="invalid-feedback">Введен неверный адрес электронной почты</div>
                                            </div>
                                        </form>';
$footerDef['lang_en']['followHTML'] = '<form method="post" action="admin/send.php" class="needs-validation" novalidate>
                                            <label for="E-Mail">Get latest updates and offers.</label>
                                            <div class="input-group mb-3">
                                                <input id="E-Mail" name="mail" type="text" class="form-control" placeholder="E-Mail" pattern="([-_\.\w]+@[-_\.a-zA-Z_]+?\.[a-zA-Z]{2,6})" aria-describedby="button-addon2" required>
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" style="border-bottom-right-radius:.25rem;border-top-right-radius:.25rem;" name="inner" value="rss" type="submit" id="button-addon2">Send</button>
                                                </div>
                                                <div class="valid-feedback">Looks good!</div>
                                                <div class="invalid-feedback">The email is not a valid email.</div>
                                            </div>
                                        </form>';
*/

