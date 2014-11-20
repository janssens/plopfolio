<?php
/*
Plugin Name: Plopfolio
Description: Allow to manage a portfolio
Version: 1.4
Author: Gaëtan Janssens
Author URI: http://plopcom.fr 
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");


define('PLUGINNAME','plopfolio');

global $LANG;

if (!defined('GSADMIN')) {define('GSADMIN', 'admin');}

if ($SITEURL==""){
  $SITEURL=suggest_site_path();
}
global $SITEURL;
define("SITEURL",$SITEURL);

i18n_merge(PLUGINNAME) || i18n_merge(PLUGINNAME,'en_US');

# register plugin
register_plugin(
	PLUGINNAME, 
	PLUGINNAME,	
	'1.4',     		
	'Gaëtan Janssens',
	'http://plopcom.fr', 
	'Allow to manage a portfolio',
	'pages',
	PLUGINNAME  
);

include(PLUGINNAME.'/settings.php');

register_script('tagmanager', $SITEURL.'plugins/'.$thisfile.'/js/bootstrap-tagmanager.js', '2.3', FALSE);
queue_script('tagmanager',GSBACK);

register_style('bootstrap-tagmanager', $SITEURL.'plugins/'.$thisfile.'/css/bootstrap-tagmanager.css', '1.0', 'screen');
queue_style('bootstrap-tagmanager',GSBACK); 

register_script('bootstrap', $SITEURL.'plugins/'.$thisfile.'/js/bootstrap.js', '2.3.1', FALSE);
queue_script('bootstrap',GSBACK);

register_script('ckeditor', 'template/js/ckeditor/ckeditor.js', '3.6.2', FALSE);
queue_script('ckeditor',GSBACK);


# hooks
//add_action('pages-sidebar', 'createSideMenu', array(PLUGINNAME, PORTFOLIOSIDEBARBUTTONNAME, 'admin'));
add_action('plugins-sidebar','createSideMenu',array(PLUGINNAME, PORTFOLIOSIDEBARSETUPBUTTONNAME, 'setup'));
add_action('nav-tab','createSideMenu',array(PLUGINNAME,PORTFOLIOSIDEBARBUTTONNAME, 'admin'));

add_filter('content','plopfolio_check');

############################### ADMIN FUNCTIONS ################################


/*******************************************************
 * @function plopfolio
 * @action create, edit and delete an entry
 */
function plopfolio() {
  if (isset($_GET['setup'])) {
    if (!file_exists(GSDATAPORTFOLIOPATH)&&!mkdir(GSDATAPORTFOLIOPATH)) {
      echo '<h3>Portfolio Manager</h3><p>The directory "<i>'.GSDATAPORTFOLIOPATH.'</i>" does
      not exist. It is required for this plugin to function properly. Please
      create it manually and make sure it is writable.</p>';
    } else {
      $settings = new PlopfolioSettings();
      if (isset($_POST['entry-big'])&&isset($_POST['entry-small'])) {
        $settings->save_settings();
      }else{
        $settings->load_settings();
      }
      $settings->edit_settings();
    }
  } else {
    if (!file_exists(GSDATAPORTFOLIOPATH)&&!mkdir(GSDATAPORTFOLIOPATH)) {
      echo '<h3>Portfolio Manager</h3><p>The directory "<i>'.GSDATAPORTFOLIOPATH.'</i>" does
      not exist. It is required for this plugin to function properly. Please
      create it manually and make sure it is writable.</p>';
    } elseif (isset($_GET['edit'])) {
      $settings = new PlopfolioSettings();
      $settings->load_settings();
      $id = empty($_GET['edit']) ? uniqid() : $_GET['edit'];
      edit_entry($id);
    } elseif (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      delete_entry($id);
    } elseif (isset($_POST['submit'])) {
      save_entry();
    } elseif (isset($_GET['pick'])) {
      listingfor($_GET['key']);
    } else {
      portfolio_overview();
    }
  }
}


/*******************************************************
 * @function portfolio_overview
 * @action list entry and provide options for editing
 */
function portfolio_overview() {
  global $SITEURL;
  $entries = get_entries();
 ?>
  <label><?php echo PORTFOLIOSIDEBARBUTTONNAME; ?></label>
  <div class="edit-nav" >
    <a href="load.php?id=<?php echo PLUGINNAME; ?>&edit"><?php echo ENTRYNEWLABEL; ?></a>
    <div class="clear"></div>
  </div>
  <br/>
  <h2 id="portfolioindex"><?php echo ENTRYNAME; ?></h2>
<?php  
  $plopfolio_keywords = array();
  if (!empty($entries)) {
	$entries2 = Array();
	$count=0;
    foreach ($entries as $entry) {
      $id = basename($entry, ".xml");
      $file = GSDATAPORTFOLIOPATH . $entry;
      $data = getXML($file);
      $entries2[$count]["id"] = $id;
      $entries2[$count]["visuel"] = $data->visuel;
      $entries2[$count]["thumb"] = $data->thumb;
      $entries2[$count]["month"] = $data->month;
      $entries2[$count]["year"] = $data->year;
      $entries2[$count]["url"] = $data->url;
      $entries2[$count]["name"] = html_entity_decode($data->name, ENT_QUOTES, 'UTF-8');
      $entries2[$count]["client"] = html_entity_decode($data->client, ENT_QUOTES, 'UTF-8');
      $entries2[$count]["keywords"] = html_entity_decode($data->keywords, ENT_QUOTES, 'UTF-8');
      $entries2[$count]["decription"] = html_entity_decode($data->decription, ENT_QUOTES, 'UTF-8');
      $count++;
    }	
     	$entriesSorted = subval_sort($entries2,'year');
    echo '<table class="highlight">';
    $i = 1;
    foreach ($entriesSorted as $entry) {
      ?>
      <tr class="<?php echo strtolower(implode(" ", explode(",", $entry["keywords"]) )); ?>">
        <td class="first" style="background-image:url(<?php if (isset($entry['thumb'])&&strlen($entry['thumb'])>4){ echo $entry['thumb'];}else{ echo "../plugins/".PLUGINNAME."/images/thumb_vide.gif"; } ?>);" >
          <a href="load.php?id=<?php echo PLUGINNAME; ?>&edit=<?php echo $entry['id']; ?>" title="Edit entry: <?php echo $entry['name']; ?>" >
          </a>
        </td>
        <td style="vertical-align:middle;">
          
          <a href="load.php?id=<?php echo PLUGINNAME; ?>&edit=<?php echo $entry['id']; ?>" title="Edit entry: <?php echo $entry['name']; ?>" style="height:60px;display:table-cell; vertical-align:middle;">
            <?php echo $entry["year"]; ?>&nbsp;<strong><?php echo $entry["name"]; ?></strong>
          </a>
        </td>
        <td class="delete">
          <a href="load.php?id=<?php echo PLUGINNAME; ?>&delete=<?php echo $entry['id']; ?>" class="delconfirm" title="Delete entry: <?php echo $entry['name']; ?>?">
            X
          </a>
        </td>
      </tr>
      <?php
      $plopfolio_keywords = array_merge($plopfolio_keywords,explode(",", $entry['keywords']));
      $i++;
    }
    echo '</table>';
  }
  echo '<p><b>' . count($entries) . '</b> ';
  echo ENTRYNAMES;
  echo '</p>';
  ?>
  <style>
    a.filtered{
      color: red !important;
    }
  </style>
  &rarr;&nbsp;<a href="#portfolioindex">Top</a><br/><br/><strong>FILTER:</strong>
  <?php
  if ($plopfolio_keywords && is_array($plopfolio_keywords)){
    foreach ($plopfolio_keywords as $i=>$v) {
      if ($v){
        $plopfolio_keywords[$i] = strtolower(trim($v));
      }else{
        unset($plopfolio_keywords[$i]);
      }
    }

    $plopfolio_keywords = array_unique($plopfolio_keywords);
    asort($plopfolio_keywords);

    foreach ($plopfolio_keywords as $i=>$v) {
      echo '<a href="#" class="filter '.$v.'" onclick="$(\'tr\').not(\'.'.$v.'\').toggle();$(\'a.filter.'.$v.'\').toggleClass(\'filtered\');return false;" >'.$v.'</a>&nbsp;';
    }

    $settings = new PlopfolioSettings();
    $settings->save_data();
    $settings->save_data(array("keywords" => implode(",",$plopfolio_keywords) ));
  }
  //var_dump($plopfolio_keywords);
}

/*******************************************************
 * @function edit_shop
 * @action edit or create new shops
 */
function edit_entry($id) {
    global $LANG;
    global $TEMPLATE;
    global $SITEURL;
    global $EDTOOL;
  if ($id) {
	  $file = GSDATAPORTFOLIOPATH . $id . '.xml';
	  $data = @getXML($file);
	  if ($data) {
		  $visuel = stripslashes($data->visuel);
		  $thumb = stripslashes($data->thumb);
		  $month = stripslashes($data->month);
		  $year = stripslashes($data->year);
		  $url = stripslashes($data->url);
		  $name = stripslashes($data->name);
		  $client = stripslashes($data->client);
		  $keywords = stripslashes($data->keywords);
		  $description = stripslashes($data->description);
	  } 
  }else{
    $keywords = '';
  }
  ?>
  <h3><?php if (empty($data)) echo ENTRYNEWLABEL; else echo ENTRYEDITLABEL; ?></h3>
  <form class="largeform" id="editform" action="load.php?id=plopfolio" method="post" accept-charset="utf-8">
    <p>
      <input name="id" type="hidden" value="<?php echo $id; ?>" />
    <p>
	  <b><?php echo ENTRYNAME ;?>:</b><br />
      <input class="text name" name="entry-name" type="text" value="<?php echo @$name; ?>" />
    </p>
    <p <?php echo (!ISURLUSED) ? "style=\"display:none\"" : "" ?>>
	  <b><?php echo ENTRYURL ;?>:</b><br />
      <input class="text url" name="entry-url" type="text" value="<?php echo @$url; ?>" />
    </p>
    <p <?php echo (!ISKEYWORDUSED) ? "style=\"display:none\"" : "" ?>>
	  <b><?php echo ENTRYKEYWORDS ;?>:</b><br />
      <input class="text keywords" name="entry-keywords" type="text" value="" autocomplete="off" />
    </p>
    <p>
	  <b><?php echo ENTRYYEAR ;?></b>
		<?php
		if (isset($year)){
			$curr_year = $year;
		}else{
			$curr_year = date("Y");
		}
		$select = "<select name=\"entry-year\">\n";
		$select .= "\t<option val=\"0\"></option>\n";
		for($y = date("Y") + 1; $y > date("Y") - 10; $y--) {
		    $select .= "\t<option value=\"".$y."\"";
		    if ($y == $curr_year) {
		        $select .= " selected=\"selected\">".$y."</option>\n";
		    } else {
		        $select .= ">".$y."</option>\n";
		    }
		}
		$select .= "</select>";
		echo $select;
		?>
	  <b><?php echo ENTRYMONTH ;?></b>
		<?php
		if (isset($month)) {
			$curr_month = $month;
		}else{
			$curr_month = date("m");
		}
    
		$month_ = array (0=>"",1=>"1 January",2=>"2 February",3=>"3 March",4=>"4 April",5=>"5 May",6=>"6 June",7=>"7 July",8=>"8 August",9=>"9 September",10=>"10 October",11=>"11 November",12=>"12 December");
		$select = "<select name=\"entry-month\">\n";
		foreach ($month_ as $key => $val) {
		    $select .= "\t<option value=\"".$key."\"";
		    if ($key == $curr_month) {
		        $select .= " selected=\"selected\">".$val."</option>\n";
		    } else {
		        $select .= ">".$val."</option>\n";
		    }
		}
		$select .= "</select>";
		echo $select;
		?>
    </p>
    <p <?php echo (!ISCLIENTUSED) ? "style=\"display:none\"" : "" ?>>
	<b><?php echo ENTRYCLIENTNAME ;?>:</b><br />
      <input class="text client" name="entry-client" type="text" value="<?php echo @$client; ?>" />
    </p>
    <p>
      <b><?php echo ENTRYIMG ;?>:</b><br />
      <input class="text img" name="entry-visuel" type="text" value="<?php echo @$visuel; ?>" id="visuel" autocomplete="off"/>(<a rel="fancybox_iframe" href="<?php echo SITEURL; ?>/<?php echo GSADMIN; ?>/upload.php?path=<?php echo BIGFILESFOLDER; ?>">?</a>)
      <div id="visuelsub"></div>
      <a rel="facybox_i" href="<?php if (isset($visuel)&&strlen($visuel)>4){ echo $visuel;}else{ echo "#"; } ?>" >
        <img id="visuelimg" src="<?php if (isset($visuel)&&strlen($visuel)>4){ echo $visuel;}else{ echo "../plugins/".PLUGINNAME."/images/vide.gif"; } ?>" height="150px" style="margin: 5px; border: 1px solid black;"/>
      </a>
    </p>
    <p>
      <b><?php echo ENTRYTHUMB ;?> :</b><br />
      <input class="text img" name="entry-thumb" type="<?php echo (USECUSTOMTHUMBNAIL) ? "text"  : "hidden" ; ?>" value="<?php echo @$thumb; ?>" id="thumb"  autocomplete="off"/>
       <div id="thumbsub"></div>
       <a rel="facybox_i" href="<?php if (isset($thumb)&&strlen($thumb)>4){ echo $thumb;}else{ echo "#"; } ?>" >
        <img id="thumbimg" src="<?php if (isset($thumb)&&strlen($thumb)>4){ echo $thumb;}else{ echo "../plugins/".PLUGINNAME."/images/thumb_vide.gif"; } ?>"  height="60px" style="margin: 5px; border: 1px solid black;"/>
       </a>
    </p>
    <p>
	  <b><?php echo ENTRYDESCRIPTION ;?>:</b><br />
      <textarea name="entry-description" id="desc"><?php echo @$description; ?></textarea>
    </p>
    <p>
      <input id="save" name="submit" type="submit" class="submit" value="OK" />
      &nbsp;&nbsp;or&nbsp;&nbsp;
      <a href="load.php?id=<?php echo PLUGINNAME; ?>" class="cancel" title="Cancel">Cancel</a>
    </p>
  </form>
  <?php 
  $settings = new PlopfolioSettings();
  $data = $settings->load_data();
?>
<script>
$("#visuel").keyup(function () {
  var imgurl = $("#visuel").val();
  $("#visuelimg").attr("src",imgurl).error(function() {
    $(this).attr("src",'../plugins/<?php echo PLUGINNAME; ?>/images/404.gif');
  });
  $('#visuelsub').fadeIn(1500);
  $("#visuelsub").load("../plugins/<?php echo PLUGINNAME; ?>/pages/pickup.php?key=" + $("#visuel").val() + "&dest=visuel");
});
<?php if (USECUSTOMTHUMBNAIL) { ?>
$("#thumb").keyup(function () {
  var imgurl = $("#thumb").val();
  $("#thumbimg").attr("src",imgurl).error(function() {
    $(this).attr("src",'../plugins/<?php echo PLUGINNAME; ?>/images/thumb_404.gif');
  });
  $('#thumbsub').fadeIn(1500);
  $("#thumbsub").load("../plugins/<?php echo PLUGINNAME; ?>/pages/pickup.php?key=" + $("#thumb").val() + "&dest=thumb");   
});
<?php }else{ ?>
  $("#visuelimg").on('selected',function(event,img,path){
    $("#thumbimg").attr("src",path+"thumbnail."+img);
    $("#thumb").attr("value",path+"thumbnail."+img);
  });
<?php } ?>
jQuery(".keywords").tagsManager({
    prefilled: <?php echo (isset($keywords))? @json_encode(explode(",",$keywords)) : '""'; ?>,
    CapitalizeFirstLetter: true,
    preventSubmitOnEnter: true,
    typeahead: true,
    typeaheadAjaxSource: null,
    typeaheadSource: <?php echo (isset($data["keywords"]))? json_encode(explode(",",$data["keywords"])) : '""'; ?>,
    delimeters: [44, 188, 13],
    backspace: [8],
    blinkBGColor_1: '#FFFF9C',
    blinkBGColor_2: '#CDE69C',
    hiddenTagListName: 'hiddenTagListA'
  });
$('a[rel*=fancybox_iframe]').fancybox({
    'type':'iframe'
});

<?php

			if(isset($EDTOOL)) $EDTOOL = returnJsArray($EDTOOL);
			if(isset($toolbar)) $toolbar = returnJsArray($toolbar); // handle plugins that corrupt this

			else if(strpos(trim($EDTOOL),'[[')!==0 && strpos(trim($EDTOOL),'[')===0){ $EDTOOL = "[$EDTOOL]"; }

			if(isset($toolbar) && strpos(trim($toolbar),'[[')!==0 && strpos($toolbar,'[')===0){ $toolbar = "[$toolbar]"; }
			$toolbar = isset($EDTOOL) ? ",toolbar: ".trim($EDTOOL,",") : '';
			$options = isset($EDOPTIONS) ? ','.trim($EDOPTIONS,",") : '';

		?>

    var editor = CKEDITOR.replace( 'desc', {
        skin : 'getsimple',
        language : '<?php echo $LANG; ?>',
        defaultLanguage : '<?php echo $LANG; ?>',
        <?php if (file_exists(GSTHEMESPATH .$TEMPLATE."/editor.css")) {
            $fullpath = suggest_site_path();
        ?>
        contentsCss: '<?php echo $fullpath; ?>theme/<?php echo $TEMPLATE; ?>/editor.css',
        <?php } ?>
        entities : false,
        uiColor : '#FFFFFF',
        height: '300px',
        baseHref : '<?php echo $SITEURL; ?>',
        tabSpaces:10,
        filebrowserBrowseUrl : 'filebrowser.php?type=all',
        filebrowserImageBrowseUrl : 'filebrowser.php?type=images',
        filebrowserWindowWidth : '730',
        filebrowserWindowHeight : '500'
        <?php echo $toolbar; ?>
        <?php echo $options; ?>
    });
</script>
<?php
}


/*******************************************************
 * @function save_entry
 * @action write $_POST data to a file
 */
function save_entry() {
  $id = $_POST['id'];
  $file = GSDATAPORTFOLIOPATH . $id . '.xml';
  
  $visuel = htmlentities($_POST['entry-visuel'], ENT_QUOTES, 'UTF-8');
  $thumb = htmlentities($_POST['entry-thumb'], ENT_QUOTES, 'UTF-8');
  $month = htmlentities($_POST['entry-month'], ENT_QUOTES, 'UTF-8');
  $year = htmlentities($_POST['entry-year'], ENT_QUOTES, 'UTF-8');
  $url = htmlentities($_POST['entry-url'], ENT_QUOTES, 'UTF-8');
  $name = htmlentities($_POST['entry-name'], ENT_QUOTES, 'UTF-8');
  $client = htmlentities($_POST['entry-client'], ENT_QUOTES, 'UTF-8');
  $keywords = htmlentities($_POST['hiddenTagListA'], ENT_QUOTES, 'UTF-8');
  $description = htmlentities($_POST['entry-description'], ENT_QUOTES, 'UTF-8');

  $xml = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><item></item>');
  $xml->addChild('name')->addCData($name);
  $xml->addChild('url',$url);
  $xml->addChild('client')->addCData( $client);
  $xml->addChild('keywords')->addCData($keywords);
  $xml->addChild('description')->addCData($description);
  $xml->addChild('month', $month);
  $xml->addChild('year', $year);
  $xml->addChild('visuel',$visuel);
  $xml->addChild('thumb',$thumb);
  XMLsave($xml, $file);

  echo '<div class="updated">'.ENTRYNOTIFYSAVING.'</div>';

  portfolio_overview();
}


/*******************************************************
 * @function delete_entry
 * @action deletes the entry
 */
function delete_entry($id) {
  $file = GSDATAPORTFOLIOPATH . $id . '.xml';
  if (file_exists($file))
    unlink($file);
  echo '<div class="updated">The entry has been deleted</div>';
  portfolio_overview();
}

/*
 * ShortCode Detection Script
 */
function plopfolio_check($contents) { 
    
    $tmpContent = $contents;
    $patternForTheLoop = '/<!--plopfolio_theloop([\[\]=_a-zA-Z]*)-->([\s\S]+)<!--\/plopfolio_theloop-->/i';

    preg_match($patternForTheLoop,$tmpContent, $matches);
    if (!empty($matches)&&isset($matches[2])) {
        $loopcontent = $matches[2];
        $keywordfilter = false;
        if(isset($matches[1])&&$matches[1]){
            $keywordfilter = explode('=',substr($matches[1],2,-1))[1];
        }

        $entriesSorted = returnPortfolioEntries($keywordfilter);
        $newHtml = "";
        foreach ($entriesSorted as $entry) {
            $entry_str = $loopcontent;
            $keys = array("keywords_with_space","img","visuel","thumb","month","year","url","name","client","customer","keywords","desc");
            foreach ($keys as $key => $value) {
                $currentPattern = '/\[plopfolio_entry_'.$value.'\]/i';
                $entry_str = preg_replace($currentPattern, $entry[$value] , $entry_str);
            }
            $newHtml .= $entry_str;
        }
        $tmpContent = preg_replace($patternForTheLoop,$newHtml,$tmpContent);
        return $tmpContent;
    }
    return $contents;
  
}



############################### SITE FUNCTIONS #################################

/*******************************************************
 * @function portfolio2
 * @action runs the portfolio plugin on the theme/site page
 */
function returnPortfolioEntries($keywordToMatch = false) {
    $entries = get_entries();
    $entriesSorted = array();
    if (!empty($entries)) {
        $entries2 = Array();
        $count=0;
        foreach ($entries as $entry) {
            $id = basename($entry, ".xml");
            $file = GSDATAPORTFOLIOPATH . $entry;
            $data = getXML($file);
            if (!$keywordToMatch||in_array($keywordToMatch,explode(',',strtolower(html_entity_decode($data->keywords, ENT_QUOTES, 'UTF-8'))))) {
                $entries2[$count]["id"] = $id;
                $entries2[$count]["visuel"] = $data->visuel; // retrocompatibilité
                $entries2[$count]["img"] = $entries2[$count]["visuel"];
                $entries2[$count]["thumb"] = $data->thumb;
                $entries2[$count]["month"] = $data->month;
                $entries2[$count]["year"] = $data->year;
                $entries2[$count]["url"] = $data->url;
                $entries2[$count]["name"] = html_entity_decode($data->name, ENT_QUOTES, 'UTF-8');
                $entries2[$count]["client"] = html_entity_decode($data->client, ENT_QUOTES, 'UTF-8');// retrocompatibilité
                $entries2[$count]["customer"] = $entries2[$count]["client"];
                $entries2[$count]["keywords"] = strtolower(html_entity_decode($data->keywords, ENT_QUOTES, 'UTF-8'));
                $entries2[$count]["keywords_with_space"] = str_replace(",", " ", $entries2[$count]["keywords"]);
                $entries2[$count]["desc"] = html_entity_decode($data->description, ENT_QUOTES, 'UTF-8');
                $count++;
            }
        }
        $entriesSorted = subval_sort($entries2,'year');
    }
    return $entriesSorted;
}
############################## HELPER FUNCTIONS ################################


/*******************************************************
 * @function get_entries
 * @returns returns all entries in GSDATAPORTFOLIOPATH
 */
function get_entries() {
  $result = array();
  $files = getFiles(GSDATAPORTFOLIOPATH);
  foreach ($files as $file) {
    if (is_file(GSDATAPORTFOLIOPATH . $file) && preg_match("/^((?!".SETTINGSFILENAME.").*).xml$/", $file) && preg_match("/^((?!".DATAFILENAME.").*).xml$/", $file) ) {
      $result[] = $file;
    }
  }
  krsort($result);
  return $result;
}

?>