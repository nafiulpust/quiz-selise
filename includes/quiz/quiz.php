<?php


////////////////////////////
// DISPLAY QUIZ
////////////////////////////

function fca_qc_do_quiz( $atts ) {

	if ( !empty ( $atts[ 'id' ] ) ) {

		$post_id = intVal ( $atts[ 'id' ] );
		$all_meta =  get_post_meta ( $post_id, '', true );
		$quiz_meta = empty ( $all_meta['quiz_cat_meta'] ) ? array() : unserialize( $all_meta['quiz_cat_meta'][0] );
		
		$quiz_meta['title'] = get_the_title ( $post_id );
		$questions = empty ( $all_meta['quiz_cat_questions'] ) ? array() : unserialize( $all_meta['quiz_cat_questions'][0] );
		$quiz_settings = empty ( $all_meta['quiz_cat_settings'] ) ? array() : unserialize( $all_meta['quiz_cat_settings'][0] );
		$restart_button = empty ( $quiz_settings['restart_button'] ) ? false : true;
		$optin_settings = empty ( $all_meta['quiz_cat_optins'] ) ? array() : unserialize( $all_meta['quiz_cat_optins'][0] );
		$timer_mode = empty( $quiz_settings['timer_mode'] ) ? 'off' : $quiz_settings['timer_mode'];
		$draw_optins = empty( $optin_settings['capture_emails'] ) ? false : true;
		$quiz_results = empty ( $all_meta['quiz_cat_results'] ) ? array() : unserialize( $all_meta['quiz_cat_results'][0] );
		$autostart_quiz = empty ( $quiz_settings['autostart_quiz'] ) ? false : true;
		
		foreach( $quiz_results as $key => $value ){
			$quiz_results[$key]['desc'] = do_shortcode( $value['desc'] );
		}

		if ( !$quiz_meta || !$questions ) {
			return '<p>Quiz Cat: ' . esc_attr__('No Quiz found', 'quiz-cat') . '</p>';
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'fca_qc_quiz_stylesheet', FCA_QC_PLUGINS_URL . '/includes/quiz/quiz.min.css', array(), FCA_QC_PLUGIN_VER );
		wp_enqueue_script( 'fca_qc_img_loaded', FCA_QC_PLUGINS_URL . '/includes/quiz/jquery.waitforimages.min.js', array(), FCA_QC_PLUGIN_VER, true );
		
		if ( $draw_optins ) {
			wp_enqueue_style( 'fca_qc_tooltipster_stylesheet', FCA_QC_PLUGINS_URL . '/includes/tooltipster/tooltipster.bundle.min.css', array(), FCA_QC_PLUGIN_VER );
			wp_enqueue_style( 'fca_qc_tooltipster_borderless_css', FCA_QC_PLUGINS_URL . '/includes/tooltipster/tooltipster-borderless.min.css', array(), FCA_QC_PLUGIN_VER );
			wp_enqueue_script( 'fca_qc_tooltipster_js', FCA_QC_PLUGINS_URL . '/includes/tooltipster/tooltipster.bundle.min.js', array('jquery'), FCA_QC_PLUGIN_VER, true );
			wp_enqueue_script( 'fca_qc_jstz_js', FCA_QC_PLUGINS_URL . '/includes/quiz/jstz.min.js', array(), FCA_QC_PLUGIN_VER, true );
		}
		
		if ( FCA_QC_DEBUG ) {
			wp_enqueue_script( 'fca_qc_quiz_js', FCA_QC_PLUGINS_URL . '/includes/quiz/quiz.js', array( 'jquery', 'fca_qc_img_loaded' ), FCA_QC_PLUGIN_VER, true );
		} else {
			wp_enqueue_script( 'fca_qc_quiz_js', FCA_QC_PLUGINS_URL . '/includes/quiz/quiz.min.js', array( 'jquery', 'fca_qc_img_loaded' ), FCA_QC_PLUGIN_VER, true );
		}
		
		//DONT SEND API KEYS TO CLIENT SIDE JS
		$unset_options = array(
			'drip_key',
			'drip_id',
			'drip_tags',
			'activecampaign_key',
			'activecampaign_url',
			'activecampaign_tags',
			'getresponse_key',
			'api_key',
			'mailchimp_groups',
			'aweber_key',
			'aweber_tags',
			'madmimi_key',	
			'madmimi_email',	
			'campaignmonitor_key',
			'campaignmonitor_id',	
			'convertkit_key',
			'convertkit_tags',
			'convertkit_key',
			'zapier_url',
		);
			
		forEach ( $unset_options as $o ) {
			if ( isSet( $optin_settings[$o] ) ) { 
				unset( $optin_settings[$o] );
			}
		}		
		
		$quiz_text_strings = fca_qc_set_quiz_text_strings( $post_id );
		global $global_quiz_text_strings;
					
		$quiz_data = array(
			'quiz_id' => $post_id,
			'quiz_meta' => $quiz_meta,
			'questions' => $questions,
			'quiz_results' => $quiz_results,
			'quiz_settings' => $quiz_settings,
			'time_taken_string' => empty( $quiz_text_strings[ 'time_taken' ] ) ? $global_quiz_text_strings[ 'time_taken' ] : $quiz_text_strings[ 'time_taken' ],
			'timeout_string' => empty( $quiz_text_strings[ 'timedout' ] ) ? $global_quiz_text_strings[ 'timedout' ] : $quiz_text_strings[ 'timedout' ],
			'wrong_string' => $quiz_text_strings[ 'wrong' ],
			'correct_string' => $quiz_text_strings[ 'correct' ],
			'your_answer_string' => $quiz_text_strings[ 'your_answer' ],
			'correct_answer_string' => $quiz_text_strings[ 'correct_answer' ],
			'optin_settings' => $optin_settings,
			'nonce' => wp_create_nonce('fca_qc_quiz_ajax_nonce'),
			'ajaxurl' => admin_url('admin-ajax.php'),
			'default_img' => FCA_QC_PLUGINS_URL . '/assets/quizcat-240x240.png',
			'gdpr_checkbox' => fca_qc_show_gdpr_checkbox(),
		);
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( $user->ID !== 0 ) {
				$quiz_data['user'] = array (
					'name' => $user->user_firstname,
					'email' => $user->user_email,
					
				);
			}
		}
		wp_localize_script( 'fca_qc_quiz_js', "quizData_$post_id", $quiz_data );
		wp_localize_script( 'fca_qc_quiz_js', "fcaQcData", array( 
			'debug' => FCA_QC_DEBUG,
			'analytics' => !defined( 'fca_qc_disable_activity' ) && function_exists('fca_qc_add_activity')
		) );

		
		//ADD IMPRESSION
		if ( function_exists('fca_qc_add_activity') ) {
			$return = fca_qc_add_activity( $post_id, 'impressions' );
		}
		
		$title = empty( $quiz_meta['title'] ) ? '' : $quiz_meta['title'];
		$desc = empty( $quiz_meta['desc'] ) ? '' : $quiz_meta['desc'];
		$desc_img_src = empty( $quiz_meta['desc_img_src'] ) ? '' : $quiz_meta['desc_img_src'];
		
		ob_start(); ?>
		
		<?php echo fca_qc_maybe_add_custom_styles( $post_id ) ?>
		
		<div class='fca_qc_quiz' id='<?php echo "fca_qc_quiz_$post_id" ?>'>
			<span class='fca_qc_mobile_check'></span>
			<?php if ( $autostart_quiz === false ) { ?>
			<p class='fca_qc_quiz_title'><?php echo fca_qc_kses_html( $title ) ?></p>
			<div class='fca_qc_quiz_description'><?php echo fca_qc_kses_html( do_shortcode( $desc ) )?></div>
			<img class='fca_qc_quiz_description_img' src='<?php echo esc_attr( $desc_img_src ) ?>'>
			<button type='button' class='fca_qc_button fca_qc_start_button'><?php echo fca_qc_kses_html( $quiz_text_strings[ 'start_quiz' ] ) ?></button>
			<?php } ?>
			
			<div class='flip-container fca_qc_quiz_div' style='display: none;'>

				<?php if( $timer_mode !== 'off' ){ ?>
					<div class='fca_qc_timer'>
						<span class='fca_qc_timer_hours'></span>
						<span class='fca_qc_timer_minutes'></span>
						<span class='fca_qc_timer_seconds'></span>
					</div>
				<?php } ?>
				<div class='fca-qc-flipper'>
					<?php echo fca_qc_do_question_panel( $post_id, $quiz_text_strings ) ?> 
					<?php echo fca_qc_do_answer_panel( $quiz_text_strings, $post_id ) ?> 
					
				</div>
			</div>
			<?php echo fca_qc_do_score_panel( $post_id, $quiz_text_strings ) ?> 
			
			<div class='fca_qc_quiz_footer' style='display: none;'>
				<span class='fca_qc_question_count'></span>		
			</div>
			<?php if ( $draw_optins && function_exists('fca_qc_do_optin_panel') ) {
					echo fca_qc_do_optin_panel( $optin_settings, $quiz_text_strings );
				}?>
			
			<?php echo fca_qc_do_your_answers_panel( $quiz_text_strings ) ?> 
			
			<?php if ( $restart_button ) {
				$button_text = fca_qc_kses_html( $quiz_text_strings[ 'retake_quiz' ] );
				echo "<button type='button' class='fca_qc_button' id='fca_qc_restart_button' style='display: none;'>$button_text</button>";
				
			}?>

			
		</div>
		<?php
		
		return ob_get_clean();
	} else {
		return '<p>Quiz Cat: ' . esc_attr__('No Quiz found', 'quiz-cat') . '</p>';
	}
}
add_shortcode( 'quiz-cat', 'fca_qc_do_quiz' );

function fca_qc_maybe_add_custom_styles( $post_id ) {
	
	$quiz_appearance = get_post_meta ( $post_id, 'quiz_cat_appearance', true );
	
	// QUIZ
	$font_color = empty( $quiz_appearance['font_color'] ) ? '#151515' : $quiz_appearance['font_color'];
	$border_thickness = empty( $quiz_appearance['border_thickness'] ) ? '0' : $quiz_appearance['border_thickness'];
	$border_color = empty( $quiz_appearance['border_color'] ) ? '#151515' : $quiz_appearance['border_color'];
	$border_radius = empty( $quiz_appearance['border_radius'] ) ? '0px' : $quiz_appearance['border_radius'];

	// START BUTTON
	$button_color = empty( $quiz_appearance['button_color'] ) ? '#58afa2' : $quiz_appearance['button_color'];
	$button_hover_color = empty( $quiz_appearance['button_hover_color'] ) ? '#3c7d73' : $quiz_appearance['button_hover_color'];
	$button_font_color = empty( $quiz_appearance['button_font_color'] ) ? '#FFFFFF' : $quiz_appearance['button_font_color'];
	$button_border_color = empty( $quiz_appearance['button_border_color'] ) ? '#3c7d73' : $quiz_appearance['button_border_color'];

	// NEXT BUTTON
	$next_button_font_color = empty( $quiz_appearance['next_button_font_color'] ) ? '#151515' : $quiz_appearance['next_button_font_color'];
	$next_button_hover_color = empty( $quiz_appearance['next_button_hover_color'] ) ? '#FFFFFF' : $quiz_appearance['next_button_hover_color'];
	$next_button_border_color = empty( $quiz_appearance['next_button_border_color'] ) ? '#151515' : $quiz_appearance['next_button_border_color'];

	// ANSWER
	$answer_font_color = empty( $quiz_appearance['answer_font_color'] ) ? '#ffffff' : $quiz_appearance['answer_font_color'];
	$answer_hover_color = empty( $quiz_appearance['answer_hover_color'] ) ? '#6868ac' : $quiz_appearance['answer_hover_color'];
	$answer_background_color = empty( $quiz_appearance['answer_background_color'] ) ? '#6d6d6d' : $quiz_appearance['answer_background_color'];
	$answer_border_thickness = empty( $quiz_appearance['answer_border_thickness'] ) ? '0' : $quiz_appearance['answer_border_thickness'];
	$answer_border_color = empty( $quiz_appearance['answer_border_color'] ) ? '#6d6d6d' : $quiz_appearance['answer_border_color'];

	// RIGHT/WRONG
	$rw_font_color = empty( $quiz_appearance['rw_font_color'] ) ? '#151515' : $quiz_appearance['rw_font_color'];
	$right_background_color = empty( $quiz_appearance['right_background_color'] ) ? '#abdc8c' : $quiz_appearance['right_background_color'];
	$wrong_background_color = empty( $quiz_appearance['wrong_background_color'] ) ? '#f57484' : $quiz_appearance['wrong_background_color'];

	// CUSTOM CSS
	$custom_css = empty( $quiz_appearance['custom_css'] ) ? '' : $quiz_appearance['custom_css'];

	ob_start(); ?>	

		<style>
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz p:not( .fca_qc_back_response ):not( #fca_qc_question_right_or_wrong ):not( .fca_qc_question_response_correct_answer ):not( .fca_qc_question_response_response ):not( .fca_qc_question_response_hint ):not( .fca_qc_question_response_item p ),
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz a:not( .fca_qc_share_link ),
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div:not( .correct-answer ):not( .wrong-answer ){
				color: <?php echo esc_attr( $font_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca-qc-back.correct-answer,
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca_qc_question_response_item.correct-answer {
				background-color: <?php echo esc_attr( $right_background_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca-qc-back.wrong-answer,
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca_qc_question_response_item.wrong-answer {
				background-color: <?php echo esc_attr( $wrong_background_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca_qc_question_response_item p {
				color: <?php echo esc_attr( $rw_font_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz{
				border: <?php echo $border_color . ' ' . $border_thickness . 'px solid' ?>;
				border-radius: <?php echo esc_attr( $border_radius ) . 'px' ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz button.fca_qc_next_question {
				color: <?php echo esc_attr( $next_button_font_color ) ?>;
				border: <?php echo esc_attr( $next_button_border_color ) . ' 2px solid' ?>;
				background-color:  transparent;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz button.fca_qc_next_question:hover {
				background-color: <?php echo esc_attr( $next_button_hover_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz button.fca_qc_button {
				background-color: <?php echo esc_attr( $button_color ) ?>;
				box-shadow: 0 2px 0 0 <?php echo esc_attr( $button_border_color ) ?>;
				color: <?php echo esc_attr( $button_font_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz button.fca_qc_button:hover {
				background-color: <?php echo esc_attr( $button_hover_color ) ?>;
			}
			
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca_qc_answer_div {
				background-color: <?php echo esc_attr( $answer_background_color ) ?>;
				border: <?php echo esc_attr( $answer_border_color ) . ' ' . esc_attr( $answer_border_thickness ) . 'px solid' ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca_qc_answer_div.fakehover,
			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz div.fca_qc_answer_div:active {
				background-color: <?php echo esc_attr( $answer_hover_color ) ?>;
			}

			<?php echo "#fca_qc_quiz_$post_id" ?>.fca_qc_quiz span.fca_qc_answer_span {
				color: <?php echo esc_attr( $answer_font_color ) ?>;
			}

			<?php echo esc_attr( $custom_css ) ?>
		</style>
	<?php
	return ob_get_clean();

}

//SET UP THE MAIN QUIZ TEXTS FOR A QUIZ - CHECK FOR LOCALIZED STRINGS, THEN ANY PHP FILTERS, THEN SHORTCODES
function fca_qc_set_quiz_text_strings( $post_id ) {
	
	global $global_quiz_text_strings;

	// Check for custom translations from the editor
	$translations = get_post_meta ( $post_id, 'quiz_cat_translations', true );
	$text_strings = empty( $translations ) ? $global_quiz_text_strings : $translations;
	
	$quiz_text_strings = apply_filters( 'fca_qc_quiz_text', $text_strings );

	// Then check for shortcode strings which overwrite translations
	$shortcode_text_strings = array (

		'no_quiz_found' => empty( $atts['no_quiz_found'] ) ? false : $atts['no_quiz_found'],
		'time_taken' => empty( $atts['time_taken'] ) ? false : $atts['time_taken'],
		'timedout' => empty( $atts['timedout'] ) ? false : $atts['timedout'],
		'correct' => empty( $atts['correct'] ) ? false : $atts['correct'],
		'wrong' => empty( $atts['wrong'] ) ? false : $atts['wrong'],
		'your_answer' => empty( $atts['your_answer'] ) ? false : $atts['your_answer'],
		'correct_answer' => empty( $atts['correct_answer'] ) ? false : $atts['correct_answer'],
		'question' => empty( $atts['question'] ) ? false : $atts['question'],
		'next' =>  empty( $atts['next'] ) ? false : $atts['next'],
		'you_got' =>  empty( $atts['you_got'] ) ? false : $atts['you_got'],
		'out_of' => empty( $atts['out_of'] ) ? false : $atts['out_of'],
		'your_answers' => empty( $atts['your_answers'] ) ? false : $atts['your_answers'],
		'start_quiz' => empty( $atts['start_quiz'] ) ? false : $atts['start_quiz'],
		'retake_quiz' => empty( $atts['retake_quiz'] ) ? false : $atts['retake_quiz'],
		'share_results' => empty( $atts['share_results'] ) ? false : $atts['share_results'],
		'i_got' => empty( $atts['i_got'] ) ? false : $atts['i_got'],
		'skip_this_step' => empty( $atts['skip_this_step'] ) ? false : $atts['skip_this_step'],
		'your_name' => empty( $atts['your_name'] ) ? false : $atts['your_name'],
		'your_email' => empty( $atts['your_email'] ) ? false : $atts['your_email'],
		'share'  => empty( $atts['share'] ) ? false : $atts['share'],
		'tweet'  => empty( $atts['tweet'] ) ? false : $atts['tweet'],
		'pin'  => empty( $atts['pin'] ) ? false : $atts['pin'],
		'email'  =>  empty( $atts['email'] ) ? false : $atts['email'], 
	
	);
	
	forEach ( $quiz_text_strings as $key => $value ) {
		if ( !empty ( $shortcode_text_strings[$key] ) && $shortcode_text_strings[$key] !== false ) {
			$quiz_text_strings[$key] = $shortcode_text_strings[$key];
		}
	}

	return $quiz_text_strings;
	
}

function fca_qc_do_question_panel( $post_id, $quiz_text_strings ) {
	
	$max_questions = 4;
	
	$questions = get_post_meta ( $post_id, 'quiz_cat_questions', true );
	
	forEach ( $questions as $question ) {
		if ( count ( $question['answers'] ) > $max_questions ) {
			$max_questions = count ( $question['answers'] );
		}
	}

	$html = "<div class='fca-qc-front' id='fca_qc_answer_container'>";
		$html .= "<p id='fca_qc_question'>" . fca_qc_kses_html( $quiz_text_strings['question'] ) . "</p>";
		$html .= "<img class='fca_qc_quiz_question_img' src=''>";
		for ( $i = 1; $i <= $max_questions; $i++ ) {

			$html .= "<div class='fca_qc_answer_div' data-question='$i'>";
			$html .= "<img class='fca_qc_quiz_answer_img' src=''>";
			$html .= "<span class='fca_qc_answer_span'></span></div>";

		}
		
	$html .= "</div>";
	
	return $html;

}

function fca_qc_do_answer_panel( $quiz_text_strings, $post_id ) {
	
	$quiz_appearance = get_post_meta ( $post_id, 'quiz_cat_appearance', true );
	
	if ( !empty( $quiz_appearance ) ) {

		$rw_font_color = empty( $quiz_appearance['rw_font_color'] ) ? '#151515' : $quiz_appearance['rw_font_color'];
		$button_hover_color = empty( $quiz_appearance['button_hover_color'] ) ? '#000' : $quiz_appearance['button_hover_color'];

	} else {

		$rw_font_color = $button_hover_color = '#000';

	}

	$html = "<div class='fca-qc-back' id='fca_qc_back_container'>";
		$html .= "<p style='color: " . $rw_font_color . "' id='fca_qc_question_right_or_wrong'></p>";
		$html .= "<img class='fca_qc_quiz_question_img' src=''>";
		$html .= "<span style='color: " . $rw_font_color . "' id='fca_qc_question_back'></span>";
		$html .= "<p style='color: " . $rw_font_color . "' id='fca_qc_back_response_p' class='fca_qc_back_response'>" . $quiz_text_strings['your_answer'] . " <span id='fca_qc_your_answer'></span></p>";
		$html .= "<p style='color: " . $rw_font_color . "' id='fca_qc_correct_answer_p' class='fca_qc_back_response'>" . $quiz_text_strings['correct_answer'] . " <span id='fca_qc_correct_answer'></span></p>";
		$html .= "<p style='color: " . $rw_font_color . "' id='fca_qc_hint_p' class='fca_qc_back_response'></p>";
		$html .= "<button type='button' class='fca_qc_next_question'>" . $quiz_text_strings['next'] . "</button>";
	$html .= "</div>";
	
	return $html;

}

function fca_qc_do_score_panel( $post_id, $quiz_text_strings ) {
	
	$html = "<div class='fca_qc_score_container' style='display:none;'>";
		$html .= "<div class='fca_qc_score_text'>" . $quiz_text_strings['you_got'] . " {{SCORE_CORRECT}} " . $quiz_text_strings['out_of'] . " {{SCORE_TOTAL}} </div>";
		$html .= "<div class='fca_qc_score_time'></div>";
		$html .= "<div class='fca_qc_score_title'></div>";
		$html .= "<img class='fca_qc_score_img' src=''>";
		$html .= "<div class='fca_qc_score_desc'></div>";			
	$html .= "</div>";
	
	return apply_filters ( 'fca_qc_result_filter', $html, $post_id, $quiz_text_strings );

}

function fca_qc_do_your_answers_panel( $quiz_text_strings ) {
	
	$html = "<div class='fca_qc_your_answer_container' style='display:none;'>";
		$html .= "<p class='fca_qc_your_answers_text'>" . $quiz_text_strings['your_answers'] . "</p>";
		//THIS IS WHERE EACH RESPONSE WILL BE INSERTED
		$html .= "<div class='fca_qc_insert_response_above'></div>";
	$html .= "</div>";
	
	return $html;

}