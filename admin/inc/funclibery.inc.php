<?php
//создаем конфиг файл с шаблона
function crConfigFile($Subfolder, $SuperUser, $Connection, $User, $Password, $folderConf, $EMHost, $EmailName, $EmailPass, $EmailEncript, $EmailPort){
  //если нет файла темплейт конфиг пхп, то значит дирректория не верна, выбиваем ошибку
  if(!file_exists($folderConf.'config_template.inc.php')){return false;}
  if(file_exists($folderConf.'config.inc.php')){return false;}
  //файл есть считываем его в переменную
  try{ //пробуем считать файл
      $strFileContent = file_get_contents($folderConf.'config_template.inc.php');
      if(!$strFileContent){return false;}
      //заменяем в переменной данные
      if($Subfolder != ''){
        $strFileContent = preg_replace("/#Subfolder/", "$Subfolder/", $strFileContent);
      }else{
        $strFileContent = preg_replace("/#Subfolder/", '', $strFileContent);
      }

      $strFileContent = preg_replace("/#SuperUser/", $SuperUser, $strFileContent);
      $strFileContent = preg_replace("/#Connection/", $Connection, $strFileContent);
      $strFileContent = preg_replace("/#User/", $User, $strFileContent);
      $strFileContent = preg_replace("/#Password/", $Password, $strFileContent);

      //если хоть что-то в почтовых данных пустое то не вносим ничего
      if($EMHost!='' and $EmailName!='' and $EmailPass!='' and $EmailPort!=''){
        $strFileContent = preg_replace("/#EmailHost/", $EMHost, $strFileContent);
        $strFileContent = preg_replace("/#EmailName/", $EmailName, $strFileContent);
        $strFileContent = preg_replace("/#EmailPass/", $EmailPass, $strFileContent);
        $strFileContent = preg_replace("/#EmailEncript/", $EmailEncript, $strFileContent);
        $strFileContent = preg_replace("/#EmailPort/", $EmailPort*1, $strFileContent);
      }else{
        $strFileContent = preg_replace("/#EmailHost/", '', $strFileContent);
        $strFileContent = preg_replace("/#EmailName/", '', $strFileContent);
        $strFileContent = preg_replace("/#EmailPass/", '', $strFileContent);
        $strFileContent = preg_replace("/#EmailEncript/", '', $strFileContent);
        $strFileContent = preg_replace("/#EmailPort/", 0, $strFileContent);
      }

      //создаем файл и пишем в его переменную = файл не создался - ошибка
      //открываем файл, если файл не существует,
      //делается попытка создать его
      $objNewFile = fopen($folderConf.'config.inc.php', "w");

      // записываем в файл текст
      fwrite($objNewFile, $strFileContent);

      // закрываем
      fclose($objNewFile);

      return true;
  }catch(Exception $e){return false;}
}


//проверяем присланный мейл адресс на корректность
function validEmail($strEmailToCheck){
  $regRegular = "/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/";

  if(preg_match($regRegular, $strEmailToCheck)) { //проверка корректности адреса почты
    return true;
  }else{
    return false;
  }
}

//проверяем адресную строку отправить (возможно много адресов через запятую)
function validAllEmails($strEmailsToCheck){
  if(stristr($strEmailsToCheck, ',') === false){//если запятых в строке нет
    if(!validEmail(trim($strEmailsToCheck))){
      return false;
    }
  }else{//если запятые в строке есть
    $arrEmails = explode(",",$strEmailsToCheck);
    foreach($arrEmails as $strEmail){
      if(!validEmail(trim($strEmail))){
        return false;
      }
    }
  }
  return true;
}

//получаем пользователей и возвращаем их в виде таблици на дивах
function getHTMLofUsers($intUserPermis){
  global $config;
  $strResult  = '';

  //получаем переменные из базы данных
  $arrUsersData = selectFromTable('sl_users', array('login','email','accesslevel','signature','edited'));


  //формируем строки используя полученные переменные
  foreach ($arrUsersData as $arrUserData) {
    //скрыть в списке себя и тех кто тебе не по силам
    if(($arrUserData['accesslevel'] > $intUserPermis or $_SESSION['new']['signature'] == $config['SuperUser']) and $arrUserData['signature'] != $_SESSION['new']['signature']){
      //скрыть неположенные кнопки
      if($arrUserData['signature'] == $_SESSION['new']['signature'] or $_SESSION['new']['signature'] != $config['SuperUser']){
        $strVisibl = 'hideMe';
      }else{
        $strVisibl = '';
      }
      // человекопонятный доступ
      if($arrUserData['accesslevel']==1){
        $arrUserData['accesslevel'] = 'Manager';
      }else{
        $arrUserData['accesslevel'] = 'Employee';
      }
      $strResult .= "<div class='row JustRowDiv'>
                      <div class='col-sm-2'>
                        ".$arrUserData['login']."
                      </div>
                      <div class='col-sm-2'>
                        ".$arrUserData['signature']."
                      </div>
                      <div class='col-sm-2'>
                        ".$arrUserData['email']."
                      </div>
                      <div class='col-sm-2'>
                        ".$arrUserData['accesslevel']."
                      </div>
                      <div class='col-sm-2'>
                        ".getNormalDate($arrUserData['edited'])."
                      </div>
                      <div class='col-sm-2'>
                        <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=moders&ChangeMod=Password&strUsSign={$arrUserData['signature']}' title='change password'><i class='fa fa-lock'></i></a>
                        <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=moders&ChangeMod=Email&strUsSign={$arrUserData['signature']}' title='change email'><i class='fa fa-at'></i></a>
                        <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=moders&ChangeMod=Name&strUsSign={$arrUserData['signature']}' title='change name'><i class='fa fa-address-book-o'></i></a>
                        <a class='btn btn-danger btn-sm btnModal $strVisibl' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=moders&ChangeMod=accesslevel&strUsSign={$arrUserData['signature']}' title='change accesslevel'><i class='fa fa-level-up'></i></a>
                        <a class='btn btn-danger btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=moders&ChangeMod=remove&strUsSign={$arrUserData['signature']}' title='delete user'><i class='fa fa-trash'></i></a>
                      </div>
                    </div>";
    }
  }

  if(!isset($arrUsersData[1])){//если нету строки в массиве кроме первой - значит есть только админ
    $strResult = 'Empty User list';
  }else{
    $strResult = "<!--шапка-->
                        <div class='row headerDiv'>
                          <div class='col-sm-2'>
                            User Name
                          </div>
                          <div class='col-sm-2'>
                            Login
                          </div>
                          <div class='col-sm-2'>
                            Email
                          </div>
                          <div class='col-sm-2'>
                            Accesslevel
                          </div>
                          <div class='col-sm-2'>
                            Last Edit
                          </div>
                          <div class='col-sm-2'>
                            Manage User
                          </div>
                        </div>
                        <!--шапка конец-->".$strResult;
  }

  return $strResult;
}

//делаем человекопонятную дату из таймстампа
function getNormalDate($intTimeStamp){
  $intTimeStamp = getCleaInt($intTimeStamp);
  if($intTimeStamp<1590000000){

  }else{
    return date("d.m.Y", $intTimeStamp);
  }
}

//делаем браузеропонятную дату из таймстампа
function getBrowserDate($intTimeStamp){
  return date("Y-m-d", $intTimeStamp);
}

//проверяет дату в формате yyyy-mm-dd
function checkDate_DD_MM_YYYY($strUserData, $boolCheckPast){
  if($strUserData == ''){return '';}
  //дата верна (внесена корректно)
  if (preg_match('/\d{4}-\d{2}-\d{2}/',  $strUserData)) {
    $arrData = explode('-',$strUserData);

    $intUserData = mktime(0, 0, 0, $arrData[1]  , $arrData[2], $arrData[0]);
    $intNowData  = time ();

    if($boolCheckPast){
      //нужно проверить что бы дата не была в прошлом
      if($intNowData-24*60*60 > $intUserData){
        //дата от пользователя находится в прошлом - ошибка
        return 'In Past';
      }else{
        //дата от пользователя - ОК
        return 'OK';
      }
    }else{
      //нужно вернуть таймстамп даты
      return $intUserData;
    }
  } else {
    //дата внесена не верно
    return 'Wrong Format';
  }
}

//проверяет строку гет запроса на дублирующиеся переменные, и чистит
function clearMyQuery($strDirtyQuery,$strVariableToRemove){

  $arrDirtyQuery = explode('&',$strDirtyQuery);
  $strClearQuery = '';
  $i=0;
  foreach($arrDirtyQuery as $strVariable){
    if(preg_match("/$strVariableToRemove=/",$strVariable)!=1){
      if($i==0){
        $strClearQuery = $strVariable;
      }else{
        $strClearQuery .= '&'.$strVariable;
      }
      $i++;
    }
  }

  return $strClearQuery;
}

//чистит строку от мусора и возвращает красивой
function getClarStr($String){
  return str_replace("\r\n","<br>", preg_replace("/(\r\n){3,}/","\r\n\r\n", preg_replace("/ +/"," ", trim(strip_tags($String)))));
}

//преобразует в натуральное число
function getCleaInt($Int){
  return abs((int)$Int);
}

//проверяет корректрость времени по маске и возвращает строку времени или ''
function getCleaTime($strTime){
  preg_match('/[0-2]?[0-9]:[0-6][0-9]/', $strTime, $arrResult);
  if(!isset($arrResult[0]) or $arrResult[0]==''){
    return '';
  }
  return $arrResult[0];
}

//чистит дробные переменные
function getCleaFloat($float){
  $name_ask = strpos($float,","); // поиск ,
  if(!empty($name_ask)){$float= str_replace(",", ".", ClarStr($float));}

  $name_ask_new = strpos($float,"."); // поиск .
  if(!empty($name_ask_new)){
    $floatArr=explode('.',$float);
    return getCleaInt($floatArr[0]).'.'.$floatArr[1];
  }else{
    return getCleaInt($float);
  }
}

//переводим время в число, что бы можно было математику применять
function getNumberFromTime($strHHmM){
  if(!empty(strpos($strHHmM,":"))){
    $arrTime = explode(':',$strHHmM);
    //переводим в секунды
    $intSeconds = $arrTime[0]*60*60 + $arrTime[1]*60;
    //переводим в дробь
    return floor(($intSeconds/60/60) * 100) / 100;
  }else{
    return '';
  }
}

//отправка пдф документа в письме
function send_pdf_to_user($strTo, $strSubj, $strMessage, $strPath, $arrFiles){
  global $email_config;

  // Файлы phpmailer
  require $strPath.'admin/lib/PHPMailer/PHPMailer.php';
  require $strPath.'admin/lib/PHPMailer/SMTP.php';
  require $strPath.'admin/lib/PHPMailer/Exception.php';

  // Переменные, которые отправляет пользователь
  // $name = $_POST['name'];
  // $email = $_POST['email'];
  // $text = $_POST['text'];
  // $file = $_FILES['myfile'];

  // Формирование самого письма
  $title = $strSubj;
  $body = "<h2>$strMessage</h2>";

  // Настройки PHPMailer
  $mail = new PHPMailer\PHPMailer\PHPMailer();
  try {
      $mail->isSMTP();
      $mail->CharSet = "UTF-8";
      $mail->SMTPAuth   = true;
      //$mail->SMTPDebug = 2;
      $mail->Debugoutput = function($str, $level) {$GLOBALS['status'][] = $str;};

      // Настройки почты
      $mail->Host       = $email_config['Host']; // SMTP сервера вашей почты
      $mail->Username   = $email_config['Username']; // Логин на почте
      $mail->Password   = $email_config['Password']; // Пароль на почте
      $mail->SMTPSecure = $email_config['SMTPSecure'];
      $mail->Port       = $email_config['Port'];
      $mail->setFrom($email_config['Username'], 'WJM Tool'); // Адрес самой почты и имя отправителя

      // Получатель письма
      $mail->addAddress($strTo);
      //$mail->addAddress('youremail@gmail.com'); // Ещё один, если нужен

      // Прикрипление файлов к письму
  $mail->addAttachment($strPath.'admin/pdfTMP/'.$arrFiles[0], $arrFiles[0]);
  $mail->addAttachment($strPath.'admin/pdfTMP/'.$arrFiles[1], $arrFiles[1]);

  // Отправка сообщения
  $mail->isHTML(true);
  $mail->Subject = $title;
  $mail->Body = $body;

  // Проверяем отравленность сообщения
  if ($mail->send()) {return true;}
  else {return false;}

  } catch (Exception $e) {
      return false;
  }
}

//перевести дробь во время
function timeFromFloat($float){
  $intSeconds = $float*60*60;
  return floor($float).':'.date('i',$intSeconds);
}

//суммируем время правильно
function getSunTime($strTime, $strTime2){
  if(empty(strpos($strTime,":"))){$strTime='0:00';}
  if(empty(strpos($strTime2,":"))){$strTime2='0:00';}

  $arrTime = explode(":",$strTime);
  $arrTime2 = explode(":",$strTime2);
  $arrTime[0] = getCleaInt($arrTime[0]);
  $arrTime[1] = getCleaInt($arrTime[1]);
  $arrTime2[0] = getCleaInt($arrTime2[0]);
  $arrTime2[1] = getCleaInt($arrTime2[1]);

  $intSumMinutes = $arrTime[1] + $arrTime2[1] - (floor(($arrTime[1] + $arrTime2[1])/60)*60);
  $intSumHours = $arrTime[0] + $arrTime2[0] + (floor(($arrTime[1] + $arrTime2[1])/60));

  if($intSumMinutes*1<10){$intSumMinutes = '0'.$intSumMinutes;}

  return $intSumHours.':'.$intSumMinutes;
}

//отнимаем время правильно
function getDifTime($strTime, $strTime2){
  if(empty(strpos($strTime,":"))){$strTime='0:00';}
  if(empty(strpos($strTime2,":"))){$strTime2='0:00';}

  $arrTime = explode(":",$strTime);
  $arrTime2 = explode(":",$strTime2);
  $arrTime[0] = getCleaInt($arrTime[0]);
  $arrTime[1] = getCleaInt($arrTime[1]);
  $arrTime2[0] = getCleaInt($arrTime2[0]);
  $arrTime2[1] = getCleaInt($arrTime2[1]);

  //если минут не хватает то отнимаем один час
  if($arrTime[1] < $arrTime2[1]){
    $arrTime[0] = $arrTime[0] - 1;
    $arrTime[1] = $arrTime[1] + 60;
    //если часов меньше чем нужно то добавляем 24
    if($arrTime[0]<0 or $arrTime[0]<$arrTime2[0]){
      $arrTime[0] = $arrTime[0]+24;
    }
  }


  $intDifMinutes = $arrTime[1] - $arrTime2[1];
  $intDifHours = $arrTime[0] - $arrTime2[0];

  if($intDifMinutes*1<10){$intDifMinutes = '0'.$intDifMinutes;}

  return $intDifHours.':'.$intDifMinutes;
}

//админ меню
function menu($Stranitsa){
  global $config, $menu;


  foreach ($menu as $link => $name){
    if($Stranitsa==$link){
    echo "<div class='myMenuListActiv'>$name</div>";
    }else{
    echo "<a class='myMenuList btnModal' href='{$config['sitelink']}admin/index.php?strPage=$link'>$name</a>"; 
    }
  }
}

function uploadImage($strFolder, $width=1980, $height=1080){ //заливает новые картинки
  global $config, $path, $_FILES;
 
  # Подключение редакторв изображений
  include_once $path . '/lib/SimpleImage/SimpleImage.class.php';

  if($_FILES['new_image']['error'] !=0){return false;} //ошибка загрузки

  $FileRandName = explode('.', $_FILES['new_image']["name"]);
  $FileRandName= time().rand(1,9).'.'.array_pop($FileRandName);  //имя для изображения
  
  //заливает картинку с подходящим размером   
  $image = new SimpleImage();
  $image->load($_FILES["new_image"]["tmp_name"]);
  $image->resize($width, $height);
  $image->save("images/$strFolder/".$FileRandName);
  //заливает миниатюру для просмотра в админке
  $image->load("images/$strFolder/".$FileRandName);
  $image->resize(200, 112);
  $image->save("images/$strFolder/mini/".$FileRandName);
    

  return $FileRandName;
}

//админ контент
function Content($Stranitsa, $intUserPermis){ 
  global $config, $menu;

  /*---------------------------------------------------------------------------------*/
  /*--Подбор контента по выбранному в меню пункту------------------------------------*/
  /*---------------------------------------------------------------------------------*/
  switch ($Stranitsa) { 
    /*---------------------------------------------------------------------------------*/
    /*--работа в раздете модераторы----------------------------------------------------*/
    /*---------------------------------------------------------------------------------*/
    case 'moders': 
      //получаем уровень доступа текущего пользователя
      echo "<div class='myShapka'>{$menu['moders']}</div>";
      echo getHTMLofUsers($intUserPermis); //вывод админов    

      if($intUserPermis==1){ //добавление юзера по дефолту и формы редактирования как возможность
        if($_GET['ChangeMod']=="Password"){//смена пароля
          echo "<div class='myShapka'>Change password for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
                <form action="" method="post" autocomplete="off">
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <label for="validationUserPass">NEW Password*</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-lock"></i></span>
                      </div>
                      <input type="password" class="form-control" id="validationUserPass" name="UserPass" aria-describedby="inputGroupPrepend1" required>
                    </div>
                  </div>
                </div>
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <label for="validationUserPass_rep">Repeat NEW Password*</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-lock"></i></span>
                      </div>
                      <input type="password" class="form-control" id="validationUserPass_rep" name="UserPass_rep" aria-describedby="inputGroupPrepend2" required>
                    </div>
                  </div>
                </div>
            
                <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
            
                  <!-- кнопки -->
                  <div class="container">
                    <div class="row" style = "color:white;" >
                      <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                        <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                      </div>
                      <div class="col-sm-3 col-lg-2">
                        <button name="strInnFromForm" value="ChanPassword" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                      </div>
                    </div>
                  </div>
                  <!-- кнопки конец-->
              </form>
              <!-- форма конец-->';
        }elseif($_GET['ChangeMod']=="Email"){//смена мейла 
          echo "<div class='myShapka'>Change email for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-4">
                  <label for="validationUserEmail">NEW Email*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-at"></i></span>
                    </div>
                    <input type="email" class="form-control" id="validationUserEmail" name="UserEmail" aria-describedby="inputGroupPrepend1" required>
                  </div>
                </div>
              </div>
          
              <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
          
                <!-- кнопки -->
                <div class="container">
                  <div class="row" style = "color:white;" >
                    <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                      <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                    </div>
                    <div class="col-sm-3 col-lg-2">
                      <button name="strInnFromForm" value="ChanEmail" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-at"></i> Save</button>
                    </div>
                  </div>
                </div>
                <!-- кнопки конец-->
            </form>
           <!-- форма конец-->';
        }elseif($_GET['ChangeMod']=="Name"){//смена имени 
          echo "<div class='myShapka'>Change name for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-4">
                  <label for="validationUserName">NEW Name*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-lock"></i></span>
                    </div>
                    <input type="text" class="form-control" id="validationUserName" name="UserName" aria-describedby="inputGroupPrepend1" required>
                  </div>
                </div>
              </div>
          
              <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
          
                <!-- кнопки -->
                <div class="container">
                  <div class="row" style = "color:white;" >
                    <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                      <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                    </div>
                    <div class="col-sm-3 col-lg-2">
                      <button name="strInnFromForm" value="ChanName" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-address-book-o"></i> Save</button>
                    </div>
                  </div>
                </div>
                <!-- кнопки конец-->
            </form>
          <!-- форма конец-->';
        }elseif($_GET['ChangeMod']=="accesslevel"){//смена уровня  
          echo "<div class='myShapka'>Change accesslevel for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
                <form action="" method="post" autocomplete="off">
                    <div class="form-row" style="padding-bottom: 15px;">
                          <div class="col-lg-4 offset-lg-4">
                            <label for="validationUserAccess">Choose access level*</label>
                          <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroupPrepend5"><i class="fa fa-level-up"></i></span>
                          </div>
                            <select class="form-control" id="validationUserAccess" name="UserAccess" aria-describedby="inputGroupPrepend5" required>
                              <option>Employee</option>
                              <option>Manager</option>
                            </select>
                          </div>
                        </div>
                      </div>
              
                      <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
              
                    <!-- кнопки -->
                    <div class="container">
                      <div class="row" style = "color:white;" >
                        <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                          <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                        </div>
                        <div class="col-sm-3 col-lg-2">
                          <button name="strInnFromForm" value="accesslevel" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-at"></i> Save</button>
                        </div>
                      </div>
                    </div>
                    <!-- кнопки конец-->
                </form>
                <!-- форма конец-->';
        }elseif($_GET['ChangeMod']=="remove"){//удаление юзера
          echo "<div class='myShapka'>Remove user - ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
                <form action="" method="post" autocomplete="off">
                      <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
              
                    <!-- кнопки -->
                    <div class="container">
                      <div class="row" style = "color:white;" >
                        <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                          <a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php"><i class="fa fa-reply"></i> Back</a>
                        </div>
                        <div class="col-sm-3 col-lg-2">
                          <button name="strInnFromForm" value="remove" id="" type="submit" class="btn btn-danger btn-sm "><i class="fa fa-trash"></i> Approve</button>
                        </div>
                      </div>
                    </div>
                    <!-- кнопки конец-->
                </form>
                <!-- форма конец-->';
        }else{   
            echo "<div class='myShapka'>Add moderator</div>";
            echo $strError.'
                    <!-- форма -->
                    <form action="" method="post" autocomplete="off">
                        <div class="form-row" style="padding-bottom: 15px;">
                          <div class="col-lg-4 offset-lg-4">
                            <label for="validationSuperUser">User Login*</label>
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-user-circle"></i></span>
                              </div>
                              <input type="text" class="form-control" id="validationSuperUser" name="SuperUser" placeholder="Inan44" aria-describedby="inputGroupPrepend" required>
                            </div>
                          </div>
                        </div>
                        <div class="form-row" style="padding-bottom: 15px;">
                          <div class="col-lg-4 offset-lg-4">
                            <label for="validationUserLogin">Name*</label>
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-user-o"></i></span>
                              </div>
                              <input type="text" class="form-control" id="validationUserLogin" name="UserLogin" placeholder="Ivan Ivanov" aria-describedby="inputGroupPrepend1" required>
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
                              <input type="email" class="form-control" id="validationUserEmail" name="UserEmail" placeholder="Ivan@ivanov.co.uk" aria-describedby="inputGroupPrepend2" required>
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
                        </div>';
                        //если добавляем суперюзер то даем возможность выбрать менеджер или работник
                        if($_SESSION['new']['signature']==$config['SuperUser']){
                          echo '<div class="form-row" style="padding-bottom: 15px;">
                                                <div class="col-lg-4 offset-lg-4">
                                                  <label for="validationUserAccess">Choose access level*</label>
                                                  <div class="input-group">
                                                    <div class="input-group-prepend">
                                                      <span class="input-group-text" id="inputGroupPrepend5"><i class="fa fa-level-up"></i></span>
                                                    </div>
                                                    <select class="form-control" id="validationUserAccess" name="UserAccess" aria-describedby="inputGroupPrepend5" required>
                                                      <option>Employee</option>
                                                      <option>Manager</option>
                                                    </select>
                                                  </div>
                                                </div>
                                              </div>';
                        }else{//если редактирует менеджер то всегда создаем работника
                          echo '<div class="form-row" style="padding-bottom: 15px;">
                                                <div class="col-lg-4 offset-lg-4">
                                                  <label for="validationUserAccess">Choose access level*</label>
                                                  <div class="input-group">
                                                    <div class="input-group-prepend">
                                                      <span class="input-group-text" id="inputGroupPrepend5"><i class="fa fa-level-up"></i></span>
                                                    </div>
                                                    <input type="text" class="form-control" id="validationUserAccess" value="Employee" name="UserAccess1" aria-describedby="inputGroupPrepend5" disabled>
                                                    <input type="hidden" class="form-control" id="validationUserAccess" value="Employee" name="UserAccess" aria-describedby="inputGroupPrepend5">
                                                  </div>
                                                </div>
                                              </div>';
                        }
                  echo '<!-- кнопки -->
                        <div class="container">
                          <div class="row" style = "color:white;" >
                            <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                              <a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php"><i class="fa fa-reply"></i> Back</a>
                            </div>
                            <div class="col-sm-3 col-lg-2">
                              <button name="strInnFromForm" value="addUser" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-address-book-o"></i> Add User</button>
                            </div>
                          </div>
                        </div>
                        <!-- кнопки конец-->
                        </form>
                        <!-- форма конец-->'; 
          }
      }else{
        echo "<div class='container-fluid' style='margin-top:5px; text-align:center;'>Viewing is disabled (for your permission level)</div>";
      }
    break;
    /*---------------------------------------------------------------------------------*/
    /*--работа в раздете личные данные-------------------------------------------------*/
    /*---------------------------------------------------------------------------------*/
    case 'MyData': 
      //получаем уровень доступа текущего пользователя
      echo "<div class='myShapka'>{$menu['MyData']}</div>";
      $arrMyData = selectFromTable('sl_users', array('login','email','accesslevel','edited'), true, 'signature', $_SESSION['new']['signature'])[0];
      
      //вывод сводного по себе
      echo "<!--шапка-->
            <div class='row headerDiv'>
              <div class='col-sm-3'>
                User Name
              </div>
              <div class='col-sm-3'>
                Login
              </div>
              <div class='col-sm-2'>
                Email
              </div>
              <div class='col-sm-2'>
                Last Edit
              </div>
              <div class='col-sm-2'>
                Manage User
              </div>
            </div>
            <!--шапка конец-->
            <div class='row JustRowDiv'>
              <div class='col-sm-3'>
                ".$arrMyData['login']."
              </div>
              <div class='col-sm-3'>
                ".$_SESSION['new']['signature']."
              </div>
              <div class='col-sm-2'>
                ".$arrMyData['email']."
              </div>
              <div class='col-sm-2'>
                ".getNormalDate($arrMyData['edited'])."
              </div>
              <div class='col-sm-2'>
                <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=MyData&ChangeMod=Password&strUsSign={$_SESSION['new']['signature']}' title='change password'><i class='fa fa-lock'></i></a>
                <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=MyData&ChangeMod=Email&strUsSign={$_SESSION['new']['signature']}' title='change email'><i class='fa fa-at'></i></a>
                <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."admin/index.php?strPage=MyData&ChangeMod=Name&strUsSign={$_SESSION['new']['signature']}' title='change name'><i class='fa fa-address-book-o'></i></a>
              </div>
            </div>";
        

      if($intUserPermis==1 or $intUserPermis==2){ //формы редактирования как возможность
        if($_GET['ChangeMod']=="Password"){//смена пароля
          echo "<div class='myShapka'>Change password for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
              <form action="" method="post" autocomplete="off">
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <label for="validationUserPass">NEW Password*</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-lock"></i></span>
                      </div>
                      <input type="password" class="form-control" id="validationUserPass" name="UserPass" aria-describedby="inputGroupPrepend1" required>
                    </div>
                  </div>
                </div>
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <label for="validationUserPass_rep">Repeat NEW Password*</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-lock"></i></span>
                      </div>
                      <input type="password" class="form-control" id="validationUserPass_rep" name="UserPass_rep" aria-describedby="inputGroupPrepend2" required>
                    </div>
                  </div>
                </div>
            
                <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
            
                  <!-- кнопки -->
                  <div class="container">
                    <div class="row" style = "color:white;" >
                      <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                        <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                      </div>
                      <div class="col-sm-3 col-lg-2">
                        <button name="strInnFromForm" value="ChanPassword" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                      </div>
                    </div>
                  </div>
                  <!-- кнопки конец-->
              </form>
              <!-- форма конец-->';
        }elseif($_GET['ChangeMod']=="Email"){//смена мейла 
          echo "<div class='myShapka'>Change email for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-4">
                  <label for="validationUserEmail">NEW Email*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-at"></i></span>
                    </div>
                    <input type="email" class="form-control" id="validationUserEmail" name="UserEmail" aria-describedby="inputGroupPrepend1" required>
                  </div>
                </div>
              </div>
          
              <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
          
                <!-- кнопки -->
                <div class="container">
                  <div class="row" style = "color:white;" >
                    <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                      <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                    </div>
                    <div class="col-sm-3 col-lg-2">
                      <button name="strInnFromForm" value="ChanEmail" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-at"></i> Save</button>
                    </div>
                  </div>
                </div>
                <!-- кнопки конец-->
            </form>
           <!-- форма конец-->';
        }elseif($_GET['ChangeMod']=="Name"){//смена имени 
          echo "<div class='myShapka'>Change name for ".$_GET['strUsSign']."</div>";
          echo '<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-4">
                  <label for="validationUserName">NEW Name*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-lock"></i></span>
                    </div>
                    <input type="text" class="form-control" id="validationUserName" name="UserName" aria-describedby="inputGroupPrepend1" required>
                  </div>
                </div>
              </div>
          
              <input type="hidden" value="'.$_GET['strUsSign'].'" class="form-control" id="validationUserSign" name="UserSign" aria-describedby="inputGroupPrepend4" required>
          
                <!-- кнопки -->
                <div class="container">
                  <div class="row" style = "color:white;" >
                    <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                      <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                    </div>
                    <div class="col-sm-3 col-lg-2">
                      <button name="strInnFromForm" value="ChanName" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-address-book-o"></i> Save</button>
                    </div>
                  </div>
                </div>
                <!-- кнопки конец-->
            </form>
          <!-- форма конец-->';
        }else{   
            echo "<div class='myShapka'>Choose option</div>";
            echo $strError;
            echo '<!-- кнопки -->
                  <div class="container">
                    <div class="row" style = "color:white;" >
                      <div class="col-sm-12 offset-sm-12 col-lg-12 offset-lg-12">
                        <a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php"><i class="fa fa-reply"></i> Back</a>
                      </div>
                    </div>
                  </div>
                  <!-- кнопки конец-->'; 
          }
      }else{
        echo "<div class='container-fluid' style='margin-top:5px; text-align:center;'>Viewing is disabled (for your permission level)</div>";
      }
    break;
    /*---------------------------------------------------------------------------------*/
    /*--работа в раздете изменить телефон----------------------------------------------*/
    /*---------------------------------------------------------------------------------*/
    case 'Telephone': 
      echo "<div class='myShapka'>{$menu['Telephone']}</div>";
      if(CheckData('sl_contacts', 'contact_for', 'Telephone')){ //tel. number есть
        $ArrayData = selectFromTable('sl_contacts', array('id', 'contact_for','contact','time','edit_by'), true, 'contact_for', 'Telephone')[0];
        $strSubmitValue = 'updIN';
        $strData=date("d.m.Y",$ArrayData['time']);
        $strEditorHTML = "<div style='text-align:center;'>Last editing $strData, user: {$ArrayData['edit_by']}</div>";
        $strContact = $ArrayData['contact'];
      }else{ //tel. number нету
        $strEditorHTML = '';
        $strContact = '';
        $strSubmitValue = 'addINTO';
      }   

      echo $strEditorHTML.'<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-4">
                  <label for="telephone">Contact tel. number*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-phone"></i></span>
                    </div>
                    <input type="text" class="form-control" id="telephone" name="Telephone" value="'.$strContact.'" aria-describedby="inputGroupPrepend1" required>
                  </div>
                </div>
              </div>
              <input type="hidden" value="Telephone" name="strContact_for" required>
              <!-- кнопки -->
              <div class="container">
                <div class="row" style = "color:white;" >
                  <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                    <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                  </div>
                  <div class="col-sm-3 col-lg-2">
                    <button name="strInnFromForm" value="'.$strSubmitValue.'" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                  </div>
                </div>
              </div>
              <!-- кнопки конец-->
            </form>
            <!-- форма конец-->';
    break;
    /*---------------------------------------------------------------------------------*/
    /*--работа в раздете изменить шапку------------------------------------------------*/
    /*---------------------------------------------------------------------------------*/
    case 'Slider': 
      if(!isset($_GET['delWithName'])){//если запроса на удаление картинки нет, то отображаем страницу
        echo "<div class='myShapka'>{$menu['Slider']}</div>";
        if(CheckData('sl_content', 'content_for', 'Slider')){ //данные для слайдера есть
          $ArrayData = selectFromTable('sl_content', array('content_for', 'text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), true, 'content_for', 'Slider')[0];
          $strSubmitValue = 'updIN';
          $strData=date("d.m.Y",$ArrayData['time']);
          $strEditorHTML = "<div style='text-align:center;'>Last editing $strData, user: {$ArrayData['edit_by']}</div>";
          $text_big=$ArrayData['text_big'];
          $text_small=$ArrayData['text_small'];            
          $text_big_en=$ArrayData['text_big_en'];
          $text_small_en=$ArrayData['text_small_en'];
        }else{ //данные для слайдера  нету
          $strSubmitValue = 'addINTO';
          $strEditorHTML = '';
          $text_big = '';
          $text_small = '';            
          $text_big_en = '';
          $text_small_en = '';
        }   


        echo $strEditorHTML.'<!-- форма -->
        <form action="" method="post" autocomplete="off">
          <div class="form-row" style="padding-bottom: 15px;">
            <div class="col-lg-4 offset-lg-4">
              <label for="header">Header Ru/En*</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                </div>
                <input type="text" class="form-control" id="header" name="header" value="'.$text_big.'" aria-describedby="inputGroupPrepend1" required>
              </div>
            </div>
          </div>
          <div class="form-row" style="padding-bottom: 15px;">
            <div class="col-lg-4 offset-lg-4">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                </div>
                <input type="text" class="form-control" id="header_en" name="header_en" value="'.$text_big_en.'" aria-describedby="inputGroupPrepend2" required>
              </div>
            </div>
          </div>
          <div class="form-row" style="padding-bottom: 15px;">
            <div class="col-lg-4 offset-lg-4">
              <label for="text_block">Text Block Ru/En*</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                </div>
                <textarea rows="4" cols="50" type="text" class="form-control" id="text_block" name="text_block" aria-describedby="inputGroupPrepend3" required>'.$text_small.'</textarea>
              </div>
            </div>
          </div>
          <div class="form-row" style="padding-bottom: 15px;">
            <div class="col-lg-4 offset-lg-4">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                </div>
                <textarea rows="4" cols="50" type="text" class="form-control" id="text_block_en" name="text_block_en" aria-describedby="inputGroupPrepend4" required>'.$text_small_en.'</textarea>
              </div>
            </div>
          </div>
          <input type="hidden" value="Slider" name="strContent_for" required>
          <!-- кнопки -->
          <div class="container">
            <div class="row" style = "color:white;" >
              <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
              </div>
              <div class="col-sm-3 col-lg-2">
                <button name="strInnFromForm" value="'.$strSubmitValue.'" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
              </div>
            </div>
          </div>
          <!-- кнопки конец-->
        </form>
        <!-- форма конец-->';

        echo "<div class='myShapka'>Images</div>";
        if(CheckData('sl_images', 'image_for', 'Slider')){ //фотки для слайдера есть
          $ArrayData = selectFromTable('sl_images', array('image_for', 'image_name', 'time','edit_by'), true, 'image_for', 'Slider');
          $htmlExistingImages = '<div class="row">';
          foreach ($ArrayData as $ArrayRow) {
            $strData=date("d.m.Y",$ArrayRow['time']);
            $htmlExistingImages .= "
              <div class='col-sm-2'>
                <div style='text-align:center;'>Added: $strData, user: {$ArrayRow['edit_by']}</div>
                <div class='col-xs-12'><img src='images/Slider/mini/{$ArrayRow['image_name']}' width='100%'></div>
                <div class='col-xs-12' style='text-align:center;'>
                  <a href='".$config['sitelink']."admin/index.php?strPage=Slider&delWithName={$ArrayRow['image_name']}' class='btn btn-danger btn-sm btnModal'><i class='fa fa-lock'></i> Remove</a>
                </div>
              </div>";
          }
          $htmlExistingImages .= '</div>';
        }else{ //фотки для слайдера  нету
          $htmlExistingImages = '';
        }   
      
        echo $htmlExistingImages.'<!-- форма -->
        <form action="" enctype="multipart/form-data" method="post" autocomplete="off">
          <div class="form-row" style="padding-bottom: 15px;">
            <div class="col-lg-4 offset-lg-4">
              <label for="new_image">Add image (570*600px)*</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-image"></i></span>
                </div>
                <input type="file" class="form-control" accept="image/jpeg,image/png,image/gif" id="new_image" name="new_image" aria-describedby="inputGroupPrepend1" required>
              </div>
            </div>
          </div>
          <input type="hidden" value="Slider" name="strImage_for" required>
          <!-- кнопки -->
          <div class="container">
            <div class="row" style = "color:white;" >
              <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
              </div>
              <div class="col-sm-3 col-lg-2">
                <button name="strInnFromForm" value="addINTO" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-image"></i> Add image</button>
              </div>
            </div>
          </div>
          <!-- кнопки конец-->
        </form>
        <!-- форма конец-->';
      }else{ //если пришел запрос на удаление картинки то отобразить переспрашивание
        $boolINDB = false;
        $boolINFiles = false;
        $boolINFilesMini = false;
        //проверяем есть ли такая картинка в базе
        if(CheckData('sl_images', 'image_name', $_GET['delWithName'])){$boolINDB = true;}
        //проверяем есть ли такая картинка в файлах
        if(file_exists($path.'images/'.$_GET['strPage'].'/'.$_GET['delWithName'])){$boolINFiles = true;}
        //проверяем есть ли такая миниатюра в файлах
        if(file_exists($path.'images/'.$_GET['strPage'].'/mini/'.$_GET['delWithName'])){$boolINFilesMini = true;}

        if($boolINDB or $boolINFiles or $boolINFilesMini){
          echo "<div class='myShapka'>Approve image removing</div>";
          
          echo '<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <input type="hidden" value="Slider" name="strImage_for" required>
              <input type="hidden" value="'.$_GET['delWithName'].'" name="image_name" required>
              <input type="hidden" value="'.$boolINDB.'" name="boolINDB" required>
              <input type="hidden" value="'.$boolINFiles.'" name="boolINFiles" required>
              <input type="hidden" value="'.$boolINFilesMini.'" name="boolINFilesMini" required>
              <!-- кнопки -->
              <div class="container">
                <a ><img src="images/Slider/mini/'.$_GET['delWithName'].'" width="250px"></a>
                <div class="row" style = "color:white;" >
                  <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                    <button name="strInnFromForm" value="RemoveIMG" id="" type="submit" class="btn btn-danger btn-sm "><i class="fa fa-lock"></i> Remove '.$_GET['delWithName'].'</button>
                  </div>
                  <div class="col-sm-3 col-lg-2">
                    <a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php?strPage=Slider"><i class="fa fa-reply"></i> Back</a>
                  </div>
                </div>
              </div>
              <!-- кнопки конец-->
            </form>
            <!-- форма конец-->';
        }else{
          echo "<div class='myShapka'>No data to removing</div>";
          echo '<a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php?strPage=Slider"><i class="fa fa-reply"></i> Back</a>';
        }
      }
    break;
    /*---------------------------------------------------------------------------------*/
    /*--работа в раздете изменить шапку------------------------------------------------*/
    /*---------------------------------------------------------------------------------*/
    case 'Skills': 
      if(!isset($_GET['delWithId'])){//если запроса на удаление скила нет, то отображаем страницу
        echo "<div class='myShapka'>{$menu['Skills']}</div>";
          switch($_GET['skill_for']){ //подбор контента для страницы скилы
            case 'hardskill':
              if(CheckData('sl_content', 'content_for', 'hardskill_header')){ //данные для заголовка хардскилов есть
                $ArrayData = selectFromTable('sl_content', array('content_for', 'text_big', 'text_big_en', 'time','edit_by'), true, 'content_for', 'hardskill_header')[0];
                $strSubmitValue = 'updIN';
                $strData=date("d.m.Y",$ArrayData['time']);
                $strEditorHTML = "<div style='text-align:center;'>Last editing $strData, user: {$ArrayData['edit_by']}</div>";
                $text_big=$ArrayData['text_big'];          
                $text_big_en=$ArrayData['text_big_en'];
              }else{ //данные для заголовка хардскилов нету
                $strSubmitValue = 'addINTO';
                $strEditorHTML = '';
                $text_big = '';         
                $text_big_en = '';
              }   

              echo "<button class='btn btn-primary btn-sm' href='{$config['sitelink']}admin/index.php?strPage=Skills&skill_for=hardskill' disabled>Hard skills</button> ";
              echo "<a class='btn btn-info btn-sm btnModal' href='{$config['sitelink']}admin/index.php?strPage=Skills&skill_for=softskill'>Soft skills</a> ";


              echo $strEditorHTML.'<!-- форма -->
              <form action="" method="post" autocomplete="off">
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <label for="header">Header Ru/En*</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                      </div>
                      <input type="text" class="form-control" id="header" name="header" value="'.$text_big.'" aria-describedby="inputGroupPrepend1" required>
                    </div>
                  </div>
                </div>
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                      </div>
                      <input type="text" class="form-control" id="header_en" name="header_en" value="'.$text_big_en.'" aria-describedby="inputGroupPrepend2" required>
                    </div>
                  </div>
                </div>
                <input type="hidden" value="" name="text_block" required>
                <input type="hidden" value="" name="text_block_en" required>
                <input type="hidden" value="hardskill_header" name="strContent_for" required>
                <!-- кнопки -->
                <div class="container">
                  <div class="row" style = "color:white;" >
                    <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                      <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                    </div>
                    <div class="col-sm-3 col-lg-2">
                      <button name="strInnFromForm" value="'.$strSubmitValue.'" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                    </div>
                  </div>
                </div>
                <!-- кнопки конец-->
              </form>
              <!-- форма конец-->';
              if(CheckData('sl_content', 'content_for', 'SkillHard')){ //данные для хардскилов есть
                $ArrayData = selectFromTable('sl_content', array('id','content_for', 'text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), true, 'content_for', 'SkillHard');
                $strExistingHTML = '';
                $i = 1;
                foreach ($ArrayData as $ArrayRow) {                
                  $strData=date("d.m.Y",$ArrayRow['time']);
                  $strExistingHTML .= '<div style="text-align:center;">Added: '.$strData.', user: '.$ArrayRow['edit_by'].'</div>
                      <!-- форма -->
                      <form action="" method="post" autocomplete="off">
                        <div class="formMargerExist'.($i%2).'">
                          <div class="form-row" style="padding-bottom: 15px;">
                            <div class="col-lg-4 offset-lg-3">
                              <label for="header">The skill name Ru*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                                </div>
                                <input type="text" class="form-control" id="header" name="header" value="'.$ArrayRow['text_big'].'" aria-describedby="inputGroupPrepend1" required>
                              </div>
                            </div>
                            <div class="col-lg-2">
                              <label for="text_block">Skill level Ru*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                                </div>
                                <input type="number" class="form-control" id="text_block" name="text_block" value="'.$ArrayRow['text_small'].'" aria-describedby="inputGroupPrepend3" required>
                              </div>
                            </div>
                          </div>
                          <div class="form-row" style="padding-bottom: 15px;">
                            <div class="col-lg-4 offset-lg-3">
                              <label for="header">The skill name En*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                                </div>
                                <input type="text" class="form-control" id="header_en" name="header_en" value="'.$ArrayRow['text_big_en'].'" aria-describedby="inputGroupPrepend2" required>
                              </div>
                            </div>
                            <div class="col-lg-2">
                              <label for="text_block">Skill level En*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                                </div>
                                <input type="number" class="form-control" id="text_block_en" name="text_block_en" value="'.$ArrayRow['text_small_en'].'" aria-describedby="inputGroupPrepend4" required>
                              </div>
                            </div>
                          </div>

                          <input type="hidden" value="'.$ArrayRow['id'].'" name="intIDinCont" required>
                          <!-- кнопки -->
                          <div class="container">
                            <div class="row" style = "color:white;" >
                              <div class="col-sm-3 offset-sm-2 col-lg-2 offset-lg-3">
                                <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                              </div>
                              <div class="col-sm-3 col-lg-2">
                                <a href="'.$config['sitelink'].'admin/index.php?strPage=Skills&skill_for=hardskill&delWithId='.$ArrayRow['id'].'" class="btn btn-danger btn-sm btnModal"><i class="fa fa-trash"></i> Remove</a>
                              </div>
                              <div class="col-sm-3 col-lg-2">
                                <button name="strInnFromForm" value="updIN" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                              </div>
                            </div>
                          </div>
                          <!-- кнопки конец-->
                        </div>
                      </form>
                      <!-- форма конец-->';
                  $i++;
                }
              }else{ //данные для хардскилов нету
                $strExistingHTML = '';
              }  
              echo $strExistingHTML.'<!-- форма -->
              <form action="" method="post" autocomplete="off">
                <div class="formMarger">
                  <div class="form-row" style="padding-bottom: 15px;">
                    <div class="col-lg-4 offset-lg-3">
                      <label for="header">The skill name Ru*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                        </div>
                        <input type="text" class="form-control" id="header" name="header" value="" aria-describedby="inputGroupPrepend1" required>
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <label for="text_block">Skill level Ru*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                        </div>
                        <input type="number" class="form-control" id="text_block" name="text_block" value="" aria-describedby="inputGroupPrepend3" required>
                      </div>
                    </div>
                  </div>
                  <div class="form-row" style="padding-bottom: 15px;">
                    <div class="col-lg-4 offset-lg-3">
                      <label for="header">The skill name En*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                        </div>
                        <input type="text" class="form-control" id="header_en" name="header_en" value="" aria-describedby="inputGroupPrepend2" required>
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <label for="text_block">Skill level En*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                        </div>
                        <input type="number" class="form-control" id="text_block_en" name="text_block_en" value="" aria-describedby="inputGroupPrepend4" required>
                      </div>
                    </div>
                  </div>

                  <input type="hidden" value="SkillHard" name="strContent_for" required>
                  <!-- кнопки -->
                  <div class="container">
                    <div class="row" style = "color:white;" >
                      <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                        <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                      </div>
                      <div class="col-sm-3 col-lg-2">
                        <button name="strInnFromForm" value="addINTO" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                      </div>
                    </div>
                  </div>
                  <!-- кнопки конец-->
                </div>
              </form>
              <!-- форма конец-->';

              
            break;
            case 'softskill':
              if(CheckData('sl_content', 'content_for', 'softskill_header')){ //данные для заголовка софтскилов есть
                $ArrayData = selectFromTable('sl_content', array('content_for', 'text_big', 'text_big_en', 'time','edit_by'), true, 'content_for', 'softskill_header')[0];
                $strSubmitValue = 'updIN';
                $strData=date("d.m.Y",$ArrayData['time']);
                $strEditorHTML = "<div style='text-align:center;'>Last editing $strData, user: {$ArrayData['edit_by']}</div>";
                $text_big=$ArrayData['text_big'];          
                $text_big_en=$ArrayData['text_big_en'];
              }else{ //данные для заголовка софтскилов нету
                $strSubmitValue = 'addINTO';
                $strEditorHTML = '';
                $text_big = '';          
                $text_big_en = '';
              }   

              echo "<a class='btn btn-info btn-sm btnModal' href='{$config['sitelink']}admin/index.php?strPage=Skills&skill_for=hardskill' >Hard skills</a> ";
              echo "<button class='btn btn-primary btn-sm' href='{$config['sitelink']}admin/index.php?strPage=Skills&skill_for=softskill' disabled>Soft skills</button> ";

              echo $strEditorHTML.'<!-- форма -->
              <form action="" method="post" autocomplete="off">
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <label for="header">Header Ru/En*</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                      </div>
                      <input type="text" class="form-control" id="header" name="header" value="'.$text_big.'" aria-describedby="inputGroupPrepend1" required>
                    </div>
                  </div>
                </div>
                <div class="form-row" style="padding-bottom: 15px;">
                  <div class="col-lg-4 offset-lg-4">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                      </div>
                      <input type="text" class="form-control" id="header_en" name="header_en" value="'.$text_big_en.'" aria-describedby="inputGroupPrepend2" required>
                    </div>
                  </div>
                </div>
                <input type="hidden" value="" name="text_block" required>
                <input type="hidden" value="" name="text_block_en" required>
                <input type="hidden" value="softskill_header" name="strContent_for" required>
                <!-- кнопки -->
                <div class="container">
                  <div class="row" style = "color:white;" >
                    <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                      <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                    </div>
                    <div class="col-sm-3 col-lg-2">
                      <button name="strInnFromForm" value="'.$strSubmitValue.'" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                    </div>
                  </div>
                </div>
                <!-- кнопки конец-->
              </form>
              <!-- форма конец-->';
              if(CheckData('sl_content', 'content_for', 'SkillSoft')){ //данные для софтскилов есть
                $ArrayData = selectFromTable('sl_content', array('id','content_for', 'text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), true, 'content_for', 'SkillSoft');
                $strExistingHTML = '';
                $i = 1;
                foreach ($ArrayData as $ArrayRow) {                
                  $strData=date("d.m.Y",$ArrayRow['time']);
                  $strExistingHTML .= '<div style="text-align:center;">Added: '.$strData.', user: '.$ArrayRow['edit_by'].'</div>
                      <!-- форма -->
                      <form action="" method="post" autocomplete="off">
                        <div class="formMargerExist'.($i%2).'">
                          <div class="form-row" style="padding-bottom: 15px;">
                            <div class="col-lg-4 offset-lg-3">
                              <label for="header">The skill name Ru*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                                </div>
                                <input type="text" class="form-control" id="header" name="header" value="'.$ArrayRow['text_big'].'" aria-describedby="inputGroupPrepend1" required>
                              </div>
                            </div>
                            <div class="col-lg-2">
                              <label for="text_block">Skill level Ru*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                                </div>
                                <input type="number" class="form-control" id="text_block" name="text_block" value="'.$ArrayRow['text_small'].'" aria-describedby="inputGroupPrepend3" required>
                              </div>
                            </div>
                          </div>
                          <div class="form-row" style="padding-bottom: 15px;">
                            <div class="col-lg-4 offset-lg-3">
                              <label for="header">The skill name En*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                                </div>
                                <input type="text" class="form-control" id="header_en" name="header_en" value="'.$ArrayRow['text_big_en'].'" aria-describedby="inputGroupPrepend2" required>
                              </div>
                            </div>
                            <div class="col-lg-2">
                              <label for="text_block">Skill level En*</label>
                              <div class="input-group">
                                <div class="input-group-prepend">
                                  <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                                </div>
                                <input type="number" class="form-control" id="text_block_en" name="text_block_en" value="'.$ArrayRow['text_small_en'].'" aria-describedby="inputGroupPrepend4" required>
                              </div>
                            </div>
                          </div>

                          <input type="hidden" value="'.$ArrayRow['id'].'" name="intIDinCont" required>
                          <!-- кнопки -->
                          <div class="container">
                            <div class="row" style = "color:white;" >
                              <div class="col-sm-3 offset-sm-2 col-lg-2 offset-lg-3">
                                <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                              </div>
                              <div class="col-sm-3 col-lg-2">
                                <a href="'.$config['sitelink'].'admin/index.php?strPage=Skills&skill_for=softskill&delWithId='.$ArrayRow['id'].'" class="btn btn-danger btn-sm btnModal"><i class="fa fa-trash"></i> Remove</a>
                              </div>
                              <div class="col-sm-3 col-lg-2">
                                <button name="strInnFromForm" value="updIN" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                              </div>
                            </div>
                          </div>
                          <!-- кнопки конец-->
                        </div>
                      </form>
                      <!-- форма конец-->';
                  $i++;
                }
              }else{ //данные для софтскилов нету
                $strExistingHTML = '';
              }  
              echo $strExistingHTML.'<!-- форма -->
              <form action="" method="post" autocomplete="off">
                <div class="formMarger">
                  <div class="form-row" style="padding-bottom: 15px;">
                    <div class="col-lg-4 offset-lg-3">
                      <label for="header">The skill name Ru*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                        </div>
                        <input type="text" class="form-control" id="header" name="header" value="" aria-describedby="inputGroupPrepend1" required>
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <label for="text_block">Skill level Ru*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                        </div>
                        <input type="number" class="form-control" id="text_block" name="text_block" value="" aria-describedby="inputGroupPrepend3" required>
                      </div>
                    </div>
                  </div>
                  <div class="form-row" style="padding-bottom: 15px;">
                    <div class="col-lg-4 offset-lg-3">
                      <label for="header">The skill name En*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                        </div>
                        <input type="text" class="form-control" id="header_en" name="header_en" value="" aria-describedby="inputGroupPrepend2" required>
                      </div>
                    </div>
                    <div class="col-lg-2">
                      <label for="text_block">Skill level En*</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                        </div>
                        <input type="number" class="form-control" id="text_block_en" name="text_block_en" value="" aria-describedby="inputGroupPrepend4" required>
                      </div>
                    </div>
                  </div>

                  <input type="hidden" value="SkillSoft" name="strContent_for" required>
                  <!-- кнопки -->
                  <div class="container">
                    <div class="row" style = "color:white;" >
                      <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                        <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                      </div>
                      <div class="col-sm-3 col-lg-2">
                        <button name="strInnFromForm" value="addINTO" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                      </div>
                    </div>
                  </div>
                  <!-- кнопки конец-->
                </div>
              </form>
              <!-- форма конец-->';

            break;

            default: 
              echo "<a class='btn btn-info btn-sm btnModal' href='{$config['sitelink']}admin/index.php?strPage=Skills&skill_for=hardskill'>Hard skills</a> ";
              echo "<a class='btn btn-info btn-sm btnModal' href='{$config['sitelink']}admin/index.php?strPage=Skills&skill_for=softskill'>Soft skills</a> ";
          }
      }else{ //если пришел запрос на удаление скила то отобразить переспрашивание
        $boolINDB = false;
        $boolDeletebleSkill = false;
        //проверяем есть ли такой скилл в базе
        if(CheckData('sl_content', 'id', $_GET['delWithId'])){
          $boolINDB = true;
          $ArrayData = selectFromTable('sl_content', array('content_for', 'text_big', 'text_small', 'text_big_en', 'text_small_en'), true, 'id', $_GET['delWithId'])[0];
        }
        //Проверяем удаляемый ли скилл
        if($ArrayData['content_for']=='SkillHard' or $ArrayData['content_for']=='SkillSoft'){$boolDeletebleSkill = true;}
        

        if($boolINDB and $boolDeletebleSkill){
          echo "<div class='myShapka'>Approve Skill removing</div>";
          echo '<!-- форма -->
            <form action="" method="post" autocomplete="off">
              <input type="hidden" value="Skill" name="strContent_for" required>
              <input type="hidden" value="'.$_GET['strPage'].'&skill_for='.$_GET['skill_for'].'" name="strPage" required>
              <input type="hidden" value="'.$_GET['delWithId'].'" name="id" required>
              <!-- кнопки -->
              <div class="container">
                Skill <h3>"'.$ArrayData['text_big'].'/'.$ArrayData['text_big_en'].'"</h3> with atr <h3>"'.$ArrayData['text_small'].'/'.$ArrayData['text_small_en'].'"</h3>
                <div class="row" style = "color:white;" >
                  <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                    <button name="strInnFromForm" value="RemoveRow" id="" type="submit" class="btn btn-danger btn-sm "><i class="fa fa-lock"></i> Remove</button>
                  </div>
                  <div class="col-sm-3 col-lg-2">
                    <a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php?strPage=Skills&skill_for='.$_GET['skill_for'].'"><i class="fa fa-reply"></i> Back</a>
                  </div>
                </div>
              </div>
              <!-- кнопки конец-->
            </form>
            <!-- форма конец-->';
        }else{
          echo "<div class='myShapka'>No data to removing</div>";
          echo '<a id="" class="btn btn-info btn-sm btnModal" href="'.$config['sitelink'].'admin/index.php?strPage=Skills&skill_for='.$_GET['skill_for'].'"><i class="fa fa-reply"></i> Back</a>';
        }
      }
    break;  
    /*---------------------------------------------------------------------------------*/
    /*--работа в раздете изменить шапку------------------------------------------------*/
    /*---------------------------------------------------------------------------------*/     
    case 'Portfolio': 
          echo "<div class='myShapka'>{$menu['Portfolio']}</div>";
          if(CheckData('sl_content', 'content_for', 'portfolio_header')){ //данные для заголовка портфолио есть
            $ArrayData = selectFromTable('sl_content', array('content_for', 'text_big', 'text_big_en', 'time','edit_by'), true, 'content_for', 'portfolio_header')[0];
            $strSubmitValue = 'updIN';
            $strData=date("d.m.Y",$ArrayData['time']);
            $strEditorHTML = "<div style='text-align:center;'>Last editing $strData, user: {$ArrayData['edit_by']}</div>";
            $text_big=$ArrayData['text_big'];          
            $text_big_en=$ArrayData['text_big_en'];
          }else{ //данные для заголовка портфолио нету
            $strSubmitValue = 'addINTO';
            $strEditorHTML = '';
            $text_big = '';         
            $text_big_en = '';
          }   

          echo $strEditorHTML.'<!-- форма -->
          <form action="" method="post" autocomplete="off">
            <div class="form-row" style="padding-bottom: 15px;">
              <div class="col-lg-4 offset-lg-4">
                <label for="header">Header Ru/En*</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                  </div>
                  <input type="text" class="form-control" id="header" name="header" value="'.$text_big.'" aria-describedby="inputGroupPrepend1" required>
                </div>
              </div>
            </div>
            <div class="form-row" style="padding-bottom: 15px;">
              <div class="col-lg-4 offset-lg-4">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                  </div>
                  <input type="text" class="form-control" id="header_en" name="header_en" value="'.$text_big_en.'" aria-describedby="inputGroupPrepend2" required>
                </div>
              </div>
            </div>
            <input type="hidden" value="" name="text_block" required>
            <input type="hidden" value="" name="text_block_en" required>
            <input type="hidden" value="portfolio_header" name="strContent_for" required>
            <!-- кнопки -->
            <div class="container">
              <div class="row" style = "color:white;" >
                <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                  <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                </div>
                <div class="col-sm-3 col-lg-2">
                  <button name="strInnFromForm" value="'.$strSubmitValue.'" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                </div>
              </div>
            </div>
            <!-- кнопки конец-->
          </form>
          <!-- форма конец-->';

          if(CheckData('sl_portfolio', 'item_for', '', true)){ //данные для портфолио есть
            $ArrayData = selectFromTable('sl_content', array('id','content_for', 'text_big', 'text_small', 'text_big_en', 'text_small_en', 'time','edit_by'), true, 'content_for', 'SkillHard');
            $strExistingHTML = '';
            $i = 1;
            foreach ($ArrayData as $ArrayRow) {                
              $strData=date("d.m.Y",$ArrayRow['time']);
              $strExistingHTML .= '<div style="text-align:center;">Added: '.$strData.', user: '.$ArrayRow['edit_by'].'</div>
                  <!-- форма -->
                  <form action="" method="post" autocomplete="off">
                    <div class="formMargerExist'.($i%2).'">
                      <div class="form-row" style="padding-bottom: 15px;">
                        <div class="col-lg-4 offset-lg-3">
                          <label for="header">The skill name Ru*</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                            </div>
                            <input type="text" class="form-control" id="header" name="header" value="'.$ArrayRow['text_big'].'" aria-describedby="inputGroupPrepend1" required>
                          </div>
                        </div>
                        <div class="col-lg-2">
                          <label for="text_block">Skill level Ru*</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                            </div>
                            <input type="number" class="form-control" id="text_block" name="text_block" value="'.$ArrayRow['text_small'].'" aria-describedby="inputGroupPrepend3" required>
                          </div>
                        </div>
                      </div>
                      <div class="form-row" style="padding-bottom: 15px;">
                        <div class="col-lg-4 offset-lg-3">
                          <label for="header">The skill name En*</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                            </div>
                            <input type="text" class="form-control" id="header_en" name="header_en" value="'.$ArrayRow['text_big_en'].'" aria-describedby="inputGroupPrepend2" required>
                          </div>
                        </div>
                        <div class="col-lg-2">
                          <label for="text_block">Skill level En*</label>
                          <div class="input-group">
                            <div class="input-group-prepend">
                              <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                            </div>
                            <input type="number" class="form-control" id="text_block_en" name="text_block_en" value="'.$ArrayRow['text_small_en'].'" aria-describedby="inputGroupPrepend4" required>
                          </div>
                        </div>
                      </div>

                      <input type="hidden" value="'.$ArrayRow['id'].'" name="intIDinCont" required>
                      <!-- кнопки -->
                      <div class="container">
                        <div class="row" style = "color:white;" >
                          <div class="col-sm-3 offset-sm-2 col-lg-2 offset-lg-3">
                            <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                          </div>
                          <div class="col-sm-3 col-lg-2">
                            <a href="'.$config['sitelink'].'admin/index.php?strPage=Skills&skill_for=hardskill&delWithId='.$ArrayRow['id'].'" class="btn btn-danger btn-sm btnModal"><i class="fa fa-trash"></i> Remove</a>
                          </div>
                          <div class="col-sm-3 col-lg-2">
                            <button name="strInnFromForm" value="updIN" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                          </div>
                        </div>
                      </div>
                      <!-- кнопки конец-->
                    </div>
                  </form>
                  <!-- форма конец-->';
              $i++;
            }
          }else{ //данные для портфолио нету
            $strExistingHTML = '';
          }
          echo $strExistingHTML.'<!-- форма -->
          <form action="" method="post" autocomplete="off">
            <div class="formMarger">
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-3">
                  <label for="header">The skill name Ru*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend1"><i class="fa fa-header"></i></span>
                    </div>
                    <input type="text" class="form-control" id="header" name="header" value="" aria-describedby="inputGroupPrepend1" required>
                  </div>
                </div>
                <div class="col-lg-2">
                  <label for="text_block">Skill level Ru*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend3"><i class="fa fa-font"></i></span>
                    </div>
                    <input type="number" class="form-control" id="text_block" name="text_block" value="" aria-describedby="inputGroupPrepend3" required>
                  </div>
                </div>
              </div>
              <div class="form-row" style="padding-bottom: 15px;">
                <div class="col-lg-4 offset-lg-3">
                  <label for="header">The skill name En*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend2"><i class="fa fa-header"></i></span>
                    </div>
                    <input type="text" class="form-control" id="header_en" name="header_en" value="" aria-describedby="inputGroupPrepend2" required>
                  </div>
                </div>
                <div class="col-lg-2">
                  <label for="text_block">Skill level En*</label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="inputGroupPrepend4"><i class="fa fa-font"></i></span>
                    </div>
                    <input type="number" class="form-control" id="text_block_en" name="text_block_en" value="" aria-describedby="inputGroupPrepend4" required>
                  </div>
                </div>
              </div>

              <input type="hidden" value="SkillHard" name="strContent_for" required>
              <!-- кнопки -->
              <div class="container">
                <div class="row" style = "color:white;" >
                  <div class="col-sm-3 offset-sm-3 col-lg-2 offset-lg-4">
                    <button type="reset" class="btn btn-info btn-sm"><i class="fa fa-times"></i> Clear</button>
                  </div>
                  <div class="col-sm-3 col-lg-2">
                    <button name="strInnFromForm" value="addINTO" id="" type="submit" class="btn btn-success btn-sm "><i class="fa fa-lock"></i> Save</button>
                  </div>
                </div>
              </div>
              <!-- кнопки конец-->
            </div>
          </form>
          <!-- форма конец-->';


        // if(CheckPortfolio('Portfolio')){ //Text в блоке портфолио   (есть)
        //       $ArrayData=CheckPortfolio('Portfolio', TRUE); 
        // if(!isset($ArrayData[1]['text_big'])){ //только один елемент в портфолио       
        //       $data=date("d.m.y",$ArrayData[0]['time']);
        //       $Edit=$ArrayData[0]['edit_by'];
        // echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";   
        // echo "<label class='col-sm-offset-3 '>Portfolio photo (1000*1000px)</label><div class='row'>
        //   <div class='col-sm-offset-3 col-sm-3 style='text-align:center;'><img src='images/portfolio/mini/{$ArrayData[0]['image1']}' width='200px'></div>";
        // if($ArrayData[0]['image2'] !=''){echo "<div class='col-sm-3 style='text-align:center;'><img src='images/portfolio/mini/{$ArrayData[0]['image2']}' width='200px'></div>";}
        // echo "</div><div class='row'>
        // <form class='col-sm-3 col-sm-offset-3' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <div class='col-sm-12'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage1'>
        //   </div></div>

        // <input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

        //   <div class='form-group last'>
        //   <div class=' col-sm-3'>
        //   <button type='submit' name='updatePortfolioImg' class='btn btn-success btn-xs' style='margin-top:10px;'>Change</button>
        // </div></div></form>
        // <form class='col-sm-3' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <div class='col-sm-12'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage2'>
        //   </div></div>

        // <input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

        //   <div class='form-group last'>
        //   <div class='col-sm-3'>
        //   <button type='submit' name='updatePortfolioImg' class='btn btn-success btn-xs' style='margin-top:10px;'>Change</button>
        // </div></div></form></div>

        // <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
        // <div class='col-sm-3'> 
        // <input type='text' class='form-control' name='Zagolovok' placeholder='{$ArrayData[0]['text_big']}' value='{$ArrayData[0]['text_big']}' required=''></div>
        // <div class='col-sm-3'> 
        // <input type='text' class='form-control' name='Zagolovok_ua' placeholder='{$ArrayData[0]['text_big_ua']}' value='{$ArrayData[0]['text_big_ua']}' required=''></div>
        // </div>
          
        //   <div class='form-group'>
        //   <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
        //   <div class='col-sm-3'> 
        // <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='{$ArrayData[0]['text_small']}' required=''>
        // {$ArrayData[0]['text_small']}
        // </textarea></div>        
        // <div class='col-sm-3'> 
        // <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='{$ArrayData[0]['text_small_ua']}' required=''>
        // {$ArrayData[0]['text_small_ua']}
        // </textarea></div>
        // </div>    

        // <input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

        //   <div class='form-group last'>
        //   <div class='col-sm-offset-3 col-sm-9'>
        //   <button type='submit' name='updatePortfolio' class='btn btn-success btn-xs'>Change</button>
        //   <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=Portfolio&delPortfolioID={$ArrayData[0]['id']}'>Remove</a>
        // </div></div></form>";

        // echo "<hr><form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <label for='SliImg' class='col-sm-3 control-label'>Portfolio photo (1000*1000px)</label>
        //   <div class='col-sm-3'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage1'>
        //   </div>
        //   <div class='col-sm-3'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' name='newImage2'>
        //   </div></div> 
          
        //   <div class='form-group'>
        //   <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
        //   <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Header' required=''></div>
        //   <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Header_en' required=''></div>
        //   </div>
          
        //   <div class='form-group'>
        //   <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
        //   <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='Description' required=''></textarea></div>
        //   <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='Description_en' required=''></textarea></div>
        //   </div>    
          
        //   <div class='form-group last'>
        //   <div class='col-sm-offset-3 col-sm-9'>
        //   <button type='submit' name='addPortfolio' class='btn btn-success btn-xs'>Add</button>
        //   <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
        // </div></div></form>";
        // }else{ //больше одного элемента в портфолио
        // foreach($ArrayData as $SomeArr){
        //       $data=date("d.m.y",$SomeArr['time']);
        //       $Edit=$SomeArr['edit_by'];
        // echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";   
        // echo "<label class='col-sm-offset-3 '>Portfolio photo (1000*1000px)</label><div class='row'>
        //   <div class='col-sm-offset-3 col-sm-3 style='text-align:center;'><img src='images/portfolio/mini/{$SomeArr['image1']}' width='200px'></div>";
        // if($SomeArr['image2'] !=''){echo "<div class='col-sm-3 style='text-align:center;'><img src='images/portfolio/mini/{$SomeArr['image2']}' width='200px'></div>";}
        // echo "</div><div class='row'>
        // <form class='col-sm-3 col-sm-offset-3' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <div class='col-sm-12'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage1'>
        //   </div></div>

        // <input type='hidden'  name='id' value='{$SomeArr['id']}'>

        //   <div class='form-group last'>
        //   <div class=' col-sm-3'>
        //   <button type='submit' name='updatePortfolioImg' class='btn btn-success btn-xs' style='margin-top:10px;'>Change</button>
        // </div></div></form>
        // <form class='col-sm-3' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <div class='col-sm-12'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage2'>
        //   </div></div>

        // <input type='hidden'  name='id' value='{$SomeArr['id']}'>

        //   <div class='form-group last'>
        //   <div class='col-sm-3'>
        //   <button type='submit' name='updatePortfolioImg' class='btn btn-success btn-xs' style='margin-top:10px;'>Change</button>
        // </div></div></form></div>

        // <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
        // <div class='col-sm-3'> 
        // <input type='text' class='form-control' name='Zagolovok' placeholder='{$SomeArr['text_big']}' value='{$SomeArr['text_big']}' required=''></div>
        // <div class='col-sm-3'> 
        // <input type='text' class='form-control' name='Zagolovok_ua' placeholder='{$SomeArr['text_big_ua']}' value='{$SomeArr['text_big_ua']}' required=''></div>
        // </div>
          
        //   <div class='form-group'>
        //   <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
        //   <div class='col-sm-3'> 
        // <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='{$SomeArr['text_small']}' required=''>
        // {$SomeArr['text_small']}
        // </textarea></div>        
        // <div class='col-sm-3'> 
        // <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='{$SomeArr['text_small_ua']}' required=''>
        // {$SomeArr['text_small_ua']}
        // </textarea></div>
        // </div>    

        // <input type='hidden'  name='id' value='{$SomeArr['id']}'>

        //   <div class='form-group last'>
        //   <div class='col-sm-offset-3 col-sm-9'>
        //   <button type='submit' name='updatePortfolio' class='btn btn-success btn-xs'>Change</button>
        //   <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=Portfolio&delPortfolioID={$SomeArr['id']}'>Remove</a>
        // </div></div></form><hr>";} 

        // echo "<hr> <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <label for='SliImg' class='col-sm-3 control-label'>Portfolio photo (1000*1000px)</label>
        //   <div class='col-sm-3'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage1'>
        //   </div>
        //   <div class='col-sm-3'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' name='newImage2'>
        //   </div></div> 
          
        //   <div class='form-group'>
        //   <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
        //   <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Header' required=''></div>
        //   <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Header_en' required=''></div>
        //   </div>
          
        //   <div class='form-group'>
        //   <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
        //   <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='Description' required=''></textarea></div>
        //   <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='Description_en' required=''></textarea></div>
        //   </div>    
          
        //   <div class='form-group last'>
        //   <div class='col-sm-offset-3 col-sm-9'>
        //   <button type='submit' name='addPortfolio' class='btn btn-success btn-xs'>Add</button>
        //   <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
        // </div></div></form>";
        // }
        //   }else{ //Text в блок портфолио (нету)
        // echo " <hr><form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
        //   <div class='form-group'>
        //   <label for='SliImg' class='col-sm-3 control-label'>Portfolio photo (1000*1000px)</label>
        //   <div class='col-sm-3'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage1'>
        //   </div>
        //   <div class='col-sm-3'> 
        //   <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' name='newImage2'>
        //   </div></div> 
          
        //   <div class='form-group'>
        //   <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
        //   <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Header' required=''></div>
        //   <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Header_en' required=''></div>
        //   </div>
          
        //   <div class='form-group'>
        //   <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
        //   <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='Description' required=''></textarea></div>
        //   <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='Description_en' required=''></textarea></div>
        //   </div>    
          
        //   <div class='form-group last'>
        //   <div class='col-sm-offset-3 col-sm-9'>
        //   <button type='submit' name='addPortfolio' class='btn btn-success btn-xs'>Add</button>
        //   <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
        // </div></div></form>";
        //   }
      break;
case 'Feedback': //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
  echo "<div class='myShapka'>{$menu['Feedback']}</div>";
if(CheckContent('Feedback')){ //Text - Header блока скилы
          $ArrayData=CheckContent('Feedback', TRUE);
          $data=date("d.m.y",$ArrayData['time']);
          $text_big=$ArrayData['text_big'];
          $text_small=$ArrayData['text_small'];
          $text_big_ua=$ArrayData['text_big_ua'];
          $text_small_ua=$ArrayData['text_small_ua'];
          $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    

  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='$text_big' value='$text_big' required=''></div>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='$text_big_ua' value='$text_big_ua' required=''></div>
          </div>   

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
          <button type='submit' name='updateFeedbackHeader' class='btn btn-success btn-xs'>Change</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form><hr>";
}else{ //заголовка нету
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Enter the header' required=''></div>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Enter the header_en' required=''></div>
          </div>       

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
          <button type='submit' name='addFeedbackHeader' class='btn btn-success btn-xs'>Add</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form><hr>";
}
  
if(CheckPartnerContent('Feedback')){ //Text в блок Descriptionы   (есть)
      $ArrayData=CheckPartnerContent('Feedback', TRUE);   
if(!isset($ArrayData[1]['text_big'])){ //один Description
$data=date("d.m.y",$ArrayData[0]['time']);
$Edit=$ArrayData[0]['edit_by']; 
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";
echo " <div style='text-align:center;'><img src='images/ClientAvatar/mini/{$ArrayData[0]['image']}' width='120px'></div>
<form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Customer photo (square)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div>

<input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updatePartnerContentFeedbackImage' class='btn btn-success btn-xs'>Change</button>
</div></div></form>

<form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Name' class='col-sm-3 control-label'>Customer Name Ru/En</label>
  <div class='col-sm-3'> 
<input type='text' class='form-control' name='Name' placeholder='{$ArrayData[0]['text_big']}' value='{$ArrayData[0]['text_big']}' required=''></div>
      <div class='col-sm-3'> 
<input type='text' class='form-control' name='Name_ua' placeholder='{$ArrayData[0]['text_big_ua']}' value='{$ArrayData[0]['text_big_ua']}' required=''></div>
</div>

  <div class='form-group'>
  <label for='Company' class='col-sm-3 control-label'>Company/position Ru/En</label>
  <div class='col-sm-3'> 
<input type='text' class='form-control' name='Company' placeholder='{$ArrayData[0]['text_company']}' value='{$ArrayData[0]['text_company']}' required=''></div>
      <div class='col-sm-3'> 
<input type='text' class='form-control' name='Company_ua' placeholder='{$ArrayData[0]['text_company_ua']}' value='{$ArrayData[0]['text_company_ua']}' required=''></div>
</div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description</label>
  <div class='col-sm-3'> 
<textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='{$ArrayData[0]['text_small']}' required=''>
{$ArrayData[0]['text_small']}
</textarea></div></div>    


<input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updatePartnerContentText' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=Feedback&delPartnerContentID={$ArrayData[0]['id']}'>Remove</a>
</div></div></form>";

echo " <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Customer photo (square)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div> 
  
  <div class='form-group'>
  <label for='Name' class='col-sm-3 control-label'>Customer Name Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Name' placeholder='Customer Name' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Name_ua' placeholder='Customer Name_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Company' class='col-sm-3 control-label'>Company/position Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Company' placeholder='Company/position' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Company_ua' placeholder='Company/position_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description</label>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='feedback' placeholder='Description' required=''></textarea></div></div>    
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addPartnerContent' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
}else{ //больше одного Descriptionа
foreach($ArrayData as $SomeArr){
$data=date("d.m.y",$SomeArr['time']);
$Edit=$SomeArr['edit_by']; 
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";
echo " <div style='text-align:center;'><img src='images/ClientAvatar/mini/{$SomeArr['image']}' width='120px'></div>
<form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Customer photo (square)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div>

<input type='hidden'  name='id' value='{$SomeArr['id']}'>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updatePartnerContentFeedbackImage' class='btn btn-success btn-xs'>Change</button>
</div></div></form>

<form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
      <label for='Name' class='col-sm-3 control-label'>Customer Name Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='Name' placeholder='{$SomeArr['text_big']}' value='{$SomeArr['text_big']}' required=''>
      </div>
      <div class='col-sm-3'>
          <input type='text' class='form-control' name='Name_ua' placeholder='{$SomeArr['text_big_ua']}' value='{$SomeArr['text_big_ua']}' required=''>
      </div>
  </div>
  
  <div class='form-group'>
      <label for='Company' class='col-sm-3 control-label'>Company/position Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='Company' placeholder='{$SomeArr['text_company']}' value='{$SomeArr['text_company']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='Company_ua' placeholder='{$SomeArr['text_company_ua']}' value='{$SomeArr['text_company_ua']}' required=''>
      </div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description</label>
  <div class='col-sm-3'> 
<textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='{$SomeArr['text_small']}' required=''>
{$SomeArr['text_small']}
</textarea></div></div>    


<input type='hidden'  name='id' value='{$SomeArr['id']}'>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updatePartnerContentText' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=Feedback&delPartnerContentID={$SomeArr['id']}'>Remove</a>
</div></div></form>";} 

echo " <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Customer photo (square)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div> 
  
  <div class='form-group'>
  <label for='Name' class='col-sm-3 control-label'>Customer Name Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Name' placeholder='Customer Name' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Name_ua' placeholder='Customer Name_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Company' class='col-sm-3 control-label'>Company/position Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Company' placeholder='Company/position' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Company_ua' placeholder='Company/position_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description</label>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='feedback' placeholder='Description' required=''></textarea></div></div>    
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addPartnerContent' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
}
  }else{ //Descriptionов нету
echo " <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Customer photo (square)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div> 
  
  <div class='form-group'>
  <label for='Name' class='col-sm-3 control-label'>Customer Name Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Name' placeholder='Customer Name' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Name_ua' placeholder='Customer Name_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Company' class='col-sm-3 control-label'>Company/position Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Company' placeholder='Company/position' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Company_ua' placeholder='Company/position_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description</label>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='feedback' placeholder='Description' required=''></textarea></div></div>    
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addPartnerContent' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }
  
break;         
case 'Partners': //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
echo "<div class='myShapka'>{$menu['Partners']}</div>";
if(CheckContent('Partners')){ //Text - Header блока скилы
      $ArrayData=CheckContent('Partners', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $text_big=$ArrayData['text_big'];
      $text_small=$ArrayData['text_small'];
      $text_big_ua=$ArrayData['text_big_ua'];
      $text_small_ua=$ArrayData['text_small_ua'];
      $Edit=$ArrayData['edit_by'];
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    

echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
      <div class='form-group'>
      <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='$text_big' value='$text_big' required=''></div>
      <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='$text_big_ua' value='$text_big_ua' required=''></div>
      </div>   

      <div class='form-group last'>
      <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
      <button type='submit' name='updatePartnersHeader' class='btn btn-success btn-xs'>Change</button>
      <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form><hr>";
}else{ //заголовка нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
      <div class='form-group'>
      <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Enter the header' required=''></div>
      <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Enter the header_en' required=''></div>
      </div>       

      <div class='form-group last'>
      <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
      <button type='submit' name='addPartnersHeader' class='btn btn-success btn-xs'>Add</button>
      <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form><hr>";
}

  
  if(CheckImages('LOGOS')){ //логотипы есть
      $ArrayData=CheckImages('LOGOS', TRUE);
if(!isset($ArrayData[1]['image_name'])){ //если есть только один слайд
$data=date("d.m.y",$ArrayData[0]['time']);
$img_name=$ArrayData[0]['image_name'];
$Edit=$ArrayData[0]['edit_by'];
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
echo "<div class='container-fluid'>
  <div class='col-sm-2'>
  <div class='col-xs-12'><img src='images/Slider/mini/$img_name' width='100%'></div>
  <div class='col-xs-12' style='text-align:center;'>
  <a href='".$config['sitelink']."admin/index.php?Page=Partners&delWithName=$img_name' class='btn btn-default btn-xs'>Remove</a>
  </div>
</div></div>";
}else{//если слайдов много
foreach($ArrayData as $Array){
$data=date("d.m.y",$Array['time']);
$img_name=$Array['image_name'];
$Edit=$Array['edit_by'];

echo "<div class='col-sm-2'>
  <div style='col-xs-12'>Added: $data, user: $Edit</div>
  <div class='col-xs-12'><img src='images/Slider/mini/$img_name' width='100%'></div>
  <div class='col-xs-12' style='text-align:center;'>
  <a href='".$config['sitelink']."admin/index.php?Page=Partners&delWithName=$img_name' class='btn btn-default btn-xs'>Remove</a>
  </div></div>";}
}            
      
      
echo " <form class='form-horizontal col-xs-12' enctype='multipart/form-data' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Add logo (180*80px)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div>       
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addLOGOImage' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }else{ //картинки для слайдера в базе нету
echo " <form class='form-horizontal' enctype='multipart/form-data' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='SliImg' class='col-sm-3 control-label'>Add logo (180*80px)</label>
  <div class='col-sm-3'> 
  <input class='btn btn-default form-control' type='file' accept='image/jpeg,image/png,image/gif' required='required' name='newImage'>
  </div></div>       
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addLOGOImage' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  } 
break;       
case 'Expirience': /////////************************************//////////////////////////********************************************/***************
echo "<div class='myShapka'>{$menu['Expirience']}</div>";
if(CheckContent('Expirience')){ //Text - Header Опыт
          $ArrayData=CheckContent('Expirience', TRUE);
          $data=date("d.m.y",$ArrayData['time']);
          $text_big=$ArrayData['text_big'];
          $text_small=$ArrayData['text_small'];
          $text_big_ua=$ArrayData['text_big_ua'];
          $text_small_ua=$ArrayData['text_small_ua'];
          $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    

  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='$text_big' value='$text_big' required=''></div>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='$text_big_ua' value='$text_big_ua' required=''></div>
          </div>   

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
          <button type='submit' name='updateExpirienceHeader' class='btn btn-success btn-xs'>Change</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form><hr>";
}else{ //Textа заголовка  нету
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Enter the header' required=''></div>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Enter the header_en' required=''></div>
          </div>       

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
          <button type='submit' name='addExpirienceHeader' class='btn btn-success btn-xs'>Add</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form><hr>";
}        
if(CheckSkill('Expirience')){ //Text в блок опыт есть
      $ArrayData=CheckSkill('Expirience', TRUE); 
if(!isset($ArrayData[1]['text_big'])){ //одина только строка Textа в опыте   
      $data=date("d.m.y",$ArrayData[0]['time']);
      $Edit=$ArrayData[0]['edit_by'];
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";   
echo " 
<form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
<div class='col-sm-3'> 
<input type='text' class='form-control' name='Zagolovok' placeholder='{$ArrayData[0]['text_big']}' value='{$ArrayData[0]['text_big']}' required=''></div>
<div class='col-sm-3'> 
<input type='text' class='form-control' name='Zagolovok_ua' placeholder='{$ArrayData[0]['text_big_ua']}' value='{$ArrayData[0]['text_big_ua']}' required=''></div>
</div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description  Ru/En</label>
  <div class='col-sm-3'> 
<input type='text' class='form-control' name='Text_sm' placeholder='{$ArrayData[0]['text_small']}' value='{$ArrayData[0]['text_small']}' required=''></div>
<div class='col-sm-3'> 
<input type='text' class='form-control' name='Text_sm_ua' placeholder='{$ArrayData[0]['text_small_ua']}' value='{$ArrayData[0]['text_small_ua']}' required=''></div>
</div>    

<input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updateHardSoftSkill' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=Skills&skill_for=hardskill&delSkillID={$ArrayData[0]['id']}'>Remove</a>
</div></div></form>";

echo " <hr><form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>        
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Header' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Header_en' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Text_sm' placeholder='Description Ru' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Text_sm_ua' placeholder='Description En' required=''></div>
  </div>    
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addExpirienceSkill' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
}else{ //больше одного Textа блоке опыт
foreach($ArrayData as $SomeArr){
      $data=date("d.m.y",$SomeArr['time']);
      $Edit=$SomeArr['edit_by'];
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";   
echo " 
<form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
  <div class='col-sm-3'> 
<input type='text' class='form-control' name='Zagolovok' placeholder='{$SomeArr['text_big']}' value='{$SomeArr['text_big']}' required=''></div>
  <div class='col-sm-3'> 
<input type='text' class='form-control' name='Zagolovok_ua' placeholder='{$SomeArr['text_big_ua']}' value='{$SomeArr['text_big_ua']}' required=''></div>
</div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description  Ru/En</label>
  <div class='col-sm-3'> 
<input type='text' class='form-control' name='Text_sm' placeholder='{$SomeArr['text_small']}' value='{$SomeArr['text_small']}' required=''></div>
<div class='col-sm-3'> 
<input type='text' class='form-control' name='Text_sm_ua' placeholder='{$SomeArr['text_small_ua']}' value='{$SomeArr['text_small_ua']}' required=''></div>
</div>    

<input type='hidden'  name='id' value='{$SomeArr['id']}'>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updateHardSoftSkill' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=Skills&skill_for=hardskill&delSkillID={$SomeArr['id']}'>Remove</a>
</div></div></form><hr>";} 

echo " <hr><form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>        
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Header' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Header_en' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Text_sm' placeholder='Description Ru' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Text_sm_ua' placeholder='Description En' required=''></div>
  </div>    
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addExpirienceSkill' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
}
  }else{ //Text в блок Опыт нету
echo " <hr><form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>        
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Header' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Header_en' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Description Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Text_sm' placeholder='Description Ru' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Text_sm_ua' placeholder='Description En' required=''></div>
  </div>    
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addExpirienceSkill' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }
  break;
case 'Contacts': //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  echo "<div class='myShapka'>{$menu['Contacts']}</div>";
if(CheckContent('Contacts')){ //Text - Header Контакты
          $ArrayData=CheckContent('Contacts', TRUE);
          $data=date("d.m.y",$ArrayData['time']);
          $text_big=$ArrayData['text_big'];
          $text_small=$ArrayData['text_small'];
          $text_big_ua=$ArrayData['text_big_ua'];
          $text_small_ua=$ArrayData['text_small_ua'];
          $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    

  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='$text_big' value='$text_big' required=''></div>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='$text_big_ua' value='$text_big_ua' required=''></div>
          </div>   

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
          <button type='submit' name='updateContactsHeader' class='btn btn-success btn-xs'>Change</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form><hr>";
}else{ //Textа заголовка  нету
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Enter the header' required=''></div>
          <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Enter the header_en' required=''></div>
          </div>       

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9' style='text-align: left;'>
          <button type='submit' name='addContactsHeader' class='btn btn-success btn-xs'>Add</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form><hr>";
}  
  
//if(CheckContent('Contacts')){ //Text контакты есть
//            $ArrayData=CheckContent('Contacts', TRUE);
//            $data=date("d.m.y",$ArrayData['time']);
//            $text_big=$ArrayData['text_big'];
//            $text_small=$ArrayData['text_small'];
//            $text_big_ua=$ArrayData['text_big_ua'];
//            $text_small_ua=$ArrayData['text_small_ua'];
//            $Edit=$ArrayData['edit_by'];
//echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
//            
//echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
//        <div class='form-group'>
//        <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
//        <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='$text_big' value='$text_big' required=''></div>
//        <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='$text_big_ua' value='$text_big_ua' required=''></div>
//        </div>
//        
//        <div class='form-group'>
//        <label for='Text_sm' class='col-sm-3 control-label'>Text Ru/En</label>
//        <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='$text_small' required=''>$text_small</textarea></div>
//        <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='$text_small_ua' required=''>$text_small_ua</textarea></div>
//        </div>    
//        
//        <div class='form-group last'>
//        <div class='col-sm-offset-3 col-sm-9'>
//        <button type='submit' name='updateContacts' class='btn btn-success btn-xs'>Change</button>
//        <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
//</div></div></form>";
//        }else{ //Text контакты нету
//echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
//        <div class='form-group'>
//        <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
//        <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Enter the header' required=''></div>
//        <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Enter the header_ua' required=''></div>
//        </div>
//        
//        <div class='form-group'>
//        <label for='Text_sm' class='col-sm-3 control-label'>Text Ru/En</label>
//        <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='Введите Text' required=''></textarea></div>
//        <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='Введите Text_ua' required=''></textarea></div>
//        </div>        
//        
//        <div class='form-group last'>
//        <div class='col-sm-offset-3 col-sm-9'>
//        <button type='submit' name='addContacts' class='btn btn-success btn-xs'>Add</button>
//        <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
//</div></div></form>";
//        }
  
  if(CheckContent('Contacts2')){ //Text контакты2 есть
      $ArrayData=CheckContent('Contacts2', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $text_big=$ArrayData['text_big'];
      $text_small=$ArrayData['text_small'];
      $text_big_ua=$ArrayData['text_big_ua'];
      $text_small_ua=$ArrayData['text_small_ua'];
      $Edit=$ArrayData['edit_by'];
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='$text_big' value='$text_big' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='$text_big_ua' value='$text_big_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Text Ru/En</label>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='$text_small' required=''>$text_small</textarea></div>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='$text_small_ua' required=''>$text_small_ua</textarea></div>
  </div>   
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updateContacts2' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }else{ //Text контакты2 нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Zagolovok' class='col-sm-3 control-label'>Header Ru/En</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok' placeholder='Enter the header' required=''></div>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Zagolovok_ua' placeholder='Enter the header_ua' required=''></div>
  </div>
  
  <div class='form-group'>
  <label for='Text_sm' class='col-sm-3 control-label'>Text Ru/En</label>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm' placeholder='Введите Text' required=''></textarea></div>
  <div class='col-sm-3'> <textarea rows='4' cols='50' type='text' class='form-control' name='Text_sm_ua' placeholder='Введите Text_ua' required=''></textarea></div>
  </div>         
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addContacts2' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }
  
if(CheckContact('Email')){ //Email есть
      $ArrayData=CheckContact('Email', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $Contact=$ArrayData['contact'];
      $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Email' class='col-sm-3 control-label'>Email</label>
          <div class='col-sm-3'> 
          <input type='text' class='form-control' name='Email' id='Email' placeholder='".$Contact."' required=''></div></div>

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9'>
          <button type='submit' name='updateEmail' class='btn btn-success btn-xs'>Change Email</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form>";
  }else{ //Email нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Email' class='col-sm-3 control-label'>Email</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Email' id='Email' placeholder='Введите Email' required=''></div></div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addEmail' class='btn btn-success btn-xs'>Add Email</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }
if(CheckContact('Facebook')){ //Facebook есть
      $ArrayData=CheckContact('Facebook', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $Contact=$ArrayData['contact'];
      $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='Facebook' class='col-sm-3 control-label'>Facebook</label>
          <div class='col-sm-3'> 
          <input type='text' class='form-control' name='Facebook' id='Facebook' placeholder='".$Contact."' required=''></div></div>

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9'>
          <button type='submit' name='updateFacebook' class='btn btn-success btn-xs'>Change Facebook</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form>";
  }else{ //Facebook нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='Facebook' class='col-sm-3 control-label'>Facebook</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='Facebook' id='Facebook' placeholder='Введите Facebook' required=''></div></div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addFacebook' class='btn btn-success btn-xs'>Add Facebook</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }
if(CheckContact('instagram')){ //instagram есть
      $ArrayData=CheckContact('instagram', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $Contact=$ArrayData['contact'];
      $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='instagram' class='col-sm-3 control-label'>instagram</label>
          <div class='col-sm-3'> 
          <input type='text' class='form-control' name='instagram' id='instagram' placeholder='".$Contact."' required=''></div></div>

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9'>
          <button type='submit' name='updateinstagram' class='btn btn-success btn-xs'>Change instagram</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form>";
  }else{ //instagram нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='instagram' class='col-sm-3 control-label'>instagram</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='instagram' id='instagram' placeholder='Введите instagram' required=''></div></div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addinstagram' class='btn btn-success btn-xs'>Add instagram</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }  
if(CheckContact('youtube')){ //youtube есть
      $ArrayData=CheckContact('youtube', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $Contact=$ArrayData['contact'];
      $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='youtube' class='col-sm-3 control-label'>youtube</label>
          <div class='col-sm-3'> 
          <input type='text' class='form-control' name='youtube' id='youtube' placeholder='".$Contact."' required=''></div></div>

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9'>
          <button type='submit' name='updateyoutube' class='btn btn-success btn-xs'>Change youtube</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form>";
  }else{ //youtube нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='youtube' class='col-sm-3 control-label'>youtube</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='youtube' id='youtube' placeholder='Введите youtube' required=''></div></div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addyoutube' class='btn btn-success btn-xs'>Add youtube</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  } 
if(CheckContact('upwork')){ //upwork есть
      $ArrayData=CheckContact('upwork', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $Contact=$ArrayData['contact'];
      $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='upwork' class='col-sm-3 control-label'>upwork</label>
          <div class='col-sm-3'> 
          <input type='text' class='form-control' name='upwork' id='upwork' placeholder='".$Contact."' required=''></div></div>

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9'>
          <button type='submit' name='updateupwork' class='btn btn-success btn-xs'>Change upwork</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form>";
  }else{ //upwork нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='upwork' class='col-sm-3 control-label'>upwork</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='upwork' id='upwork' placeholder='Введите upwork' required=''></div></div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addupwork' class='btn btn-success btn-xs'>Add upwork</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  } 
if(CheckContact('linkedin')){ //linkedin есть
      $ArrayData=CheckContact('linkedin', TRUE);
      $data=date("d.m.y",$ArrayData['time']);
      $Contact=$ArrayData['contact'];
      $Edit=$ArrayData['edit_by'];
  echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";    
      
  echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
          <div class='form-group'>
          <label for='linkedin' class='col-sm-3 control-label'>linkedin</label>
          <div class='col-sm-3'> 
          <input type='text' class='form-control' name='linkedin' id='linkedin' placeholder='".$Contact."' required=''></div></div>

          <div class='form-group last'>
          <div class='col-sm-offset-3 col-sm-9'>
          <button type='submit' name='updatelinkedin' class='btn btn-success btn-xs'>Change linkedin</button>
          <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
  </div></div></form>";
  }else{ //linkedin нету
echo " <form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  <div class='form-group'>
  <label for='linkedin' class='col-sm-3 control-label'>linkedin</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='linkedin' id='linkedin' placeholder='Введите linkedin' required=''></div></div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addlinkedin' class='btn btn-success btn-xs'>Add linkedin</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  } 
  
break;
case 'MapLocPoints': //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
 echo "<div class='myShapka'>Map locations</div>";
if(CheckMapContent()){ //Text в блок карта   (есть)
      $ArrayData=CheckMapContent(TRUE);   
if(!isset($ArrayData[1]['latitude'])){ //точек на карту одна
$data=date("d.m.y",$ArrayData[0]['time']);
$Edit=$ArrayData[0]['edit_by']; 
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";
echo "
<form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  
  <div class='form-group'>
      <label for='id' class='col-sm-3 control-label'>Point ID</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='fake_id' value='0' disabled></div></div>

  <input type='hidden'  name='id' value='{$ArrayData[0]['id']}'>

  <div class='form-group'>
      <label for='lat' class='col-sm-3 control-label'>Latitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lat' placeholder='50.439148' value='{$ArrayData[0]['latitude']}' required=''></div></div>
  
  <div class='form-group'>
      <label for='lng' class='col-sm-3 control-label'>Longitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lng' placeholder='30.523342' value='{$ArrayData[0]['longitude']}' required=''></div></div>
  
   <div class='form-group'>
      <label for='loc_name' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name' placeholder='Header Ru' value='{$ArrayData[0]['loc_name']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name_ua' placeholder='Header Ua' value='{$ArrayData[0]['loc_name_ua']}' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address1' class='col-sm-3 control-label'>Short name Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1' placeholder='Short name Ru' value='{$ArrayData[0]['loc_address1']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1_ua' placeholder='Short name Ua' value='{$ArrayData[0]['loc_address1_ua']}' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address2' class='col-sm-3 control-label'>Address Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2' placeholder='Address Ru' value='{$ArrayData[0]['loc_address2']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2_ua' placeholder='Address Ua' value='{$ArrayData[0]['loc_address2_ua']}' required=''>
      </div>
  </div>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updateMapLocContent' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=MapLocPoints&delMapLocContentID={$ArrayData[0]['id']}'>Remove</a>
</div></div></form>";

echo " <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
          
  <div class='form-group'>
      <label for='lat' class='col-sm-3 control-label'>Latitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lat' placeholder='50.439148' required=''></div></div>
  
  <div class='form-group'>
      <label for='lng' class='col-sm-3 control-label'>Longitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lng' placeholder='30.523342' required=''></div></div>
  
   <div class='form-group'>
      <label for='loc_name' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name' placeholder='Header Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name_ua' placeholder='Header Ua' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address1' class='col-sm-3 control-label'>Short name Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1' placeholder='Short name Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1_ua' placeholder='Short name Ua' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address2' class='col-sm-3 control-label'>Address Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2' placeholder='Address Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2_ua' placeholder='Address Ua' required=''>
      </div>
  </div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addMapLocContent' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
}else{ //точек на карту больше одной
$i=0;
foreach($ArrayData as $SomeArr){
$data=date("d.m.y",$SomeArr['time']);
$Edit=$SomeArr['edit_by']; 
echo "<div style='text-align:center;'>Last editing $data, user: $Edit</div>";
echo "
<form class='form-horizontal' role='form' action='' method='post' style='margin-top:30px;'>
  
  <div class='form-group'>
      <label for='id' class='col-sm-3 control-label'>Point ID</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='fake_id' value='$i' disabled></div></div>

  <input type='hidden'  name='id' value='{$SomeArr['id']}'>

  <div class='form-group'>
      <label for='lat' class='col-sm-3 control-label'>Latitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lat' placeholder='50.439148' value='{$SomeArr['latitude']}' required=''></div></div>
  
  <div class='form-group'>
      <label for='lng' class='col-sm-3 control-label'>Longitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lng' placeholder='30.523342' value='{$SomeArr['longitude']}' required=''></div></div>
  
   <div class='form-group'>
      <label for='loc_name' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name' placeholder='Header Ru' value='{$SomeArr['loc_name']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name_ua' placeholder='Header Ua' value='{$SomeArr['loc_name_ua']}' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address1' class='col-sm-3 control-label'>Short name Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1' placeholder='Short name Ru' value='{$SomeArr['loc_address1']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1_ua' placeholder='Short name Ua' value='{$SomeArr['loc_address1_ua']}' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address2' class='col-sm-3 control-label'>Address Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2' placeholder='Address Ru' value='{$SomeArr['loc_address2']}' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2_ua' placeholder='Address Ua' value='{$SomeArr['loc_address2_ua']}' required=''>
      </div>
  </div>

  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='updateMapLocContent' class='btn btn-success btn-xs'>Change</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php?Page=MapLocPoints&delMapLocContentID={$SomeArr['id']}'>Remove</a>
</div></div></form>";$i++;} 

echo " <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
          
  <div class='form-group'>
      <label for='lat' class='col-sm-3 control-label'>Latitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lat' placeholder='50.439148' required=''></div></div>
  
  <div class='form-group'>
      <label for='lng' class='col-sm-3 control-label'>Longitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lng' placeholder='30.523342' required=''></div></div>
  
   <div class='form-group'>
      <label for='loc_name' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name' placeholder='Header Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name_ua' placeholder='Header Ua' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address1' class='col-sm-3 control-label'>Short name Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1' placeholder='Short name Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1_ua' placeholder='Short name Ua' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address2' class='col-sm-3 control-label'>Address Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2' placeholder='Address Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2_ua' placeholder='Address Ua' required=''>
      </div>
  </div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addMapLocContent' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
}
  }else{ //карт нету
echo " <form class='form-horizontal' role='form' action='' enctype='multipart/form-data' method='post' style='margin-top:30px;'>
          
  <div class='form-group'>
      <label for='lat' class='col-sm-3 control-label'>Latitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lat' placeholder='50.439148' required=''></div></div>
  
  <div class='form-group'>
      <label for='lng' class='col-sm-3 control-label'>Longitude</label>
  <div class='col-sm-3'> <input type='text' class='form-control' name='lng' placeholder='30.523342' required=''></div></div>
  
   <div class='form-group'>
      <label for='loc_name' class='col-sm-3 control-label'>Header Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name' placeholder='Header Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_name_ua' placeholder='Header Ua' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address1' class='col-sm-3 control-label'>Short name Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1' placeholder='Short name Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address1_ua' placeholder='Short name Ua' required=''>
      </div>
  </div>
  
   <div class='form-group'>
      <label for='loc_address2' class='col-sm-3 control-label'>Address Ru/En</label>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2' placeholder='Address Ru' required=''>
      </div>
      <div class='col-sm-3'> 
          <input type='text' class='form-control' name='loc_address2_ua' placeholder='Address Ua' required=''>
      </div>
  </div>
  
  <div class='form-group last'>
  <div class='col-sm-offset-3 col-sm-9'>
  <button type='submit' name='addMapLocContent' class='btn btn-success btn-xs'>Add</button>
  <a class='btn btn-default btn-xs' href='{$config['sitelink']}admin/index.php'>Cancel</a>
</div></div></form>";
  }
  
break; 
  

default: echo "<div class='myShapka'>Choose one of pages in menu</div>";
}


}