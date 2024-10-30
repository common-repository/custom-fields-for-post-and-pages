<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
if(current_user_can('manage_options')):

$ripath = plugins_url( '', __FILE__);
$args = array(  'public'   => true /*,   '_builtin' => false*/ );
$rif_types = array('text'=>'Text Field', 'select'=>'Drop Down', 'textarea'=>'Text Area', 'checkbox'=>'Checkboxes', 'radio'=>'Radio Buttons');

$ripost_types = get_post_types($args);




?>
<div class="wrap ripladmin">
<form action="" method="post">
	<input type="hidden" name="_ricfanonce" value="<?php echo wp_create_nonce( 'ricfn-nonce' ); ?>" />
  <p><b>Visible in : </b>
	<?php
		foreach ( $ripost_types as $k=>$post_type ) {
			$sel = '';
			echo '<input type="checkbox" name="ptype[]" '.$sel.' value="'.$k.'">'.$post_type.' ';
		}
  ?>
  </p>
  <p><b>Field Type : </b>
	<select name="ftype" id="ft"><?php
		foreach ( $rif_types as $k=>$post_type ) {
			$sel = '';
			//if($k==$ritype){ $sel = 'selected="selected"'; }
		   echo '<option '.$sel.' value="'.$k.'">'.$post_type.'</option>';
		}
    ?></select>
  </p>
  <p><b>Label : </b> <input type="text" name="lab" /></p>
  <p class="sc">
  	<b>Placeholder : </b> <input type="text" name="ph" />
  </p>
  <p class="mc" id="mc">
  	<b>Choices : </b> <input type="text" name="choices[]" placeholder="" />
  </p>
  <p  class="mc"><a id="riaddnew">Add More Choice</a></p>

  <p><input type="submit" value="Create Field" /></p>
</form>
<?php
//postList type='post' cat='23' tag='24' ordby='date' ord='asc' count='10' offset='0' temp='t1' hide='date,author' exrpt='50';

if(isset($_POST['_ricfanonce']) && wp_verify_nonce( $_POST['_ricfanonce'], 'ricfn-nonce' )){
	if(isset($_POST['ptype'])){ $h = '';
		if($_POST['oph']){ $h = implode(',' , $_POST['oph']); }
		$ptype = array(); $ftype = ''; $lab = ''; $ph = ''; $rich = array(); 
		$ptype = $_POST['ptype'];
		foreach($ptype as $k=>$val){
			if( strlen($val) < 30 ){ $ptype[$k] = sanitize_text_field($val); }
		}
		
		if( strlen($_POST['ftype']) < 30 ){ $ftype =  sanitize_text_field($_POST['ftype']); }
		if( strlen($_POST['lab']) < 30 ){ $lab =  sanitize_text_field($_POST['lab']); }
		if( strlen($_POST['ph']) < 30 ){ $ph =  sanitize_text_field($_POST['ph']); }
		$rich = $_POST['choices'];
		foreach($rich as $k=>$val){
			if( strlen($val) < 30 ){ $rich[$k] = sanitize_text_field($val); }
		}
		
		//echo '<pre>';
		/*print_r($ptype); echo '<br>';
		echo $ftype; echo '<br>';
		echo $lab; echo '<br>';
		echo $ph; echo '<br>';
		echo '<pre>';
		print_r($rich);*/
		
		$slug = str_replace(" ","-", strtolower($lab));
		$newfiels = array();
		$newfiels[$slug] = array('label'=>$lab,
							'type'=>$ftype,
							'ph'=>$ph,
							'vis'=>$ptype,
							'ch'=>$rich
							);
		
		//print_r($newfiels);
		$ri_custom_field = unserialize(get_option( 'ri_custom_field_opt' ));
		$ri_custom_field[] = $newfiels;
		update_option( 'ri_custom_field_opt', serialize($ri_custom_field) );
	}
}

if(get_option( 'ri_custom_field_opt' )){
	$ri_custom_fields_disp = unserialize(get_option( 'ri_custom_field_opt' ));
	
}
else{ if(add_option( 'ri_custom_field_opt' )){  } }

if($ri_custom_fields_disp){
	/*echo '<pre>';
	print_r($ri_custom_fields_disp);*/
	
	
	
	echo '<table class="wp-list-table widefat fixed striped pages">';
	echo '<thead><tr> <td>Field Name</td> <td>Slug</td> <td>Field Type</td> <td>Placeholder</td> <td>Choices (for multiple choices)</td> <td>Visibility</td> </tr></thead><tbody>';
	foreach($ri_custom_fields_disp as $ri_custom_field_disp){
		foreach($ri_custom_field_disp as $slug=>$field){
			echo '<tr>
			<td>'.$field['label'].'</td>
			<td>'.$slug.'</td>
			<td>'.$field['type'].'</td>
			<td>'.$field['ph'].'</td>
			<td>'; 
			if(sizeof($field['ch'])>0){ echo implode(',', $field['ch']); }
			echo '</td>
			<td>';
			if(sizeof($field['vis'])>0){ echo implode(',', $field['vis']); }
			echo '</td>
			</tr>';
		}
	}
	echo '</tbody></table>';
}


endif;
?>
</div>