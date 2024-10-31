<?php

/*
Plugin Name: Paged Gallery
Plugin URI: http://andrey.eto-ya.com/wordpress/my-plugins/paged-gallery-plugin
Description: Divides your wordpress image gallery into several pages.
Author: Andrey K.
Version: 0.7
Requires at least: 2.8.6
Tested up to: 3.2.1
Stable tag: 0.7

Author URI: http://andrey.eto-ya.com/
*/


/*  Copyright 2009 Andrey K. (email: mywebcat@yandex.ru, URL: http://andrey.eto-ya.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


$aeyc_pg_default_options= array(
		'order'=> 'ASC', 'orderby'=> 'menu_order ID','itemtag'=> 'dl',
		'icontag'=> 'dt', 'captiontag'=> 'dd', 'columns'=> 3, 'size'=> 'thumbnail', 
		'perpage'=> 9, 'link'=>'file', 'show_edit_links'=>'Y', 'use_shortcode'=>'gallery', 'exclude'=>''
	);


$aeyc_paged_gallery_options= get_option('aeyc_paged_gallery_options');

if ( !is_array($aeyc_paged_gallery_options) ) {
		$aeyc_paged_gallery_options= $aeyc_pg_default_options;
	}


load_plugin_textdomain('paged_gallery', '/wp-content/plugins/paged-gallery');

if ($aeyc_paged_gallery_options['use_shortcode']=='gallery') {
	remove_shortcode('gallery');
	add_shortcode('gallery', 'aeyc_paged_gallery_shortcode');
	add_action('wp_head', 'aeyc_paged_gallery_css');
}
elseif ($aeyc_paged_gallery_options['use_shortcode']=='pgallery')
{
	add_shortcode('pgallery', 'aeyc_paged_gallery_shortcode');
	add_action('wp_head', 'aeyc_pgallery_css');
}

add_action('admin_menu', 'aeyc_paged_gallery_control_menu');

function aeyc_paged_gallery_shortcode($attr) {
	global $post;
	global $aeyc_pg_default_options, $aeyc_paged_gallery_options;

	$options= array_merge($aeyc_pg_default_options, $aeyc_paged_gallery_options, array('id'=> $post->ID));

	static $instance = 0;
	$instance++;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts($options, $attr));


	if ( empty($exclude) ) {
		$exclude= '';
	}

	$id = intval($id);
	$attachments = get_children( array('post_parent'=>$id, 'post_status'=>'inherit', 'post_type'=> 'attachment', 'post_mime_type'=>'image', 'order'=> $order, 'orderby'=>$orderby, 'exclude'=> $exclude,) );

	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";

		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
		return $output;
	}

	$attcount= count($attachments);
	$numpages= ceil($attcount/$perpage);

	$gallery_page= intval($_GET['gallery_page']);

	if ( empty($gallery_page) || $gallery_page<=0 )
		$gallery_page=1;

	if ($numpages>1)
	{
		$page_link= get_permalink();
		$page_link_perma= true;
		if ( strpos($page_link, '?')!==false )
			$page_link_perma= false;

		$gplist= '<div class="gallery_pages_list">'.__('Pages').' '.$post->post_title.':&nbsp; ';
		for ( $j=1; $j<= $numpages; $j++)
		{
			if ( $j==$gallery_page )
				$gplist .= '[<strong class="current_gallery_page_num"> '.$j.' </strong>]&nbsp; ';
			else
				$gplist .= '[ <a href="'.$page_link. ( ($page_link_perma?'?':'&amp;') ). 'gallery_page='.$j.'">'.$j.'</a> ]&nbsp; ';
		}

		$gplist .= '</div>';
	}
	else
		$gplist= '';

	$isup = $gallery_page*$perpage;
	$iinf= ($gallery_page-1)*$perpage;

	$selector = "gallery-{$instance}";

	$i = 0;
	$k=0;

	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$columns = intval($columns);
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;

	$selector = "gallery-{$instance}";

	$output = "<div id='$selector' class='$aeyc_paged_gallery_options[use_shortcode] galleryid-{$id}'>";

	foreach ( $attachments as $id => $attachment ) {
		if ( $i >= $iinf && $i < $isup )
		{
		$linkfrom = (isset($link) && 'file'==$link)? wp_get_attachment_link($id, $size, false, false) : wp_get_attachment_link($id, $size, true, false);

		$output .= "<{$itemtag} class='gallery-item' style='width: {$itemwidth}%;'>";
		$output .= "\n<{$icontag} class='gallery-icon'>$linkfrom</{$icontag}>";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "\n<{$captiontag} class='gallery-caption'>" 
				. wptexturize($attachment->post_excerpt) ."</{$captiontag}>";
		}

		if ( current_user_can('edit_pages') && $aeyc_paged_gallery_options['show_edit_links']=='Y' ) {
			$output .= ' <a href="'. get_edit_post_link( $attachment->ID ). '&amp;nomenu=1">'.'Edit'.'</a>';
		}

		$output .= "</{$itemtag}>\n";

		if ( $columns > 0 && (++$k) % $columns == 0 )
			$output .= "<div style=\"clear: both;\"></div>\n";
	}
	$i++;
	}

	$output .= "\n<br style='clear: both;' />$gplist\n</div>\n";


	return $output;
}


function aeyc_paged_gallery_css() {

echo '
<style type="text/css" title="">
.gallery { margin: auto;}
.gallery .gallery-item {float: left; margin-top: 10px; text-align: center;}
.gallery img {border: 3px double #cfcfcf;}
.gallery .gallery-caption {margin-left: 0;}
.gallery_pages_list {text-align:center; margin-top:24px}
</style>
';
}

function aeyc_pgallery_css() {
echo '
<style type="text/css" title="">
.pgallery {margin: auto;}
.pgallery .gallery-item {float: left; margin-top: 10px; text-align: center;}
.pgallery img {border: 3px double #cfcfcf;}
.pgallery .gallery-caption {margin-left: 0;}

.gallery_pages_list {text-align:center; margin-top:24px}
</style>
';
}

function aeyc_paged_gallery_options() {

	global $aeyc_pg_default_options, $aeyc_paged_gallery_options;

	$domain= 'paged_gallery';

	if ( 'POST'== $_SERVER['REQUEST_METHOD'] )
	{
		if ($_POST['aeyc_pg_reset_settings']=='reset')
			delete_option('aeyc_paged_gallery_options');
		else {
				$new_pg_options= array();
				foreach (array_keys($aeyc_pg_default_options) as $key) {
					if ( !empty($_POST[$key]) )
					$new_pg_options[$key]= trim(ereg_replace('[^a-zA-Z0-9_ ]', '', $_POST[$key]));
				}

				update_option('aeyc_paged_gallery_options', array_merge($aeyc_pg_default_options, $new_pg_options));

				$aeyc_paged_gallery_options= get_option('aeyc_paged_gallery_options');
		}	
	}

  	$aeyc_paged_gallery_options= array_merge($aeyc_pg_default_options, $aeyc_paged_gallery_options);

?>
<div class="wrap">

<div id="icon-upload" class="icon32"></div>

<h2><? _e('Paged Gallery Settings', $domain); ?></h2>
<form method="post" action="" name="aeyc_page_gallery_form" id="aeyc_page_gallery_form" >

<table class="form-table" style="width:740px">
<tr valign="top">
 <th scope="row"><? _e('Use shortcode', $domain); ?></th>
 <td><input type="radio" name="use_shortcode" value="gallery" <?php echo $aeyc_paged_gallery_options['use_shortcode']=='gallery'?'checked="checked"':'' ?> /> [gallery] &nbsp;
 <input type="radio" name="use_shortcode" value="pgallery" <?php echo $aeyc_paged_gallery_options['use_shortcode']=='pgallery'?'checked="checked"':'' ?> /> [pgallery]<br /><small><? _e('If you select <code>[gallery]</code> (default) then the plugin replaces standard wordpress gallery,<br />else you may use both [gallery] and [pgallery] in your blog (and, for instance, to apply another plugin to wordpress gallery shortcode).', $domain); ?></small></td>
 </tr>

<tr valign="top">
 <th scope="row"><? _e('Show edit mediafile link?', $domain); ?></th>
 <td><input type="radio" name="show_edit_links" value="Y" <?php echo $aeyc_paged_gallery_options['show_edit_links']=='Y'?'checked="checked"':''; ?> /> <?php _e('Yes'); ?> &nbsp; <input type="radio"  name="show_edit_links" value="N" <?php echo $aeyc_paged_gallery_options['show_edit_links']=='N'?'checked="checked"':''; ?> /> <?php echo __('No'), '<br />', __('For those who has permission, of course', $domain);
 
 ?>
 </td>
 </tr>

<tr valign="top">
 <th scope="row"><? _e('How many columns?', $domain); ?></th>
 <td><input type="text" value="<?php echo $aeyc_paged_gallery_options['columns'] ?>" name="columns" size="3">
 </td>
 </tr>

<tr valign="top">
 <th scope="row"><? _e('How many images per page?', $domain); ?></th>
 <td><input type="text" value="<?php echo $aeyc_paged_gallery_options['perpage'] ?>" name="perpage" size="3"><br /><small><? _e('Not bad if it is multiple of the above number ;)', $domain); ?>
</small>
 </td>
 </tr>

<tr valign="top">
 <th scope="row"><? _e('Preview size', $domain); ?></th>
 <td><input type="radio" name="size" value="thumbnail" <?php echo $aeyc_paged_gallery_options['size']=='thumbnail'?'checked="checked"':''; ?> /> thumbnail
 
 &nbsp; <input type="radio" name="size" value="medium" <?php echo $aeyc_paged_gallery_options['size']=='medium'?'checked="checked"':''; ?> /> medium

 &nbsp; <input type="radio" name="size" value="large" <?php echo $aeyc_paged_gallery_options['size']=='large'?'checked="checked"':''; ?> /> large
 
 </td>
 </tr>

 <tr valign="top">
 <th scope="row"><? _e('Link to', $domain); ?></th>
 <td><input type="radio" value="file" <?php echo $aeyc_paged_gallery_options['link']=='file'?'checked="checked"':''; ?>" name="link" /> file
 &nbsp;
<input type="radio" value="attachment" <?php echo $aeyc_paged_gallery_options['link']=='attachment'?'checked="checked"':''; ?>" name="link" /> attachment


 </td>
 </tr>

<tr valign="top">
 <td colspan="2">
<?php
$restpars= array(
	'order'=>array('ASC', 'DESC'),
	'orderby'=>array('menu_order ID', 'title'),
	'itemtag'=>array('dl', 'div' ,'p'),
	'icontag'=>array('dt', 'div' ,'p'),
	'captiontag'=>array('dd', 'div' ,'p')
);
	
foreach ($restpars as $par=>$arrv ) {
	echo "$par: <select name='$par'>";
	foreach ($arrv as $p=>$v) {
		echo "<option value='$v' ". ($v==$aeyc_paged_gallery_options[$par]?"selected='selected'":""). ">$v</option>";
	}
	echo "</select>\n&nbsp;\n";

}

?>

</td>
<tr>
<td><br /><input name="aeyc_pg_reset_settings" id="aeyc_pg_reset_settings" value="0" type="hidden" />
<input type="submit" class="button-primary" name="Submit" value=" <?php _e('Save settings', $domain) ?> " /></td>
 <td style="text-align:center"><br /><input type="button" onclick="javascript:{document.getElementById('aeyc_pg_reset_settings').value='reset'; document.getElementById('aeyc_page_gallery_form').submit();}" class="button" name="Submit" value=" <?php _e('Reset defaults', $domain) ?> " /></td>
 </tr>

 </table>


</form>
</div>
<?php
}

 
function aeyc_paged_gallery_control_menu() {
	add_media_page( __('Paged Gallery Settings'), 'Paged Gallery', 'manage_options', 'aeyc_paged_gallery_settings', $func = 'aeyc_paged_gallery_options' );

}

?>
