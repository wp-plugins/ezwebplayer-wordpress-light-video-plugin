<?php
/*
Plugin Name: EZWebPlayer Lite
Version: 1.5
Description: Hello and thank you for installing the EZWebPlayer Lite video plug-in. With this video plug-in you will have the option to play .flv, .mp4, and even YouTube.com videos. You will also have the option of using up to 12 different color schemes to help you integrate the EZWebPlayer Lite seamlessly into your current WP Blog. You can have a play arrow over the video player when the video is paused or stopped and you can even show a thumbnail of the video when the play button has not been pressed
Author: EZWebPlayer.com
Author URI: http://www.ezwebplayer.com
*/

$surl = get_option('home');
//wp-content/plugins/ezwebplayerlite/Player/Skins/SquaredSkin/Colors
$purl = $surl . '/wp-content/plugins/ezwebplayerlite';
$defaultvalues = array();
$defaultvalues['spvideourl'] = '';
$defaultvalues['videoid'] = '';
$defaultvalues['skinurl'] = $purl . '/Player/Skins/SquaredSkin/Colors/'.get_option('ezwplayer_skin');//Beige.xml;
$defaultvalues['thumbnailURL'] = $purl . '/Player/Images/Thumbnail.jpg';
$defaultvalues['width'] = get_option('ezwplayer_width');
$defaultvalues['height'] = get_option('ezwplayer_height');
$defaultvalues['PLAYARROW'] = get_option('ezwplayer_allowplayerarrow');

function ezwebplayerlite_replace ($content) {
    global $wpdb;
    global $defaultvalues;
    global $surl;
    global $purl;
    $matches = array();
    preg_match_all("/\[EZWebPlayerLite ([^]]*)\/\]/i", $content, $matches); //find all ezwebplayer command lines
    $stingtoreplace = array();
    
    for ($i = 0; $i < count($matches); $i ++) {
        $stingtoreplace[$i] = $matches[$i];
        $matches[$i] = str_replace(array('&#8221;' , '&#8243;'), '', $matches[$i]);
    }
    $attributes = array();
    for ($i = 0; $i < count($matches[1]); $i ++) {
        preg_match_all("/([.\w]*)=(.*?) /i", $matches[1][$i], $attributes[$i]); //parse attributes for them
    }
    
    $arguments = array();
    for ($i = 0; $i < count($attributes); $i ++) {
        for ($j = 0; $j < count($attributes[$i][1]); $j ++) {
            $arguments[$i][strtoupper($attributes[$i][1][$j])] = str_replace('"', '', $attributes[$i][2][$j]); //set attribute name => attribute value
        }
    }
    /*
    [0] => Array
        (
            [VIDEOURL] => http://matthewjohnwilson.com/Video/43video.flv
            [HEIGHT] => 400
            [WIDTH] => 300
        )

    [1] => Array
        (
            [VIDEOURL] => http://mywordpress.sibers.com/wp-content/uploads/2010/03/edwardkhill.flv
            [THUMBNAILURL] => http://matthewjohnwilson.com/Player/Images/Thumbnail.jpg
            [SKINURL] => NavyBlue.xml
            [PLAYARROW] => True
            [HEIGHT] => 300
            [WIDTH] => 200
        )

    */
//   echo '<pre>';
//   print_r($arguments);
//   exit();

    $curval = array();
    foreach($arguments as $arg){
        $carg = array();        
        $carg['VIDEOURL'] = $arg['VIDEOURL'];
        $carg['THUMBNAILURL'] = (isset($arg['THUMBNAILURL']))?"&ScreenShoturl={$arg['THUMBNAILURL']}":'';
        $carg['SKINURL'] = (isset($arg['SKINURL'])) ? $purl . '/Player/Skins/SquaredSkin/Colors/'.$arg['SKINURL'] : $defaultvalues['skinurl'];
        $carg['PLAYARROW'] = (isset($arg['PLAYARROW'])) ? $arg['PLAYARROW'] : $defaultvalues['PLAYARROW'];
        $carg['HEIGHT'] = (isset($arg['HEIGHT'])) ? $arg['HEIGHT'] : $defaultvalues['height'];
        $carg['WIDTH'] = (isset($arg['WIDTH'])) ? $arg['WIDTH']:$defaultvalues['width'];
        $curval[] = $carg;
    }
    $flashpl = "
    <object align=\"middle\" width=\"%WIDTH%\" height=\"%HEIGHT%\" id=\"%EZWEBPLAYERID%\">
        <param value=\" Skinxml=%SKINURL%&BasicVideoswf={$purl}/Player/BasicVideoSettings/BasicVideoSettings.swf&Videourl=%VIDEOURL%%THUMBNAILURL%\" name=\"FlashVars\">
        <param value=\"false\" name=\"menu\">
        <param value=\"%WIDTH%\" name=\"width\">
        <param value=\"%HEIGHT%\" name=\"height\">
        <param value=\"always\" name=\"allowScriptAccess\">
        <param value=\"high\" name=\"quality\">
        <param value=\"#000000\" name=\"bgcolor\">
        <param value=\"%SKINURL%\" name=\"movie\">
        <param value=\"true\" name=\"allowFullScreen\">

        <embed  width=\"%WIDTH%\"
                height=\"%HEIGHT%\"
                pluginspage=\"http://www.adobe.com/go/getflashplayer\"
                src=\"{$purl}/Player/Skins/SquaredSkin/SWF/Standard.swf\"
                bgcolor=\"#000000\"
                quality=\"best\"
                allowscriptaccess=\"always\"
                allowfullscreen=\"true\"
                menu=\"false\"
                flashvars=\"Skinxml=%SKINURL%&BasicVideoswf={$purl}/Player/BasicVideoSettings/BasicVideoSettings.swf&Videourl=%VIDEOURL%%THUMBNAILURL%\"
                name=\"%EZWEBPLAYERID%\">
    </object>
    <div class=\"ezwp-view-count\">%VIEWCOUNTING%</div>
            ";
    $table_name = $wpdb->prefix . "ezwp_videocount";
    foreach($curval as $i => $cr){
        $query = "SELECT viewcount FROM $table_name WHERE videoid = \"{$cr['VIDEOURL']}\"";
        $viewcount = $wpdb->get_results($query, ARRAY_A);
        $viewcount = @$viewcount[0]['viewcount'];
        if(!is_home()){            
            if(isset($viewcount[0]) && !empty($viewcount[0])){
                $query = "UPDATE $table_name SET viewcount=viewcount+1 WHERE videoid=\"{$cr['VIDEOURL']}\";";                
            }
            else{                
                $query = "INSERT INTO $table_name VALUES(NULL,\"{$cr['VIDEOURL']}\",1);";
                $viewcount = 1;
            }
            $wpdb->query($query);
        }
        
        $tmp = $flashpl;
        foreach ($cr as $key => $value) {
            $tmp = str_replace("%$key%", $value, $tmp);
        }
        $tmp = str_replace("%VIEWCOUNTING%", $viewcount, $tmp);
        $rnd = rand();
        $tmp = str_replace("%EZWEBPLAYERID%", "ezwebplayerlite$i-$rnd", $tmp);
        $content = str_replace($stingtoreplace[0][$i], $tmp, $content);
    }
    return $content;
}
function ezwebplayerlite_admin_menu () {
    add_options_page('EZWebPlayer Lite options', 'EZWebPlayer Lite', 8, 'ezwebplayerlite.php', 'ezwebplayerlite_options_page');
    $page = add_menu_page('ezwebplayerlite', 'EZWebPlayer Lite', 'administrator', __FILE__, 'ezwebplayerlite_admin_submenu');
    //add_menu_page('Test Toplevel', 'Test Toplevel', 'administrator', __FILE__, 'mt_toplevel_page')
    add_action('admin_print_scripts-' . $page, 'ezwebplayerlite_js');
}
function ezwebplayerlite_options_page () {        
    $skins = array( "Beige.xml" => "Beige.xml",
                    "DarkGrey.xml" => "DarkGrey.xml",
                    "Grey.xml" => "Grey.xml",
                    "NavyBlue.xml" => "NavyBlue.xml",
                    "Night.xml" => "Night.xml",
                    "Olive.xml" => "Olive.xml",
                    "Pink.xml" => "Pink.xml",
                    "Red.xml" => "Red.xml",
                    "SkyBlue.xml" => "SkyBlue.xml",
                    "StandardSkin.xml" => "StandardSkin.xml",
                    "Taupe.xml" => "Taupe.xml",
                    "White.xml" => "White.xml");
    $msg = '';
    // Process form submission
    if (isset($_POST) && !empty($_POST)) {
        update_option('ezwplayer_skin', $_POST['ezwplayer_skin']);
        update_option('ezwplayer_player', $_POST['ezwplayer_player']);
        $sizes = array();
        $tmp = $_POST['whsize'];
        $xind = stripos($tmp, 'x');
        $sizes['width'] = substr($tmp, 0, $xind);
        $sizes['height'] = substr($tmp, ($xind + 1));
        update_option('ezwplayer_width', $sizes['width']);
        update_option('ezwplayer_height', $sizes['height']);
        $ezwpan = (isset($_POST['ezwplayer_allowplayerarrow']) && !empty($_POST['ezwplayer_allowplayerarrow'])) ? 'TRUE' : 'FALSE';
        update_option('ezwplayer_allowplayerarrow', $ezwpan);
    }

    $skinurl = get_option('ezwplayer_skin');
    $allowarrow = get_option('ezwplayer_allowplayerarrow');
    $ezwp_width = get_option('ezwplayer_width');
    $ezwp_height = get_option('ezwplayer_height');
    ?>
<style type="text/css">
    .ezwp-table{
        background-color: #F1F1F1;
        border: 1px solid gray;
        margin:0px;
    }
    .ezwp-table td{
        padding:0px;
    }
    .ezwp-table td div{
        margin:10px 35px;
    }
    .ezwp-faq-links{
        background-color:white;
        border:1px solid;
        margin:10px;
        padding:10px;
    }
    .ezwp-faq-links li{
    }
    .ezwp-h1{
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .ezwp-tabb{
        display: block;
        /*padding: 15px;*/
    }
</style>
<script type="text/javascript">
    function seloptions(sizetype){
        var mstring = '';
        if(sizetype=='16by9'){
            mstring = "<option value='200x128' <?php echo (($ezwp_width.'x'.$ezwp_height) == '200x128') ? "selected='selected'" : ''; ?>>200x128</option>";
            mstring += "<option value='250x156' <?php echo (($ezwp_width.'x'.$ezwp_height) == '250x156')? "selected='selected'" : ''; ?>>250x156</option>";
            mstring += "<option value='300x184' <?php echo (($ezwp_width.'x'.$ezwp_height) == '300x184') ?  "selected='selected'" : ''; ?>>300x184</option>";
            mstring += "<option value='350x213' <?php echo (($ezwp_width.'x'.$ezwp_height) == '350x213') ? "selected='selected'" : ''; ?>>350x213</option>";
            mstring += "<option value='400x242' <?php echo (($ezwp_width.'x'.$ezwp_height) == '400x242') ? "selected='selected'" : ''; ?>>400x242</option>";
            mstring += "<option value='450x270' <?php echo (($ezwp_width.'x'.$ezwp_height) == '450x270') ? "selected='selected'" : ''; ?>>450x270</option>";
            mstring += "<option value='500x301' <?php echo (($ezwp_width.'x'.$ezwp_height) == '500x301') ? "selected='selected'" : ''; ?>>500x301</option>";
            mstring += "<option value='550x331' <?php echo (($ezwp_width.'x'.$ezwp_height) == '550x331') ? "selected='selected'" : ''; ?>>550x331</option>";
            mstring += "<option value='600x361' <?php echo (($ezwp_width.'x'.$ezwp_height) == '600x361') ? "selected='selected'" : ''; ?>>600x361</option>";
            mstring += "<option value='650x389' <?php echo (($ezwp_width.'x'.$ezwp_height) == '650x389') ? "selected='selected'" : ''; ?>>650x389</option>";
            mstring += "<option value='700x417' <?php echo (($ezwp_width.'x'.$ezwp_height) == '700x417') ? "selected='selected'" : ''; ?>>700x417</option>";
            mstring += "<option value='750x445' <?php echo (($ezwp_width.'x'.$ezwp_height) == '750x445') ? "selected='selected'" : ''; ?>>750x445</option>";
            mstring += "<option value='800x473' <?php echo (($ezwp_width.'x'.$ezwp_height) == '800x473') ? "selected='selected'" : ''; ?>>800x473</option>";
            mstring += "<option value='850x501' <?php echo (($ezwp_width.'x'.$ezwp_height) == '850x501') ? "selected='selected'" : ''; ?>>850x501</option>";
            mstring += "<option value='900x529' <?php echo (($ezwp_width.'x'.$ezwp_height) == '900x529') ? "selected='selected'" : ''; ?>>900x529</option>";
            mstring += "<option value='950x557' <?php echo (($ezwp_width.'x'.$ezwp_height) == '950x557') ? "selected='selected'" : ''; ?>>950x557</option>";
            jQuery("#whsize").html(mstring);
        }
        else{
            mstring = "<option value='200x165' <?php echo (($ezwp_width.'x'.$ezwp_height) == '200x165') ? "selected='selected'" : ''; ?>>200x165</option>";
            mstring += "<option value='250x203' <?php echo (($ezwp_width.'x'.$ezwp_height) == '250x203') ? "selected='selected'" : ''; ?>>250x203</option>";
            mstring += "<option value='350x278' <?php echo (($ezwp_width.'x'.$ezwp_height) == '350x278') ? "selected='selected'" : ''; ?>>350x278</option>";
            mstring += "<option value='400x318' <?php echo (($ezwp_width.'x'.$ezwp_height) == '400x318') ? "selected='selected'" : ''; ?>>400x318</option>";
            mstring += "<option value='450x358' <?php echo (($ezwp_width.'x'.$ezwp_height) == '450x358') ? "selected='selected'" : ''; ?>>450x358</option>";
            mstring += "<option value='500x397' <?php echo (($ezwp_width.'x'.$ezwp_height) == '500x397') ? "selected='selected'" : ''; ?>>500x397</option>";
            mstring += "<option value='550x436' <?php echo (($ezwp_width.'x'.$ezwp_height) == '550x436') ? "selected='selected'" : ''; ?>>550x436</option>";
            mstring += "<option value='600x473' <?php echo (($ezwp_width.'x'.$ezwp_height) == '600x473') ? "selected='selected'" : ''; ?>>600x473</option>";
            mstring += "<option value='650x511' <?php echo (($ezwp_width.'x'.$ezwp_height) == '650x511') ? "selected='selected'" : ''; ?>>650x511</option>";
            mstring += "<option value='700x548' <?php echo (($ezwp_width.'x'.$ezwp_height) == '700x548') ? "selected='selected'" : ''; ?>>700x548</option>";
            mstring += "<option value='750x590' <?php echo (($ezwp_width.'x'.$ezwp_height) == '750x590') ? "selected='selected'" : ''; ?>>750x590</option>";
            mstring += "<option value='800x623' <?php echo (($ezwp_width.'x'.$ezwp_height) == '800x623') ? "selected='selected'" : ''; ?>>800x623</option>";
            mstring += "<option value='850x661' <?php echo (($ezwp_width.'x'.$ezwp_height) == '850x661') ? "selected='selected'" : ''; ?>>850x661</option>";
            mstring += "<option value='900x693' <?php echo (($ezwp_width.'x'.$ezwp_height) == '900x693') ? "selected='selected'" : ''; ?>>900x693</option>";
            mstring += "<option value='950x736' <?php echo (($ezwp_width.'x'.$ezwp_height) == '950x736') ? "selected='selected'" : ''; ?>>950x736</option>";
            jQuery("#whsize").html(mstring);
        }
    }
</script>
<div class="wrap" >
    <div style="float:left;margin:15px 0px"><img title="EZWebPlayer Lite Wordpress Plugin" src="<?php echo get_option('home')?>/wp-content/plugins/ezwebplayerlite/Player/Images/logo.png" /><br /></div>
    <div style="float:right">
        <span><a target="_blank" href="http://www.ezwebplayer.com/support/ezwebplayer-wordpress-lite-plugin" title="Plug-In home page">Plug-In Home Page</a></span>&nbsp;|&nbsp;
        <span><a target="_blank" href="http://www.ezwebplayer.com/support/2010/05/upgrade-to-ezwebplayer-wordpress-pro-plugin/" title="Upgrade plug-in">UPGRADE</a></span>
    </div>
    <table class="form-table ezwp-table">
        <tbody>
            <tr>
                <td>
                    <div style="margin:10px">
                    Hello and thank you for installing the EZWebPlayer Lite video plug-in.
                    With this video plug-in you will have the option to play .flv, .mp4, and even YouTube.com videos.
                    You will also have the option of using up to 12 different color schemes to help you integrate the
                    EZWebPlayer Lite seamlessly into your current WP Blog.
                    When in your post, click the blue video play icon to insert videos into your post.
                    </div>
                    <img src="<?php echo get_option('home')?>/wp-content/plugins/ezwebplayerlite/Player/Images/thumb_lite.png" alt="" />
                </td>
            </tr>
        </tbody>
    </table>
    <h1 class="ezwp-h1">DEFAULT OPTIONS</h1>
    <form method="post" action="options-general.php?page=ezwebplayerlite.php">
        <table class="form-table ezwp-table">
            <tbody>
                <tr>
                    <td>
                        <!--<div>
                            The first thing you have to do is select a color scheme for your new EzWebPlayer Lite
                            that will intergrate easily into your current WP Blog.
                        </div>-->
                        <div>
                            <span class="ezwp-tabb">
                            Select a Color Scheme&nbsp;<select class='selectstyle' name='ezwplayer_skin'>
                                    <?php
				    echo '$skinurl = ' . $skinurl;
                                    $selected = '';
                                    foreach ($skins as $key => $value) {
                                        if ($key == $skinurl) {
                                            $selected = 'selected';
                                        } else
                                            $selected = '';
                                        echo '<option value="' . $key . '" ' . $selected . ' >' . $key . '</option>';
                                    }
                                    ?>
                            </select>
                            </span>
                        </div>
                        <div>
                            <span class="ezwp-tabb">
                            <?php $playerval = get_option('ezwplayer_player'); ?>
                            Select a Player ratio<select class='selectstyle' name='ezwplayer_player' onchange="seloptions(this.value)">
                                <option value="16by9" <?php echo (stripos($playerval,'16by9') !== FALSE) ? 'selected="selected"' : ''; ?>>16:9</option>
                                <option value="4by3" <?php echo (stripos($playerval,'4by3') !== FALSE) ? 'selected="selected"' : ''; ?>>4:3</option>
                            </select>
                            </span>
                        </div>

                        <div>
                            <span class="ezwp-tabb">
                            Select a Player size&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select id='whsize' name='whsize'></select>
                            </span>
                        </div>
                        

                        <div>                            
                            Do you want to view our play arrow over your video when the player is paused or stopped?<br />
                            <span class="ezwp-tabb">
                                <label for="arrow">View a Play Arrow</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="arrow" type="checkbox" name="ezwplayer_allowplayerarrow" value="1" <?php echo ($allowarrow == 'TRUE')?'checked="checked"':'' ?> />
                            </span>
                        </div>
                        <div>
                            <input type='submit' class='button-primary' value='  Save Changes  ' />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <div style="float: left;">
                <img src="<?php echo get_option('home')?>/wp-content/plugins/ezwebplayerlite/Player/Images/thumb_lite.png" alt="" />
            </div>
            <div style="float: left; padding-left: 20px; padding-top: 50px; font-size: 1.5em; line-height: 25px;">
                You can insert videos into your post<br/>
                by clicking this play icon in the post toolbar.
            </div>
        </div>
    </form>
    <!--<h1 class="ezwp-h1">FAQ</h1>
    <table class="form-table ezwp-table" style="margin-bottom:15px;">
        <tbody>
            <tr>
                <td>
                    <ul class="ezwp-faq-links">
                        <li><a href="#" title="#">How do I add a basic video into my WP Blog?</a></li>
                        <li><a href="#" title="#">How do I override the use of the Play Arrow set in my Default Options?</a></li>
                        <li><a href="#" title="#">How do I add a Thumbnail to my video?</a></li>
                        <li><a href="#" title="#">How do I add both Thumbnail and override my Default Play Arrow Options?</a></li>
                        <li><a href="#" title="#">How do I set up a video to use an alternate Color Scheme from the one I use in my Default Options?</a></li>
                        <li><a href="#" title="#">How do I Change my EZWebPlayer Life's Size?</a></li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>-->
</div>
<!--
<table>
    <tr>
        <th>Example code</th>
        <td>[EZWebPlayerLite file=videos/file1.flv width=400 height=300 SkinURL=SkyBlue.xml
		/]</td>
    </tr>
    <tr>
        <th>Description</th>
        <td>EZWebPlayerLite - this param runs this plugin<br />
		file - filepath where video is, from site root. You can use .FLV or .MP4 video files<br />
		This video may be from different site e.g. http://www.site.com/Video/2009/Biscayne/Biscayne_Mangroves1.flv <br />
		width - player width<br />
		height - player height<br />
		skin - name of the flash skin.<br />
        </td>
    </tr>
</table>-->
<script type="text/javascript">
    seloptions('<?php echo $playerval?>');
</script>

    <?php
}
function ezwebplayerlite_admin_submenu () {
    global $wpdb;

//    echo '<pre>';
//print_r($_GET);
//exit();

    if((isset ($_GET['name']) && !empty($_GET['name'])) || (isset($_GET['date_from']) && !empty($_GET['date_from'])) && array_key_exists('search', $_GET)) {
        $name = mysql_real_escape_string($_GET['name']);
        $formatDateFrom = date('Y-m-d', strtotime(mysql_real_escape_string($_GET['date_from'])));
        $dateFrom = mysql_real_escape_string($_GET['date_from']);
        $dateTo = mysql_real_escape_string($_GET['date_to']);
        $formatDateTo = (!empty($_GET['date_to'])) ? date('Y-m-d', strtotime(mysql_real_escape_string($_GET['date_to']))) : date('Y-m-d 23:59:59');
        
        $query = "SELECT * FROM {$wpdb->prefix}ezwp_videocount LEFT JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}ezwp_videocount.postID={$wpdb->prefix}posts.id  where {$wpdb->prefix}posts.post_title like '" . $name . "%' AND {$wpdb->prefix}posts.post_date >= '" . $formatDateFrom . "' AND {$wpdb->prefix}posts.post_date <= '" . $formatDateTo . "'";
    
//        echo '<pre>';
//        print_r($query);
//        exit();
    } else {
        $query = "SELECT * FROM {$wpdb->prefix}ezwp_videocount LEFT JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}ezwp_videocount.postID={$wpdb->prefix}posts.id";
    }

//    echo '<pre>';
//    print_r($query);
//    exit();

    $items = $wpdb->get_results($query); ?>


<style type="text/css">
    .fleft {float: left;}
    .fright {float: right;}
    .clear {float: none; height: 0px;}

    .padl_30 {padding-left: 30px !important;}
    .padt_20 {padding-top: 20px !important;}

    .content{background-color: #F1F1F1; border: 1px solid gray; margin: 0px !important; padding: 23px 33px;}

    #filter li {float: left;}
    #filter li label {float: left; padding-top: 4px;}

    #results thead {background: #0059ae; color: #fff; }
</style>

<script type="text/javascript" >
    jQuery(document).ready(function(){
        jQuery('#date_from').datepicker();
        jQuery('#date_to').datepicker();
    });
</script>
    <div style="float:left;margin:15px 0px"><img title="EZWebPlayer Lite Wordpress Plugin" src="<?php echo get_option('home')?>/wp-content/plugins/ezwebplayerlite/Player/Images/logo.png" /><br /></div>
    <div style="float:right">
        <span><a target="_blank" href="http://www.ezwebplayer.com/support/ezwebplayer-wordpress-lite-plugin" title="Plug-In home page">Plug-In Home Page</a></span>&nbsp;|&nbsp;
        <span><a target="_blank" href="http://www.ezwebplayer.com/support/2010/05/upgrade-to-ezwebplayer-wordpress-pro-plugin/" title="Upgrade plug-in">UPGRADE</a></span>
    </div>
    <div class="clear"></div>
    <h1>Recent Video Post</h1>
<div class="content">
    <div class="fleft filter_block padl_30">
        <form id="filter" action="<?php echo get_option("home") . "/wp-admin/admin.php?page=ezwebplayerlite/ezwebplayerlite.php" ?>" method="get">
            <input name="page" value="ezwebplayerlite/ezwebplayerlite.php" type="hidden" />
            <input name="action" value="recent" type="hidden" />

            <ul>
                <li>
                    <label for="name">Name</label>
                    <input name="name" id="name" type="text" value="<?php if (isset($name) && !empty($name)) { echo $name; } ?>" />
                </li>
                <li>
                    <label for="date_from">Date From</label>
                    <input size="9" name="date_from" id="date_from" type="text" value="<?php if (isset($dateFrom) && !empty($dateFrom)) { echo $dateFrom; } ?>" />
                </li>
                <li>
                    <label for="date_to">Date To</label>
                    <input size="9" name="date_to" id="date_to" type="text" value="<?php if (isset($dateTo) && !empty($dateTo)) { echo $dateTo; } ?>" />
                </li>
                <li>
                    <input type="submit" name="search" value="Search" />
                </li>
                <li>
                    <input type="button" value="Clear" onclick='window.location.href = "<?php echo get_option("home") . "/wp-admin/admin.php?page=ezwebplayerlite/ezwebplayerlite.php&action=recent" ?>"' />
                </li>
            </ul>
        </form>
    </div>
    <div class="clear"></div>

    <div id="results" class="padt_20">
        <table width="95%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <td width="30px;">&nbsp;</td>
                    <td>Name</td>
                    <td>Views</td>
                    <td width="11%">Date added</td>
                    <td width="200px;">Actions</td>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;

                foreach ($items as $item) : ?>

                <tr>
                    <td><?php echo $i; ?></td>

                    <td><a href="<?php echo $item->guid ?>"><?php echo $item->post_title; ?></a></td>

                    <td><?php echo $item->viewcount; ?></td>

                    <td><?php echo date('m/d/Y', strtotime($item->post_date)); ?></td>

                    <td>
                        <?php edit_post_link('Edit', null, null, $item->ID); ?> |
                        <a href="<?php echo $item->guid ?>">View Post</a>
                    </td>
                </tr>
                <?php $i++; endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
    <?php
}

function ezwebplayerlite_activate () {
    add_option('ezwplayer_skin', 'Beige.xml'); //skin name
    add_option('ezwplayer_height', '242'); //player height
    add_option('ezwplayer_width', '400'); //player width
    add_option('ezwplayer_player', '16by9');//aspect ratio
    add_option("ezwplayer_allowplayerarrow",'FALSE');
    add_option('whsize'); //
    global $wpdb;
    //video view counting table
    $sql = "CREATE TABLE {$wpdb->prefix}ezwp_videocount (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
                  postID MEDIUMINT(8) UNSIGNED NOT NULL,
				  videoid char(255) DEFAULT '0',
				  viewcount bigint(10) NOT NULL,
				  PRIMARY KEY id (id)
				);";
    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
function ezwebplayerlite_deactivate () {
    global $wpdb;
    delete_option("ezwplayer_allowplayerarrow");
    delete_option('ezwplayer_skin');
    delete_option('ezwplayer_height');
    delete_option('ezwplayer_width');
    delete_option('ezwplayer_player');
    delete_option('whsize');

    $sql = "DROP TABLE IF EXISTS {$wpdb->prefix}ezwp_videocount"; //delete table with video view counts
    $wpdb->query($sql);
}

function ezwebplayerlite_media_buttons_context($fl) {
    $media_title = __('Add EZWebPlayer Lite Videos');
	$image_src = get_option('home') . "/wp-content/plugins/ezwebplayerlite/playicon.png";
	$fl .= '<a href="' . get_option("home") . '/wp-content/plugins/ezwebplayerlite/ezwplite_form.phpTB_iframe=true" class="thickbox" title="' . $media_title . '" onclick="return false;"><img src="' . $image_src . '" title="' . $media_title . '" alt="' . $media_title . '" /></a>';
    return $fl;
}

function ezwebplayerlite_save_post()
{
    global $wpdb;

    $id = $_POST['ID'];
    $titlePost = mysql_real_escape_string($_POST['post_title']);
    $contentPost = $_POST['post_content'];

    $matches = array();
    preg_match_all("/\[EZWebPlayerLite ([^]]*)\/\]/i", $contentPost, $matches); //find all ezwebplayer command lines

    $stingtoreplace = array();

    for ($i = 0; $i < count($matches); $i ++) {
        $stingtoreplace[$i] = $matches[$i];
        $matches[$i] = str_replace(array('&#8221;' , '&#8243;'), '', $matches[$i]);
    }
    $attributes = array();
    for ($i = 0; $i < count($matches[1]); $i ++) {
        preg_match_all("/([.\w]*)=(.*?) /i", $matches[1][$i], $attributes[$i]); //parse attributes for them
    }

    $arguments = array();
    for ($i = 0; $i < count($attributes); $i ++) {
        for ($j = 0; $j < count($attributes[$i][1]); $j ++) {
            $arguments[$i][strtoupper($attributes[$i][1][$j])] = str_replace('"', '', $attributes[$i][2][$j]); //set attribute name => attribute value
        }
    }

    $table_name = $wpdb->prefix . "ezwp_videocount";
    foreach ($arguments as $i => $cr) {
        $videoURL = str_replace('\\', '', $cr['VIDEOURL']);
        $query = "SELECT * FROM $table_name WHERE postID = \"{$id}\"";
        $viewPost = $wpdb->get_results($query, ARRAY_A);
   
        if (!empty ($viewPost)) {
            $query = "UPDATE $table_name SET videoid=\"{$videoURL}\" WHERE postID=\"{$id}\";";
        } else {
            $query = "INSERT INTO $table_name VALUES(NULL, \"{$id}\",\"{$videoURL}\", 1);";
        }
        $wpdb->query($query);
    }
}

function ezwebplayerlite_admin_init()
{
    /* Register our script and style. */
    wp_register_script('jqueryUI', WP_PLUGIN_URL . '/ezwebplayerlite/js/jquery-ui-1.7.3.custom.min.js');
    wp_register_style('jqueryUICSS', WP_PLUGIN_URL . '/ezwebplayerlite/css/ui-lightness/jquery-ui-1.7.3.custom.css');
}

function ezwebplayerlite_js()
{
    wp_enqueue_script('jqueryUI');
}

function ezwebplayerlite_style()
{
    wp_enqueue_style('jqueryUICSS');
}

register_activation_hook(__FILE__, 'ezwebplayerlite_activate');
register_deactivation_hook(__FILE__, 'ezwebplayerlite_deactivate');

add_action('admin_print_styles', 'ezwebplayerlite_style');
add_action('admin_init', 'ezwebplayerlite_admin_init');
add_action('admin_menu', 'ezwebplayerlite_admin_menu');

add_filter("media_buttons_context", "ezwebplayerlite_media_buttons_context");
add_filter('the_content', 'ezwebplayerlite_replace');
add_filter('publish_post', 'ezwebplayerlite_save_post');
?>