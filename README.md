# SimplyLanding
SimplyLanding (SL) - is simply CMS and visualization for the creation and managing basic dual-language landing site 

## Install guide
1. Download repository
1. Unpack downloaded archive into hosting folder
1. Open folder from step 2 via your browser
1. Complete setup from the wizard, go to admin panel for add all data into your new site  (http(s)://yourdomainand sitefolder/**admin**/)

**That's all..**

 If you need to use custom design(theme) ﹣ you can read about all output variables which will bring data into your HTML pages below
## Files info  
- root folder ← contains install file, errors files and theme files (css, index, page...)
  - admin folder ← contains login and index files for CMS ﹣ all for giving you a possibility to manage your site content
    - css folder ← contains css for admin panel interface
    - images folder ← contains images uploaded from admin panel and other site images
    - inc folder ← contains PHP libraries for working with database and interfaces
    - lib folder ← contains foreign libraries such as bootstrap or font awesome
    

## Variables info
If you need to use your own HTML design with SL CMS - add into your page (in the top of the file) PHP block below (and change file format to PHP - example: index.html→index.php) - it will connect CMS to your HTML file and then all data inserted from the administration panel will be able to show at your page - just insert any variable from the list below  
> example: if I need to insert slider header text at someplace of my HTML page, I will write at that place that  
`<?=$arrAllData[Slider][header]?>`  

`<?php`   
`  // errors rule`  
`  error_reporting(E_ALL & ~E_NOTICE); // Уровень вывода ошибок (без нотисов)`  
`  // Absolute path`  
`  $path = dirname(__FILE__) . '/';`  
`  // Connecting database config and scripts`  
`  include_once $path . 'admin/inc/db.inc.php';`  
`  include_once $path . 'admin/inc/funclibery.inc.php';`  
`  include_once $path . 'admin/inc/getData.inc.php';`  
`  //there is no config file - not installed, then we start the installation`  
`  //we check if there is data about the superuser, if everything is there - ok, no, then the installation is not finished`  
`  if(file_exists($path.'admin/inc/config.inc.php')){`  
`    // Config connection`  
`    include_once $path.'admin/inc/config.inc.php';`  
`    //connect to the database for all outgoing requests`  
`    $objDB = GoToDB($config['connetion'], $config['user'], $config['password']);`  
`    //check if there is a connection and if there is our superadmin`  
`    if(!$objDB or !checkUserBy('signature',$config['SuperUser'], false)){`  
`      //no, we send for installation`  
`      header("Location: install.php");exit;}`  
`  //there is no config file, we send for installation`  
`  }else{header("Location: install.php");exit;}`  
`  // Encoding header`  
`  header('Content-type: text/html; charset='.$config['encoding']);`  
`  //get all variables for index.php file - details ---- → Readme`  
`  $arrAllData = getVariables(basename(__FILE__),$strLanguage);`  
`?>`  

### Variables list  

`$arrAllData[Slider][header]`

`$arrAllData[Slider][description]`

`$arrAllData[hardskill_header][header]`

`$arrAllData[hardskill_header][description]`

`$arrAllData[softskill_header][header]`

`$arrAllData[softskill_header][description]`

`$arrAllData[SkillHard][headers][0-n]`

`$arrAllData[SkillHard][descriptions][0-n]`

`$arrAllData[SkillSoft][headers][0-n]`

`$arrAllData[SkillSoft][descriptions][0-n]`

`$arrAllData[portfolio_header][header]`

`$arrAllData[portfolio_header][description]`

`$arrAllData[Feedback][header]`

`$arrAllData[Feedback][description]`

`$arrAllData[partners_header][header]`

`$arrAllData[partners_header][description]`

`$arrAllData[сontacts_header][header]`

`$arrAllData[сontacts_header][description]`

`$arrAllData[сontacts_sec_header][header]`

`$arrAllData[сontacts_sec_header][description]`

`$arrAllData[Email][header]`

`$arrAllData[Email][description]`

`$arrAllData[Facebook][header]`

`$arrAllData[Facebook][description]`

`$arrAllData[instagram][header]`

`$arrAllData[instagram][description]`

`$arrAllData[youtube][header]`

`$arrAllData[youtube][description]`

`$arrAllData[upwork][header]`

`$arrAllData[upwork][description]`

`$arrAllData[linkedin][header]`

`$arrAllData[linkedin][description]`

`$arrAllData[sitetitle][header]`

`$arrAllData[sitetitle][description]`

`$arrAllData[prColor][header]`

`$arrAllData[prColor][description]`

`$arrAllData[sitefont][header]`

`$arrAllData[sitefont][description]`

`$arrAllData[addScripts][header]`

`$arrAllData[addScripts][description]`

`$arrAllData[phone][header]`

`$arrAllData[phone][description]`

`$arrAllData[About][header]`

`$arrAllData[About][description]`

`$arrAllData[location][header]`

`$arrAllData[location][description]`

`$arrAllData[feedbacks][images][0-n]`

`$arrAllData[feedbacks][names][0-n]`

`$arrAllData[feedbacks][positions][0-n]`

`$arrAllData[feedbacks][texts][0-n]`

`$arrAllData[FavIco][image]`

`$arrAllData[SliderIMG][images][0-n]`

`$arrAllData[Partners][images][0-n]`

`$arrAllData[portfolio][ids][0-n]`

`$arrAllData[portfolio][item_categorys][0-n]`

`$arrAllData[portfolio][images][0-n][0-n]`

`$arrAllData[portfolio][headers][0-n]`

`$arrAllData[portfolio][texts][0-n]`

`$arrAllData[categories][names][0-n]`

`$arrAllData[categories][images][0-n]`

