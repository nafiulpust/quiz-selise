/* jshint asi: true */

jQuery(document).ready(function($){
	$('.fca-qc-quiz_title').change(function(){
		$('#title').val( $(this).val() )
	})
	
	if( fcaQcAdminData.isNewQuiz ) {
		var quizType = $('#fca_qc_quiz_type').val()
		var newTitle = ''
		switch( quizType ) {
			case 'mc':
				newTitle = 'My Multiple Choice Quiz'
				break;
				
			case 'pt':
				newTitle = 'My Personality Quiz'
				break;
				
			case 'wq':
				newTitle = 'My Weighted Quiz'
				break;
				
			
		}
		$('.fca-qc-quiz_title').val( newTitle ).trigger('change')
	}
	//DISABLE DESCRIPTION METABOX FROM DRAGGABLE ( WILL BREAK THE WYSIHTML )
	$('.meta-box-sortables').sortable( "destroy" )
	$('.handle-actions').hide()

	$('.postbox .hndle').css('cursor', 'auto')
	
	
	$('#fca_qc_results_meta_box .postbox-header h2').addClass('fca-qc-color2')
	$('#fca_qc_questions_meta_box .postbox-header h2').addClass('fca-qc-color3')
	$('#fca_qc_description_meta_box .postbox-header h2').addClass('fca-qc-color1')
	
	$('.qc_radio_input').click( function() {
		$(this).closest('.radio-toggle').children('label').removeClass('selected')
		$(this).closest('label').addClass('selected')
	})

	if ( $('.fca_qc_custom_css').length > 0 ) {
		wp.codeEditor.initialize( $( '.fca_qc_custom_css' ), fcaQcAdminData.code_editor )
	}

	//SET THE SLUG IF ITS EMPTY
	if ( $('#post_name').val() === '' ) {
		$('#post_name').val( $('#post_ID').val() )
	}
	
	//SET TRANSLATIONS FOR ON/OFF SWITCHES
	$( '.onoffswitch-inner' ).each( function(){
		$(this).attr('data-content-on', fcaQcAdminData.on_string )
		$(this).attr('data-content-off', fcaQcAdminData.off_string )
	})
		
	//SET UP SAVE AND PREVIEW BUTTONS, THEN HIDE THE PUBLISHING METABOX
	var saveButton = '<button type="submit" class="button-primary" id="fca_qc_submit_button">' + fcaQcAdminData.save_string + '</buttton>'
	var previewButton = '<button type="button" class="button-secondary" id="fca_qc_preview_button">' + fcaQcAdminData.preview_string + '</buttton>'

	$( '#normal-sortables' ).append( saveButton )
	$( '#normal-sortables' ).append( previewButton )
		
	//BEFOREUNLOAD WARNING HANDLER
	var loadState = ''
	$( '.postbox .inside textarea, .fca_qc_input_wide, .fca_qc_text_input' ).each( function( i, textarea ){ loadState += textarea.value } )
	$( '.postbox .inside .onoffswitch-checkbox' ).each( function( i, checkbox ){ loadState += checkbox.checked } )

	$( window ).bind( 'beforeunload', function( e ){
		var unloadState = ''
		$( '.postbox .inside textarea, .fca_qc_input_wide, .fca_qc_text_input' ).each( function( i, textarea ){ unloadState += textarea.value } )
		$( '.postbox .inside .onoffswitch-checkbox' ).each( function( i, checkbox ){ unloadState += checkbox.checked } )
		if( loadState != unloadState ){
			return true
		} else {
			e = null
		}
	})

	//SUBMIT / SAVE HANDLER
	$('#fca_qc_submit_button').click(function(event) {
		$( window ).unbind( 'beforeunload' )
	
		// Add target
		var thisForm = $(this).closest('form')
		thisForm.removeAttr('target')

		// Remove preview url
		$('#fca_qc_quiz_preview_url').val('')

	})


	$('#post').submit(function(event) {

		// Submit questions
		fca_qc_save_question_json()

		// Submit results
		fca_qc_save_result_json()

	})
	
	//MAKES CLICKING LABELS AUTO-SELECT THE NEXT ITEM
	$('.fca_qc_admin_label').click(function(e) {
		$( this ).next().focus()
	})
	
	if( $('#fca_qc_quiz_type').val() === 'pt' ) {
		$('#fca_qc_answer_mode_tr').hide()
		$('#fca_qc_hints_toggle_tr').hide()
		$('.fca_qc_result_score_value').hide()
		$('.fca_qc_question_texta').attr('placeholder', 'e.g. Do you like catnip?' )
	}
	
	if( $('#fca_qc_quiz_type').val() === 'wq' ) {
		$('#fca_qc_hints_toggle_tr').hide()
	}
	
	 
	//MAKES SHORTCODE INPUT AUTO-SELECT THE TEXT WHEN YOU CLICK IT
	$('.fca_qc_shortcode_input').click(function(e) {
		this.select()
	})
	
	$('#fca_qc_shortcode_label').click(function(e) {
		$('.fca_qc_shortcode_input').select()
	})
	
	$('#fca_qc_preview_button').click(function(event) {
		
		event.preventDefault()

		// Add target
		var thisForm = $(this).closest('form')
		thisForm.prop('target', '_blank')

		// Submit questions
		fca_qc_save_question_json()

		// Submit results
		fca_qc_save_result_json()

		thisForm.submit()

	})	
	
	$( '#submitdiv' ).hide()
	
	//HIDE "ADD IMAGE" BUTTONS IF IMAGE HAS BEEN SET
	$('.fca_qc_image').each(function(index){
		if ( $(this).attr('src') !== '' ) {
			$(this).siblings('.fca_qc_quiz_image_upload_btn').hide()
		}
		
	})
	
	// ACTIVATE TOOLTIPS
	jQuery.widget.bridge( 'jQueryUITooltipFCAQC', jQuery.ui.tooltip )
	$('.fca_qc_tooltip').jQueryUITooltipFCAQC({
		position: { my: 'left', at: 'right+2' }
	})
	
	//NAVIGATION TABS	
	$('#general-nav').click(function(){
		$( '.nav-tab-active').removeClass( 'nav-tab-active' )
		$( this ).addClass( 'nav-tab-active' ).blur()
		fca_qc_hide_metaboxes()
		
		
		$( '#fca_qc_description_meta_box, #fca_qc_quiz_settings_meta_box, #fca_qc_quiz_timer_meta_box' ).show()	
		
		
	}).click()
	
	$('#questions-nav').click(function(){
		$( '.nav-tab-active').removeClass( 'nav-tab-active' )
		$( this ).addClass( 'nav-tab-active' ).blur()
		fca_qc_hide_metaboxes()
		$( '#fca_qc_questions_meta_box, #fca_qc_question_settings_meta_box' ).show()

	})
	
	$('#results-nav').click(function(){
		$( '.nav-tab-active').removeClass( 'nav-tab-active' )
		$( this ).addClass( 'nav-tab-active' ).blur()
		fca_qc_hide_metaboxes()
		$( '#fca_qc_results_meta_box, #fca_qc_social_sharing_meta_box, #fca_qc_email_optin_meta_box' ).show()	

	})

	$('#appearance-nav').click(function(){
		$( '.nav-tab-active').removeClass( 'nav-tab-active' )
		$( this).addClass( 'nav-tab-active' ).blur()
		fca_qc_hide_metaboxes()
		$( '#fca_qc_quiz_appearance_meta_box, #fca_qc_startbtn_appearance_meta_box, #fca_qc_answers_appearance_meta_box, #fca_qc_rwpanel_appearance_meta_box, #fca_qc_custom_css_meta_box' ).show()	
		
		// ALWAYS HIDE RWPANEL METABOX IF IT'S NOT MULTIPLE CHOICE
		if ( $('#fca_qc_quiz_type').val() === 'mc' ){
			$('.fca_qc_hide_answers_input').each( function(){
				if ( $(this).prop('checked') && $(this).val() === 'hide' ) {
					$('#fca_qc_rwpanel_appearance_meta_box').hide()
				} else {
					$('#fca_qc_rwpanel_appearance_meta_box').show()
				}
			})
		} else {
			$('#fca_qc_rwpanel_appearance_meta_box').hide()
		}
	})

	$('#translations-nav').click(function(){
		$('.nav-tab-active').removeClass('nav-tab-active')
		$(this).addClass('nav-tab-active').blur()
		fca_qc_hide_metaboxes()
		$('#fca_qc_translations_meta_box').show()	

	})

	//THE ADD QUESTION BUTTON
	$( '.fca_qc_add_question_btn' ).click(function() {
		fca_qc_new_question()
		fca_qc_load_question_modal( $( '.fca_qc_question_item' ).last().data('question'), 'animateup' )
	
	})
	
	//THE ADD RESULT BUTTON
	$( '.fca_qc_add_result_btn' ).click(function() {
		fca_qc_new_result()
		fca_qc_load_result_modal( $( '.fca_qc_result_item' ).last().data('result'), 'animateup' )
	})
	
	/*
	//RN NOTE: ADDED CLOSE BUTTON MAYBE USE THAT INSTEAD...
	$('.fca-qc-modal').click(function(e){
		
		if ( e.target ) {
			var t = e.target
			if ( t.id === 'fca-qc-question-modal' ) {
				fca_qc_close_question_modal()
				
			}
			if ( t.id === 'fca-qc-result-modal' ) {
				fca_qc_close_result_modal()
				
			}
		}
	})
	*/
	
	
	$( document ).on( 'keyup', function(e) {
		if ( e.key == "Escape" ){
			if( $( '#fca-qc-question-modal' ).is( ':visible' ) ) {
				fca_qc_close_question_modal()				
			}
			if( $( '#fca-qc-result-modal' ).is( ':visible' ) ) {
				fca_qc_close_result_modal()				
			}
		}
	})
		
	$('#fca-qc-question-modal .fca-qc-modal-controls .dashicons-no-alt').click(function(e){
		fca_qc_close_question_modal()	
	})
	$('#fca-qc-result-modal .fca-qc-modal-controls .dashicons-no-alt').click(function(e){
		fca_qc_close_result_modal()	
	})
		
	$('#fca-qc-question-modal .fca-qc-modal-controls .dashicons-arrow-left-alt2').click(function(e){
		fca_qc_save_question_modal()
		var question_id = $( '#fca-qc-question-id' ).val()
		var $question = $( '[data-question_id="' + question_id  + '"]' )
		
		fca_qc_load_question_modal( $question.prev().data('question'), 'animateup' )
	})
	
	$('#fca-qc-question-modal .fca-qc-modal-controls .dashicons-arrow-right-alt2').click(function(e){
		fca_qc_save_question_modal()
		var question_id = $( '#fca-qc-question-id' ).val()
		var $question = $( '[data-question_id="' + question_id  + '"]' )
		
		fca_qc_load_question_modal( $question.next().data('question'), 'animateup' )	
	})
	
	$('#fca-qc-result-modal .fca-qc-modal-controls .dashicons-arrow-left-alt2').click(function(e){
		fca_qc_save_result_modal()
		var result_id = $( '#fca-qc-result-id' ).val()
		var $result = $( '[data-result_id="' + result_id  + '"]' )
		
		fca_qc_load_result_modal( $result.prev().data('result'), 'animateup' )
	})
	
	$('#fca-qc-result-modal .fca-qc-modal-controls .dashicons-arrow-right-alt2').click(function(e){
		fca_qc_save_result_modal()
		var result_id = $( '#fca-qc-result-id' ).val()
		var $result = $( '[data-result_id="' + result_id  + '"]' )
		
		fca_qc_load_result_modal( $result.next().data('result'), 'animateup' )	
	})

	$('#fca-qc-question-modal .fca-qc-modal-controls .fca_qc_copy_question').click(function(e){
		fca_qc_save_question_modal()
		var question_id = $( '#fca-qc-question-id' ).val()
		var $question = $( '[data-question_id="' + question_id  + '"]' )
		fca_qc_copy_question( $question )
		fca_qc_load_question_modal( $( '.fca_qc_question_item' ).last().data( 'question'  ), 'animateup' )
	})
	
	$('#fca-qc-result-modal .fca-qc-modal-controls .fca_qc_copy_result').click(function(e){
		fca_qc_save_result_modal()
		var result_id = $( '#fca-qc-result-id' ).val()
		var $result = $( '[data-result_id="' + result_id  + '"]' )
		fca_qc_copy_result( $result )
		fca_qc_load_result_modal( $( '.fca_qc_result_item' ).last().data('result'), 'animateup' )
	})
	
	//SHOW OUR MAIN DIV AFTER WE'RE DONE WITH DOM CHANGES


	fca_qc_add_drag_and_drop_sort()
	fca_qc_add_question_and_result_click_handlers()
	
	fca_qc_delete_button_handlers()
	fca_qc_add_answer_button_handlers()
	fca_qc_attach_image_upload_handlers()
	
	fca_qc_set_question_numbers()
	fca_qc_set_score_ranges()
	fca_qc_set_default_ids()
	
	$( '#wpbody-content').show()
})

function fca_qc_copy_question( $target ) {
	var $ = jQuery
	var newId = fca_qc_new_GUID()
	var div_to_append = fcaQcAdminData.questionDiv.replace(/{{ID}}/g, newId )
	
	$( '.fca_qc_sortable_questions' ).append( div_to_append )
		
	var targetData = JSON.parse( JSON.stringify( $target.data('question') ) )
	//MAKE A COPY OF THE OBJECT..OTHERWISE IT SEEMS TO MODIFY ORIGINAL ONE HERE
	targetData.id = newId
	$( '.fca_qc_question_item' ).last().data( 'question', targetData )
	
	fca_qc_add_drag_and_drop_sort()
	fca_qc_add_question_and_result_click_handlers()
	fca_qc_delete_button_handlers()
	fca_qc_set_score_ranges()
	fca_qc_set_question_numbers()
	
}

function fca_qc_new_question() {
	var $ = jQuery
	var newId = fca_qc_new_GUID()
	var div_to_append = fcaQcAdminData.questionDiv.replace(/{{ID}}/g, newId )
	
	$( '.fca_qc_sortable_questions' ).append( div_to_append )
	
	fca_qc_add_drag_and_drop_sort()
	fca_qc_add_question_and_result_click_handlers()
	fca_qc_delete_button_handlers()
	fca_qc_set_score_ranges()
	fca_qc_set_question_numbers()
	
}

function fca_qc_new_result() {
	var $ = jQuery
	var newId = fca_qc_new_GUID()
	var div_to_append = fcaQcAdminData.resultDiv.replace(/{{ID}}/g, newId )
	
	$( '.fca_qc_sortable_results' ).append( div_to_append )
	
	fca_qc_add_drag_and_drop_sort()
	fca_qc_add_question_and_result_click_handlers()
	fca_qc_delete_button_handlers()
	fca_qc_set_score_ranges()
	
}

function fca_qc_copy_result( $target ) {
	var $ = jQuery
	var newId = fca_qc_new_GUID()
	var div_to_append = fcaQcAdminData.resultDiv.replace(/{{ID}}/g, newId )
		
	var targetData = JSON.parse( JSON.stringify( $target.data('result') ) )
	//MAKE A COPY OF THE OBJECT..OTHERWISE IT SEEMS TO MODIFY ORIGINAL ONE HERE
	targetData.id = newId
	$( '.fca_qc_sortable_results' ).append( div_to_append )
	$( '.fca_qc_result_item' ).last().data( 'result', targetData )
	
	fca_qc_add_drag_and_drop_sort()
	fca_qc_add_question_and_result_click_handlers()
	fca_qc_delete_button_handlers()
	fca_qc_set_score_ranges()
	
}

function fca_qc_animate_modal( animClass ) {
	var $ = jQuery
	
	$('.fca-qc-modal .fca-qc-modal-inner').addClass( animClass )

	window.setTimeout(function(){
		$('.fca-qc-modal .fca-qc-modal-inner').removeClass( animClass )
	}, 400 )
}

function fca_qc_set_default_ids() {
	var $ = jQuery
	$('.fca_qc_question_item').each(function(){
		var data = $(this).data( 'question' )
		
		if( data.id === "{{ID}}" ) {
			data.id = fca_qc_new_GUID()
			$(this).data( 'question', data )
			$(this).attr( 'data-question_id', data.id )
		}
	})
	
	$('.fca_qc_result_item').each(function(){
		var data = $(this).data('result')
		
		if( data.id === "{{ID}}" ) {
			data.id = fca_qc_new_GUID()
			if( fcaQcAdminData.isNewQuiz ) {
				data.title = 'Grumpy Cat'
				$(this).find('.fca_qc_result_score_title').text( data.title )
			}
			$(this).data( 'result', data )
			$(this).attr( 'data-result_id', data.id )
		}

	})
	
	var quizType = $('#fca_qc_quiz_type').val()
	if( quizType === 'pt' && fcaQcAdminData.isNewQuiz ) {
		//ADD A SECOND DEFAULT RESULT
		fca_qc_new_result()
		var $newResult = $( '.fca_qc_result_item' ).last()
		var newItemData = $newResult.data( 'result' )
		newItemData.title = 'Happy Cat'
		$newResult.find('.fca_qc_result_score_title').text( newItemData.title )
		$newResult.data( 'result', newItemData )
	}
}
//GLOBAL FUNCTIONS

function fca_qc_hide_metaboxes(){
	var $ = jQuery
	
	$('#fca_qc_quiz_settings_meta_box, #fca_qc_quiz_timer_meta_box, #fca_qc_social_sharing_meta_box, #fca_qc_email_optin_meta_box, #fca_qc_description_meta_box, #fca_qc_weighted_questions_meta_box, #fca_qc_add_weighted_result_meta_box, #fca_qc_questions_meta_box, #fca_qc_results_meta_box, #fca_qc_personality_questions_meta_box, #fca_qc_add_personality_result_meta_box, #fca_qc_quiz_appearance_meta_box, #fca_qc_startbtn_appearance_meta_box, #fca_qc_answers_appearance_meta_box, #fca_qc_rwpanel_appearance_meta_box, #fca_qc_custom_css_meta_box, #fca_qc_translations_meta_box, #fca_qc_question_settings_meta_box').hide()
	
}


function fca_qc_add_answer_button_handlers() {
	var $ = jQuery
	$('.fca_qc_add_answer_btn').unbind( 'click' )

	$('.fca_qc_add_answer_btn').click(function() {
		var newId = fca_qc_new_GUID()
		var quizType = $('#fca_qc_quiz_type').val()
		
		switch( quizType ) {
			case 'mc':
				div_to_append = fcaQcAdminData.answerDiv
				div_to_append = div_to_append.replace( /{{answer_id}}/g, newId )
				div_to_append = div_to_append.replace( /{{answer_text}}/g, '' )		
				
				$('.fca_qc_add_answer_btn').before( div_to_append )
				
				fca_qc_delete_button_handlers()

				fca_qc_attach_image_upload_handlers()
				break
				
			case 'wq':
				fca_qc_add_weighted_answer()
				break
				
			case 'pt':
				fca_qc_add_personality_answer()
				break
		}
		
		
	})
}


//THE DELETE QUESTION BUTTON
function fca_qc_delete_button_handlers() {
	var $ = jQuery
	
	$('.fca_qc_delete_icon').unbind( 'click' )
	
	$('.fca_qc_delete_icon').click( function(){	
		if ( confirm( fcaQcAdminData.sureWarning_string ) ) {
			$( this ).closest( '.fca_qc_deletable_item' ).remove()
			fca_qc_set_question_numbers()
			fca_qc_set_score_ranges()
			
		}
	})
		 
}

//MAKES QUESTION AND RESULT LABELS TOGGLE THE INPUT VISIBILITY ON CLICK
function fca_qc_add_question_and_result_click_handlers() {
	var $ = jQuery
	$( '.fca_qc_question_item' ).unbind( 'click' )

	$( '.fca_qc_question_item' ).click( function(e) {
		var trash = $(e.target).hasClass('fca_qc_delete_icon')
		if ( fcaQcDragCheck === false && !trash ) {
			fca_qc_load_question_modal( $(this).data('question') )
			
		}
		
	})	
	
	$( '.fca_qc_result_item' ).unbind( 'click' )
	
	$( '.fca_qc_result_item' ).click( function(e) {
		var trash = $(e.target).hasClass('fca_qc_delete_icon')
		if ( fcaQcDragCheck === false && !trash ) {
			fca_qc_load_result_modal( $(this).data('result') )	
		}
		
	})	
	
	$( '.fca_qc_question_input_div, .fca_qc_result_input_div, .fca_qc_delete_icon' ).bind( 'click', function(e) {
		e.stopPropagation()
	})
	
}

////////////////
// HELPER FUNCTIONS
////////////////
function fca_qc_load_question_modal( question, animClass ) {
	if( fcaQcAdminData.debug ) {
		console.log( question )
	}
	if( typeof( question ) === 'undefined' ) {
		return
	}
	
	var $ = jQuery
	var $question = $( '[data-question_id="' + question.id  + '"]' )
	var questionNumber = 1 + $question.index()
	var quizType = $('#fca_qc_quiz_type').val()
	
	
	$( '#fca-qc-question-number' ).text( questionNumber )
	$( '#fca-qc-question-text' ).val( question.question )
	$( '#fca-qc-question-id' ).val( question.id )
	
	//SET/RESET IMAGE STATE
	
	$( '#fca_qc_quiz_question_image').siblings('.fca_qc_image').attr('src', '' )
	$( '#fca-qc-question-modal' ).find( '.fca_qc_quiz_image_upload_btn' ).show()
	
	
	if( question.img ){
		
		$( '#fca_qc_quiz_question_image').siblings( '.fca_qc_image' ).attr( 'src', question.img )
		$( '#fca-qc-question-modal' ).find( '.fca_qc_quiz_image_upload_btn' ).hide()
	}
	
	//MAYBE DISABLE NEXT/PREV BUTTON
	if( questionNumber === 1 ) {
		$( '#fca-qc-question-modal' ).find( '.dashicons-arrow-left-alt2' ).addClass('disabled')
	} else {
		$( '#fca-qc-question-modal' ).find( '.dashicons-arrow-left-alt2' ).removeClass('disabled')
	}
	
	if( questionNumber === (1 + $( '.fca_qc_question_item' ).last().index()) ) {
		$( '#fca-qc-question-modal' ).find( '.dashicons-arrow-right-alt2' ).addClass('disabled')
	} else {
		$( '#fca-qc-question-modal' ).find( '.dashicons-arrow-right-alt2' ).removeClass('disabled')
	}
	
	switch( quizType ) {
		case 'mc':
			fca_qc_load_question_answers( question.answers )
			break
			
		case 'wq':
			fca_qc_load_weighted_answers( question.answers )
			break
			
		case 'pt':
			fca_qc_load_personality_answers( question.answers )
			break
	}
	
	
	$('body').css('overflow', 'hidden')
	$( '#fca-qc-question-modal' ).show()
	
	if ( typeof( animClass ) !== 'undefined' ) {
		fca_qc_animate_modal( animClass )		
	}
	
	
}

function fca_qc_load_question_answers( answers ) {
	var $ = jQuery
	
	$( '.fca_qc_answer_input_div' ).remove()
	
	for( var i = 0; i < answers.length; i++ ) {
		var div_to_append = fcaQcAdminData.answerDiv
		if( i == 0 ) {
			div_to_append = fcaQcAdminData.correctAnswerDiv			
		}
		
		div_to_append = div_to_append.replace( /{{answer_id}}/g, answers[i].id )
		div_to_append = div_to_append.replace( /{{answer_text}}/g, answers[i].answer )
		div_to_append = div_to_append.replace( /{{hint}}/g, answers[i].hint )
		
		
		$( '.fca_qc_add_answer_btn' ).before( div_to_append )
		if ( answers[i].img ) {
			$('.fca_qc_answer_input_div').last().find('.fca_qc_image').attr( 'src', answers[i].img )
			$('.fca_qc_answer_input_div').last().find('.fca_qc_quiz_image_upload_btn').hide()	
		}
	}
	//EXPLANATION/HINT ENABLE TOGGLE
	
	if ( $('#fca_qc_explanations').prop('checked') ) {
		$('.fca_qc_explanations_tr').show()
	} else {
		$('.fca_qc_explanations_tr').hide()
	}
	
	fca_qc_delete_button_handlers()

	fca_qc_attach_image_upload_handlers()
		
}

function fca_qc_save_question_answers() {
	var $ = jQuery
	var quizType = $('#fca_qc_quiz_type').val()
	var answers = []
		
	$( '.fca_qc_answer_input_div' ).each(function(){
		answers.push({
			id: $( this ).find( '.fca-qc-answer_id' ).val(),
			answer: $( this ).find( '#fca-qc-answer-text' ).val(),
			img: $( this ).find( '.fca_qc_image' ).attr( 'src' ),
			hint: $( '#fca-qc-hint-text' ).val(),
			points:  $( this ).find( '.fca-qc-weighted-question-points' ).val(),
			results: $( this ).find( '.fca_qc_answer_personality' ).val()
		})
	})
	
	return answers
}

function fca_qc_save_question_modal() {
	var $ = jQuery
	var question_id = $( '#fca-qc-question-id' ).val()
	var $question = $('[data-question_id="' + question_id  + '"]')
	var questionData = {
		id: question_id,
		question: $( '#fca-qc-question-text' ).val(),
		img: $( '#fca_qc_quiz_question_image').siblings('.fca_qc_image').attr( 'src' ),
		answers: fca_qc_save_question_answers(),
		
	}
	
	$question.find( '.fca_qc_quiz_heading_text' ).text( $( '#fca-qc-question-text' ).val() )
	
	$question.data( 'question', questionData )
	
	if( fcaQcAdminData.debug ) {
		console.log( questionData )
	}
}

function fca_qc_save_result_modal() {
	var $ = jQuery
	var result_id = $( '#fca-qc-result-id' ).val()
	var $result = $('[data-result_id="' + result_id  + '"]')
	var wysihtml5Editor = $( '.fca-qc-result_description' ).data("wysihtml5")
	var resultData = {
		id: result_id, 
		title: $( '#fca-qc-result-title' ).val(),
		desc: wysihtml5Editor.getValue(),
		img: $( '#fca_qc_quiz_result_image').siblings('.fca_qc_image').attr( 'src' ),
		url: $( '#fca-qc-result-url').val(),
		min: $('.fca-qc-result_min' ).val(),	
		max: $('.fca-qc-result_max' ).val(),
		groups: $('#fca_qc_quiz_result_mailchimp_groups' ).val(),
		tags: $('#results_tag_hidden_input' ).val()
	}
	 
	$result.find( '.fca_qc_result_score_title' ).text( resultData.title )
	
	$result.data( 'result', resultData )
	
	if( fcaQcAdminData.debug ) {
		console.log( resultData )
	}
} 

function fca_qc_close_question_modal() {
	var $ = jQuery
	fca_qc_save_question_modal()
	$('.fca-qc-modal').hide()
	$('body').css('overflow', 'auto')
}

function fca_qc_close_result_modal() {
	var $ = jQuery
	fca_qc_save_result_modal()
	$('.fca-qc-modal').hide()
	$('body').css('overflow', 'auto')
}


function fca_qc_save_question_json() {
	
	var $ = jQuery
	var questions = []
	var $questionList = $( '.fca_qc_question_item' )

	$( $questionList ).each(function(){					
		questions.push( $(this).data( 'question' ) )		
	})
	
	$('#fca_qc_questions_json').val( JSON.stringify( questions ) )
	
}

function fca_qc_save_result_json() {
	
	var $ = jQuery
	var results = []
	var $resultList = $( '.fca_qc_result_item' )
	$resultList.each( function() {
		results.push( $(this).data( 'result' ) )
	})

	$('#fca_qc_results_json').val( JSON.stringify( results ) )

}

function fca_qc_load_result_modal( result, animClass ) {
	if( fcaQcAdminData.debug ) {
		console.log( result )
	}
	if( typeof( result ) === 'undefined' ) {
		return
	}
	
	var $ = jQuery
	var $result = $( '[data-result_id="' + result.id  + '"]' )
	var resultNumber = 1 + $result.index()
	var quizType = $('#fca_qc_quiz_type').val()
	var wysihtml5Editor = $( '.fca-qc-result_description' ).data("wysihtml5")
	wysihtml5Editor.setValue( result.desc )
	
	$( '#fca-qc-result-id' ).val( result.id )
	$( '#fca-qc-result-title' ).val( result.title )
	$( '#fca-qc-result-number' ).text( resultNumber )
	
		
	$( '#fca_qc_quiz_result_image').siblings('.fca_qc_image').attr('src', '' )
	$( '#fca-qc-result-modal' ).find( '.fca_qc_quiz_image_upload_btn' ).show()
	if( result.img ){		
		$( '#fca_qc_quiz_result_image').siblings( '.fca_qc_image' ).attr( 'src', result.img )
		$( '#fca-qc-result-modal' ).find( '.fca_qc_quiz_image_upload_btn' ).hide()
	}
	
	if( typeof( fca_qc_load_tags ) !== 'undefined' ) {
		
		var mailchimpGroups = typeof( result.groups ) === 'undefined' ? [] : result.groups
		$('#fca_qc_quiz_result_mailchimp_groups').val( mailchimpGroups )
		$('#fca_qc_quiz_result_mailchimp_groups').select2().trigger('change')
		
		var providerTags = typeof( result.tags ) === 'undefined' ? '' : result.tags
		$('#results_tag_hidden_input' ).val( result.tags )
		$('#results-tag-div').children().remove()
		fca_qc_load_tags()		
	}
	
	//MAYBE DISABLE NEXT/PREV BUTTON
	if( resultNumber === 1 ) {
		$( '#fca-qc-result-modal' ).find( '.dashicons-arrow-left-alt2' ).addClass('disabled')
	} else {
		$( '#fca-qc-result-modal' ).find( '.dashicons-arrow-left-alt2' ).removeClass('disabled')
	}
	
	if( resultNumber === (1 + $( '.fca_qc_result_item' ).last().index()) ) {
		$( '#fca-qc-result-modal' ).find( '.dashicons-arrow-right-alt2' ).addClass('disabled')
	} else {
		$( '#fca-qc-result-modal' ).find( '.dashicons-arrow-right-alt2' ).removeClass('disabled')
	}
	
	switch( quizType ) {
					
		case 'pt':
			fca_qc_load_pt_result( result )
			break
			
		default:
			$('.fca-qc-result_min' ).val( result.min )
			$('.fca-qc-result_max' ).val( result.max )
			break;
			
	}
	
	$('body').css('overflow', 'hidden')
	$( '#fca-qc-result-modal' ).show()
	
	if ( typeof( animClass ) !== 'undefined' ) {
		fca_qc_animate_modal( animClass )		
	}
	
}

function fca_qc_load_pt_result( result ) {
	var $ = jQuery
	//LOAD PERSONALITY TYPE MULTISELECTS
	
}

//FINDS RANGE OF RESULTS FOR EACH RESULT AUTOMATICALLY.
//results -> based on question count, divided by result count, with rounding to cover all
//e.g. 5 ANSWERS, 3 RESULTS = [0-1],[2-3],[4-5]
//at max ( equal to questions ) -> remove ability to add more
//when question or result count changes, have to re-calculate
function fca_qc_set_score_ranges() {
	
	var $ = jQuery
	var maxPoints = $( '.fca_qc_question_item' ).length
	var resultCount = $( '.fca_qc_result_item' ).length
	if ( $('#fca_qc_quiz_type').val() === 'pt' ) {
		return
	}
	//WEIGHTED ANSWERS, SUM THE MAX
	if ( $('#fca_qc_quiz_type').val() === 'wq' ) {
		maxPoints = 0
		$( '.fca_qc_question_item' ).each(function(){
			var maximumForThisQuestion = 0
			var thisQuestionData = $(this).data( 'question' )
			for ( var i = 0; i< thisQuestionData.answers.length; i++ ) {
				var answerPointValue = Number( thisQuestionData.answers[i].points )
				if ( answerPointValue > maximumForThisQuestion ) {
					maximumForThisQuestion = answerPointValue
				}	
			}			
			maxPoints = maxPoints + maximumForThisQuestion
		})
		
	}
	var remainder = maxPoints % resultCount
	$( '.fca_qc_result_item' ).each( function( index, item ){
		var result = $(item).data('result')
		var min = 0
		if ( index > 0 ) {
			var prev = $(item).prev().data('result')
			min = 1 + Number( prev.max )
		}
		if ( remainder ){
			if ( index <= remainder ){
				var max = min + ( Math.floor( maxPoints / resultCount ) )
			} else {
				var max = ( min - 1 ) + ( Math.floor( maxPoints / resultCount ) )
			}
		} else { 
			var max = ( index + 1 ) * ( Math.floor( maxPoints / resultCount ) )
		}
		
		result.min = min
		result.max = max
		$(item).data( 'result', result )
		var displayString = min + '-' + max + ' ' + fcaQcAdminData.points_string + ': '
		if ( min === max ) {
			 displayString = min + ' ' + fcaQcAdminData.points_string + ': '
		}
		if ( min > max ){
			displayString = fcaQcAdminData.unused_string
		}
		$(item).find('.fca_qc_result_score_value').text( displayString )
	})
}


function fca_qc_set_question_numbers(){
	var $ = jQuery
	var n = 1;
	$('.fca_qc_question_item').each(function() {
		$(this).find( '.fca_qc_quiz_heading_question_number' ).text( fcaQcAdminData.question_string + ' ' + n + ': ')
		n = n + 1
	})
}


////////////////
// MEDIA UPLOAD
////////////////
		
function fca_qc_attach_image_upload_handlers() {
	var $ = jQuery
	//ACTION WHEN CLICKING IMAGE UPLOAD
	$('.fca_qc_quiz_image_upload_btn, .fca_qc_image, .fca_qc_quiz_image_change_btn').unbind( 'click' )
	//HANDLER FOR RESULTS AND META IMAGES
	$('.fca_qc_quiz_image_upload_btn, .fca_qc_image, .fca_qc_quiz_image_change_btn').click(function(e) {
		
		e.preventDefault()
		var $this = $( this )
		
		//IF WE CLICK ON THE IMAGE VS THE BUTTON IT HAS TO WORK A LITTLE DIFFERENTLY
		if ( $(this).hasClass( 'fca_qc_quiz_image_change_btn' ) ) {
			$this = $( this.parentNode ).siblings('.fca_qc_quiz_image_upload_btn')
		} else if ( $(this).hasClass( 'fca_qc_image' ) ) {
			$this = $( this ).siblings('.fca_qc_quiz_image_upload_btn')
		}
		
		var wpmedia = wp.media( {
			frame: "post",
			title: fcaQcAdminData.selectImage_string,
			multiple: false
		}).open()
			.on('insert', function(){
				//GET VALUE FROM WP MEDIA UPLOAD THING
				var image = wpmedia.state().get('selection').first()
				var display = wpmedia.state().display( image ).toJSON()
				image = image.toJSON()
				//Do something with attachment.id and/or attachment.url here
				var image_url = image.sizes[display.size].url
	
				//ASSIGN VALUE
				if ( image_url ) {
					$this.siblings( '#fca_qc_quiz_description_image_src' ).val( image_url )
					$this.siblings( '.fca_qc_image' ).attr( 'src', image_url )	
					//UNHIDE THE REMOVE AND CHANGE IMAGE BUTTONS
					$this.siblings('.fca_qc_image_hover_controls').find('.fca_qc_quiz_image_change_btn').show()
					$this.siblings('.fca_qc_image_hover_controls').find('.fca_qc_quiz_image_revert_btn').show()
				}

				$this.hide()
				
			})
	})
	
	//ACTION WHEN CLICKING REMOVE IMAGE
	$('.fca_qc_quiz_image_revert_btn').unbind( 'click' )
	$('.fca_qc_quiz_image_revert_btn').click( function(e) {
		
		$( this.parentNode ).siblings('.fca_qc_image').attr('src', '' )
		$( this.parentNode ).siblings('.fca_qc_quiz_image_upload_btn').show()
		$( this ).hide()
		$( this ).siblings( '.fca_qc_quiz_image_upload_btn' ).hide()
		
	})
}

//DRAG AND DROP SUPPORT
var fcaQcDragCheck = false
function fca_qc_add_drag_and_drop_sort() {
	var $ = jQuery

	$( '.fca_qc_sortable_results, .fca_qc_sortable_questions, .fca_qc_question_input_div' ).sortable({
		revert: true,
		cancel: ':input,button, .fca-wysiwyg-html',
		start: function(){
			fcaQcDragCheck = true
		},
		stop: function( ){
			fcaQcDragCheck = false			
		}

	})

	$( '.fca_qc_sortable_results' ).unbind( 'sortupdate' )
	$( '.fca_qc_sortable_results' ).on( 'sortupdate', function( event, ui ) {
		fca_qc_set_score_ranges()
	})


	$( '.fca_qc_sortable_questions' ).unbind( 'sortupdate' )
	$( '.fca_qc_sortable_questions' ).on( 'sortupdate', function( event, ui ) {
		fca_qc_set_question_numbers()
	})

}

//GUID Generation ( http://stackoverflow.com/questions/105034/create-guid-uuid-in-javascript/21963136#21963136 )
var fca_qc_hash_seed = []
for (var i=0; i<256; i++) { 
	fca_qc_hash_seed[i] = (i<16?'0':'')+(i).toString(16)
}

function fca_qc_new_GUID() {
	var d0 = Math.random()*0x100000000>>>0
	var d1 = Math.random()*0x100000000>>>0
	var d2 = Math.random()*0x100000000>>>0
	var d3 = Math.random()*0x100000000>>>0
	
	return fca_qc_hash_seed[d0&0xff]+fca_qc_hash_seed[d0>>8&0xff]+fca_qc_hash_seed[d0>>16&0xff]+fca_qc_hash_seed[d0>>24&0xff]+'-'+
	fca_qc_hash_seed[d1&0xff]+fca_qc_hash_seed[d1>>8&0xff]+'-'+fca_qc_hash_seed[d1>>16&0x0f|0x40]+fca_qc_hash_seed[d1>>24&0xff]+'-'+
	fca_qc_hash_seed[d2&0x3f|0x80]+fca_qc_hash_seed[d2>>8&0xff]+'-'+fca_qc_hash_seed[d2>>16&0xff]+fca_qc_hash_seed[d2>>24&0xff]+
	fca_qc_hash_seed[d3&0xff]+fca_qc_hash_seed[d3>>8&0xff]+fca_qc_hash_seed[d3>>16&0xff]+fca_qc_hash_seed[d3>>24&0xff]
}
