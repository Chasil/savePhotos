jQuery(document).ready(function( $ ){

	jQuery('body').on('click', '.savePhotos', function(){
		if(jQuery('body').find('.ch_cb:checked').length === 0){
			alert('Brak zaznaczonych produktow');
			return false;
		}
	});

	jQuery('.ch_cb').attr('checked', true);
	
	jQuery('body').on('click', '#ch_select_all', function(){
		jQuery('.ch_cb').attr('checked', true);
	});
	jQuery('body').on('click', '#ch_deselect_all', function(){
		jQuery('.ch_cb').attr('checked', false);
	});
});