<?php
require_once( '../../../wp-config.php' );
require_once( '../../../wp-includes/classes.php' );
require_once( '../../../wp-includes/functions.php' );
require_once( '../../../wp-includes/plugin.php' );
require_once( '../../../wp-admin/admin.php' );

//wp_deregister_script('jquery');
//wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"), false, '1.3.2');
//wp_enqueue_script('jquery');

    if (isset($_POST) && !empty($_POST)) {
        $videoUrl = $_POST['videourl'];
        $thumbnailUrl = $_POST['thumbnailurl'];
        $skinurl = $_POST['skin'];

        $playerval = $_POST['player'];
        $xind = explode('x', $_POST['whsize']);
        $sizes = array();
        $sizes['width'] = $xind[0];
        $sizes['height'] = $xind[1];

        //Validate domain
        if (!preg_match('/^(http):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $videoUrl)) {
            $error['videourl'] = 'Please check video url';
        }
        if (!empty ($thumbnailUrl)) {
            if (!preg_match('/^(http):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $thumbnailUrl)) {
                $error['thumbnailurl'] = 'Please check thumbnail url';
            }
        }

        if (!isset($error) && empty($error)) {
            $post = array();

            $post['post_content'] = "[EZWebPlayerLite VIDEOURL=\"$videoUrl\"";
            $post['post_content'] .= (isset($thumbnailUrl) && !empty($thumbnailUrl)) ? " THUMBNAILURL=\"$thumbnailUrl\"" : "" ;
            $post['post_content'] .= (isset($skinurl) && !empty($skinurl)) ? " SKINURL=\"$skinurl\"" : "" ;
            $post['post_content'] .= (array_key_exists('playarrow', $_POST)) ? " PLAYARROW=\"TRUE\"" : "" ;
            $post['post_content'] .= (isset($playerval) && !empty($playerval)) ? " WIDTH=\"$xind[0]\"" : "" ;
            $post['post_content'] .= (isset($playerval) && !empty($playerval)) ? " HEIGHT=\"$xind[1]\"" : "" ;
            $post['post_content'] .= " /]";

            $out = <<<EOF
            <script type="text/javascript">
                /* <![CDATA[ */
                var win = window.dialogArguments || opener || parent || top;
                win.send_to_editor('{$post['post_content']}');
                /* ]]> */
            </script>
EOF;
            printf($out);
            exit();
        }
    }

/**
 * Load setings default values 
 * 
 * 09.07.2010 - Rinat
 */
$skinurl = get_option('ezwplayer_skin');
$playerval = get_option('ezwplayer_player');
$sizes['width'] = get_option('ezwplayer_width');
$sizes['height'] = get_option('ezwplayer_height');    
    


$skins = array( "" => "Default",
                    "Beige.xml" => "Beige.xml",
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
    
?>

<link href="<?php echo get_option('home');?>/wp-admin/load-styles.php?c=0&dir=ltr&load=global,wp-admin"  rel="stylesheet" type="text/css" />
<link href="<?php echo get_option('home');?>/wp-admin/css/colors-fresh.css"  rel="stylesheet" type="text/css" />
<script src="<?php echo get_option('home') . '/wp-includes/js/jquery/jquery.js'; ?>" type="text/javascript" ></script>

<style type="text/css">
    #form ul {list-style: none; margin: 0; padding: 0;}
    #form ul li label {width: 150px; float: left;}
    #form #videourl, #form #thumbnailurl, #form select  {width: 300px;}

    .block_form {padding: 0 20px 0 20px;}

    .text-right {text-align: right;}
    .padl20 {padding-left: 20px;}

    .error {margin-left: 150px; width: 295px; padding: 0 0 0 5px;}
</style>

<script type="text/javascript">
    function seloptions(sizetype){
        if (sizetype=='16by9') {
            mstring = "<option value='200x128' <?php echo (($sizes['width'].'x'.$sizes['height']) == '200x128') ? "selected='selected'" : ''; ?>>200x128</option>";
            mstring += "<option value='250x156' <?php echo (($sizes['width'].'x'.$sizes['height']) == '250x156')? "selected='selected'" : ''; ?>>250x156</option>";
            mstring += "<option value='300x184' <?php echo (($sizes['width'].'x'.$sizes['height']) == '300x184') ?  "selected='selected'" : ''; ?>>300x184</option>";
            mstring += "<option value='350x213' <?php echo (($sizes['width'].'x'.$sizes['height']) == '350x213') ? "selected='selected'" : ''; ?>>350x213</option>";
            mstring += "<option value='400x242' <?php echo (($sizes['width'].'x'.$sizes['height']) == '400x242') ? "selected='selected'" : ''; ?>>400x242</option>";
            mstring += "<option value='450x270' <?php echo (($sizes['width'].'x'.$sizes['height']) == '450x270') ? "selected='selected'" : ''; ?>>450x270</option>";
            mstring += "<option value='500x301' <?php echo (($sizes['width'].'x'.$sizes['height']) == '500x301') ? "selected='selected'" : ''; ?>>500x301</option>";
            mstring += "<option value='550x331' <?php echo (($sizes['width'].'x'.$sizes['height']) == '550x331') ? "selected='selected'" : ''; ?>>550x331</option>";
            mstring += "<option value='600x361' <?php echo (($sizes['width'].'x'.$sizes['height']) == '600x361') ? "selected='selected'" : ''; ?>>600x361</option>";
            mstring += "<option value='650x389' <?php echo (($sizes['width'].'x'.$sizes['height']) == '650x389') ? "selected='selected'" : ''; ?>>650x389</option>";
            mstring += "<option value='700x417' <?php echo (($sizes['width'].'x'.$sizes['height']) == '700x417') ? "selected='selected'" : ''; ?>>700x417</option>";
            mstring += "<option value='750x445' <?php echo (($sizes['width'].'x'.$sizes['height']) == '750x445') ? "selected='selected'" : ''; ?>>750x445</option>";
            mstring += "<option value='800x473' <?php echo (($sizes['width'].'x'.$sizes['height']) == '800x473') ? "selected='selected'" : ''; ?>>800x473</option>";
            mstring += "<option value='850x501' <?php echo (($sizes['width'].'x'.$sizes['height']) == '850x501') ? "selected='selected'" : ''; ?>>850x501</option>";
            mstring += "<option value='900x529' <?php echo (($sizes['width'].'x'.$sizes['height']) == '900x529') ? "selected='selected'" : ''; ?>>900x529</option>";
            mstring += "<option value='950x557' <?php echo (($sizes['width'].'x'.$sizes['height']) == '950x557') ? "selected='selected'" : ''; ?>>950x557</option>";
            jQuery("#whsize").html(mstring);
        } else if (sizetype=='4by3') {
            mstring = "<option value='200x165' <?php echo (($sizes['width'].'x'.$sizes['height']) == '200x165') ? "selected='selected'" : ''; ?>>200x165</option>";
            mstring += "<option value='250x203' <?php echo (($sizes['width'].'x'.$sizes['height']) == '250x203') ? "selected='selected'" : ''; ?>>250x203</option>";
            mstring += "<option value='350x278' <?php echo (($sizes['width'].'x'.$sizes['height']) == '350x278') ? "selected='selected'" : ''; ?>>350x278</option>";
            mstring += "<option value='400x318' <?php echo (($sizes['width'].'x'.$sizes['height']) == '400x318') ? "selected='selected'" : ''; ?>>400x318</option>";
            mstring += "<option value='450x358' <?php echo (($sizes['width'].'x'.$sizes['height']) == '450x358') ? "selected='selected'" : ''; ?>>450x358</option>";
            mstring += "<option value='500x397' <?php echo (($sizes['width'].'x'.$sizes['height']) == '500x397') ? "selected='selected'" : ''; ?>>500x397</option>";
            mstring += "<option value='550x436' <?php echo (($sizes['width'].'x'.$sizes['height']) == '550x436') ? "selected='selected'" : ''; ?>>550x436</option>";
            mstring += "<option value='600x473' <?php echo (($sizes['width'].'x'.$sizes['height']) == '600x473') ? "selected='selected'" : ''; ?>>600x473</option>";
            mstring += "<option value='650x511' <?php echo (($sizes['width'].'x'.$sizes['height']) == '650x511') ? "selected='selected'" : ''; ?>>650x511</option>";
            mstring += "<option value='700x548' <?php echo (($sizes['width'].'x'.$sizes['height']) == '700x548') ? "selected='selected'" : ''; ?>>700x548</option>";
            mstring += "<option value='750x590' <?php echo (($sizes['width'].'x'.$sizes['height']) == '750x590') ? "selected='selected'" : ''; ?>>750x590</option>";
            mstring += "<option value='800x623' <?php echo (($sizes['width'].'x'.$sizes['height']) == '800x623') ? "selected='selected'" : ''; ?>>800x623</option>";
            mstring += "<option value='850x661' <?php echo (($sizes['width'].'x'.$sizes['height']) == '850x661') ? "selected='selected'" : ''; ?>>850x661</option>";
            mstring += "<option value='900x693' <?php echo (($sizes['width'].'x'.$sizes['height']) == '900x693') ? "selected='selected'" : ''; ?>>900x693</option>";
            mstring += "<option value='950x736' <?php echo (($sizes['width'].'x'.$sizes['height']) == '950x736') ? "selected='selected'" : ''; ?>>950x736</option>";
            jQuery("#whsize").html(mstring);
        } else {
            mstring = "<option value='' <?php echo (($sizes['width'].'x'.$sizes['height']) == '950x557') ? "selected='selected'" : ''; ?>>Default</option>";
            jQuery("#whsize").html(mstring);
        }
    }
</script>

<h1 class="padl20">POST MY VIDEOS</h1>
<div class="content">
    <div class="ezwp-whitebackground">
        <form method="post" id="form" action="">
            <div class="block_form">
                <ul>
                    <li>
                        <label for="videourl">Video URL</label>
                        <input <?php if (isset($error) && !empty ($error['videourl'])) { echo 'style="border: 1px solid red;"'; } ?> id="videourl" name="videourl" type="text" value="<?php if(isset ($videoUrl) && !empty ($videoUrl)) {echo $videoUrl;} ?>" />
                        <i style="color: red;">&nbsp;(required)</i>
                        <?php if (isset($error) && !empty ($error['videourl'])) : ?><div class="error"><?php echo $error['videourl'] ?></div> <?php endif; ?>
                    </li>
                    <li>
                        <label for="thumbnailurl">Thumbnail URL</label>
                        <input <?php if (isset($error) && !empty ($error['thumbnailurl'])) { echo 'style="border: 1px solid red;"'; } ?> id="thumbnailurl" name="thumbnailurl" type="text" value="<?php if(isset ($thumbnailUrl) && !empty ($thumbnailUrl)) {echo $thumbnailUrl;} ?>" />
                    </li>
                    <li>
                        <label for="skin">Select a Color Scheme</label>
                        <select class='selectstyle' name='skin'>
                            <?php $selected = '';
                            foreach ($skins as $key => $value) {
                                if ($key == $skinurl) {
                                    $selected = 'selected';
                                } else
                                    $selected = '';
                                echo '<option value="' . $key . '" ' . $selected . ' >' . $value . '</option>';
                            } ?>
                        </select>
                    </li>
                    <li>
                        <label for="select_ratio">Select a Player ratio</label>
                        <select class='selectstyle' name='player' onchange="seloptions(this.value)">
                            <option value="">Default</option>
                            <option value="16by9" <?php echo (stripos($playerval,'16by9') !== FALSE) ? 'selected="selected"' : ''; ?>>16:9</option>
                            <option value="4by3" <?php echo (stripos($playerval,'4by3') !== FALSE) ? 'selected="selected"' : ''; ?>>4:3</option>
                        </select>
                    </li>
                    <li>
                        <label>Select a Player size</label>
                        <select id='whsize' name='whsize'></select>
                    </li>
                    <li>
                        <label for="playarrow">View a Play Arrow </label>
                        <input id="playarrow" name="playarrow" type="checkbox" value="1" <?php if (array_key_exists('playarrow', $_POST)) {echo 'checked="checked"';} ?> />
                    </li>
                    <li class="text-right">
                        <input type="submit" class="button-primary" value="Post Video" />
                    </li>
                </ul>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    seloptions('<?php echo $playerval?>');
</script>