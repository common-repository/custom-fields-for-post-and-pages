jQuery(document).ready(function($) {
    $('.mc').hide();
	$('#ft').change(function(){
		if($(this).val()=='select' || $(this).val()=='checkbox' || $(this).val()=='radio'){
			$('.mc').show(); $('.sc').hide();
		}
		else{
			$('.sc').show(); $('.mc').hide();
		}
	});
	
	$("#riaddnew").click(function(){
		$('#mc').append('<br><b></b><input type="text" name="choices[]" placeholder="" />');	
	});
});	