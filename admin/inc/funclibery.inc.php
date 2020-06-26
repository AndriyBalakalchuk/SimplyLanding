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
  $arrUsersData = selectFromTable('Users', array('login','email','accesslevel','signature','edited'));


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
                        <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."usmanage.php?strPage=Password&strUsSign={$arrUserData['signature']}' title='change password'><i class='fa fa-lock'></i></a>
                        <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."usmanage.php?strPage=Email&strUsSign={$arrUserData['signature']}' title='change email'><i class='fa fa-at'></i></a>
                        <a class='btn btn-warning btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."usmanage.php?strPage=Name&strUsSign={$arrUserData['signature']}' title='change name'><i class='fa fa-address-book-o'></i></a>
                        <a class='btn btn-danger btn-sm btnModal $strVisibl' style='margin: 0px;' href='".$config['sitelink']."usmanage.php?strPage=accesslevel&strUsSign={$arrUserData['signature']}' title='change accesslevel'><i class='fa fa-level-up'></i></a>
                        <a class='btn btn-danger btn-sm btnModal' style='margin: 0px;' href='".$config['sitelink']."usmanage.php?strPage=remove&strUsSign={$arrUserData['signature']}' title='delete user'><i class='fa fa-trash'></i></a>
                      </div>
                    </div>";
    }
  }

  if(!isset($arrUsersData[1])){//если нету строки в массиве кроме первой - значит есть только админ
    $strResult = 'Empty User list';
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


//отправка письма с уведомлением
function sendNotification($strEmail, $strSubject, $strMessage){

  //// Заголовки
  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=utf-8\r\n";
  //$headers .= "From: zeos.ua@gmail.com\r\n";

  //// Приоритеты важности письма
  $headers .= "X-MSMail-Priority: High\r\n";
  $headers .= "X-Priority: 1\r\n";
  $headers .= "Priority: Urgent\r\n";
  $headers .= "Importance: High\r\n";

  //// Название программы с которой было отправлено сообщение
  $headers .= "X-Mailer: WJM\r\n";

  //// Отправка c проверкой
  if (mail($strEmail, $strSubject, $strMessage, $headers)) {
      return true;
  }else{
      return false;
  }
}

//создаем таблицу в которую выводим задачи (общий лог)
function getHTMLofTasks($intUserPermission, $intStatusOfJob){
  global $config_priority;

  $arrColumnsClass = array(); // содержит класы для коронок гость или сотрудник

  if($intUserPermission == 1){//если пользователь менеджер, отображать для менелдера
    $arrColumnsClass[1] = array('col-sm-4','col-sm-4','col-sm-1','col-sm-1','col-sm-2');//классы для менеджера на список со статусом 1
    $arrColumnsClass[2] = array('col-sm-2','col-sm-2','col-sm-1','col-sm-1','col-sm-1','col-sm-2','col-sm-3');//классы для менеджера на список со статусом 2
    $arrColumnsClass[3] = array('col-sm-2','col-sm-2','col-sm-2','col-sm-1','col-sm-1','col-sm-2','col-sm-2');//классы для менеджера на список со статусом 3
    $arrColumnsClass[4] = array('col-sm-2','col-sm-2','col-sm-2','col-sm-1','col-sm-1','col-sm-2','col-sm-2');//классы для менеджера на список со статусом 4
    $arrColumnsClass[5] = array('col-sm-1','col-sm-2','col-sm-1','col-sm-2','col-sm-1','col-sm-1','col-sm-2','col-sm-2');//классы для менеджера на список со статусом 5
    $arrColumnsClass[6] = array('col-sm-2','col-sm-2','col-sm-2','col-sm-2','col-sm-2','col-sm-2');//классы для менеджера на список со статусом 6
    $strHider = '';
    $strHiderEmp = '';
    $strHiderManager = 'hideMe';
    $strWhoApprove = 'submitManag';
  }elseif ($intUserPermission == 2) { //если пользователь работник, отображать для работника
    $arrColumnsClass[1] = array('col-sm-4','col-sm-4','col-sm-1','col-sm-1','col-sm-2');//классы для сотрудника на список со статусом 1
    $arrColumnsClass[2] = array('col-sm-2','col-sm-2','col-sm-1','col-sm-1','col-sm-1','col-sm-2','col-sm-3');//классы для сотрудника на список со статусом 2
    $arrColumnsClass[3] = array('col-sm-2','col-sm-2','col-sm-2','col-sm-1','col-sm-1','col-sm-2','col-sm-2');//классы для сотрудника на список со статусом 3
    $arrColumnsClass[4] = array('col-sm-2','col-sm-2','col-sm-2','col-sm-1','col-sm-1','col-sm-2','col-sm-2');//классы для сотрудника на список со статусом 4
    $arrColumnsClass[5] = array('col-sm-1','col-sm-2','col-sm-1','col-sm-2','col-sm-1','col-sm-1','col-sm-2','col-sm-2');//классы для сотрудника на список со статусом 5
    $arrColumnsClass[6] = array('col-sm-2','col-sm-2','col-sm-2','col-sm-2','col-sm-2','col-sm-2');//классы для сотрудника на список со статусом 6
    $strHider = '';
    $strHiderEmp = 'hideMe';
    $strHiderManager = '';
    $strWhoApprove = 'submitEmpl';
  }else { // юзер = гость, скрыть почти все
    $arrColumnsClass[1] = array('col-sm-4','col-sm-7','hideMe','hideMe','col-sm-1');//классы для гостя на список со статусом 1
    $arrColumnsClass[2] = array('col-sm-2','col-sm-3','col-sm-2','col-sm-2','col-sm-2','hideMe','col-sm-1');//классы для гостя на список со статусом 2
    $arrColumnsClass[3] = array('col-sm-2','col-sm-3','col-sm-2','col-sm-2','col-sm-2','hideMe','col-sm-1');//классы для гостя на список со статусом 3
    $arrColumnsClass[4] = array('col-sm-2','col-sm-3','col-sm-2','col-sm-2','col-sm-2','hideMe','col-sm-1');//классы для гостя на список со статусом 4
    $arrColumnsClass[5] = array('col-sm-2','col-sm-2','col-sm-1','col-sm-2','col-sm-2','col-sm-2','hideMe','col-sm-1');//классы для гостя на список со статусом 5
    $arrColumnsClass[6] = array('col-sm-2','col-sm-3','col-sm-3','col-sm-3','hideMe','col-sm-1');//классы для гостя на список со статусом 6
    $strHider = 'hideMe';
    $strHiderEmp = '';
    $strHiderManager = '';
    $strWhoApprove = '';
  }

  $arrTasksRows = selectFromTable('big_task_log', array('due_by',
                                                        'date_added',
                                                        'date_created',
                                                        'date_started',
                                                        'date_completed',
                                                        'canceled_in_date',
                                                        'id',
                                                        'status',
                                                        'job_name',
                                                        'work_was_started',
                                                        'task_priority',
                                                        'customer_name',
                                                        'site_address',
                                                        'contact_phone_number_and_name',
                                                        'notes_or_more_detail',
                                                        'staff_initial',
                                                        'managers_comment',
                                                        'gsn_or_id_of_machine'));

  if($arrTasksRows === false){return array('empty now','empty now','empty now','empty now','empty now','empty now','','');}

  $i = 0;
  //цикл по строкам
  foreach($arrTasksRows as $arrTaskRow){
    //если в ячейке со статусом задачи что-то есть то обрабатываем
    if($arrTaskRow['status'] != ""){
      //перехватить дату и записать ее в виде строки старт
      //дата получения данной заявки
      if($arrTaskRow['date_added']*1>0){$arrTaskRow['date_added'] = getNormalDate($arrTaskRow['date_added']);}
      //дата когда должно быть сделано
      if($arrTaskRow['due_by']*1>0){$arrTaskRow['due_by'] = getNormalDate($arrTaskRow['due_by']);}
      //дата передачи на работника работы
      if($arrTaskRow['date_created']*1>0){$arrTaskRow['date_created'] = getNormalDate($arrTaskRow['date_created']);}
      //дата старта работы
      if($arrTaskRow['date_started']*1>0){$arrTaskRow['date_started'] = getNormalDate($arrTaskRow['date_started']);}
      //дата завершения работы
      if($arrTaskRow['date_completed']*1>0){$arrTaskRow['date_completed'] = getNormalDate($arrTaskRow['date_completed']);}
      //дата отмены работы
      if($arrTaskRow['canceled_in_date']*1>0){$arrTaskRow['canceled_in_date'] = getNormalDate($arrTaskRow['canceled_in_date']);}
      //перехватить дату и записать ее в виде строки конец
      switch ($arrTaskRow['status']) {
        case 1:
          $srtHtmlMWAIT = "<div class='row JustRowDiv'>
                              <div class='{$arrColumnsClass[1][0]}'>
                                ".$arrTaskRow['id']."
                              </div>
                              <div class='{$arrColumnsClass[1][1]}'>
                                ".$arrTaskRow['job_name']."
                              </div>
                              <div class='{$arrColumnsClass[1][2]}'>
                                ".$config_priority[$arrTaskRow['task_priority']]."
                              </div>
                              <div class='{$arrColumnsClass[1][3]}'>
                                ".$arrTaskRow['due_by']."
                              </div>
                              <div class='{$arrColumnsClass[1][4]}'>
                                <a class='btn btn-info btn-sm' id='ModalInfoAct".$i."' title='Details'><i class='fa fa-info-circle'></i></a>
                                <a class='btn btn-warning btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."definetask.php?strId=".$arrTaskRow['id']."&strPrior=".$arrTaskRow['task_priority']."&strDueBy=".$arrTaskRow['due_by']."' title='Define job priority and worker'><i class='fa fa-cogs'></i></a>
                                <a class='btn btn-danger btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTaskRow['id']."' title='Cancel job'><i class='fa fa-trash'></i></a>
                              </div>
                            </div>".$srtHtmlMWAIT;
          $strBigModalHTML = '<div class="modal fade bd-example-modal-sm" id="ModalInfo'.$i.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
                                 <strong>Job name:</strong> '.$arrTaskRow['job_name'].'<br>
                                 <strong>Custumer name:</strong> '.$arrTaskRow['customer_name'].'<br>
                                 <strong>Machine ID:</strong> '.$arrTaskRow['gsn_or_id_of_machine'].'<br>
                                 <strong>Priority:</strong> '.$config_priority[$arrTaskRow['task_priority']].'<br>
                                 <strong>Date when all must be done:</strong> '.$arrTaskRow['due_by'].'<br>
                                 <strong>Site/address:</strong> '.$arrTaskRow['site_address'].'<br>
                                 <strong>Contact phone number (and name):</strong> '.$arrTaskRow['contact_phone_number_and_name'].'<br>
                                 <strong>Job Details:</strong> '.$arrTaskRow['notes_or_more_detail'].'<br>
                               </div>
                             </div>
                           </div>' . $strBigModalHTML;
          $strAdditionalModalScript = '$("#ModalInfoAct'.$i.'").click(function() {$("#ModalInfo'.$i.'").modal(\'show\');});'."\n" . $strAdditionalModalScript;
          break;
        case 2:
          $srtHtmlWWAIT = "<div class='row JustRowDiv'>
                              <div class='{$arrColumnsClass[2][0]}'>
                                ".$arrTaskRow['id']."
                              </div>
                              <div class='{$arrColumnsClass[2][1]}'>
                                ".$arrTaskRow['job_name']."
                              </div>
                              <div class='{$arrColumnsClass[2][2]}'>
                                ".$config_priority[$arrTaskRow['task_priority']]."
                              </div>
                              <div class='{$arrColumnsClass[2][3]}'>
                                ".$arrTaskRow['date_created']."
                              </div>
                              <div class='{$arrColumnsClass[2][4]}'>
                                ".$arrTaskRow['staff_initial']."
                              </div>
                              <div class='{$arrColumnsClass[2][5]}'>
                                ".$arrTaskRow['managers_comment']."
                              </div>
                              <div class='{$arrColumnsClass[2][6]}'>
                                <a class='btn btn-info btn-sm' id='ModalInfoAct".$i."' title='Details'><i class='fa fa-info-circle'></i></a>
                                <a class='btn btn-success btn-sm btnModal $strHider' href='".$config['sitelink']."index.php?strDo=Start&strId=".$arrTaskRow['id']."' title='Quick job start' ><i class='fa fa-play'></i></a>
                                <a class='btn btn-warning btn-sm $strHider $strHiderEmp' id='ModalFinishAct".$i."' title='Quick job finish'><i class='fa fa-bolt'></i></a>
                                <a class='btn btn-info btn-sm $strHider' target='_self' href='".$config['sitelink']."jobsheet.php?strId=".$arrTaskRow['id']."' title='Open job spreadsheet'><i class='fa fa-folder-open'></i></a>
                                <a class='btn btn-warning btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."definetask.php?strId=".$arrTaskRow['id']."&strPrior=".$arrTaskRow['task_priority']."&strDueBy=".$arrTaskRow['due_by']."' title='Change job priority or worker'><i class='fa fa-cogs'></i></a>
                                <a class='btn btn-danger btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTaskRow['id']."' title='Cancel job'><i class='fa fa-trash'></i></a>
                              </div>
                            </div>".$srtHtmlWWAIT;
          $strBigModalHTML = '<div class="modal fade bd-example-modal-sm" id="ModalInfo'.$i.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
                                 <strong>Job name:</strong> '.$arrTaskRow['job_name'].'<br>
                                 <strong>Custumer name:</strong> '.$arrTaskRow['customer_name'].'<br>
                                 <strong>Machine ID:</strong> '.$arrTaskRow['gsn_or_id_of_machine'].'<br>
                                 <strong>Priority:</strong> '.$config_priority[$arrTaskRow['task_priority']].'<br>
                                 <strong>Date when all must be done:</strong> '.$arrTaskRow['due_by'].'<br>
                                 <strong>Site/address:</strong> '.$arrTaskRow['site_address'].'<br>
                                 <strong>Contact phone number (and name):</strong> '.$arrTaskRow['contact_phone_number_and_name'].'<br>
                                 <strong>Job Details:</strong> '.$arrTaskRow['notes_or_more_detail'].'<br>
                                 <strong>Staff Initial:</strong> '.$arrTaskRow['staff_initial'].'<br>
                                 <strong>Date Created:</strong> '.$arrTaskRow['date_created'].'<br>
                                 <span class="'.$strHider.'"><strong>Manager comment:</strong><br>'.$arrTaskRow['managers_comment'].'<br></span>
                               </div>
                             </div>
                           </div>
                           <div class="modal fade bd-example-modal-sm" id="ModalFinish'.$i.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-sm"><div class="modal-content">
                                 <div class="container" style="padding: 7px 0px 7px 0px;">
                                   Please, approve that you want to mark job <strong>"'.$arrTaskRow['id'].'"</strong> as done.
                                   <!-- форма старт -->
                                   <form action="'.$config['sitelink'].'jobsheet.php?strId='.$arrTaskRow['id'].'" method="post">
                                      <input id="" name="strTaskId" type="hidden" value="'.$arrTaskRow['id'].'">
                                      <input id="" name="'.$strWhoApprove.'" type="hidden" value="true">
                                      <button name="strInnFromForm" value="SaveJobSheet" id="" type="submit" class="btn btn-success btn-sm btnModal"><i class="fa fa-check-circle"></i>  Mark task as DONE</button>
                                   </form>
                                   <!-- форма конец -->
                                   <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                                 </div>
                               </div>
                             </div>
                           </div>' . $strBigModalHTML;
          $strAdditionalModalScript = '$("#ModalInfoAct'.$i.'").click(function() {$("#ModalInfo'.$i.'").modal(\'show\');});'."\n".
                                      '$("#ModalFinishAct'.$i.'").click(function() {$("#ModalFinish'.$i.'").modal(\'show\');});'."\n".
                                      '$("#clCurMod'.$i.'").click(function() {$("#ModalFinish'.$i.'").modal(\'hide\');});'."\n" . $strAdditionalModalScript;
          break;
        case 3:
          $srtHtmlINPROG = "<div class='row JustRowDiv'>
                              <div class='{$arrColumnsClass[3][0]}'>
                                ".$arrTaskRow['id']."
                              </div>
                              <div class='{$arrColumnsClass[3][1]}'>
                                ".$arrTaskRow['job_name']."
                              </div>
                              <div class='{$arrColumnsClass[3][2]}'>
                                ".$config_priority[$arrTaskRow['task_priority']]."
                              </div>
                              <div class='{$arrColumnsClass[3][3]}'>
                                ".$arrTaskRow['date_created']."
                              </div>
                              <div class='{$arrColumnsClass[3][4]}'>
                                ".$arrTaskRow['staff_initial']."
                              </div>
                              <div class='{$arrColumnsClass[3][5]}'>
                                ".$arrTaskRow['date_started']."
                              </div>
                              <div class='{$arrColumnsClass[3][6]}'>
                                <a class='btn btn-info btn-sm' id='ModalInfoAct".$i."' title='Details'><i class='fa fa-info-circle'></i></a>
                                <a class='btn btn-warning btn-sm $strHider' id='ModalFinishAct".$i."' title='Quick job finish'><i class='fa fa-bolt'></i></a>
                                <a class='btn btn-info btn-sm $strHider' target='_self' href='".$config['sitelink']."jobsheet.php?strId=".$arrTaskRow['id']."' title='Open job spreadsheet'><i class='fa fa-folder-open'></i></a>
                                <a class='btn btn-warning btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."definetask.php?strId=".$arrTaskRow['id']."&strPrior=".$arrTaskRow['task_priority']."&strDueBy=".$arrTaskRow['due_by']."' title='Change job priority or worker'><i class='fa fa-cogs'></i></a>
                                <a class='btn btn-danger btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTaskRow['id']."' title='Cancel job'><i class='fa fa-trash'></i></a>
                              </div>
                            </div>".$srtHtmlINPROG;
          $strBigModalHTML = '<div class="modal fade bd-example-modal-sm" id="ModalInfo'.$i.'" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                  <div class="modal-content">
                                    <strong>Job name:</strong> '.$arrTaskRow['job_name'].'<br>
                                    <strong>Custumer name:</strong> '.$arrTaskRow['customer_name'].'<br>
                                    <strong>Machine ID:</strong> '.$arrTaskRow['gsn_or_id_of_machine'].'<br>
                                    <strong>Priority:</strong> '.$config_priority[$arrTaskRow['task_priority']].'<br>
                                    <strong>Date when all must be done:</strong> '.$arrTaskRow['due_by'].'<br>
                                    <strong>Site/address:</strong> '.$arrTaskRow['site_address'].'<br>
                                    <strong>Contact phone number (and name):</strong> '.$arrTaskRow['contact_phone_number_and_name'].'<br>
                                    <strong>Job Details:</strong> '.$arrTaskRow['notes_or_more_detail'].'<br>
                                    <strong>Staff Initial:</strong> '.$arrTaskRow['staff_initial'].'<br>
                                    <strong>Date Created:</strong> '.$arrTaskRow['date_created'].'<br>
                                    <span class="'.$strHider.'"><strong>Date Started:</strong> <br>'.$arrTaskRow['date_started'].'<br></span>
                                    <span class="'.$strHider.'"><strong>Manager comment:</strong> <br>'.$arrTaskRow['managers_comment'].'<br></span>
                                  </div>
                                </div>
                              </div>
                              <div class="modal fade bd-example-modal-sm" id="ModalFinish'.$i.'" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                  <div class="modal-content">
                                    <div class="container" style="padding: 7px 0px 7px 0px;">
                                        Please, approve that you want to mark job <strong>"'.$arrTaskRow['id'].'"</strong> as done.
                                        <!-- форма старт -->
                                        <form action="'.$config['sitelink'].'jobsheet.php?strId='.$arrTaskRow['id'].'" method="post">
                                          <input id="" name="strTaskId" type="hidden" value="'.$arrTaskRow['id'].'">
                                          <input id="" name="'.$strWhoApprove.'" type="hidden" value="true">
                                          <button name="strInnFromForm" value="SaveJobSheet" id="" type="submit" class="btn btn-success btn-sm btnModal"><i class="fa fa-check-circle"></i>  Mark task as DONE</button>
                                        </form>
                                        <!-- форма конец -->
                                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                                      </div>
                                    </div>
                                  </div>
                                </div>' . $strBigModalHTML;
          $strAdditionalModalScript = '$("#ModalInfoAct'.$i.'").click(function() {$("#ModalInfo'.$i.'").modal(\'show\');});'."\n".
                                      '$("#ModalFinishAct'.$i.'").click(function() {$("#ModalFinish'.$i.'").modal(\'show\');});'."\n".
                                      '$("#clCurMod'.$i.'").click(function() {$("#ModalFinish'.$i.'").modal(\'hide\');});'."\n" . $strAdditionalModalScript;
          break;
        case 4:
          $srtHtmlONCHECK = "<div class='row JustRowDiv'>
                                <div class='{$arrColumnsClass[4][0]}'>
                                  ".$arrTaskRow['id']."
                                </div>
                                <div class='{$arrColumnsClass[4][1]}'>
                                  ".$arrTaskRow['job_name']."
                                </div>
                                <div class='{$arrColumnsClass[4][2]}'>
                                  ".$config_priority[$arrTaskRow['task_priority']]."
                                </div>
                                <div class='{$arrColumnsClass[4][3]}'>
                                  ".$arrTaskRow['date_created']."
                                </div>
                                <div class='{$arrColumnsClass[4][4]}'>
                                  ".$arrTaskRow['staff_initial']."
                                </div>
                                <div class='{$arrColumnsClass[4][5]}'>
                                  ".$arrTaskRow['date_started']."
                                </div>
                                <div class='{$arrColumnsClass[4][6]}'>
                                  <a class='btn btn-info btn-sm' id='ModalInfoAct".$i."' title='Details'><i class='fa fa-info-circle'></i></a>
                                  <a class='btn btn-info btn-sm $strHider' target='_self' href='".$config['sitelink']."jobsheet.php?strId=".$arrTaskRow['id']."' title='Open job spreadsheet'><i class='fa fa-folder-open'></i></a>
                                  <a class='btn btn-success btn-sm $strHider $strHiderEmp' id='ModalFinishAct".$i."' title='Mark task as DONE'><i class='fa fa-check-circle'></i></a>
                                  <a class='btn btn-danger btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTaskRow['id']."' title='Cancel job'><i class='fa fa-trash'></i></a>
                                </div>
                              </div>".$srtHtmlONCHECK;
          $strBigModalHTML = '<div class="modal fade bd-example-modal-sm" id="ModalInfo'.$i.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
                                 <strong>Job name:</strong> '.$arrTaskRow['job_name'].'<br>
                                 <strong>Custumer name:</strong> '.$arrTaskRow['customer_name'].'<br>
                                 <strong>Machine ID:</strong> '.$arrTaskRow['gsn_or_id_of_machine'].'<br>
                                 <strong>Priority:</strong> '.$config_priority[$arrTaskRow['task_priority']].'<br>
                                 <strong>Date when all must be done:</strong> '.$arrTaskRow['due_by'].'<br>
                                 <strong>Site/address:</strong> '.$arrTaskRow['site_address'].'<br>
                                 <strong>Contact phone number (and name):</strong> '.$arrTaskRow['contact_phone_number_and_name'].'<br>
                                 <strong>Job Details:</strong> '.$arrTaskRow['notes_or_more_detail'].'<br>
                                 <strong>Staff Initial:</strong> '.$arrTaskRow['staff_initial'].'<br>
                                 <strong>Date Created:</strong> '.$arrTaskRow['date_created'].'<br>
                                 <span class="'.$strHider.'"><strong>Date Started:</strong> <br>'.$arrTaskRow['date_started'].'<br></span>
                                 <span class="'.$strHider.'"><strong>Manager comment:</strong> <br>'.$arrTaskRow['managers_comment'].'<br></span>
                               </div>
                             </div>
                           </div>
                           <div class="modal fade bd-example-modal-sm" id="ModalFinish'.$i.'" tabindex="-1" role="dialog" aria-hidden="true">
                             <div class="modal-dialog modal-sm">
                               <div class="modal-content">
                                 <div class="container" style="padding: 7px 0px 7px 0px;">
                                     Please, approve that you want to mark job <strong>"'.$arrTaskRow['id'].'"</strong> as done.
                                     <!-- форма старт -->
                                     <form action="'.$config['sitelink'].'jobsheet.php?strId='.$arrTaskRow['id'].'" method="post">
                                       <input id="" name="strTaskId" type="hidden" value="'.$arrTaskRow['id'].'">
                                       <input id="" name="'.$strWhoApprove.'" type="hidden" value="true">
                                       <button name="strInnFromForm" value="SaveJobSheet" id="" type="submit" class="btn btn-success btn-sm btnModal"><i class="fa fa-check-circle"></i>  Mark task as DONE</button>
                                     </form>
                                     <!-- форма конец -->
                                     <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                                   </div>
                                 </div>
                               </div>
                             </div>' . $strBigModalHTML;
          $strAdditionalModalScript = '$("#ModalInfoAct'.$i.'").click(function() {$("#ModalInfo'.$i.'").modal(\'show\');});'."\n".
                                      '$("#ModalFinishAct'.$i.'").click(function() {$("#ModalFinish'.$i.'").modal(\'show\');});'."\n". $strAdditionalModalScript;
          break;
        case 5:
          $srtHtmlDONE = "<div class='row JustRowDiv'>
                            <div class='{$arrColumnsClass[5][0]}'>
                              ".$arrTaskRow['id']."
                            </div>
                            <div class='{$arrColumnsClass[5][1]}'>
                              ".$arrTaskRow['job_name']."
                            </div>
                            <div class='{$arrColumnsClass[5][2]}'>
                              ".$config_priority[$arrTaskRow['task_priority']]."
                            </div>
                            <div class='{$arrColumnsClass[5][3]}'>
                              ".$arrTaskRow['date_created']."
                            </div>
                            <div class='{$arrColumnsClass[5][4]}'>
                              ".$arrTaskRow['date_completed']."
                            </div>
                            <div class='{$arrColumnsClass[5][5]}'>
                              ".$arrTaskRow['staff_initial']."
                            </div>
                            <div class='{$arrColumnsClass[5][6]}'>
                              ".$arrTaskRow['date_started']."
                            </div>
                            <div class='{$arrColumnsClass[5][7]}'>
                              <a class='btn btn-info btn-sm' id='ModalInfoAct".$i."' title='Details'><i class='fa fa-info-circle'></i></a>
                              <a class='btn btn-info btn-sm $strHider' target='_self' href='".$config['sitelink']."jobsheet.php?strId=".$arrTaskRow['id']."' title='Open job spreadsheet'><i class='fa fa-folder-open'></i></a>
                            </div>
                          </div>".$srtHtmlDONE;
          $strBigModalHTML = '<div class="modal fade bd-example-modal-sm" id="ModalInfo'.$i.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
                                 <strong>Job name:</strong> '.$arrTaskRow['job_name'].'<br>
                                 <strong>Custumer name:</strong> '.$arrTaskRow['customer_name'].'<br>
                                 <strong>Machine ID:</strong> '.$arrTaskRow['gsn_or_id_of_machine'].'<br>
                                 <strong>Priority:</strong> '.$config_priority[$arrTaskRow['task_priority']].'<br>
                                 <strong>Date when all must be done:</strong> '.$arrTaskRow['due_by'].'<br>
                                 <strong>Site/address:</strong> '.$arrTaskRow['site_address'].'<br>
                                 <strong>Contact phone number (and name):</strong> '.$arrTaskRow['contact_phone_number_and_name'].'<br>
                                 <strong>Job Details:</strong> '.$arrTaskRow['notes_or_more_detail'].'<br>
                                 <strong>Staff Initial:</strong> '.$arrTaskRow['staff_initial'].'<br>
                                 <strong>Date Created:</strong> '.$arrTaskRow['date_created'].'<br>
                                 <span class="'.$strHider.'"><strong>Date Started:</strong> <br>'.$arrTaskRow['date_started'].'<br></span>
                                 <strong>Date completed:</strong> '.$arrTaskRow['date_completed'].'<br>
                                 <span class="'.$strHider.'"><strong>Manager comment:</strong> <br>'.$arrTaskRow['managers_comment'].'<br></span>
                               </div>
                             </div>
                           </div>' . $strBigModalHTML;
          $strAdditionalModalScript = '$("#ModalInfoAct'.$i.'").click(function() {$("#ModalInfo'.$i.'").modal(\'show\');});'."\n" . $strAdditionalModalScript;
          break;
        case 6:
          $srtHtmlCancel = "<div class='row JustRowDiv'>
                              <div class='{$arrColumnsClass[6][0]}'>
                                ".$arrTaskRow['id']."
                              </div>
                              <div class='{$arrColumnsClass[6][1]}'>
                                ".$arrTaskRow['job_name']."
                              </div>
                              <div class='{$arrColumnsClass[6][2]}'>
                                ".$arrTaskRow['date_created']."
                              </div>
                              <div class='{$arrColumnsClass[6][3]}'>
                                ".$arrTaskRow['canceled_in_date']."
                              </div>
                              <div class='{$arrColumnsClass[6][4]}'>
                                ".$arrTaskRow['date_started']."
                              </div>
                              <div class='{$arrColumnsClass[6][5]}'>
                                <a class='btn btn-info btn-sm' id='ModalInfoAct".$i."' title='Details'><i class='fa fa-info-circle'></i></a>
                                <a class='btn btn-success btn-sm btnModal $strHider $strHiderEmp' href='".$config['sitelink']."index.php?strDo=Redim&strId=".$arrTaskRow['id']."' title='Redim job'><i class='fa fa-recycle'></i></a>
                              </div>
                            </div>".$srtHtmlCancel;
          $strBigModalHTML = '<div class="modal fade bd-example-modal-sm" id="ModalInfo'.$i.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
                                 <strong>Job name:</strong> '.$arrTaskRow['job_name'].'<br>
                                 <strong>Custumer name:</strong> '.$arrTaskRow['customer_name'].'<br>
                                 <strong>Machine ID:</strong> '.$arrTaskRow['gsn_or_id_of_machine'].'<br>
                                 <strong>Priority:</strong> '.$config_priority[$arrTaskRow['task_priority']].'<br>
                                 <strong>Date when all must be done:</strong> '.$arrTaskRow['due_by'].'<br>
                                 <strong>Site/address:</strong> '.$arrTaskRow['site_address'].'<br>
                                 <strong>Contact phone number (and name):</strong> '.$arrTaskRow['contact_phone_number_and_name'].'<br>
                                 <strong>Job Details:</strong> '.$arrTaskRow['notes_or_more_detail'].'<br>
                                 <strong>Staff Initial:</strong> '.$arrTaskRow['staff_initial'].'<br>
                                 <strong>Date Created:</strong> '.$arrTaskRow['date_created'].'<br>
                                 <span class="'.$strHider.'"><strong>Date Started:</strong> <br>'.$arrTaskRow['date_started'].'<br></span>
                                 <strong>Canceled:</strong> '.$arrTaskRow['canceled_in_date'].'<br>
                                 <span class="'.$strHider.'"><strong>Manager comment:</strong> <br>'.$arrTaskRow['managers_comment'].'<br></span>
                               </div>
                             </div>
                           </div>' . $strBigModalHTML;
          $strAdditionalModalScript = '$("#ModalInfoAct'.$i.'").click(function() {$("#ModalInfo'.$i.'").modal(\'show\');});'."\n" . $strAdditionalModalScript;
          break;
        default:
          $srtHtmlMWAIT = "ERROR, can't find. status undefined";
      }
    }
    $i++;
  }

  $arrResult = array();

  if($srtHtmlMWAIT != ""){$arrResult[1]=$srtHtmlMWAIT;}else{$arrResult[1]='empty now';}
  if($srtHtmlWWAIT != ""){$arrResult[2]=$srtHtmlWWAIT;}else{$arrResult[2]='empty now';}
  if($srtHtmlINPROG != ""){$arrResult[3]=$srtHtmlINPROG;}else{$arrResult[3]='empty now';}
  if($srtHtmlONCHECK != ""){$arrResult[4]=$srtHtmlONCHECK;}else{$arrResult[4]='empty now';}
  if($srtHtmlDONE != ""){$arrResult[5]=$srtHtmlDONE;}else{$arrResult[5]='empty now';}
  if($srtHtmlCancel != ""){$arrResult[6]=$srtHtmlCancel;}else{$arrResult[6]='empty now';}
  //для модальных окон с информацией
  if($strBigModalHTML != ""){$arrResult[7] = $strBigModalHTML;}else{$arrResult[7] = '';}
  if($strAdditionalModalScript != ""){$arrResult[8] = $strAdditionalModalScript;}else{$arrResult[8] = '';}

  return $arrResult;
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

//
function genTaskListForMe($intUserPermission, $strUserSing){
  $strResult = '';
  $srtHtmlMWAIT = '';
  $srtHtmlINPROG = '';
  $srtHtmlONCHECK = '';
  $srtHtmlWWAIT = '';

$arrTableData = selectFromTable('big_task_log', array('id','notes_or_more_detail','status','job_name','customer_name','gsn_or_id_of_machine','need_for_parts','staff_initial','task_priority','due_by'));
//проработка возможных действий для менеджера (допуск = 1)
// статуст 1 - отмена или кнопка для выбор приоритета и работника (создаст новый джоб шит) - отображаем так как нужно действие
// статуст 2 - отмена или заменить работника (опция доступна в общем списке, тут не отображается) - не отображаем
// статуст 3 - отмена или кнопка для выбор приоритета и работника (создаст новый джоб шит) - отображаем только если есть детали которые не подтверждены
// статуст 4 - отмена или подтвердить на статус пять, или отмена детелей(вернет на статус 3) - отображаем если нужно ок на статус пять
// статуст 5 - вернуть на статус 4 (опция доступна в общем списке, тут не отображается) -не отображать
// статуст 6 - возврат на статус на котором был отменен (опция доступна в общем списке, тут не отображается)
//цикл по строкам
if($intUserPermission==1){
  for ($i=0; $i < count($arrTableData); $i++) {
    switch ($arrTableData[$i]['status']) {
      case 1:
        $srtHtmlMWAIT = "<div class='row JustRowDiv'>
                           <div class='col-sm-3'>
                              ".$arrTableData[$i]['id']."
                           </div>
                           <div class='col-sm-4'>
                              ".$arrTableData[$i]['notes_or_more_detail']."
                           </div>
                           <div class='col-sm-5'>
                              <a class='btn btn-success btn-sm btnModal' href='".$config['sitelink']."definetask.php?strId=".$arrTableData[$i]['id']."&strPrior=".$arrTableData[$i]['task_priority']."&strDueBy=".getNormalDate($arrTableData[$i]['due_by'])."'><i class='fa fa-cogs'></i> Define task</a>
                              <a class='btn btn-danger btn-sm btnModal' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTableData[$i]['id']."'><i class='fa fa-trash'></i> Cancel</a>
                           </div>
                        </div>".$srtHtmlMWAIT;
        break;
      case 2:
        break;
      case 3:
        if($arrTableData[$i]['need_for_parts'] == 'true'){
          $srtHtmlINPROG = "<div class='row JustRowDiv'>
                              <div class='col-sm-3'>
                                ".$arrTableData[$i]['id']."
                              </div><div class='col-sm-4'>
                                ".$arrTableData[$i]['notes_or_more_detail']."
                              </div>
                              <div class='col-sm-5'>
                                <a class='btn btn-success btn-sm' target='_blank' href='".$config['sitelink']."jobsheet.php?strId=".$arrTableData[$i]['id']."'><i class='fa fa-tasks'></i> Approve parts purchaising</a>
                                <a class='btn btn-danger btn-sm btnModal' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTableData[$i]['id']."'><i class='fa fa-trash'></i> Cancel</a>
                              </div>
                            </div>".$srtHtmlINPROG;
        }
        break;
      case 4:
        $srtHtmlONCHECK = "<div class='row JustRowDiv'>
                              <div class='col-sm-3'>
                                ".$arrTableData[$i]['id']."
                              </div>
                              <div class='col-sm-4'>
                                ".$arrTableData[$i]['notes_or_more_detail']."
                              </div>
                              <div class='col-sm-5'>
                                <!-- форма старт -->
                                <form action='".$config['sitelink']."jobsheet.php?strId=".$arrTableData[$i]['id']."' method='post'>
                                  <input id='' name='strTaskId' type='hidden' value='".$arrTableData[$i]['id']."'>
                                  <input id='' name='submitManag' type='hidden' value='true'>
                                  <button name='strInnFromForm' value='SaveJobSheet' id='' type='submit' class='btn btn-success btn-sm btnModal'><i class='fa fa-check-circle'></i> Mark task as DONE</button>
                                </form>
                                <!-- форма конец -->
                                <a class='btn btn-danger btn-sm btnModal' href='".$config['sitelink']."index.php?strDo=Remove&strId=".$arrTableData[$i]['id']."'><i class='fa fa-trash'></i> Cancel</a>
                              </div>
                            </div>".$srtHtmlONCHECK;
        break;
      case 5:
        break;
      case 6:
        break;
      default:
        $srtHtmlMWAIT = "ERROR, can't find. status undefined";
    }
  }
}elseif($intUserPermission==2){
  for ($i=0; $i < count($arrTableData); $i++) {
    //если в ячейке со статусом задачи что-то есть то обрабатываем при условии что сошлись инициалы работника
   if($arrTableData[$i]['staff_initial'] == $strUserSing){
     switch ($arrTableData[$i]['status']) {
       case 1:
         break;
       case 2:
         $srtHtmlWWAIT = "<div class='row JustRowDiv'>
                            <div class='col-sm-3'>
                              ".$arrTableData[$i]['id']."
                            </div>
                            <div class='col-sm-4'>
                              ".$arrTableData[$i]['notes_or_more_detail']."</br>
                              Custumer name: ".$arrTableData[$i]['customer_name']."</br>
                              Machine ID: ".$arrTableData[$i]['gsn_or_id_of_machine']."
                            </div>
                          <div class='col-sm-5'>
                            <a class='btn btn-success btn-sm' target='_blank' href='".$config['sitelink']."jobsheet.php?strId=".$arrTableData[$i]['id']."'><i class='fa fa-file-excel-o'></i> Open job and start work</a>
                          </div>
                        </div>".$srtHtmlWWAIT;
         break;
       case 3:
         $srtHtmlINPROG = "<div class='row JustRowDiv'>
                              <div class='col-sm-3'>
                                ".$arrTableData[$i]['id']."
                              </div>
                              <div class='col-sm-4'>
                                ".$arrTableData[$i]['notes_or_more_detail']."</br>
                                Custumer name: ".$arrTableData[$i]['customer_name']."</br>
                                Machine ID: ".$arrTableData[$i]['gsn_or_id_of_machine']."
                              </div>
                              <div class='col-sm-5'>
                                <a class='btn btn-success btn-sm' target='_blank' href='".$config['sitelink']."jobsheet.php?strId=".$arrTableData[$i]['id']."'><i class='fa fa-file-excel-o'></i> Open job to continue work</a>
                              </div>
                            </div>".$srtHtmlINPROG;
         break;
       case 4:
         break;
       case 5:
         break;
       case 6:
         break;
       default:
         $srtHtmlMWAIT = "ERROR, can't find. status undefined";
     }
   }
  }
}

if($srtHtmlMWAIT!='' or $srtHtmlINPROG!='' or $srtHtmlONCHECK!='' or $srtHtmlWWAIT!=''){
  $strResult = "<div class='row headerDiv'><div class='col-sm-3'>Jobsheet Number</div><div class='col-sm-4'>Job info</div><div class='col-sm-5'>Job management</div></div>".$srtHtmlMWAIT.$srtHtmlWWAIT.$srtHtmlINPROG.$srtHtmlONCHECK;
}else{
  $strResult = 'all that necessary you can do already done';
}

 return $strResult;
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

//Верстаем ПДФ для счета заказчику
function getPdfClientCost($strTaskId, $strPath){
  global $config;
  // Require composer autoload
  require_once $strPath.'admin/lib/MPDF/vendor/autoload.php';

  include_once $strPath."admin/lib/MPDF/mpdf.php";
  //==============================================================
  // html код
  //==============================================================

  //получаем данные для вноса в счет
  $arrTableData = selectFromTable('big_task_log', array('staff_initial',
                                                        'date_started',
                                                        'customer_name',
                                                        'date_completed',
                                                        'work_spreadsheet',
                                                        'id',
                                                        'site_address',
                                                        'contact_phone_number_and_name'),
                                                        true, 'id', $strTaskId)[0];
 //нету такой задачи..
  if($arrTableData['id'] != $strTaskId){
    return false;
  }

  $arrTableData['work_spreadsheet'] = unserialize($arrTableData['work_spreadsheet']);
//**********************************************
//математика
//**********************************************
//получаем количесво часов из суммы всех в листе
  $intTotalHours = '0:00';
  for($i=0;$i<15;$i++){
    if(!empty(strpos($arrTableData['work_spreadsheet']['Hours'][$i],":"))){
      $intTotalHours = getSunTime($intTotalHours,$arrTableData['work_spreadsheet']['Hours'][$i]);
    }
  }

  $intOfficeHours = getDifTime($arrTableData['work_spreadsheet']['Travel return time'],$arrTableData['work_spreadsheet']['Travel out time']);

  $intTotalParts = 0;
  for($i=0;$i<12;$i++){
    if($arrTableData['work_spreadsheet']['Price'][$i] !=''){
      $intTotalParts = $intTotalParts + $arrTableData['work_spreadsheet']['Price'][$i];
    }
  }

  $intTotalParts = $intTotalParts*(getCleaInt($arrTableData['work_spreadsheet']['Parts markup'])/100+1);
  $intTotalHoursLab = (getNumberFromTime(getSunTime($intOfficeHours,$intTotalHours)))*$arrTableData['work_spreadsheet']['Hourly applied rate'];
  $intTotalMileage = $arrTableData['work_spreadsheet']['Staff mileage']*$arrTableData['work_spreadsheet']['Mileage rate/mile'];

  //делаем перенос строки если она слишком длинная
  if(strlen($arrTableData['site_address'])>32){
    $arrTextAddr = explode("★",wordwrap($arrTableData['site_address'], 32, "★"));
  }else{
    $arrTextAddr = array($arrTableData['site_address'],'');
  }
  if(strlen($arrTableData['contact_phone_number_and_name'])>32){
    $arrTextContacts = explode("★",wordwrap($arrTableData['contact_phone_number_and_name'], 32, "★"));
  }else{
    $arrTextContacts = array($arrTableData['contact_phone_number_and_name'],'');
  }

  $html = '<div style="width: 210mm; height: 297mm">
    <svg version="1.0" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="'.(210*3.779).'" height="'.(297*3.779).'" viewBox="0 0 612 792" enable-background="new 0 0 612 792" xml:space="preserve">
    <g id="Layer_1">
    	<g>
    		<rect x="326.882" y="52.852" fill="#FFFFFF" width="249.851" height="15.387"/>
    		<rect x="36" y="68.239" fill="#FFFFFF" width="186.838" height="15.387"/>
    		<rect x="326.882" y="68.239" fill="#FFFFFF" width="249.851" height="15.387"/>
    		<rect x="326.882" y="83.625" fill="#FFFFFF" width="67.408" height="15.387"/>
    		<rect x="530.572" y="83.625" fill="#FFFFFF" width="46.16" height="15.387"/>
    		<rect x="36" y="99.012" fill="#FFFFFF" width="186.838" height="92.32"/>
    		<rect x="326.882" y="99.012" fill="#FFFFFF" width="67.408" height="76.934"/>
    		<rect x="530.572" y="99.012" fill="#FFFFFF" width="46.16" height="15.387"/>
    		<rect x="394.29" y="114.399" fill="#FFFFFF" width="182.442" height="15.387"/>
    		<rect x="530.572" y="129.786" fill="#FFFFFF" width="46.16" height="46.16"/>
    		<rect x="326.882" y="175.946" fill="#FFFFFF" width="67.408" height="15.387"/>
    		<rect x="530.572" y="175.946" fill="#FFFFFF" width="46.16" height="15.387"/>
    		<rect x="36" y="206.719" fill="#FFFFFF" width="540.732" height="15.387"/>
    		<rect x="36" y="237.493" fill="#FFFFFF" width="540.732" height="114.301"/>
    		<rect x="36" y="367.181" fill="#FFFFFF" width="540.732" height="230.801"/>
    		<rect x="35.267" y="52.119" fill="#D9D2E9" width="187.571" height="16.12"/>
    		<rect x="222.106" y="52.119" fill="#F3F3F3" width="104.776" height="16.12"/>
    		<rect x="222.106" y="67.506" fill="#F3F3F3" width="104.776" height="16.12"/>
    		<rect x="35.267" y="82.893" fill="#D9D2E9" width="187.571" height="16.12"/>
    		<rect x="222.106" y="82.893" fill="#F3F3F3" width="104.776" height="16.12"/>
    		<rect x="393.558" y="82.893" fill="#F3F3F3" width="137.015" height="16.12"/>
    		<rect x="222.106" y="98.279" fill="#F3F3F3" width="104.776" height="77.667"/>
    		<rect x="393.558" y="98.279" fill="#F3F3F3" width="137.015" height="16.12"/>
    		<rect x="393.558" y="129.053" fill="#D9D9D9" width="137.015" height="46.893"/>
    		<rect x="222.106" y="175.213" fill="#C9DAF8" width="104.776" height="16.12"/>
    		<rect x="393.558" y="175.213" fill="#D9D9D9" width="137.015" height="16.12"/>
    		<rect x="35.267" y="190.6" fill="#C9DAF8" width="541.465" height="16.12"/>
    		<rect x="35.267" y="221.373" fill="#F3F3F3" width="541.465" height="16.12"/>
    		<rect x="35.267" y="351.061" fill="#F3F3F3" width="541.465" height="16.12"/>
    		<rect x="35.267" y="597.248" fill="#F3F3F3" width="541.465" height="17.585"/>
    		<path fill="none" stroke="#000000" stroke-width="0.7327" stroke-miterlimit="10" d="M576.366,597.981v78.398 M35.267,676.014
    			h541.465 M35.267,660.627h541.465 M35.267,645.24h541.465 M35.267,629.854h541.465 M35.267,614.467h541.465 M576.366,351.794
    			v244.722 M35.267,582.228h541.465 M35.267,566.841h541.465 M35.267,551.455h541.465 M35.267,536.068h541.465 M35.267,520.682
    			h541.465 M35.267,505.295h541.465 M35.267,489.908h541.465 M35.267,474.521h541.465 M35.267,459.135h541.465 M35.267,443.748
    			h541.465 M35.267,428.361h541.465 M35.267,412.975h541.465 M35.267,397.588h541.465 M35.267,382.201h541.465 M35.267,366.814
    			h541.465 M576.366,222.106v128.222 M35.267,341.902h541.465 M35.267,332.377h541.465 M35.267,322.852h541.465 M35.267,313.327
    			h541.465 M35.267,303.802h541.465 M35.267,294.277h541.465 M35.267,284.752h541.465 M35.267,275.227h541.465 M35.267,265.702
    			h541.465 M35.267,256.176h541.465 M35.267,246.651h541.465 M35.267,237.126h541.465 M576.366,52.119v168.521 M35.267,206.353
    			h541.465 M35.267,190.966h541.465 M222.106,175.579h354.626 M222.106,160.193h354.626 M222.106,144.806h354.626 M222.106,129.419
    			h354.626 M222.106,114.033h354.626 M35.267,98.646h541.465 M35.267,83.259h541.465 M35.267,52.486h541.465 M530.206,366.448
    			v230.068 M530.206,129.053v62.28 M530.206,82.893v31.506 M393.924,629.487v46.893 M393.924,222.106v128.222 M393.924,52.119
    			v168.521 M35.267,67.873H394.29 M326.516,614.101v62.279 M326.516,52.119v139.213 M222.472,52.119v168.521 M111.102,629.487
    			v46.893 M111.102,351.794v244.722 M111.102,190.6v30.041 M35.634,597.981v78.398 M35.634,351.794v244.722 M35.634,222.106v128.222
    			 M35.634,52.119v168.521"/>
    		<path fill="none" stroke="#000000" stroke-width="1.4654" stroke-miterlimit="10" d="M35.267,597.248h541.465 M35.267,351.061
    			h541.465 M35.267,221.373h541.465"/>
    	</g>
      <g>
        <text transform="matrix(1 0 0 1 94.6152 48.2329)" font-family="\'Arial-BoldMT\'" font-size="11.4301">Greenheath Limited - Job Sheet</text>
        <text transform="matrix(1 0 0 1 353.2588 47.1328)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Job Ref:</text>
        <text transform="matrix(1 0 0 1 509.9111 48.6357)" font-family="\'ArialMT\'" font-size="9.5251">'.$strTaskId.'</text>
        <text transform="matrix(1 0 0 1 544.4932 48.6357)" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['staff_initial'].'</text>
        <text transform="matrix(1 0 0 1 114.3984 64.0225)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Name:</text>
        <text transform="matrix(1 0 0 1 278.958 64.0225)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Date Start</text>
        <text transform="matrix(1 0 0 1 365 64.0225)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getNormalDate($arrTableData['date_started']).'</text>
        <text transform="matrix(1 0 0 1 438.9844 71.312)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Applied billing rates</text>
        <text transform="matrix(1 0 0 1 140.3921 79.4092)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['customer_name'].'</text>
        <text transform="matrix(1 0 0 1 272.6143 79.4092)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Date Finish</text>
        <text transform="matrix(1 0 0 1 365 79.4092)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getNormalDate($arrTableData['date_completed']).'</text>
        <text transform="matrix(1 0 0 1 365 94.7959)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Travel out time'].'</text>
        <text transform="matrix(1 0 0 1 365 110.1826)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Travel return time'].'</text>
        <text transform="matrix(1 0 0 1 365 171.729)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Staff mileage'].'</text>
        <text transform="matrix(1 0 0 1 365 187.1157)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Machine hours'].'</text>
        <text transform="matrix(1 0 0 1 75 217.5024)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Make'].'</text>';
for ($i=0; $i < 12; $i++) {
$html.= '<text transform="matrix(1 0 0 1 42.8936 '.(245+$i*9.45).')" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Parts'][$i].'</text>
        <text transform="matrix(1 0 0 1 485 '.(245+$i*9.45).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Price'][$i]*(getCleaInt($arrTableData['work_spreadsheet']['Parts markup'])/100+1).'</text>';
}
$html.= '<text transform="matrix(1 0 0 1 168 217.5024)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Model'].'</text>
        <text transform="matrix(1 0 0 1 312 217.5024)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Serial No'].'</text>
        <text transform="matrix(1 0 0 1 490 217.5024)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Registration number &/or Plant#'].'</text>
        <text transform="matrix(1 0 0 1 93.8828 94.7959)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Address &amp; Tel: </text>
        <text transform="matrix(1 0 0 1 135 110)" text-anchor="middle">
          <tspan x="10" y="0" font-family="\'Arial-BoldMT\'" font-size="9.5251">Site/address:</tspan>
          <tspan x="10" y="10" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextAddr[0].'</tspan>
          <tspan x="10" y="20" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextAddr[1].'</tspan>
          <tspan x="20" y="30" font-family="\'Arial-BoldMT\'" font-size="9.5251">Contact phone number (and name):</tspan>
          <tspan x="10" y="40" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextContacts[0].'</tspan>
          <tspan x="8" y="50" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextContacts[1].'</tspan>
        </text>
        <text transform="matrix(1 0 0 1 256.2002 94.7959)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Travel out time</text>
        <text transform="matrix(1 0 0 1 441.3662 94.7959)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Hourly applied rate</text>
        <text transform="matrix(1 0 0 1 558 94.7959)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$arrTableData['work_spreadsheet']['Hourly applied rate'].'</text>
        <text transform="matrix(1 0 0 1 243.4893 110.1826)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Travel return time</text>
        <text transform="matrix(1 0 0 1 450.8779 110.1826)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Mileage rate/mile</text>
        <text transform="matrix(1 0 0 1 558 110.1826)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$arrTableData['work_spreadsheet']['Mileage rate/mile'].'</text>
        <text transform="matrix(1 0 0 1 264.6797 125.5693)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Offsite hours</text>
        <text transform="matrix(1 0 0 1 365 125.5693)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$intOfficeHours.'</text>
        <text transform="matrix(1 0 0 1 455.1045 125.5693)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Billing totals </text>
        <text transform="matrix(1 0 0 1 262.5674 140.9561)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Labour hours</text>
        <text transform="matrix(1 0 0 1 365 140.9561)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$intTotalHours.'</text>
        <text transform="matrix(1 0 0 1 476.3047 140.9561)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total parts </text>
        <text transform="matrix(1 0 0 1 558 140.9561)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$intTotalParts.'</text>
        <text transform="matrix(1 0 0 1 272.0928 156.3423)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total hours</text>
        <text transform="matrix(1 0 0 1 365 156.3423)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getSunTime($intOfficeHours,$intTotalHours).'</text>
        <text transform="matrix(1 0 0 1 435.5674 156.3423)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total hours / Labour</text>
        <text transform="matrix(1 0 0 1 558 156.3423)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$intTotalHoursLab.'</text>
        <text transform="matrix(1 0 0 1 261.4941 171.729)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Staff mileage:</text>
        <text transform="matrix(1 0 0 1 466.7695 171.729)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total mileage</text>
        <text transform="matrix(1 0 0 1 558 171.729)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$intTotalMileage.'</text>
        <text transform="matrix(1 0 0 1 253.5635 187.1157)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Machine hours:</text>
        <text transform="matrix(1 0 0 1 469.9512 187.1157)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total ex VAT</text>
        <text transform="matrix(1 0 0 1 558 187.1157)" text-anchor="middle" font-family="\'Arial-BoldMT\'" font-size="9.5251">£'.($intTotalParts+$intTotalHoursLab+$intTotalMileage).'</text>
        <text transform="matrix(1 0 0 1 60.9111 202.5024)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Make</text>
        <text transform="matrix(1 0 0 1 152.499 202.5024)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Model</text>
        <text transform="matrix(1 0 0 1 287.3154 202.5024)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Serial No</text>
        <text transform="matrix(1 0 0 1 410.4092 202.5024)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Registration number &amp;/or Plant #</text>
        <text transform="matrix(1 0 0 1 188.4014 233.2759)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Parts used </text>
        <text transform="matrix(1 0 0 1 473.4219 233.2759)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Price</text>
        <text transform="matrix(1 0 0 1 62.377 362.9639)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Date</text>
        <text transform="matrix(1 0 0 1 249.9482 362.9639)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Descripton of work carried out </text>
        <text transform="matrix(1 0 0 1 536.4336 362.9639)" font-family="\'Arial-BoldMT\'" font-size="9.5251">HH:MM</text>';

for ($i=0; $i < 15; $i++) {
$html.= '<text transform="matrix(1 0 0 1 80 '.(377.2529+$i*15.4).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.getNormalDate($arrTableData['work_spreadsheet']['arrDate'][$i]).'</text>
        <text transform="matrix(1 0 0 1 121.584 '.(377.2529+$i*15.4).')" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Description'][$i].'</text>
        <text transform="matrix(1 0 0 1 555 '.(377.2529+$i*15.4).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Hours'][$i].'</text>';
      }

$html.= '<text transform="matrix(1 0 0 1 53.584 610.1631)" font-family="\'Arial-BoldMT\'" font-size="11.4301">Job completion </text>
        <text transform="matrix(1 0 0 1 141.1836 610.1631)" font-family="\'Arial-BoldMT\'" font-size="7.6201">- use INITIALS as signature. By prining your name and initialling below you are elctronically singing this document </text>
        <text transform="matrix(1 0 0 1 156.8955 626.0029)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Customer </text>
        <text transform="matrix(1 0 0 1 438.9844 626.0029)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Fitter</text>
        <text transform="matrix(1 0 0 1 38.1309 641.3906)" font-family="\'ArialMT\'" font-size="9.5251">Customer name:</text>
        <text transform="matrix(1 0 0 1 240 641.3906)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['customer_name'].'</text>
        <text transform="matrix(1 0 0 1 240 656.7773)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Customer Sign'].'</text>
        <text transform="matrix(1 0 0 1 240 672.1641)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Customer PO#'].'</text>
        <text transform="matrix(1 0 0 1 340.543 641.3906)" font-family="\'ArialMT\'" font-size="9.5251">Fitter name:</text>
        <text transform="matrix(1 0 0 1 485 641.3906)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.checkUserBy('signature', $arrTableData['staff_initial'], true, array('login'))[0]['login'].'</text>
        <text transform="matrix(1 0 0 1 485 672.1641)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getNormalDate($arrTableData['date_completed']).'</text>
        <text transform="matrix(1 0 0 1 42.8936 656.7773)" font-family="\'ArialMT\'" font-size="9.5251">Customer Sign:</text>
        <text transform="matrix(1 0 0 1 346.8965 656.7773)" font-family="\'ArialMT\'" font-size="9.5251">Fitter sign:</text>
        <text transform="matrix(1 0 0 1 485 656.7773)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['staff_initial'].'</text>
        <text transform="matrix(1 0 0 1 42.8984 672.1641)" font-family="\'ArialMT\'" font-size="9.5251">Customer PO#:</text>
        <text transform="matrix(1 0 0 1 368.5928 672.1641)" font-family="\'ArialMT\'" font-size="9.5251">Date:</text>
        <text transform="matrix(1 0 0 1 115.8643 686.8555)" fill="#B7B7B7" font-family="\'ArialMT\'" font-size="9.5251">Greenheaht Limited, Toggamfarm, Newfen Gravel Drove, Lakenheath, Suffolk, IP27 9LN. </text>
        <text transform="matrix(1 0 0 1 49.9209 697.8076)" fill="#B7B7B7" font-family="\'ArialMT\'" font-size="9.5251">T: +44 (0) 1842 862068 - E: info@greenheath.co.uk - Company Registration: 4408503 - VAT Registration: GB794908667</text>
      </g>
    </g>
    </svg>
  </div>';


  //==============================================================
  // настройки MPDF
  //==============================================================
  // Create an instance of the class:
  //Кодировка | Формат | Размер шрифта | Шрифт / Отступы: слева | справа | сверху | снизу | шапка | подвал
  $mpdf = new mPDF('utf-8', array(210,297), '0', 'Arial', 0, 0, 0, 0, 0, 0);
  // Write some HTML code:
  $mpdf->WriteHTML($html, 2); //A4

  // Output a PDF file directly to the browser
  // $mpdf->Output();
  // Saves file on the server as 'filename.pdf'
  $strFoldeForPDF = $strPath.'admin/pdfTMP/';
  $mpdf->Output($strFoldeForPDF.'Job sheet - '.$strTaskId.' - Customer Sheet.pdf', 'F');

  return 'Job sheet - '.$strTaskId.' - Customer Sheet.pdf';
}


//Верстаем ПДФ для счета флатерну
function getPdfFlaternCost($strTaskId, $strPath){
  global $config;
  // Require composer autoload
  require_once $strPath.'admin/lib/MPDF/vendor/autoload.php';

  include_once $strPath."admin/lib/MPDF/mpdf.php";
  //==============================================================
  // html код
  //==============================================================

  //получаем данные для вноса в счет
  $arrTableData = selectFromTable('big_task_log', array('staff_initial',
                                                        'date_started',
                                                        'customer_name',
                                                        'date_completed',
                                                        'work_spreadsheet',
                                                        'id',
                                                        'site_address',
                                                        'contact_phone_number_and_name'),
                                                        true, 'id', $strTaskId)[0];
 //нету такой задачи..
  if($arrTableData['id'] != $strTaskId){
    return false;
  }

  $arrTableData['work_spreadsheet'] = unserialize($arrTableData['work_spreadsheet']);
//**********************************************
//математика
//**********************************************
//получаем количесво часов из суммы всех в листе
  $intTotalHours = '0:00';
  for($i=0;$i<15;$i++){
    if(!empty(strpos($arrTableData['work_spreadsheet']['Hours'][$i],":"))){
      $intTotalHours = getSunTime($intTotalHours,$arrTableData['work_spreadsheet']['Hours'][$i]);
    }
  }

  $intOfficeHours = getDifTime($arrTableData['work_spreadsheet']['Travel return time'],$arrTableData['work_spreadsheet']['Travel out time']);

  $intTotalParts = 0;
  for($i=0;$i<12;$i++){
    if($arrTableData['work_spreadsheet']['Price'][$i] !=''){
      $intTotalParts = $intTotalParts + $arrTableData['work_spreadsheet']['Price'][$i];
    }
  }

  $intTotalParts = $intTotalParts*(getCleaInt($arrTableData['work_spreadsheet']['Parts markup'])/100+1);
  $intTotalHoursLab = (getNumberFromTime(getSunTime($intOfficeHours,$intTotalHours)))*$arrTableData['work_spreadsheet']['Hourly applied rate'];
  $intTotalMileage = $arrTableData['work_spreadsheet']['Staff mileage']*$arrTableData['work_spreadsheet']['Mileage rate/mile'];

  //делаем перенос строки если она слишком длинная
  if(strlen($arrTableData['site_address'])>32){
    $arrTextAddr = explode("★",wordwrap($arrTableData['site_address'], 32, "★"));
  }else{
    $arrTextAddr = array($arrTableData['site_address'],'');
  }
  if(strlen($arrTableData['contact_phone_number_and_name'])>32){
    $arrTextContacts = explode("★",wordwrap($arrTableData['contact_phone_number_and_name'], 32, "★"));
  }else{
    $arrTextContacts = array($arrTableData['contact_phone_number_and_name'],'');
  }

  $html = '<div style="width: 210mm; height: 297mm">
    <svg version="1.0" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="'.(210*3.779).'" height="'.(297*3.779).'" viewBox="0 0 612 792" enable-background="new 0 0 612 792" xml:space="preserve">
      <g id="Layer_1">
      	<g>
      		<rect x="326.882" y="55.783" fill="#FFFFFF" width="249.851" height="15.387"/>
      		<rect x="36" y="71.169" fill="#FFFFFF" width="186.838" height="16.852"/>
      		<rect x="326.882" y="71.169" fill="#FFFFFF" width="67.408" height="16.852"/>
      		<rect x="530.572" y="71.169" fill="#FFFFFF" width="46.16" height="16.852"/>
      		<rect x="530.572" y="88.021" fill="#FFFFFF" width="46.16" height="15.387"/>
      		<rect x="36" y="103.408" fill="#FFFFFF" width="186.838" height="92.32"/>
      		<rect x="530.572" y="103.408" fill="#FFFFFF" width="46.16" height="15.387"/>
      		<rect x="326.882" y="118.795" fill="#FFFFFF" width="249.851" height="15.387"/>
      		<rect x="326.882" y="134.182" fill="#FFFFFF" width="67.408" height="30.773"/>
      		<rect x="530.572" y="134.182" fill="#FFFFFF" width="46.16" height="30.773"/>
      		<rect x="530.572" y="164.955" fill="#FFFFFF" width="46.16" height="15.387"/>
      		<rect x="326.882" y="180.342" fill="#FFFFFF" width="67.408" height="15.387"/>
      		<rect x="530.572" y="180.342" fill="#FFFFFF" width="46.16" height="15.387"/>
      		<rect x="36" y="211.115" fill="#FFFFFF" width="540.732" height="15.387"/>
      		<rect x="36" y="241.889" fill="#FFFFFF" width="540.732" height="114.301"/>
      		<rect x="111.468" y="371.577" fill="#FFFFFF" width="465.264" height="15.387"/>
      		<rect x="36" y="386.963" fill="#FFFFFF" width="540.732" height="215.414"/>
      		<rect x="36" y="619.229" fill="#FFFFFF" width="540.732" height="15.387"/>
      		<rect x="35.267" y="55.05" fill="#D9D2E9" width="187.571" height="16.119"/>
      		<rect x="222.106" y="55.05" fill="#F3F3F3" width="104.776" height="16.119"/>
      		<rect x="222.106" y="70.437" fill="#F3F3F3" width="104.776" height="17.584"/>
      		<rect x="393.558" y="70.437" fill="#F3F3F3" width="137.015" height="17.584"/>
      		<rect x="35.267" y="87.289" fill="#D9D2E9" width="187.571" height="16.119"/>
      		<rect x="222.106" y="87.289" fill="#F3F3F3" width="104.776" height="16.119"/>
      		<rect x="326.149" y="87.289" fill="#EAD1DC" width="68.141" height="16.119"/>
      		<rect x="393.558" y="87.289" fill="#F3F3F3" width="137.015" height="16.119"/>
      		<rect x="222.106" y="102.676" fill="#F3F3F3" width="104.776" height="77.666"/>
      		<rect x="326.149" y="102.676" fill="#EAD1DC" width="68.141" height="16.119"/>
      		<rect x="393.558" y="102.676" fill="#F3F3F3" width="137.015" height="16.119"/>
      		<rect x="393.558" y="133.449" fill="#D9D9D9" width="137.015" height="31.506"/>
      		<rect x="326.149" y="164.223" fill="#EAD1DC" width="68.141" height="16.119"/>
      		<rect x="393.558" y="164.223" fill="#D9D9D9" width="137.015" height="16.119"/>
      		<rect x="222.106" y="179.609" fill="#C9DAF8" width="104.776" height="16.119"/>
      		<rect x="393.558" y="179.609" fill="#D9D9D9" width="137.015" height="16.119"/>
      		<rect x="35.267" y="194.996" fill="#C9DAF8" width="541.465" height="16.119"/>
      		<rect x="35.267" y="225.77" fill="#F3F3F3" width="541.465" height="16.119"/>
      		<rect x="35.267" y="355.457" fill="#F3F3F3" width="541.465" height="16.12"/>
      		<rect x="35.267" y="370.844" fill="#F4CCCC" width="76.201" height="16.12"/>
      		<rect x="35.267" y="601.645" fill="#F3F3F3" width="541.465" height="17.585"/>
      		<rect x="393.558" y="633.884" fill="#EAD1DC" width="183.175" height="31.506"/>
      		<path fill="none" stroke="#000000" stroke-width="0.7327" stroke-miterlimit="10" d="M576.366,55.05v625.726 M35.267,680.41
      			h541.465 M35.267,665.023h541.465 M35.267,649.637h541.465 M35.267,634.25h541.465 M35.267,618.863h541.465 M35.267,602.011
      			h541.465 M35.267,586.624h541.465 M35.267,571.237h541.465 M35.267,555.851h541.465 M35.267,540.464h541.465 M35.267,525.077
      			h541.465 M35.267,509.69h541.465 M35.267,494.304h541.465 M35.267,478.917h541.465 M35.267,463.53h541.465 M35.267,448.144
      			h541.465 M35.267,432.757h541.465 M35.267,417.37h541.465 M35.267,401.983h541.465 M35.267,386.597h541.465 M35.267,371.21
      			h541.465 M35.267,355.824h541.465 M35.267,346.298h541.465 M35.267,336.773h541.465 M35.267,327.249h541.465 M35.267,317.723
      			h541.465 M35.267,308.198h541.465 M35.267,298.673h541.465 M35.267,289.148h541.465 M35.267,279.623h541.465 M35.267,270.098
      			h541.465 M35.267,260.573h541.465 M35.267,251.047h541.465 M35.267,241.522h541.465 M35.267,226.136h541.465 M35.267,210.749
      			h541.465 M35.267,195.362h541.465 M222.106,179.976h354.626 M222.106,164.589h354.626 M222.106,149.202h354.626 M222.106,133.815
      			h354.626 M222.106,118.429h354.626 M35.267,103.042h541.465 M35.267,87.655h541.465 M35.267,70.803h541.465 M35.267,55.417
      			h541.465 M530.206,370.844v231.533 M530.206,225.77v130.42 M530.206,133.449v62.279 M530.206,70.437v48.358 M393.924,633.884
      			v46.893 M393.924,55.05v301.14 M326.516,618.497v62.279 M326.516,55.05v140.678 M222.472,55.05v171.452 M111.102,633.884v46.893
      			 M111.102,355.457v246.92 M111.102,194.996v31.506 M35.634,55.05v625.726"/>
      		<path fill="#808080" d="M551.912,691.217l-3.205-3.205l0.904-0.904l2.301,2.295l4.866-4.865l0.904,0.91L551.912,691.217z
      			 M547.425,683.544v9.118c0,0.717,0.579,1.303,1.303,1.303h9.118c0.716,0,1.303-0.586,1.303-1.303v-9.118
      			c0-0.716-0.587-1.303-1.303-1.303h-9.118C548.004,682.241,547.425,682.828,547.425,683.544z"/>
      	</g>
        <g>
        	<text transform="matrix(1 0 0 1 73.3672 50.5576)" font-family="\'Arial-BoldMT\'" font-size="14.2877">Greenheath Limited - Job Sheet</text>
        	<text transform="matrix(1 0 0 1 334.209 50.1343)" font-family="\'Arial-BoldMT\'" font-size="14.2877">Job Ref:</text>
        	<text transform="matrix(1 0 0 1 540 50.5576)" text-anchor="end" font-family="\'Arial-BoldMT\'" font-size="14.2877">'.$strTaskId.'</text>
        	<text transform="matrix(1 0 0 1 532.7705 50.5576)" font-family="\'Arial-BoldMT\'" font-size="14.2877">'.$arrTableData['staff_initial'].'</text>
        	<text transform="matrix(1 0 0 1 114.3984 66.9526)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Name:</text>
        	<text transform="matrix(1 0 0 1 278.958 66.9526)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Date Start</text>
        	<text transform="matrix(1 0 0 1 370 66.9526)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getNormalDate($arrTableData['date_started']).'</text>
        	<text transform="matrix(1 0 0 1 471.9561 66.9526)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Rates</text>
        	<text transform="matrix(1 0 0 1 135 83.4023)" text-anchor="middle" font-family="\'Arial-BoldMT\'" font-size="11.4301">'.$arrTableData['customer_name'].'</text>
          <text transform="matrix(1 0 0 1 135 114)" text-anchor="middle">
            <tspan x="10" y="0" font-family="\'Arial-BoldMT\'" font-size="9.5251">Site/address:</tspan>
            <tspan x="10" y="10" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextAddr[0].'</tspan>
            <tspan x="10" y="20" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextAddr[1].'</tspan>
            <tspan x="20" y="30" font-family="\'Arial-BoldMT\'" font-size="9.5251">Contact phone number (and name):</tspan>
            <tspan x="10" y="40" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextContacts[0].'</tspan>
            <tspan x="8" y="50" font-family="\'Arial-BoldMT\'" font-size="9.5251">'.$arrTextContacts[1].'</tspan>
          </text>
        	<text transform="matrix(1 0 0 1 272.6143 83.8052)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Date Finish</text>
        	<text transform="matrix(1 0 0 1 370 83.8052)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getNormalDate($arrTableData['date_completed']).'</text>
        	<text transform="matrix(1 0 0 1 365 98.6191)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Travel out time'].'</text>
        	<text transform="matrix(1 0 0 1 365 115.4717)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Travel return time'].'</text>
        	<text transform="matrix(1 0 0 1 441.3662 83.8052)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Hourly applied rate</text>
        	<text transform="matrix(1 0 0 1 555 83.8052)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$arrTableData['work_spreadsheet']['Hourly applied rate'].'</text>
        	<text transform="matrix(1 0 0 1 93.8828 99.1919)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Address &amp; Tel: </text>
        	<text transform="matrix(1 0 0 1 256.2002 99.1919)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Travel out time</text>
        	<text transform="matrix(1 0 0 1 450.8779 99.1919)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Mileage rate/mile</text>
        	<text transform="matrix(1 0 0 1 555 99.1919)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$arrTableData['work_spreadsheet']['Mileage rate/mile'].'</text>
        	<text transform="matrix(1 0 0 1 243.4893 114.5781)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Travel return time</text>
        	<text transform="matrix(1 0 0 1 466.7607 114.5781)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Parts markup</text>
        	<text transform="matrix(1 0 0 1 555 114.5781)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Parts markup'].'</text>
        	<text transform="matrix(1 0 0 1 264.6797 129.9648)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Offsite hours</text>
        	<text transform="matrix(1 0 0 1 363 129.9648)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$intOfficeHours.'</text>
        	<text transform="matrix(1 0 0 1 470.4912 129.9648)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Totals</text>
        	<text transform="matrix(1 0 0 1 262.5674 145.3516)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Labour hours</text>
        	<text transform="matrix(1 0 0 1 363 145.3516)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$intTotalHours.'</text>
        	<text transform="matrix(1 0 0 1 476.3047 145.3516)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total parts </text>
        	<text transform="matrix(1 0 0 1 555 145.3516)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$intTotalParts.'</text>
        	<text transform="matrix(1 0 0 1 272.0928 160.7383)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total hours</text>
        	<text transform="matrix(1 0 0 1 363 160.7383)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getSunTime($intOfficeHours,$intTotalHours).'</text>
        	<text transform="matrix(1 0 0 1 363 175.7383)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Staff mileage'].'</text>
        	<text transform="matrix(1 0 0 1 475.7832 160.7383)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total hours</text>
        	<text transform="matrix(1 0 0 1 555 160.7383)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">£'.$intTotalHoursLab.'</text>
        	<text transform="matrix(1 0 0 1 261.4941 176.125)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Staff mileage:</text>
        	<text transform="matrix(1 0 0 1 466.7695 176.125)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total mileage</text>
        	<text transform="matrix(1 0 0 1 540 176.125)" font-family="\'ArialMT\'" font-size="9.5251">£'.$intTotalMileage.'</text>
        	<text transform="matrix(1 0 0 1 253.5635 191.5117)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Machine hours:</text>
        	<text transform="matrix(1 0 0 1 363 191.5117)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Machine hours'].'</text>
        	<text transform="matrix(1 0 0 1 469.9512 191.5117)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Total ex VAT</text>
        	<text transform="matrix(1 0 0 1 540 191.5117)" font-family="\'Arial-BoldMT\'" font-size="9.5251">£'.($intTotalParts+$intTotalHoursLab+$intTotalMileage).'</text>
        	<text transform="matrix(1 0 0 1 60.9111 206.8989)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Make</text>
        	<text transform="matrix(1 0 0 1 152.499 206.8989)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Model</text>
        	<text transform="matrix(1 0 0 1 287.3154 206.8989)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Serial No</text>
        	<text transform="matrix(1 0 0 1 410.4092 206.8989)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Registration number &amp;/or Plant #</text>
        	<text transform="matrix(1 0 0 1 75 221.8989)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Make'].'</text>
        	<text transform="matrix(1 0 0 1 170 221.8989)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Model'].'</text>
        	<text transform="matrix(1 0 0 1 315 221.8989)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Serial No'].'</text>
        	<text transform="matrix(1 0 0 1 485 221.8989)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Registration number &/or Plant#'].'</text>
        	<text transform="matrix(1 0 0 1 38.1973 237.6724)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Parts used </text>
        	<text transform="matrix(1 0 0 1 428.7266 237.6724)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Supplier name</text>
        	<text transform="matrix(1 0 0 1 541.5625 237.6724)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Price</text>';
  for ($i=0; $i < 12; $i++) {
  $html.= '<text transform="matrix(1 0 0 1 38.1973 '.(249+$i*9.45).')" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Parts'][$i].'</text>
          <text transform="matrix(1 0 0 1 470 '.(249+$i*9.45).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Supplier'][$i].'</text>
          <text transform="matrix(1 0 0 1 555 '.(249+$i*9.45).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Price'][$i]*(getCleaInt($arrTableData['work_spreadsheet']['Parts markup'])/100+1).'</text>';
  }
  $html.= '<text transform="matrix(1 0 0 1 62.377 367.3604)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Date</text>
        	<text transform="matrix(1 0 0 1 249.9482 367.3604)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Descripton of work carried out </text>
        	<text transform="matrix(1 0 0 1 536.4336 367.3604)" font-family="\'Arial-BoldMT\'" font-size="9.5251">HH:MM</text>';

  for ($i=0; $i < 15; $i++) {
  $html.= '<text transform="matrix(1 0 0 1 80 '.(383+$i*15.4).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.getNormalDate($arrTableData['work_spreadsheet']['arrDate'][$i]).'</text>
          <text transform="matrix(1 0 0 1 114 '.(383+$i*15.4).')" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Description'][$i].'</text>
          <text transform="matrix(1 0 0 1 555 '.(383+$i*15.4).')" text-anchor="middle" font-family="\'ArialMT\'" font-size="7.6201">'.$arrTableData['work_spreadsheet']['Hours'][$i].'</text>';
        }

  $html.= '<text transform="matrix(1 0 0 1 53.584 614.5596)" font-family="\'Arial-BoldMT\'" font-size="11.4301">Job completion </text>
        	<text transform="matrix(1 0 0 1 141.1836 614.5596)" font-family="\'Arial-BoldMT\'" font-size="7.6201">- use INITIALS as signature. By prining your name and initialling below you are elctronically singing this document </text>
        	<text transform="matrix(1 0 0 1 156.8955 630.3994)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Customer </text>
        	<text transform="matrix(1 0 0 1 438.9844 630.3994)" font-family="\'Arial-BoldMT\'" font-size="9.5251">Fitter</text>
        	<text transform="matrix(1 0 0 1 38.1309 645.7861)" font-family="\'ArialMT\'" font-size="9.5251">Customer name:</text>
        	<text transform="matrix(1 0 0 1 215.9102 645.7861)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['customer_name'].'</text>
        	<text transform="matrix(1 0 0 1 215.9097 661.0361)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Customer Sign'].'</text>
        	<text transform="matrix(1 0 0 1 215.9097 676.2861)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['work_spreadsheet']['Customer PO#'].'</text>
        	<text transform="matrix(1 0 0 1 482.0596 645.7861)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.checkUserBy('signature', $arrTableData['staff_initial'], true, array('login'))[0]['login'].'</text>
        	<text transform="matrix(1 0 0 1 482.0596 661.0361)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.$arrTableData['staff_initial'].'</text>
        	<text transform="matrix(1 0 0 1 482.0596 676.2861)" text-anchor="middle" font-family="\'ArialMT\'" font-size="9.5251">'.getNormalDate($arrTableData['date_completed']).'</text>
        	<text transform="matrix(1 0 0 1 340.543 645.7861)" font-family="\'ArialMT\'" font-size="9.5251">Fitter name:</text>
        	<text transform="matrix(1 0 0 1 42.8936 661.1729)" font-family="\'ArialMT\'" font-size="9.5251">Customer Sign:</text>
        	<text transform="matrix(1 0 0 1 346.8965 661.1729)" font-family="\'ArialMT\'" font-size="9.5251">Fitter sign:</text>
        	<text transform="matrix(1 0 0 1 42.8984 676.5596)" font-family="\'ArialMT\'" font-size="9.5251">Customer PO#:</text>
        	<text transform="matrix(1 0 0 1 368.5928 676.5596)" font-family="\'ArialMT\'" font-size="9.5251">Date:</text>
        	<text transform="matrix(1 0 0 1 492.6992 691.9463)" font-family="\'ArialMT\'" font-size="9.5251">Submit?</text>
        	<text transform="matrix(1 0 0 1 115.8643 706.6377)" fill="#B7B7B7" font-family="\'ArialMT\'" font-size="9.5251">Greenheaht Limited, Toggamfarm, Newfen Gravel Drove, Lakenheath, Suffolk, IP27 9LN. </text>
        	<text transform="matrix(1 0 0 1 49.9209 717.5908)" fill="#B7B7B7" font-family="\'ArialMT\'" font-size="9.5251">T: +44 (0) 1842 862068 - E: info@greenheath.co.uk - Company Registration: 4408503 - VAT Registration: GB794908667</text>
        </g>
      </g>
    </svg>
  </div>';


  //==============================================================
  // настройки MPDF
  //==============================================================
  // Create an instance of the class:
  //Кодировка | Формат | Размер шрифта | Шрифт / Отступы: слева | справа | сверху | снизу | шапка | подвал
  $mpdf = new mPDF('utf-8', array(210,297), '0', 'Arial', 0, 0, 0, 0, 0, 0);
  // Write some HTML code:
  $mpdf->WriteHTML($html, 2); //A4

  // Output a PDF file directly to the browser
  // $mpdf->Output();
  // Saves file on the server as 'filename.pdf'
  $strFoldeForPDF = $strPath.'admin/pdfTMP/';
  $mpdf->Output($strFoldeForPDF.'Job sheet - '.$strTaskId.' - Fitter Sheet.pdf', 'F');
  return 'Job sheet - '.$strTaskId.' - Fitter Sheet.pdf';
}


//вносим данные в новую строку гугл таблици
function addToSpreadsheet($strTaskId, $strPath){
    global $config;

  //получаем данные для вноса в счет
  $arrTableData = selectFromTable('big_task_log', array('labour_plus_mileage',
                                                        'work_spreadsheet',
                                                        'customer_name',
                                                        'contact_phone_number_and_name',
                                                        'site_address',
                                                        'staff_initial',
                                                        'id'),
                                                        true, 'id', $strTaskId)[0];

 //нету такой задачи..
  if($arrTableData['id'] != $strTaskId){
    return false;
  }
  $arrTableData['work_spreadsheet'] = unserialize($arrTableData['work_spreadsheet']);


  //получаем общую строимость деталей для клиента
  $intTotalParts = 0;
  for ($i=0; $i<count($arrTableData['work_spreadsheet']['Price']);$i++) {
    $intTotalParts = $intTotalParts+$arrTableData['work_spreadsheet']['Price'][$i]*1;
  }
  $intTotalPartsClient = $intTotalParts*(getCleaInt($arrTableData['work_spreadsheet']['Parts markup'])/100+1);

  $intTotalMil = $arrTableData['work_spreadsheet']['Staff mileage']*$arrTableData['work_spreadsheet']['Mileage rate/mile'];

  //если есть сумма в ячейке с ценой
  if($arrTableData['labour_plus_mileage']*1+$intTotalPartsClient*1>0){
    //вносим в таблицу оплат
    try{
        // Require composer autoload
        require_once $strPath.'admin/lib/php2gsheets/vendor/autoload.php';
        //Reading data from spreadsheet.
        $client = new \Google_Client();
        //название для процесса
        $client->setApplicationName('PHP2GoogleSheets');
        //тип подключения к таблице
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        //принцип работы с таблицой
        $client->setAccessType('offline');
        //путь к файлу ключу для авторизации в гугл
        $client->setAuthConfig($strPath . 'admin/lib/php2gsheets/PHP2spreadsheetAPI-4bcc0fa28826.json');
        //создаем подключение
        $service = new Google_Service_Sheets($client);

        // https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/update
        //вносим одну строку данных
        $values = [
          ["",
          "",
          getBrowserDate(time()), //втавить дату Timestamp
          $_SESSION['new']['email'], //вставить мейл Email Address
          "Greenheath Limited", //вставить слово Greenheath Limited
          "Workshop", //вставить слово Workshop
          '="'.$arrTableData['customer_name'].'"&CHAR(10)&CHAR(10)&"'.$arrTableData['site_address'].' '.$arrTableData['contact_phone_number_and_name'].'"', //вставить адрес и имя заказчика
          $arrTableData['work_spreadsheet']['Customer PO#'], //вставить PO
          "",
          'Job sheet ref #'.$strTaskId.' '.$arrTableData['staff_initial'], //вставить идентификатор заявки Raise PO from:	PO # or Jobsheet Ref# STAFF INITIAL
          '="Total parts £'.$intTotalPartsClient.'"&CHAR(10)&"Total hours / Labour £'.($arrTableData['labour_plus_mileage']*1-$intTotalMil).'"&CHAR(10)&"Total mileage £'.$intTotalMil.'"&CHAR(10)&"Total ex VAT £'.($intTotalPartsClient*1+$arrTableData['labour_plus_mileage']*1).'"', //вставить Detail with breakdown of costs
          "GBP £", //вствить валюту и сделать ее жирнее
          $intTotalPartsClient*1+$arrTableData['labour_plus_mileage']*1], //втавить сумму затрат Total (pre vat)
        ];
        //задаем действие (внос значений)
        $body    = new Google_Service_Sheets_ValueRange( [ 'values' => $values ] );
        //опция для вноса значений в первую попавшуюся пустую строку
        $insert = [
          'insertDataOption' => 'INSERT_ROWS'
        ];

        // valueInputOption - определяет способ интерпретации входных данных
        // https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption
        // RAW | USER_ENTERED
        $options = array( 'valueInputOption' => 'USER_ENTERED' );
        //сама строка вноса
        $service->spreadsheets_values->append( $config['SprInvoiReque'], $config['SheInvoiReque'], $body, $options, $insert);
      }catch(Exception $e){return false;}
  }


  //если есть сумма за детали
 if($intTotalParts>0){
   //вносим в таблицу оплат
   try{
       // Require composer autoload
       require_once $strPath.'admin/lib/php2gsheets/vendor/autoload.php';
       //Reading data from spreadsheet.
       $client = new \Google_Client();
       //название для процесса
       $client->setApplicationName('PHP2GoogleSheets');
       //тип подключения к таблице
       $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
       //принцип работы с таблицой
       $client->setAccessType('offline');
       //путь к файлу ключу для авторизации в гугл
       $client->setAuthConfig($strPath . 'admin/lib/php2gsheets/PHP2spreadsheetAPI-4bcc0fa28826.json');
       //создаем подключение
       $service = new Google_Service_Sheets($client);

       // https://developers.google.com/sheets/api/reference/rest/v4/spreadsheets.values/update
       //вносим стролько строк данных сколько предал юзер в джобшите
       $values = array();
       for ($i=0; $i<count($arrTableData['work_spreadsheet']['Price']); $i++) {
         if($arrTableData['work_spreadsheet']['Price'][$i] != 0 or $arrTableData['work_spreadsheet']['Price'][$i] != '' or $arrTableData['work_spreadsheet']['Supplier'][$i] != '' or $arrTableData['work_spreadsheet']['Parts'][$i] != ''){
           $values[] = array(getBrowserDate(time()),//втавить дату Timestamp
                             $_SESSION['new']['email'],//вставить мейл адрес
                             "Greenheath Limited",//вставить компанию
                             'Job sheet ref #'.$strTaskId.' '.$arrTableData['staff_initial'],//вставить номер заявки и инициалы исполнителя
                             $arrTableData['staff_initial'],//вставить инициалы
                             $arrTableData['work_spreadsheet']['Supplier'][$i],//вставить поставщики
                             $arrTableData['work_spreadsheet']['Parts'][$i],//вставить детали
                             $arrTableData['work_spreadsheet']['Price'][$i],//цену закупки
                             "Account");//вставить тип оплаты
         }
       }

       //задаем действие (внос значений)
       $body    = new Google_Service_Sheets_ValueRange( [ 'values' => $values ] );
       //опция для вноса значений в первую попавшуюся пустую строку
       $insert = [
         'insertDataOption' => 'INSERT_ROWS'
       ];

       // valueInputOption - определяет способ интерпретации входных данных
       // https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption
       // RAW | USER_ENTERED
       $options = array( 'valueInputOption' => 'USER_ENTERED' );
       //сама строка вноса
       $service->spreadsheets_values->append( $config['SprOrderEntry'], $config['SheOrderEntry'], $body, $options, $insert);
     }catch(Exception $e){return false;}
 }


  return true;
}
