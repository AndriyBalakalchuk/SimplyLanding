<?php
//подтягиваем контент для страниц и возвращаем в виде большого массива
function getVariables($strForPage,$strLanguage ='',$intItemId='nAn'){
  global $config, $navbarDef, $navbarDef_en, $feedbackDef, $footerDef;
  //переменная агрегатор данных
  $arrAllData = array();
  //переменные посредники
  $arrMidlers = array();
  $arrMidler = array();
  //счетчики для формирования массивов
  $intSkHaCount = 0;
  $intSkSoCount = 0;
  $intFeedCount = 0;
  $intImgSliCount = 0;
  $intImgPartCount = 0;
  $intPortCount = 0;

  /*--------------------------------------------*/
  /*----подготовка формы сборки мейлов связи----*/
  /*--------------------------------------------*/
  $arrAllData['footer'] = array();
  $arrAllData['footer'] = $footerDef['lang'.$strLanguage];
  /*--------------------------------------------*/
  /*---------подготовка навбара-----------------*/
  /*--------------------------------------------*/
  if($strLanguage ==''){$arrNavbar = $navbarDef;}else{$arrNavbar = $navbarDef_en;}
    $arrAllData['Navbar'] = array();
  foreach ($arrNavbar as $arrElement) {
    $arrAllData['Navbar'][] = $arrElement;
  }




  //если мы на главной странице
  if($strForPage == 'index.php'){

    /*--------------------------------------------*/
    /*---------подготовка формы обратной связи----*/
    /*--------------------------------------------*/
    $arrAllData['feedbackForm'] = array();
    $arrAllData['feedbackForm'] = $feedbackDef['form'.$strLanguage];

    /*--------------------------------------------*/
    /*---------таблица контента-------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для слайдера - создаст таблицу если ее небыло
    CheckData('sl_content', 'content_for', 'Slider');
    $arrMidlers = selectFromTable('sl_content', array('content_for', 'text_big'.$strLanguage, 'text_small'.$strLanguage));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        switch ($arrMidler['content_for']) {
          case 'Slider':
            $arrAllData['Slider'] = array();
            $arrAllData['Slider']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Slider']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'hardskill_header':
            $arrAllData['hardskill_header'] = array();
            $arrAllData['hardskill_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['hardskill_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'softskill_header':
            $arrAllData['softskill_header'] = array();
            $arrAllData['softskill_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['softskill_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Skills':
            $arrAllData['Skills'] = array();
            $arrAllData['Skills']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Skills']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Categories':
            $arrAllData['Categories'] = array();
            $arrAllData['Categories']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Categories']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'SkillHard':
            if($intSkHaCount == 0){
            $arrAllData['SkillHard'] = array();
            $arrAllData['SkillHard']['headers'] = array();
            $arrAllData['SkillHard']['descriptions'] = array();
            }
            $intSkHaCount++;
            $arrAllData['SkillHard']['headers'][] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['SkillHard']['descriptions'][] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'SkillSoft':
            if($intSkSoCount == 0){
            $arrAllData['SkillSoft'] = array();
            $arrAllData['SkillSoft']['headers'] = array();
            $arrAllData['SkillSoft']['descriptions'] = array();
            }
            $intSkSoCount++;
            $arrAllData['SkillSoft']['headers'][] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['SkillSoft']['descriptions'][] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'portfolio_header':
            $arrAllData['portfolio_header'] = array();
            $arrAllData['portfolio_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['portfolio_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Feedback':
            $arrAllData['Feedback'] = array();
            $arrAllData['Feedback']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Feedback']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'partners_header':
            $arrAllData['partners_header'] = array();
            $arrAllData['partners_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['partners_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'сontacts_header':
            $arrAllData['сontacts_header'] = array();
            $arrAllData['сontacts_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['сontacts_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'сontacts_sec_header':
            $arrAllData['сontacts_sec_header'] = array();
            $arrAllData['сontacts_sec_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['сontacts_sec_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Email':
            $arrAllData['Email'] = array();
            $arrAllData['Email']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Email']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Facebook':
            $arrAllData['Facebook'] = array();
            $arrAllData['Facebook']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Facebook']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'instagram':
            $arrAllData['instagram'] = array();
            $arrAllData['instagram']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['instagram']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'youtube':
            $arrAllData['youtube'] = array();
            $arrAllData['youtube']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['youtube']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'upwork':
            $arrAllData['upwork'] = array();
            $arrAllData['upwork']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['upwork']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'linkedin':
            $arrAllData['linkedin'] = array();
            $arrAllData['linkedin']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['linkedin']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'sitetitle':
            $arrAllData['sitetitle'] = array();
            $arrAllData['sitetitle']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['sitetitle']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'prColor':
            $arrAllData['prColor'] = array();
            $arrAllData['prColor']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['prColor']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'sitefont':
            $arrAllData['sitefont'] = array();
            $arrAllData['sitefont']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['sitefont']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'addScripts':
            $arrAllData['addScripts'] = array();
            $arrAllData['addScripts']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['addScripts']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'phone':
            $arrAllData['phone'] = array();
            $arrAllData['phone']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['phone']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'About':
            $arrAllData['About'] = array();
            $arrAllData['About']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['About']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'location':
            $arrAllData['location'] = array();
            $arrAllData['location']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['location']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
        }
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['Slider'])){
      $arrAllData['Slider'] = array();
      $arrAllData['Slider']['header'] = 'Lorem ipsum';
      $arrAllData['Slider']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['hardskill_header'])){
      $arrAllData['hardskill_header'] = array();
      $arrAllData['hardskill_header']['header'] = 'Lorem ipsum';
      $arrAllData['hardskill_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['softskill_header'])){
      $arrAllData['softskill_header'] = array();
      $arrAllData['softskill_header']['header'] = 'Lorem ipsum';
      $arrAllData['softskill_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Skills'])){
      $arrAllData['Skills'] = array();
      $arrAllData['Skills']['header'] = 'Lorem ipsum';
      $arrAllData['Skills']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Categories'])){
      $arrAllData['Categories'] = array();
      $arrAllData['Categories']['header'] = 'Lorem ipsum';
      $arrAllData['Categories']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['SkillHard'])){
      $arrAllData['SkillHard'] = array();
      $arrAllData['SkillHard']['headers'] = array('Lorem ipsum');
      $arrAllData['SkillHard']['descriptions'] = array('50');
    }
    if(!is_array($arrAllData['SkillSoft'])){
      $arrAllData['SkillSoft'] = array();
      $arrAllData['SkillSoft']['headers'] = array('Lorem ipsum');
      $arrAllData['SkillSoft']['descriptions'] = array('50');
    }
    if(!is_array($arrAllData['portfolio_header'])){
      $arrAllData['portfolio_header'] = array();
      $arrAllData['portfolio_header']['header'] = 'Lorem ipsum';
      $arrAllData['portfolio_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Feedback'])){
      $arrAllData['Feedback'] = array();
      $arrAllData['Feedback']['header'] = 'Lorem ipsum';
      $arrAllData['Feedback']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['partners_header'])){
      $arrAllData['partners_header'] = array();
      $arrAllData['partners_header']['header'] = 'Lorem ipsum';
      $arrAllData['partners_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['сontacts_header'])){
      $arrAllData['сontacts_header'] = array();
      $arrAllData['сontacts_header']['header'] = 'Lorem ipsum';
      $arrAllData['сontacts_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['сontacts_sec_header'])){
      $arrAllData['сontacts_sec_header'] = array();
      $arrAllData['сontacts_sec_header']['header'] = 'Lorem ipsum';
      $arrAllData['сontacts_sec_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Email'])){
      $arrAllData['Email'] = array();
      $arrAllData['Email']['header'] = 'Lorem@ipsum';
      $arrAllData['Email']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Facebook'])){
      $arrAllData['Facebook'] = array();
      $arrAllData['Facebook']['header'] = 'Lorem ipsum';
      $arrAllData['Facebook']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['instagram'])){
      $arrAllData['instagram'] = array();
      $arrAllData['instagram']['header'] = 'Lorem ipsum';
      $arrAllData['instagram']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['youtube'])){
      $arrAllData['youtube'] = array();
      $arrAllData['youtube']['header'] = 'Lorem ipsum';
      $arrAllData['youtube']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['upwork'])){
      $arrAllData['upwork'] = array();
      $arrAllData['upwork']['header'] = 'Lorem ipsum';
      $arrAllData['upwork']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['linkedin'])){
      $arrAllData['linkedin'] = array();
      $arrAllData['linkedin']['header'] = 'Lorem ipsum';
      $arrAllData['linkedin']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['sitetitle'])){
      $arrAllData['sitetitle'] = array();
      $arrAllData['sitetitle']['header'] = 'Simply title';
      $arrAllData['sitetitle']['description'] = '';
    }
    if(!is_array($arrAllData['prColor'])){
      $arrAllData['prColor'] = array();
      $arrAllData['prColor']['header'] = '#00a3ff';
      $arrAllData['prColor']['description'] = '';
    }
    if(!is_array($arrAllData['sitefont'])){
      $arrAllData['sitefont'] = array();
      $arrAllData['sitefont']['header'] = "'Roboto', sans-serif";
      $arrAllData['sitefont']['description'] = '';
    }
    if(!is_array($arrAllData['addScripts'])){
      $arrAllData['addScripts'] = array();
      $arrAllData['addScripts']['header'] = '';
      $arrAllData['addScripts']['description'] = '';
    }
    if(!is_array($arrAllData['phone'])){
      $arrAllData['phone'] = array();
      $arrAllData['phone']['header'] = '+000 564-45-45';
      $arrAllData['phone']['description'] = '';
    }
    if(!is_array($arrAllData['About'])){
      $arrAllData['About'] = array();
      $arrAllData['About']['header'] = 'Lorem ipsum';
      $arrAllData['About']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['location'])){
      $arrAllData['location'] = array();
      $arrAllData['location']['header'] = 'USA, California';
      $arrAllData['location']['description'] = '';
    }
    
    /*--------------------------------------------*/
    /*---------таблица отзывов--------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для отзывов - создаст таблицу если ее небыло
    CheckData('sl_feedback', 'text_big', '', true);
    $arrMidlers = selectFromTable('sl_feedback', array('id','image','text_big'.$strLanguage, 'text_company'.$strLanguage, 'text_small'));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        if($intFeedCount==0){
        $arrAllData['feedbacks'] = array();
        $arrAllData['feedbacks']['images'] = array();
        $arrAllData['feedbacks']['names'] = array();
        $arrAllData['feedbacks']['positions'] = array();
        $arrAllData['feedbacks']['texts'] = array();
        }
        $intFeedCount++;
        $arrAllData['feedbacks']['images'][] = '<img class="avatar" src="'.$config['sitelink'].'admin/images/ClientAvatar/'.$arrMidler['image'].'">';
        $arrAllData['feedbacks']['names'][] = $arrMidler['text_big'.$strLanguage];
        $arrAllData['feedbacks']['positions'][] = $arrMidler['text_company'.$strLanguage];
        $arrAllData['feedbacks']['texts'][] = $arrMidler['text_small'];
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['feedbacks'])){
      $arrAllData['feedbacks'] = array();
      $arrAllData['feedbacks']['images'] = array('<img class="avatar" src="'.getSVGplaceholder(70, 70).'">');
      $arrAllData['feedbacks']['names'] = array('Lorem ipsum');
      $arrAllData['feedbacks']['positions'] = array('Lorem ipsum');
      $arrAllData['feedbacks']['texts'] = array('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
    }
    /*--------------------------------------------*/
    /*---------таблица изображений----------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для слайдера - создаст таблицу если ее небыло
    CheckData('sl_images', 'image_for', 'Slider');
    $arrMidlers = selectFromTable('sl_images', array('image_for', 'image_name'));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        switch ($arrMidler['image_for']) {
          case 'Slider':
            if($intImgSliCount==0){
              $arrAllData['SliderIMG'] = array();
              $arrAllData['SliderIMG']['images'] = array();
            }
            $intImgSliCount++;
            $arrAllData['SliderIMG']['images'][] = $config['sitelink'].'admin/images/Slider/'.$arrMidler['image_name'];
          break;
          case 'FavIco':
            $arrAllData['FavIco'] = array();
            $arrAllData['FavIco']['image'] = $config['sitelink'].'admin/images/'.$arrMidler['image_name'];
          break;
          case 'Partners':
            if($intImgPartCount==0){
              $arrAllData['Partners'] = array();
              $arrAllData['Partners']['images'] = array();
            }
            $intImgPartCount++;
            $arrAllData['Partners']['images'][] = '<img src="'.$config['sitelink'].'admin/images/Slider/'.$arrMidler['image_name'].'">';
          break;
        }
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['SliderIMG'])){
      $arrAllData['SliderIMG'] = array();
      $arrAllData['SliderIMG']['images'] = array(getSVGplaceholder(570, 600));
    }
    if(!is_array($arrAllData['FavIco'])){
      $arrAllData['FavIco'] = array();
      $arrAllData['FavIco']['image'] = $config['Favicon'];
    }
    if(!is_array($arrAllData['Partners'])){
      $arrAllData['Partners'] = array();
      $arrAllData['Partners']['images'] = array('<div style="width:570px;height:600px;">'.getSVGplaceholder(570, 600).'</div>');
    }

    /*--------------------------------------------*/
    /*---------таблица портфолио------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для портфолио - создаст таблицу если ее небыло
    CheckData('sl_portfolio', 'item_for', '', true);
    $arrMidlers = selectFromTable('sl_portfolio', array('id','item_category'.$strLanguage,'images','text_big'.$strLanguage, 'text_small'.$strLanguage));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        if($intPortCount==0){
        $arrAllData['portfolio'] = array();
        $arrAllData['portfolio']['ids'] = array();
        $arrAllData['portfolio']['item_categorys'] = array();
        $arrAllData['portfolio']['images'] = array();
        $arrAllData['portfolio']['headers'] = array();
        $arrAllData['portfolio']['texts'] = array();
        }
        $intPortCount++;
        $arrAllData['portfolio']['ids'][] = $arrMidler['id'];
        $arrAllData['portfolio']['item_categorys'][] = $arrMidler['item_category'.$strLanguage];
        $arrImages_or = getImagesFromStr($arrMidler['images']);
        if(is_array($arrImages_or)){
          $arrAllData['portfolio']['images'][] = $arrImages_or;
        }else{
          $arrAllData['portfolio']['images'][] = array(getSVGplaceholder(475, 525));
        }
        $arrAllData['portfolio']['headers'][] = $arrMidler['text_big'.$strLanguage];
        $arrAllData['portfolio']['texts'][] = mb_strimwidth(Strip_tags(trim($arrMidler['text_small'.$strLanguage])), 0, 295, "...");
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['portfolio'])){
      $arrAllData['portfolio'] = array();
      $arrAllData['portfolio']['ids'] = array('nAn');
      $arrAllData['portfolio']['item_categorys'] = array('nAn');
      $arrAllData['portfolio']['images'] = array(getSVGplaceholder(475, 525));
      $arrAllData['portfolio']['headers'] = array('Lorem ipsum');
      $arrAllData['portfolio']['texts'] = array('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
    }
    /*--------------------------------------------*/
    /*-------выводим категории для портфолио------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для портфолио - создаст таблицу если ее небыло
    $arrAllData['categories'] = getCategorys('Blocks'.$strLanguage);
    //нету категорий 
    if($arrAllData['categories']=='no categories yet'){
      $arrAllData['categories'] = array();
      $arrAllData['categories']['names'] = array('Lorem ipsum');
      $arrAllData['categories']['images'] = array(getSVGplaceholder(475, 525));
    }
  }elseif($strForPage == 'item.php'){//если мы на странице итема
    /*--------------------------------------------*/
    /*---------таблица изображений----------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для слайдера - создаст таблицу если ее небыло
    CheckData('sl_images', 'image_for', 'Slider');
    $arrMidlers = selectFromTable('sl_images', array('image_for', 'image_name'));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        switch ($arrMidler['image_for']) {
          case 'FavIco':
            $arrAllData['FavIco'] = array();
            $arrAllData['FavIco']['image'] = $config['sitelink'].'admin/images/'.$arrMidler['image_name'];
          break;
        }
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['FavIco'])){
      $arrAllData['FavIco'] = array();
      $arrAllData['FavIco']['image'] = $config['Favicon'];
    }

    /*--------------------------------------------*/
    /*---------таблица контента-------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для слайдера - создаст таблицу если ее небыло
    CheckData('sl_content', 'content_for', 'Slider');
    $arrMidlers = selectFromTable('sl_content', array('content_for', 'text_big'.$strLanguage, 'text_small'.$strLanguage));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        switch ($arrMidler['content_for']) {
          case 'hardskill_header':
            $arrAllData['hardskill_header'] = array();
            $arrAllData['hardskill_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['hardskill_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'softskill_header':
            $arrAllData['softskill_header'] = array();
            $arrAllData['softskill_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['softskill_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'portfolio_header':
            $arrAllData['portfolio_header'] = array();
            $arrAllData['portfolio_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['portfolio_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'SkillHard':
            if($intSkHaCount == 0){
            $arrAllData['SkillHard'] = array();
            $arrAllData['SkillHard']['headers'] = array();
            $arrAllData['SkillHard']['descriptions'] = array();
            }
            $intSkHaCount++;
            $arrAllData['SkillHard']['headers'][] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['SkillHard']['descriptions'][] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'SkillSoft':
            if($intSkSoCount == 0){
            $arrAllData['SkillSoft'] = array();
            $arrAllData['SkillSoft']['headers'] = array();
            $arrAllData['SkillSoft']['descriptions'] = array();
            }
            $intSkSoCount++;
            $arrAllData['SkillSoft']['headers'][] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['SkillSoft']['descriptions'][] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Categories':
            $arrAllData['Categories'] = array();
            $arrAllData['Categories']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Categories']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Feedback':
            $arrAllData['Feedback'] = array();
            $arrAllData['Feedback']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Feedback']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'сontacts_sec_header':
            $arrAllData['сontacts_sec_header'] = array();
            $arrAllData['сontacts_sec_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['сontacts_sec_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Email':
            $arrAllData['Email'] = array();
            $arrAllData['Email']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Email']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Facebook':
            $arrAllData['Facebook'] = array();
            $arrAllData['Facebook']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Facebook']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'instagram':
            $arrAllData['instagram'] = array();
            $arrAllData['instagram']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['instagram']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'youtube':
            $arrAllData['youtube'] = array();
            $arrAllData['youtube']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['youtube']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'upwork':
            $arrAllData['upwork'] = array();
            $arrAllData['upwork']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['upwork']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'linkedin':
            $arrAllData['linkedin'] = array();
            $arrAllData['linkedin']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['linkedin']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'sitetitle':
            $arrAllData['sitetitle'] = array();
            $arrAllData['sitetitle']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['sitetitle']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'prColor':
            $arrAllData['prColor'] = array();
            $arrAllData['prColor']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['prColor']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'sitefont':
            $arrAllData['sitefont'] = array();
            $arrAllData['sitefont']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['sitefont']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'addScripts':
            $arrAllData['addScripts'] = array();
            $arrAllData['addScripts']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['addScripts']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'phone':
            $arrAllData['phone'] = array();
            $arrAllData['phone']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['phone']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'About':
            $arrAllData['About'] = array();
            $arrAllData['About']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['About']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'location':
            $arrAllData['location'] = array();
            $arrAllData['location']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['location']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
        }
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['softskill_header'])){
      $arrAllData['softskill_header'] = array();
      $arrAllData['softskill_header']['header'] = 'Lorem ipsum';
      $arrAllData['softskill_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Skills'])){
      $arrAllData['Skills'] = array();
      $arrAllData['Skills']['header'] = 'Lorem ipsum';
      $arrAllData['Skills']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Categories'])){
      $arrAllData['Categories'] = array();
      $arrAllData['Categories']['header'] = 'Lorem ipsum';
      $arrAllData['Categories']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['SkillHard'])){
      $arrAllData['SkillHard'] = array();
      $arrAllData['SkillHard']['headers'] = array('Lorem ipsum');
      $arrAllData['SkillHard']['descriptions'] = array('50');
    }
    if(!is_array($arrAllData['portfolio_header'])){
      $arrAllData['portfolio_header'] = array();
      $arrAllData['portfolio_header']['header'] = 'Lorem ipsum';
      $arrAllData['portfolio_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['SkillSoft'])){
      $arrAllData['SkillSoft'] = array();
      $arrAllData['SkillSoft']['headers'] = array('Lorem ipsum');
      $arrAllData['SkillSoft']['descriptions'] = array('50');
    }
    if(!is_array($arrAllData['Feedback'])){
      $arrAllData['Feedback'] = array();
      $arrAllData['Feedback']['header'] = 'Lorem ipsum';
      $arrAllData['Feedback']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['сontacts_sec_header'])){
      $arrAllData['сontacts_sec_header'] = array();
      $arrAllData['сontacts_sec_header']['header'] = 'Lorem ipsum';
      $arrAllData['сontacts_sec_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Email'])){
      $arrAllData['Email'] = array();
      $arrAllData['Email']['header'] = 'Lorem@ipsum';
      $arrAllData['Email']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Facebook'])){
      $arrAllData['Facebook'] = array();
      $arrAllData['Facebook']['header'] = 'Lorem ipsum';
      $arrAllData['Facebook']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['instagram'])){
      $arrAllData['instagram'] = array();
      $arrAllData['instagram']['header'] = 'Lorem ipsum';
      $arrAllData['instagram']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['youtube'])){
      $arrAllData['youtube'] = array();
      $arrAllData['youtube']['header'] = 'Lorem ipsum';
      $arrAllData['youtube']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['upwork'])){
      $arrAllData['upwork'] = array();
      $arrAllData['upwork']['header'] = 'Lorem ipsum';
      $arrAllData['upwork']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['linkedin'])){
      $arrAllData['linkedin'] = array();
      $arrAllData['linkedin']['header'] = 'Lorem ipsum';
      $arrAllData['linkedin']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['sitetitle'])){
      $arrAllData['sitetitle'] = array();
      $arrAllData['sitetitle']['header'] = 'Simply title';
      $arrAllData['sitetitle']['description'] = '';
    }
    if(!is_array($arrAllData['prColor'])){
      $arrAllData['prColor'] = array();
      $arrAllData['prColor']['header'] = '#00a3ff';
      $arrAllData['prColor']['description'] = '';
    }
    if(!is_array($arrAllData['sitefont'])){
      $arrAllData['sitefont'] = array();
      $arrAllData['sitefont']['header'] = "'Roboto', sans-serif";
      $arrAllData['sitefont']['description'] = '';
    }
    if(!is_array($arrAllData['addScripts'])){
      $arrAllData['addScripts'] = array();
      $arrAllData['addScripts']['header'] = '';
      $arrAllData['addScripts']['description'] = '';
    }
    if(!is_array($arrAllData['phone'])){
      $arrAllData['phone'] = array();
      $arrAllData['phone']['header'] = '+000 564-45-45';
      $arrAllData['phone']['description'] = '';
    }
    if(!is_array($arrAllData['About'])){
      $arrAllData['About'] = array();
      $arrAllData['About']['header'] = 'Lorem ipsum';
      $arrAllData['About']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['location'])){
      $arrAllData['location'] = array();
      $arrAllData['location']['header'] = 'USA, California';
      $arrAllData['location']['description'] = '';
    }
    
    /*--------------------------------------------*/
    /*---------таблица портфолио------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для портфолио - создаст таблицу если ее небыло
    CheckData('sl_portfolio', 'item_for', '', true);
    $arrMidlers = selectFromTable('sl_portfolio', array('id','item_category'.$strLanguage,'images','text_big'.$strLanguage, 'text_small'.$strLanguage), true, 'id', $_GET['item']);
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        if($intPortCount==0){
        $arrAllData['portfolio'] = array();
        $arrAllData['portfolio']['ids'] = array();
        $arrAllData['portfolio']['item_categorys'] = array();
        $arrAllData['portfolio']['images'] = array();
        $arrAllData['portfolio']['headers'] = array();
        $arrAllData['portfolio']['texts'] = array();
        }
        $intPortCount++;
        $arrAllData['portfolio']['ids'][] = $arrMidler['id'];
        $arrAllData['portfolio']['item_categorys'][] = $arrMidler['item_category'.$strLanguage];
        $arrImages_or = getImagesFromStr($arrMidler['images']);
        if(is_array($arrImages_or)){
          $arrAllData['portfolio']['images'][] = $arrImages_or;
        }else{
          $arrAllData['portfolio']['images'][] = array(getSVGplaceholder(475, 525));
        }
        $arrAllData['portfolio']['headers'][] = $arrMidler['text_big'.$strLanguage];
        $arrAllData['portfolio']['texts'][] = $arrMidler['text_small'.$strLanguage];
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['portfolio'])){
      $arrAllData['portfolio'] = array();
      $arrAllData['portfolio']['ids'] = array('nAn');
      $arrAllData['portfolio']['item_categorys'] = array('nAn');
      $arrAllData['portfolio']['images'] = array(getSVGplaceholder(475, 525));
      $arrAllData['portfolio']['headers'] = array('Lorem ipsum');
      $arrAllData['portfolio']['texts'] = array('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
    }
    /*--------------------------------------------*/
    /*-------выводим категории для портфолио------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для портфолио - создаст таблицу если ее небыло
    $arrAllData['categories'] = getCategorys('Blocks'.$strLanguage);
    //нету категорий 
    if($arrAllData['categories']=='no categories yet'){
      $arrAllData['categories'] = array();
      $arrAllData['categories']['names'] = array('Lorem ipsum');
      $arrAllData['categories']['images'] = array(getSVGplaceholder(475, 525));
    }
  }elseif($strForPage == 'category.php'){//если мы на странице категорий
    /*--------------------------------------------*/
    /*---------таблица изображений----------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для слайдера - создаст таблицу если ее небыло
    CheckData('sl_images', 'image_for', 'Slider');
    $arrMidlers = selectFromTable('sl_images', array('image_for', 'image_name'));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        switch ($arrMidler['image_for']) {
          case 'FavIco':
            $arrAllData['FavIco'] = array();
            $arrAllData['FavIco']['image'] = $config['sitelink'].'admin/images/'.$arrMidler['image_name'];
          break;
        }
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['FavIco'])){
      $arrAllData['FavIco'] = array();
      $arrAllData['FavIco']['image'] = $config['Favicon'];
    }
    /*--------------------------------------------*/
    /*---------таблица контента-------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для слайдера - создаст таблицу если ее небыло
    CheckData('sl_content', 'content_for', 'Slider');
    $arrMidlers = selectFromTable('sl_content', array('content_for', 'text_big'.$strLanguage, 'text_small'.$strLanguage));
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        switch ($arrMidler['content_for']) {
          case 'hardskill_header':
            $arrAllData['hardskill_header'] = array();
            $arrAllData['hardskill_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['hardskill_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'softskill_header':
            $arrAllData['softskill_header'] = array();
            $arrAllData['softskill_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['softskill_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'portfolio_header':
            $arrAllData['portfolio_header'] = array();
            $arrAllData['portfolio_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['portfolio_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'SkillHard':
            if($intSkHaCount == 0){
            $arrAllData['SkillHard'] = array();
            $arrAllData['SkillHard']['headers'] = array();
            $arrAllData['SkillHard']['descriptions'] = array();
            }
            $intSkHaCount++;
            $arrAllData['SkillHard']['headers'][] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['SkillHard']['descriptions'][] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'SkillSoft':
            if($intSkSoCount == 0){
            $arrAllData['SkillSoft'] = array();
            $arrAllData['SkillSoft']['headers'] = array();
            $arrAllData['SkillSoft']['descriptions'] = array();
            }
            $intSkSoCount++;
            $arrAllData['SkillSoft']['headers'][] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['SkillSoft']['descriptions'][] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Categories':
            $arrAllData['Categories'] = array();
            $arrAllData['Categories']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Categories']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Feedback':
            $arrAllData['Feedback'] = array();
            $arrAllData['Feedback']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Feedback']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'сontacts_sec_header':
            $arrAllData['сontacts_sec_header'] = array();
            $arrAllData['сontacts_sec_header']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['сontacts_sec_header']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Email':
            $arrAllData['Email'] = array();
            $arrAllData['Email']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Email']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'Facebook':
            $arrAllData['Facebook'] = array();
            $arrAllData['Facebook']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['Facebook']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'instagram':
            $arrAllData['instagram'] = array();
            $arrAllData['instagram']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['instagram']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'youtube':
            $arrAllData['youtube'] = array();
            $arrAllData['youtube']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['youtube']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'upwork':
            $arrAllData['upwork'] = array();
            $arrAllData['upwork']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['upwork']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'linkedin':
            $arrAllData['linkedin'] = array();
            $arrAllData['linkedin']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['linkedin']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'sitetitle':
            $arrAllData['sitetitle'] = array();
            $arrAllData['sitetitle']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['sitetitle']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'prColor':
            $arrAllData['prColor'] = array();
            $arrAllData['prColor']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['prColor']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'sitefont':
            $arrAllData['sitefont'] = array();
            $arrAllData['sitefont']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['sitefont']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'addScripts':
            $arrAllData['addScripts'] = array();
            $arrAllData['addScripts']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['addScripts']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'phone':
            $arrAllData['phone'] = array();
            $arrAllData['phone']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['phone']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'About':
            $arrAllData['About'] = array();
            $arrAllData['About']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['About']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
          case 'location':
            $arrAllData['location'] = array();
            $arrAllData['location']['header'] = $arrMidler['text_big'.$strLanguage];
            $arrAllData['location']['description'] = $arrMidler['text_small'.$strLanguage];
          break;
        }
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['softskill_header'])){
      $arrAllData['softskill_header'] = array();
      $arrAllData['softskill_header']['header'] = 'Lorem ipsum';
      $arrAllData['softskill_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Skills'])){
      $arrAllData['Skills'] = array();
      $arrAllData['Skills']['header'] = 'Lorem ipsum';
      $arrAllData['Skills']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Categories'])){
      $arrAllData['Categories'] = array();
      $arrAllData['Categories']['header'] = 'Lorem ipsum';
      $arrAllData['Categories']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['SkillHard'])){
      $arrAllData['SkillHard'] = array();
      $arrAllData['SkillHard']['headers'] = array('Lorem ipsum');
      $arrAllData['SkillHard']['descriptions'] = array('50');
    }
    if(!is_array($arrAllData['portfolio_header'])){
      $arrAllData['portfolio_header'] = array();
      $arrAllData['portfolio_header']['header'] = 'Lorem ipsum';
      $arrAllData['portfolio_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['SkillSoft'])){
      $arrAllData['SkillSoft'] = array();
      $arrAllData['SkillSoft']['headers'] = array('Lorem ipsum');
      $arrAllData['SkillSoft']['descriptions'] = array('50');
    }
    if(!is_array($arrAllData['Feedback'])){
      $arrAllData['Feedback'] = array();
      $arrAllData['Feedback']['header'] = 'Lorem ipsum';
      $arrAllData['Feedback']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['сontacts_sec_header'])){
      $arrAllData['сontacts_sec_header'] = array();
      $arrAllData['сontacts_sec_header']['header'] = 'Lorem ipsum';
      $arrAllData['сontacts_sec_header']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Email'])){
      $arrAllData['Email'] = array();
      $arrAllData['Email']['header'] = 'Lorem@ipsum';
      $arrAllData['Email']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['Facebook'])){
      $arrAllData['Facebook'] = array();
      $arrAllData['Facebook']['header'] = 'Lorem ipsum';
      $arrAllData['Facebook']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['instagram'])){
      $arrAllData['instagram'] = array();
      $arrAllData['instagram']['header'] = 'Lorem ipsum';
      $arrAllData['instagram']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['youtube'])){
      $arrAllData['youtube'] = array();
      $arrAllData['youtube']['header'] = 'Lorem ipsum';
      $arrAllData['youtube']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['upwork'])){
      $arrAllData['upwork'] = array();
      $arrAllData['upwork']['header'] = 'Lorem ipsum';
      $arrAllData['upwork']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['linkedin'])){
      $arrAllData['linkedin'] = array();
      $arrAllData['linkedin']['header'] = 'Lorem ipsum';
      $arrAllData['linkedin']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['sitetitle'])){
      $arrAllData['sitetitle'] = array();
      $arrAllData['sitetitle']['header'] = 'Simply title';
      $arrAllData['sitetitle']['description'] = '';
    }
    if(!is_array($arrAllData['prColor'])){
      $arrAllData['prColor'] = array();
      $arrAllData['prColor']['header'] = '#00a3ff';
      $arrAllData['prColor']['description'] = '';
    }
    if(!is_array($arrAllData['sitefont'])){
      $arrAllData['sitefont'] = array();
      $arrAllData['sitefont']['header'] = "'Roboto', sans-serif";
      $arrAllData['sitefont']['description'] = '';
    }
    if(!is_array($arrAllData['addScripts'])){
      $arrAllData['addScripts'] = array();
      $arrAllData['addScripts']['header'] = '';
      $arrAllData['addScripts']['description'] = '';
    }
    if(!is_array($arrAllData['phone'])){
      $arrAllData['phone'] = array();
      $arrAllData['phone']['header'] = '+000 564-45-45';
      $arrAllData['phone']['description'] = '';
    }
    if(!is_array($arrAllData['About'])){
      $arrAllData['About'] = array();
      $arrAllData['About']['header'] = 'Lorem ipsum';
      $arrAllData['About']['description'] = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
    }
    if(!is_array($arrAllData['location'])){
      $arrAllData['location'] = array();
      $arrAllData['location']['header'] = 'USA, California';
      $arrAllData['location']['description'] = '';
    }
    
    /*--------------------------------------------*/
    /*---------таблица портфолио------------------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для портфолио - создаст таблицу если ее небыло
    CheckData('sl_portfolio', 'item_for', '', true);
    global $boolShowAll;
    if($boolShowAll){
      $arrMidlers = selectFromTable('sl_portfolio', array('id','item_category'.$strLanguage,'images','text_big'.$strLanguage, 'text_small'.$strLanguage));
    }else{
      $arrMidlers = selectFromTable('sl_portfolio', array('id','item_category'.$strLanguage,'images','text_big'.$strLanguage, 'text_small'.$strLanguage), true, 'item_category'.$strLanguage, $_GET['category']);
    }
    //идем по всем строкам таблици и сортируем данные в массив
    if($arrMidlers != ''){
      foreach($arrMidlers as $arrMidler){
        if($intPortCount==0){
          $arrAllData['portfolio'] = array();
          $arrAllData['portfolio']['ids'] = array();
          $arrAllData['portfolio']['item_categorys'] = array();
          $arrAllData['portfolio']['images'] = array();
          $arrAllData['portfolio']['headers'] = array();
          $arrAllData['portfolio']['texts'] = array();
        }
        $intPortCount++;
        $arrAllData['portfolio']['ids'][] = $arrMidler['id'];
        $arrAllData['portfolio']['item_categorys'][] = $arrMidler['item_category'.$strLanguage];
        $arrImages_or = getImagesFromStr($arrMidler['images']);
        if(is_array($arrImages_or)){
          $arrAllData['portfolio']['images'][] = $arrImages_or;
        }else{
          $arrAllData['portfolio']['images'][] = array(getSVGplaceholder(475, 525));
        }
        $arrAllData['portfolio']['headers'][] = $arrMidler['text_big'.$strLanguage];
        $arrAllData['portfolio']['texts'][] = mb_strimwidth(Strip_tags(trim($arrMidler['text_small'.$strLanguage])), 0, 295, "...");
      }//конец цикла по данной таблице
    }
    //проверяем все ли данные есть и если нету, вносим рыбу или плейсхолдеры
    if(!is_array($arrAllData['portfolio'])){
      $arrAllData['portfolio'] = array();
      $arrAllData['portfolio']['ids'] = array('nAn');
      $arrAllData['portfolio']['item_categorys'] = array('nAn');
      $arrAllData['portfolio']['images'] = array(getSVGplaceholder(475, 525));
      $arrAllData['portfolio']['headers'] = array('Lorem ipsum');
      $arrAllData['portfolio']['texts'] = array('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
    }
    /*--------------------------------------------*/
    /*-------выводим категории для портфолио------*/
    /*--------------------------------------------*/
    //проверяем есть ли данные для портфолио - создаст таблицу если ее небыло
    $arrAllData['categories'] = getCategorys('Blocks'.$strLanguage);
    //нету категорий 
    if($arrAllData['categories']=='no categories yet'){
      $arrAllData['categories'] = array();
      $arrAllData['categories']['names'] = array('Lorem ipsum');
      $arrAllData['categories']['images'] = array(getSVGplaceholder(475, 525));
    }
  }

  return $arrAllData;
}