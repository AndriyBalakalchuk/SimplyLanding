<?php
# Общие настройки
$config							= array();
$config['sitelink']				= ($_SERVER['HTTPS']==""?'http://':'https://').$_SERVER['HTTP_HOST'].'/'.'#Subfolder'; # URL сайта, со слэшем на конце
$config['sitename']				= '[SL]'; # Заголовок сайта
$config['encoding']				= 'utf-8'; # Кодировка
$config['font-family']			= 'Open Sans'; # шрифт на сайте
$config['Version']              = 'v: 0.1';  #версия ПО
$config['SuperUser']            = '#SuperUser';  #главный админ(доп возможности)
$config['WaitingGIF']           = $config['sitelink'].'/admin/images/load.svg';  #анимация для ожиданий
$config['Logo']                 = $config['sitelink'].'/admin/images/SimplyLanding-logo.png';  #лого для сайта
$config['Favicon']              = $config['sitelink'].'/admin/images/favicon-32x32.png';  #фавикон для сайта
$config['Copyright']            = 'Created by <a target="_blank" href="http://bvstud.io/">BVStudio</a> in 2020.';  #мелкий текст внизу интерфейса

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
$navbarDef['Home']              = array('Головна','#hello',$config['sitelink'].'index.php#hello');
$navbarDef_en['Home']           = array('Home','#hello',$config['sitelink'].'index.php#hello');
$navbarDef['About Me']          = array('Про мене','#about-me',$config['sitelink'].'index.php#about-me');
$navbarDef_en['About Me']       = array('About Me','#about-me',$config['sitelink'].'index.php#about-me');
$navbarDef['My Skills']         = array('Навички','#my-skills',$config['sitelink'].'index.php#my-skills');
$navbarDef_en['My Skills']      = array('My Skills','#my-skills',$config['sitelink'].'index.php#my-skills');
$navbarDef['Services']          = array('Послуги','#my-services',$config['sitelink'].'index.php#my-services');
$navbarDef_en['Services']       = array('Services','#my-services',$config['sitelink'].'index.php#my-services');
$navbarDef['Portfolio']         = array('Портфоліо','#latest-works',$config['sitelink'].'index.php#latest-works');
$navbarDef_en['Portfolio']      = array('Portfolio','#latest-works',$config['sitelink'].'index.php#latest-works');
$navbarDef['Contact']           = array('Контакти','#contact-me',$config['sitelink'].'#contact-me');
$navbarDef_en['Contact']        = array('Contact','#contact-me',$config['sitelink'].'#contact-me');
$navbarDef['LanguageSwich']     = array('<span>UA</span> EN',$config['sitelink'].'admin/change_lang.php',$config['sitelink'].'admin/change_lang.php');
$navbarDef_en['LanguageSwich']  = array('UA <span>EN</span>',$config['sitelink'].'admin/change_lang.php',$config['sitelink'].'admin/change_lang.php');


# Переводы формы обратной связи
$feedbackDef                        = array();
$feedbackDef['form']                = array();
$feedbackDef['form_en']             = array();
$feedbackDef['form']['name']        = 'Ім`я';
$feedbackDef['form_en']['name']     = 'Name';
$feedbackDef['form']['pl_name']     = 'Ваше ім`я';
$feedbackDef['form_en']['pl_name']  = 'Type your name';
$feedbackDef['form']['phone']       = 'Номер телефону';
$feedbackDef['form_en']['phone']    = 'Phone number';
$feedbackDef['form']['pl_phone']    = 'Внесіть Ваш номер телефону';
$feedbackDef['form_en']['pl_phone'] = 'Type your phone number';
$feedbackDef['form']['mail']        = 'E-mail адреса';
$feedbackDef['form_en']['mail']     = 'E-mail';
$feedbackDef['form']['pl_mail']     = 'Внесіть Вашу E-mail адресу';
$feedbackDef['form_en']['pl_mail']  = 'Type your e-mail address';
$feedbackDef['form']['message']     = 'Ваше повідомлення';
$feedbackDef['form_en']['message']  = 'Your message';
$feedbackDef['form']['pl_message']  = 'Внесіть Ваше повідомлення';
$feedbackDef['form_en']['pl_message']= 'Type your message here';
$feedbackDef['form']['Send']        = 'Надіслати';
$feedbackDef['form_en']['Send']     = 'Send message';
$feedbackDef['form']['Ok']          = 'Ок';
$feedbackDef['form_en']['Ok']       = 'Looks good!';

# Переводы для футера
$footerDef                          = array();
$footerDef['lang']                  = array();
$footerDef['lang_en']               = array();
$footerDef['lang']['follow']        = 'Підписатися';
$footerDef['lang_en']['follow']     = 'Follow me';
$footerDef['lang']['navigation']    = 'Навігація';
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

