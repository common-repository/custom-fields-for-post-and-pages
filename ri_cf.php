<?php
/*
Plugin Name: Custom Fields for post and pages
Description: Create custom fields for posts and pages.
Tags: post custom field, posts, custom, custom fields, fields
Author URI: http://zeniasverden.dk/
Author: Kjeld Hansen
Text Domain: ri_custom_field
Requires at least: 4.0
Tested up to: 4.4.2
Version: 1.0
*/


 if ( ! defined( 'ABSPATH' ) ) exit; 
add_action('admin_menu','ri_custom_field_admin_menu');
function ri_custom_field_admin_menu() { 
    add_menu_page(
		"Custom Fields",
		" Fields",
		8,
		__FILE__,
		"ri_custom_field_admin_menu_list",
		plugins_url( 'img/plugin-icon.png', __FILE__) 
	); 
}

function ri_custom_field_admin_menu_list(){
	wp_enqueue_script( 'ricf_script', plugin_dir_url( __FILE__ ) . 'js/ricf.js' );
	include 'ricf-admin.php';
}


//add_action( 'admin_head', 'ri_custom_field_admin_css' );
add_action( 'admin_enqueue_scripts', 'ri_custom_field_admin_css' );
function ri_custom_field_admin_css(){
	wp_register_style( 'ri_custom_field_admin_wp_admin_css', plugins_url( '/css/admin.css', __FILE__), false, '1.0.0' );
    wp_enqueue_style( 'ri_custom_field_admin_wp_admin_css' );	
}

if (!shortcode_exists('postList')) {
	add_shortcode('postList', 'ri_custom_field_ri_list_posts');
}


if (!function_exists('ri_custom_field_ri_list_posts')){
	function ri_custom_field_ri_list_posts($args){
		
	}
}


#################################################################################################################################
/**
 * Register meta box(es).
 */
function realmeta_register_cf_meta_boxes() {
    add_meta_box( 'ri-cf', __( 'The Custom Fields', 'textdomain' ), 'realmeta_cf_display_callback', '' );
}
add_action( 'add_meta_boxes', 'realmeta_register_cf_meta_boxes' );
 

function realmeta_cf_display_callback( $post ) {
    
        wp_nonce_field( 'realmeta_inner_custom_box', 'realmeta_inner_custom_box_nonce' );
		$ptype = 'post';
		if(isset($_REQUEST['post_type'])){ $ptype = $_REQUEST['post_type']; }
		else if(isset($_REQUEST['post'])){
			 $ptype = get_post_type($_REQUEST['post']);
		}
if(get_option( 'ri_custom_field_opt' )){
	$ri_custom_fields_disp = unserialize(get_option( 'ri_custom_field_opt' ));

	if($ri_custom_fields_disp){
		foreach($ri_custom_fields_disp as $ri_custom_field_disp){
			foreach($ri_custom_field_disp as $slug=>$field){
				if(in_array($ptype, $field['vis'], true)){
				
				$value = unserialize(get_post_meta( $post->ID, '_ricf_'.$slug, true ));
				?>
                <div class="ricf_col">
                <label for="ri_<?php echo $slug; ?>">
                    <?php _e( $field['label'], 'textdomain' ); ?> : 
                </label> <?php 
				
				if($field['type']=='text'){ ?>
					<input type="text" id="ri_<?php echo $slug; ?>" name="ri_<?php echo $slug; ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo $field['ph']; ?>"  />
				<?php }
				if($field['type']=='textarea'){ ?>
					<textarea  id="ri_<?php echo $slug; ?>" name="ri_<?php echo $slug; ?>"  placeholder="<?php echo $field['ph']; ?>"> <?php echo esc_attr( $value ); ?> </textarea>
				<?php }
				if($field['type']=='select'){ $opts = $field['ch']; ?>
					<select id="ri_<?php echo $slug; ?>" name="ri_<?php echo $slug; ?>">
                    	<?php  
							foreach ($opts as $k=>$v){ $sel = '';
								if($k==esc_attr( $value ) && esc_attr( $value )!==''){ $sel = ' selected="selected" '; }
								echo '<option '.$sel.' value="'.$k.'">'.$v.'</option>';	
							}
						?>
                    </select>
				<?php }
				if($field['type']=='radio'){  $opts = $field['ch']; ?>
					<?php  
						foreach ($opts as $k=>$v){
							$sel = '';
							if($k==esc_attr( $value )  && esc_attr( $value )!==''){ $sel = ' checked="checked" '; }
							echo '<input type="radio" id="'.$slug.'" name="ri_'.$slug.'" '.$sel.' value="'.$k.'"><span>'.$v.'</span>';	
						}
					?>
				<?php }
				if($field['type']=='checkbox'){  $opts = $field['ch']; ?>
					<?php   //print_r($value);
						foreach ($opts as $k=>$v){
							$sel = '';
							if($value){ //if(in_array($k, $value, true)){ $sel = ' checked="checked" ';  } 
								foreach($value as $chv){ if($k==$chv){ $sel = ' checked="checked" '; } } 
							 }
							echo '<input type="checkbox" id="'.$slug.'" name="ri_'.$slug.'[]" '.$sel.' value="'.$k.'"><span>'.$v.'</span>';	
						}
					?>
				<?php } ?>
				</div><?php
				}  
			}
		}
		
	}

}

}
add_action( 'save_post', 'realmeta_save_cf_meta_box' );

function realmeta_save_cf_meta_box( $post_id ) {
 
        if ( ! isset( $_POST['realmeta_inner_custom_box_nonce'] ) ) {
            return $post_id;
        }
 
        $nonce = $_POST['realmeta_inner_custom_box_nonce'];
 
        if ( ! wp_verify_nonce( $nonce, 'realmeta_inner_custom_box' ) ) {
            return $post_id;
        }
 	$ptype = 'post';
	if(isset($_REQUEST['post_type'])){ $ptype = $_REQUEST['post_type']; }
	else if(isset($_REQUEST['post'])){
		 $ptype = get_post_type($_REQUEST['post']);
	}
	if(get_option( 'ri_custom_field_opt' )){
		$ri_custom_fields_disp = unserialize(get_option( 'ri_custom_field_opt' ));
		if($ri_custom_fields_disp){
			foreach($ri_custom_fields_disp as $ri_custom_field_disp){
				foreach($ri_custom_field_disp as $slug=>$field){
					if(in_array($ptype, $field['vis'], true)){
						if($field['type']=='checkbox'){
							$ridata = $_POST['ri_'.$slug] ;
						}else{	$ridata = sanitize_text_field( $_POST['ri_'.$slug] );  }
						update_post_meta( $post_id, '_ricf_'.$slug, serialize($ridata) );
					}
				}
			}
		}
	}
		
}

//###################################################

