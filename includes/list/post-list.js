var $ = jQuery
$('#fca-qc-add-new-button').click(function(){
	$('.fca-qc-modal').show()
})

$('.fca-qc-modal-close').click(function(){
	$('.fca-qc-modal').hide()
}) 

$( document ).on( 'keyup', function(e) {
	if ( e.key == "Escape" ){
		$('.fca-qc-modal').hide()
	}
})