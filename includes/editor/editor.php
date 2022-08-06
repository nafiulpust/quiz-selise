<?php

////////////////////////////
// EDITOR PAGE 
////////////////////////////

//ENQUEUE ANY SCRIPTS OR CSS FOR OUR ADMIN PAGE EDITOR
function fca_qc_admin_enqueue( $hook ) {
	global $post;

	if ( ($hook == 'post-new.php' || $hook == 'post.php')  &&  $post->post_type === 'fca_qc_quiz' ) {
		wp_enqueue_media();
		wp_enqueue_style('dashicons');
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tooltip');

		wp_enqueue_script('fca_qc_wysi_js_main', FCA_QC_PLUGINS_URL . '/includes/wysi/wysihtml.min.js', array(), FCA_QC_PLUGIN_VER, true );
		wp_enqueue_style('fca_qc_wysi_css', FCA_QC_PLUGINS_URL . '/includes/wysi/wysi.min.css', array(), FCA_QC_PLUGIN_VER );
		wp_enqueue_script('fca_qc_wysi_js', FCA_QC_PLUGINS_URL . '/includes/wysi/wysi.min.js', array( 'jquery', 'fca_qc_wysi_js_main' ), FCA_QC_PLUGIN_VER, true );

		$editor_dependencies = array( 'fca_qc_wysi_js_main', 'fca_qc_wysi_js', 'jquery','jquery-ui-core', 'jquery-ui-tooltip');

		if ( FCA_QC_DEBUG ) {
			wp_enqueue_script('fca_qc_admin_js', FCA_QC_PLUGINS_URL . '/includes/editor/editor.js', $editor_dependencies, FCA_QC_PLUGIN_VER, true );
			wp_enqueue_style( 'fca_qc_admin_stylesheet', FCA_QC_PLUGINS_URL . '/includes/editor/editor.css', array(), FCA_QC_PLUGIN_VER );

		} else {
			wp_enqueue_script('fca_qc_admin_js', FCA_QC_PLUGINS_URL . '/includes/editor/editor.min.js', $editor_dependencies, FCA_QC_PLUGIN_VER, true );
			wp_enqueue_style( 'fca_qc_admin_stylesheet', FCA_QC_PLUGINS_URL . '/includes/editor/editor.min.css', array(), FCA_QC_PLUGIN_VER );
		}


		$admin_data = array (

			'debug' => FCA_QC_DEBUG,
			'isNewQuiz' => $hook == 'post-new.php',
			//A TEMPLATE DIV OF THE QUESTION AND RESULT DIVS, SO WE CAN ADD MORE OF THEM VIA JAVASCRIPT
			'questionDiv' => 	fca_qc_render_question(),
			'resultDiv' 	=> 	fca_qc_render_result(),
			'answerDiv' 	=> 	fca_qc_render_answer(),
			'correctAnswerDiv' 	=> 	fca_qc_render_answer( true ),

			//SOME LOCALIZATION STRINGS FOR JAVASCRIPT STUFF
			'sureWarning_string' => esc_attr__( 'Are you sure?', 'quiz-cat'),
			'selectImage_string' => esc_attr__('Select Image', 'quiz-cat' ),

			'unused_string' =>  esc_attr__('Unused', 'quiz-cat') . ':',
			'points_string' =>  esc_attr__('Points', 'quiz-cat'),
			'question_string' =>  esc_attr__('Question', 'quiz-cat'),
			'save_string' =>  esc_attr__('Save', 'quiz-cat'),
			'preview_string' =>  esc_attr__('Save & Preview', 'quiz-cat'),
			'on_string' =>  esc_attr__('YES', 'quiz-cat'),
			'off_string' =>  esc_attr__('NO', 'quiz-cat'),



			'stylesheet' => FCA_QC_PLUGINS_URL . '/includes/wysi/wysi.min.css',
			'code_editor' => wp_enqueue_code_editor( [ 'type' => 'text/css', 'codemirror' => [ 'autoRefresh' => true, 'lineWrapping' => true ] ] ),
			'add_new_link' => admin_url( 'edit.php' ) . "?post_type=fca_qc_quiz&page=fca-qc-list&add_new=1",
		);

		wp_localize_script( 'fca_qc_admin_js', 'fcaQcAdminData', $admin_data );
		wp_localize_script( 'fca_qc_wysi_js', 'fcaQcAdminData', $admin_data );
	}

}
add_action( 'admin_enqueue_scripts', 'fca_qc_admin_enqueue', 10, 1 );

function fca_qc_admin_nav() {
	global $post;
	if ( $post->post_type === 'fca_qc_quiz'	 ) {
		ob_start();
		?>
<div id="qc-nav">
	<h1 class="nav-tab-wrapper">
		<a href="#" id="general-nav" class="nav-tab fca-qc-color1 nav-tab-active"><?php esc_attr_e('1. Setup', 'quiz-cat') ?></a>
		<a href="#" id="questions-nav" class="nav-tab fca-qc-color3"><?php esc_attr_e('2. Questions', 'quiz-cat') ?></a>
		<a href="#" id="results-nav" class="nav-tab fca-qc-color2"><?php esc_attr_e('3. Results', 'quiz-cat') ?></a>
		<?php if ( function_exists ('fca_qc_save_appearance_settings') ) { ?>
			<a href="#" id="appearance-nav" class="nav-tab"><?php esc_attr_e('Appearance', 'quiz-cat') ?></a>
		<?php } ?>
		<a href="#" id="translations-nav" class="nav-tab"><?php esc_attr_e('Default Text', 'quiz-cat') ?></a>
	</h1>
</div>
<?php
		echo ob_get_clean();
	}
}
add_action( 'edit_form_after_title', 'fca_qc_admin_nav' );


//ADD META BOXES TO EDIT CPT PAGE
function fca_qc_add_custom_meta_boxes( $post ) {

	add_meta_box(
		'fca_qc_description_meta_box',
		esc_attr__( 'Quiz Setup', 'quiz-cat' ),
		'fca_qc_render_setup_meta_box',
		null,
		'normal',
		'high'
	);

	add_meta_box(
		'fca_qc_questions_meta_box',
		esc_attr__( 'Quiz Questions', 'quiz-cat' ),
		'fca_qc_render_questions_meta_box',
		null,
		'normal',
		'high'
	);

	add_meta_box(
		'fca_qc_question_settings_meta_box',
		esc_attr__( 'Question Settings', 'quiz-cat' ),
		'fca_qc_render_question_settings_meta_box',
		null,
		'normal',
		'default'
	);

	add_meta_box(
		'fca_qc_results_meta_box',
		esc_attr__( 'Quiz Results', 'quiz-cat' ),
		'fca_qc_render_results_meta_box',
		null,
		'normal',
		'high'
	);

	add_meta_box(
		'fca_qc_quiz_settings_meta_box',
		esc_attr__( 'Quiz Settings', 'quiz-cat' ),
		'fca_qc_render_quiz_settings_meta_box',
		null,
		'normal',
		'default'
	);

	add_meta_box(
		'fca_qc_translations_meta_box',
		esc_attr__( 'Quiz Text', 'quiz-cat' ),
		'fca_qc_render_translations_metabox',
		null,
		'normal',
		'default'
	);

}
add_action( 'add_meta_boxes_fca_qc_quiz', 'fca_qc_add_custom_meta_boxes' );

//RENDER THE DESCRIPTION META BOX
function fca_qc_render_setup_meta_box( $post ) {

	$settings = get_post_meta ( $post->ID, 'quiz_cat_settings', true );

	$title = get_the_title ( $post->ID );
	$quiz_meta = get_post_meta ( $post->ID, 'quiz_cat_meta', true );
	$quiz_meta = empty( $quiz_meta ) ? array() : $quiz_meta;
	$quiz_meta['desc'] = empty ( $quiz_meta['desc'] ) ? '' : $quiz_meta['desc'];
	$quiz_meta['desc_img_src'] = empty ( $quiz_meta['desc_img_src'] ) ? '' : $quiz_meta['desc_img_src'];
	$quiz_type = empty( $settings['quiz_type'] ) ? sanitize_text_field( $_GET['quiz_type'] ) : $settings['quiz_type'];

	if ( FCA_QC_PLUGIN_PACKAGE !== 'Free' ){
		fca_qc_add_premium_assets( $post, $quiz_type );
	}
	//ADD A HIDDEN PREVIEW URL INPUT
	ob_start(); ?>
	<?php echo fca_qc_input( 'quiz_preview_url', '', get_permalink( $post ), 'hidden', "id='fca_qc_quiz_preview_url'" ) ?>
	<?php echo fca_qc_input( 'quiz_type', '', $quiz_type, 'hidden', "id='fca_qc_quiz_type'" ) ?>
	<table class='fca_qc_inner_setting_table'>
		<tr>
			<th><?php esc_html_e('Title', 'quiz-cat') ?></th>
			<td><?php echo fca_qc_input( 'quiz_title', '', $title ) ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e('Description', 'quiz-cat') ?></th>
			<td>
				<?php echo fca_qc_add_wysiwyg( $quiz_meta['desc'], 'fca_qc_quiz_description' ) ?>
				<?php echo fca_qc_add_image_input( $quiz_meta['desc_img_src'], 'quiz_description_image_src', 'fca_qc_quiz_description_image_src' ) ?>
			</td>
		</tr>
	</table>
	<?php

	echo ob_get_clean();
}

//RENDER THE ADD QUESTION META BOX
function fca_qc_render_questions_meta_box( $post ) {

	$questions = get_post_meta ( $post->ID, 'quiz_cat_questions', true );

	echo fca_qc_render_question_modal();
	echo fca_qc_input( 'questions_json', '', json_encode( $questions ), 'hidden', "id='fca_qc_questions_json'" );

	ob_start(); ?>
<p class='fca_qc_quiz_instructions'><?php esc_attr_e( 'Add your questions to ask and the possible responses. Drag to re-order.', 'quiz-cat' )?></p>
<div class='fca_qc_sortable_questions'>
<?php if ( empty ( $questions ) ) {
	echo fca_qc_render_question();
} else {
	forEach ( $questions as $question ) {
		echo fca_qc_render_question( $question );
	}
} ?>
</div>
<button type='button' title='<?php esc_attr_e( 'Add a new Question', 'quiz-cat' ) ?>' class='fca_qc_add_question_btn button-secondary fca_qc_add_btn' >
<span class='dashicons dashicons-plus' style='vertical-align: text-top;'></span><?php esc_attr_e( 'New Question', 'quiz-cat' ) ?></button>
<?php
	echo ob_get_clean();

}

//RENDER THE ADD QUESTION META BOX
function fca_qc_render_question_settings_meta_box( $post ) {

	$settings = get_post_meta ( $post->ID, 'quiz_cat_settings', true );
	$show_explanations = empty ( $settings['explanations'] ) ? '' : true;
	$shuffle_questions = empty ( $settings['shuffle_questions'] ) ? '' : true;
	$hide_answers = empty ( $settings['hide_answers'] ) ? '' : true;

	ob_start(); ?>
	<table class='fca_qc_setting_table' >

	<?php
	if ( function_exists ('fca_qc_save_quiz_settings_premium' ) ) { ?>
		<?php fca_qc_answer_mode_toggle( $settings ) ?>
		<tr id='fca_qc_hints_toggle_tr'>
			<th>
				<label class='fca_qc_admin_label' for='fca_qc_explanations'><?php echo esc_attr__('Enable Explanations', 'quiz-cat') . fca_qc_tooltip( __('Show an explanation or reasoning why an answer is correct. This adds a new input on each question', 'quiz-cat') ) ?></label>
			</th>
			<td>
				<div class='onoffswitch'>
					<input type='checkbox' class='onoffswitch-checkbox' id='fca_qc_explanations' style='display:none;' name='fca_qc_explanations' <?php checked( $show_explanations ) ?> ></input>
					<label class='onoffswitch-label' for='fca_qc_explanations'><span class='onoffswitch-inner'><span class='onoffswitch-switch'></span></span></label>
				</div>
			</td>
		</tr>
		<tr>
			<th>
				<label class='fca_qc_admin_label' for='fca_qc_shuffle_question_order'><?php echo esc_attr__('Shuffle Question Order', 'quiz-cat') . fca_qc_tooltip( __( 'Shuffle or randomize the order of questions each time someone takes your quiz.','quiz-cat') ) ?></label>
			</th>
			<td>
				<div class='onoffswitch'>
					<input type='checkbox' class='onoffswitch-checkbox' id='fca_qc_shuffle_question_order' style='display:none;' name='fca_qc_shuffle_question_order' <?php checked( $shuffle_questions ) ?> ></input>
				<label class='onoffswitch-label' for='fca_qc_shuffle_question_order'><span class='onoffswitch-inner'><span class='onoffswitch-switch'></span></span></label>
				</div>
			</td>
		</tr>
	<?php } else { ?>
		<tr>
			<th>
				<label class='fca_qc_admin_label' for='fca_qc_hide_answers_until_end'><?php esc_attr_e('Hide Answers Until End of Quiz', 'quiz-cat') ?></label>
			</th>
			<td>
			<div class='onoffswitch'>
				<input type='checkbox' class='onoffswitch-checkbox' id='fca_qc_hide_answers_until_end' style='display:none;' name='fca_qc_hide_answers_until_end' <?php checked( $hide_answers ) ?> ></input>
				<label class='onoffswitch-label' for='fca_qc_hide_answers_until_end'><span class='onoffswitch-inner'><span class='onoffswitch-switch'></span></span></label>
			</div>
			<td>
		</tr>
	<?php } ?>
	</table>
	<?php
	echo ob_get_clean();

}

// RENDER A QUESTION META BOX
// INPUT: ARRAY->$question
// OUTPUT: HTML 
function fca_qc_render_question( $question = array() ) {

	if ( empty ( $question ) ) {
		$question = array(
			'question' => '',
			'correct' => '',
			'img' => '',
			'hint' => '',
			'answers' => array(
				array(
					'answer' => '',
					'img' => '',
					'hint' => '',
				),
				array(
					'answer' => '',
					'img' => '',
					'hint' => '',
				),
			),
			'id' => '{{ID}}',
		);
	}

	$question['id'] = empty( $question['id'] ) ? '{{ID}}' : $question['id'];
	ob_start();
	?>
	<div class='fca_qc_question_item fca_qc_deletable_item' data-question_id='<?php echo esc_attr( $question['id'] ) ?>' data-question='<?php echo esc_attr( json_encode( $question ) ) ?>' >
		<?php echo fca_qc_add_delete_button() ?>

		<p class='fca_qc_question_label'>
			<span class='fca_qc_quiz_heading_question_number'><?php esc_attr_e('Question', 'quiz-cat')?></span>
			<span class='fca_qc_quiz_heading_text'><?php esc_html_e( $question['question'] ) ?></span>
		</p>
	</div>
	<?php
	return ob_get_clean();

}

function fca_qc_render_question_modal() {
	$copy_svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 16 16"><g transform="translate(0, 0)"><path fill="#444444" d="M11,12H1c-0.553,0-1-0.447-1-1V1c0-0.552,0.447-1,1-1h10c0.553,0,1,0.448,1,1v10C12,11.553,11.553,12,11,12z "></path> <path data-color="color-2" fill="#444444" d="M15,16H4v-2h10V4h2v11C16,15.553,15.553,16,15,16z"></path></g></svg>';

	ob_start();	?>
<div class='fca-qc-modal' id='fca-qc-question-modal' >
	<div class='fca-qc-modal-inner'>
		<div class="fca-qc-modal-controls">
			<span title="<?php esc_attr_e( "Copy Question", 'quiz-cat' ) ?>" class="fca_qc_copy_icon fca_qc_copy_question"><?php echo $copy_svg ?></span>
			<span title="<?php esc_attr_e( "Previous Question", 'quiz-cat' ) ?>" class="dashicons dashicons-arrow-left-alt2"></span>
			<span title="<?php esc_attr_e( "Next Question", 'quiz-cat' ) ?>"class="dashicons dashicons-arrow-right-alt2"></span>
			<span title="<?php esc_attr_e( "Save & Close", 'quiz-cat' ) ?>"class="dashicons dashicons-no-alt"></span>
		</div>
		<h2><?php esc_attr_e( 'Question', 'quiz-cat' )?> <span id='fca-qc-question-number'></span></h2>
		<?php echo fca_qc_input( 'question_id', '', '', 'hidden', "id='fca-qc-question-id'" ) ?>
		<table class='fca_qc_inner_setting_table'>
			<tr>
				<th><?php esc_attr_e('Question', 'quiz-cat') ?></th>
				<td><?php echo fca_qc_input( 'question_text', esc_attr__( 'e.g. Can cats fly?', 'quiz-cat' ), '', 'textarea', 'id="fca-qc-question-text"' ) . fca_qc_add_image_input( '', '', 'fca_qc_quiz_question_image' ) ?>
				</td>
			</tr>
		</table>
		<button type='button' title='<?= esc_attr_e( 'New Answer', 'quiz-cat') ?>' class='button-secondary fca_qc_add_btn fca_qc_add_answer_btn' >
		<span class='dashicons dashicons-plus' style='vertical-align: text-top;'></span><?= esc_attr_e('New Answer', 'quiz-cat') ?></button>


    </div>
</div><?php
	return ob_get_clean();

}

function fca_qc_render_answer( $correct_answer = false ) {
	ob_start();

	if( $correct_answer ) {	?>
	<div class='fca_qc_answer_input_div'>
		<?php echo fca_qc_input( 'answer_id', '', '{{answer_id}}', 'hidden' ) ?>
		<table class='fca_qc_inner_setting_table'>
			<tr>
				<th class='fca_qc_answer_header'><?php esc_attr_e( 'Correct Answer', 'quiz-cat' ) ?></th>
				<td>
					<?php echo fca_qc_input( '', esc_attr__( 'e.g. No', 'quiz-cat' ), '{{answer_text}}', 'textarea', 'id="fca-qc-answer-text"' ) ?>
				</td>
			</tr>
			<?php if ( function_exists( 'fca_qc_save_quiz_settings_premium' ) ) { ?>
			<tr class='fca_qc_explanations_tr'>
				<th><?php esc_attr_e( 'Explanation', 'quiz-cat' ) ?></th>
				<td><?php echo fca_qc_input( '', esc_attr__('Explanation', 'quiz-cat'), '{{hint}}', 'textarea', 'id="fca-qc-hint-text"' ) ?></td>
			</tr>
			<tr>
				<th></th>
				<td><?php echo fca_qc_add_image_input( '', 'answer_image' ) ?></td>
			</tr>
			<?php } ?>
		</table>
	</div>
	<?php } else { ?>
	<div class='fca_qc_answer_input_div fca_qc_deletable_item'>
		<?php echo fca_qc_input( 'answer_id', '', '', 'hidden' ) ?>
		<table class='fca_qc_inner_setting_table'>
			<tr>
				<th class='fca_qc_answer_header'><?php esc_attr_e( 'Wrong Answer', 'quiz-cat' ) ?><?php echo fca_qc_add_delete_button() ?></th>
				<td><?php echo fca_qc_input( '', esc_attr__( 'e.g. Yes', 'quiz-cat' ), '{{answer_text}}', 'textarea', 'id="fca-qc-answer-text"' ) ?>
				<?php if ( function_exists( 'fca_qc_save_quiz_settings_premium' ) ) {
					echo fca_qc_add_image_input( '', 'answer_image' );
				}?>
				</td>
			</tr>
		</table>
	</div>
	<?php
	}
	return ob_get_clean();
}

function fca_qc_render_results_meta_box( $post ) {

	$results = get_post_meta ( $post->ID, 'quiz_cat_results', true );
	$settings = get_post_meta ( $post->ID, 'quiz_cat_settings', true );
	$result_mode = empty ( $settings['result_mode'] ) ? 'basic' : $settings['result_mode'];

	echo fca_qc_render_result_modal();
	echo fca_qc_input( 'results_json', '', json_encode( $results ), 'hidden', "id='fca_qc_results_json'" );

	ob_start(); ?>
<p class='fca_qc_quiz_instructions'><?php esc_attr_e('Add your results based on the number of correct answers. This is optional. Drag to re-order.', 'quiz-cat') ?></p>
<?php if( function_exists( 'fca_qc_save_quiz_settings_premium' ) ) { ?>
<table class='fca_qc_setting_table'>
	<tr>
		<th>
			<label class='fca_qc_admin_label' for='fca_qc_result_mode'><?php echo esc_attr__('Result Mode', 'quiz-cat') . fca_qc_tooltip( __('Choose to show a results screen at the end of the quiz, or redirect to a new page when a user completes the quiz.', 'quiz-cat') ) ?></label>
		</th>
		<td>
			<div class='radio-toggle'>
				<label class='<?php echo $result_mode === 'basic' ? 'selected' : '' ?>'><?php esc_attr_e('Results Screen', 'quiz-cat') ?>
					<input class="qc_radio_input fca_qc_result_mode_input" name="fca_qc_result_mode" type="radio" value="basic" <?php checked( $result_mode, 'basic' ) ?> />
				</label><label class='<?php echo $result_mode === 'redirect' ? 'selected' : '' ?>'><?php esc_attr_e('Redirect to URL', 'quiz-cat') ?>
					<input class="qc_radio_input fca_qc_result_mode_input" name="fca_qc_result_mode" type="radio" value="redirect" <?php checked( $result_mode, 'redirect' ) ?> />
				</label>
			</div>
		</td>
	</tr>
</table>
	<?php }?>
	<div class='fca_qc_sortable_results'>
<?php if ( empty ( $results ) ) {
	echo fca_qc_render_result();
} else {
	forEach ( $results as $result ) {
		echo fca_qc_render_result( $result );
	}
} ?>
</div>
<button type='button' title='<?= esc_attr_e( 'New Result', 'quiz-cat') ?>' class='button-secondary fca_qc_add_btn fca_qc_add_result_btn' >
<span class='dashicons dashicons-plus' style='vertical-align: text-top;'></span><?= esc_attr_e('New Result', 'quiz-cat') ?></button>
<?php
	echo ob_get_clean();
}


function fca_qc_render_result_modal() {
	$copy_svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 16 16"><g transform="translate(0, 0)"><path fill="#444444" d="M11,12H1c-0.553,0-1-0.447-1-1V1c0-0.552,0.447-1,1-1h10c0.553,0,1,0.448,1,1v10C12,11.553,11.553,12,11,12z "></path> <path data-color="color-2" fill="#444444" d="M15,16H4v-2h10V4h2v11C16,15.553,15.553,16,15,16z"></path></g></svg>';

	ob_start();	?>
<div class='fca-qc-modal' id='fca-qc-result-modal' >
	<div class='fca-qc-modal-inner'>
		<div class="fca-qc-modal-controls">
			<span title="<?php esc_attr_e( "Copy Result", 'quiz-cat' ) ?>" class="fca_qc_copy_icon fca_qc_copy_result"><?php echo $copy_svg ?></span>
			<span title="<?php esc_attr_e( "Previous Result", 'quiz-cat' ) ?>" class="dashicons dashicons-arrow-left-alt2"></span>
			<span title="<?php esc_attr_e( "Next Result", 'quiz-cat' ) ?>" class="dashicons dashicons-arrow-right-alt2"></span>
			<span title="<?php esc_attr_e( "Save & Close", 'quiz-cat' ) ?>" class="dashicons dashicons-no-alt"></span>
		</div>
		<h2><?php esc_attr_e( 'Result', 'quiz-cat' )?> <span id='fca-qc-result-number'></span></h2>
		<?php echo fca_qc_input( 'result_id', '', '', 'hidden', "id='fca-qc-result-id'" ) ?>
		<table class='fca_qc_inner_setting_table'>

			<tr>
				<th><?php esc_attr_e('Title', 'quiz-cat') ?></th>
				<td><?php echo fca_qc_input( 'result_title', esc_attr__( 'e.g. Grumpy Cat', 'quiz-cat' ), '', 'text', 'id="fca-qc-result-title"' ) ?></td>
			</tr>
			<tr class='fca_qc_result_row_default' >
				<th><?php esc_attr_e('Description', 'quiz-cat') ?></th>
				<td><?php echo fca_qc_add_wysiwyg( '', 'result_description' ) ?>
					<?php echo fca_qc_add_image_input( '', '', 'fca_qc_quiz_result_image' ) ?>
				</td>
			</tr>
			<tr class='fca_qc_result_row_minmax' >
				<th><?php esc_attr_e('Minimum Score', 'quiz-cat') ?></th>
				<td><?php echo fca_qc_input( 'result_min', '', '', 'number', 'readonly' ) ?></td>
			</tr>
			<tr class='fca_qc_result_row_minmax' >
				<th><?php esc_attr_e('Maximum Score', 'quiz-cat') ?></th>
				<td><?php echo fca_qc_input( 'result_max', '', '', 'number', 'readonly' ) ?></td>
			</tr>
			<?php if ( function_exists ( 'fca_qc_save_quiz_settings_premium' ) ) { ?>
					<tr class='fca_qc_result_row_url'>
						<th><?php esc_attr_e( 'Redirect URL', 'quiz-cat' ) ?></th>
						<td><?php echo fca_qc_input( 'result_url', 'http://mycoolsite.com/grumpy-cat', '', 'url', 'id="fca-qc-result-url"' ) ?></td>
					</tr>
			<?php } ?>
			<?php if ( function_exists ( 'fca_qc_add_tag_div' ) ) {
				echo fca_qc_add_tag_div( 'results', '' ); ?>
				<tr class='fca_qc_mailchimp_api_settings'>
					<th>
						<label class='fca_qc_admin_label fca_qc_admin_settings_label' for='fca_qc_quiz_result_mailchimp_groups'><?php echo esc_attr__('Interest Groups', 'quiz-cat') . fca_qc_tooltip( esc_attr__("If you use MailChimp Groups opt-in feature, select one or more interest groups quiz takers should be added to.  Optional.", 'quiz-cat') ) ?></label>
					</th>
					<td>
						<span style='display: none;' class='fca_qc_icon dashicons dashicons-image-rotate fca_qc_spin'></span>
						<select style="width: 300px; border: 1px solid #ddd; border-radius: 0;" data-placeholder="&#8681; <?php echo esc_attr__('Select Interest Groups (Optional)', 'quiz-cat') ?> &#8681;" class="fca_qc_multiselect fca_qc_mailchimp_groups" id="fca_qc_quiz_result_mailchimp_groups" multiple="multiple" name="fca_qc_quiz_result_mailchimp_groups[][]">
						</select>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div><?php
	return ob_get_clean();

}

function fca_qc_add_delete_button() {

	return "<span title='Delete item' class='dashicons dashicons-trash fca_qc_delete_icon'></span>";

}

function fca_qc_render_result( $result = array() ) {

	if ( empty ( $result ) ) {
		$result = array(
			'title' => '',
			'desc' => '',
			'img' => '',
			'url' => '',
			'tags' => array(),
			'id' => '{{ID}}',
			'min' => '',
			'max' => '',
		);
	}

	$result['id'] = empty( $result['id'] ) ? '{{ID}}' : $result['id'];
	ob_start();
	?>
	<div class='fca_qc_result_item fca_qc_deletable_item' data-result_id='<?php echo esc_attr( $result['id'] ) ?>' data-result='<?php echo esc_attr( json_encode( $result ) ) ?>' >
		<?php echo fca_qc_add_delete_button() ?>

		<p class='fca_qc_result_label'>
			<span class='fca_qc_result_score_value'></span>
			<span class='fca_qc_result_score_title'><?php esc_html_e( $result['title'] ) ?></span>
		</p>
	</div>
	<?php
	return ob_get_clean();

}



function fca_qc_render_translations_metabox ( $post ) {

	global $global_quiz_text_strings;
	$translations = get_post_meta ( $post->ID, 'quiz_cat_translations', true );
	$text_strings = empty( $translations ) ? $global_quiz_text_strings : $translations;

	forEach( $global_quiz_text_strings as $key => $value ){
		if( empty( $translations[$key] ) ){
			$text_strings[$key] = $value;
		}
	}

	echo "<table class='fca_qc_setting_table'>";

	$premium_translations = array(
		'timedout',
		'time_taken',
		'retake_quiz',
		'share_results',
		'i_got',
		'skip_this_step',
		'your_name',
		'your_email',
		'share',
		'tweet',
		'pin',
		'email'
	);

	forEach ( $text_strings as $key => $value ) {

		if( empty( $value ) ){
			$value = $global_quiz_text_strings[ $key ];
		}

		if ( FCA_QC_PLUGIN_PACKAGE !== 'Free' || !in_array( $key, $premium_translations ) ){

			echo "<tr class='fca_qc_translation_settings'>";

				echo "<th>";
					echo "<label class='fca_qc_admin_label' for='fca_qc_" . $key . "_translation'>" . esc_attr( $global_quiz_text_strings[$key] ) . "</label>";
				echo "</th>";

				echo "<td>";
					echo fca_qc_input( $key, '', $value, 'translation' );
				echo "</td>";

			echo "</tr>";

		}

	}

	echo "</table>";

}

//RENDER THE QUIZ SETTINGS META BOX 
function fca_qc_render_quiz_settings_meta_box( $post ) {

	$settings = get_post_meta ( $post->ID, 'quiz_cat_settings', true );
	$settings = empty( $settings ) ? array() : $settings;
	$quiz_type = empty( $settings['quiz_type'] ) ? '' : $settings['quiz_type'];
	$shortcode = '[quiz-cat id="' . $post->ID . '"]';

	$restart_button = empty ( $settings['restart_button'] ) ? '' : "checked='checked'";
	$disable_scroll = empty ( $settings['disable_scroll'] ) ? '' : "checked='checked'";
	$autostart_quiz = empty ( $settings['autostart_quiz'] ) ? '' : "checked='checked'";

	ob_start(); ?>
	<table class='fca_qc_setting_table'>
		<tr>
			<th>
				<label class='fca_qc_admin_label' id='fca_qc_shortcode_label' for='fca_qc_shortcode_input'>
				<?php esc_html_e('Shortcode', 'quiz-cat') ?>
				<?php echo fca_qc_tooltip( esc_attr__('Paste the shortcode in to a post or page to embed this quiz.', 'quiz-cat') ) ?>
				</label>
			</th>
			<td><?php echo fca_qc_input( 'shortcode_input', '', $shortcode, 'shortcode', 'readonly' ) ?></td>
		</tr>

		<?php if ( function_exists ('fca_qc_save_quiz_settings_premium' ) ) {
			fca_qc_timer_mode( $settings ); ?>
			<tr>
				<th>
					<label class='fca_qc_admin_label' for='fca_qc_autostart_quiz'><?php echo esc_attr__('Auto Start Quiz', 'quiz-cat') . fca_qc_tooltip( __('Shows the first quiz question immediately, instead of the "Start Quiz" button.', 'quiz-cat' ) ) ?></label>
				</th>
				<td>
					<div class='onoffswitch'>
						<input type='checkbox' class='onoffswitch-checkbox' id='fca_qc_autostart_quiz' style='display:none;' name='fca_qc_autostart_quiz' <?php echo $autostart_quiz ?>></input>
					<label class='onoffswitch-label' for='fca_qc_autostart_quiz'><span class='onoffswitch-inner'><span class='onoffswitch-switch'></span></span></label>
					</div>
				</td>
			</tr>

			<tr>
				<th>
					<label class='fca_qc_admin_label' for='fca_qc_show_restart_button'><?php echo esc_attr__('Show "Restart Quiz" Button', 'quiz-cat') ?></label>
				</th>
				<td>
					<div class='onoffswitch'>
						<input type='checkbox' class='onoffswitch-checkbox' id='fca_qc_show_restart_button' style='display:none;' name='fca_qc_show_restart_button' <?php echo $restart_button ?>></input>
					<label class='onoffswitch-label' for='fca_qc_show_restart_button'><span class='onoffswitch-inner'><span class='onoffswitch-switch'></span></span></label>
					</div>
				</td>
			</tr>
		<?php } ?>

	</table>
<?php
	echo ob_get_clean();
}

//CUSTOM SAVE HOOK
function fca_qc_save_post( $post_id ) {

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		return $post_id;
	}

	//ONLY DO OUR STUFF IF ITS A REAL SAVE, NOT A NEW IMPORTED ONE
	if ( array_key_exists ( 'fca_qc_quiz_preview_url', $_POST ) ) {

		//SAVING META DATA ( DESCRIPTION, IMAGE )
		update_post_meta ( $post_id, 'quiz_cat_meta', array(
			'desc' => empty ( $_POST['fca_qc_quiz_description'] ) ? '' : fca_qc_kses_html( $_POST['fca_qc_quiz_description'] ),
			'desc_img_src' => empty ( $_POST['fca_qc_quiz_description_image_src'] ) ? '' : esc_url( $_POST['fca_qc_quiz_description_image_src'] ),
		) );

		//SAVING QUESTIONS
		update_post_meta( $post_id, 'quiz_cat_questions', fca_qc_kses_html( json_decode( stripslashes_deep( $_POST['fca_qc_questions_json'] ), true ) ) );

		//SAVING RESULTS
		update_post_meta( $post_id, 'quiz_cat_results', fca_qc_kses_html( json_decode( stripslashes_deep( $_POST['fca_qc_results_json'] ), true ) ) );

		fca_qc_save_quiz_translations( $post_id );

		if ( function_exists('fca_qc_save_appearance_settings') ) {
			fca_qc_save_appearance_settings( $post_id );
		}
		if ( function_exists('fca_qc_save_quiz_settings_premium') ) {
			fca_qc_save_quiz_settings_premium( $post_id );
		} else {
			fca_qc_save_quiz_settings( $post_id );
		}

		wp_publish_post( $post_id );

	}
}
add_action( 'save_post_fca_qc_quiz', 'fca_qc_save_post' );

//SAVING SETTINGS
function fca_qc_save_quiz_settings( $post_id ) {

	$settings = array();

	$fields = array (
		'fca_qc_hide_answers_until_end'	=> 'hide_answers',
		'fca_qc_result_mode'			=> 'result_mode',
		'fca_qc_quiz_type'				=> 'quiz_type',
		'fca_qc_disable_scroll'			=> 'disable_scroll',
	);

	forEach ( $fields as $key => $value ) {
		$settings[$value] = empty ( $_POST[$key] ) ? '' : fca_qc_sanitize_text( $_POST[$key] );
	}

	update_post_meta ( $post_id, 'quiz_cat_settings', $settings );
}

//SAVE TRANSLATIONS
function fca_qc_save_quiz_translations( $post_id ) {

	global $global_quiz_text_strings;
	$fields = $settings = array();
	$premium_translations = array(
		'timedout',
		'time_taken',
		'retake_quiz',
		'share_results',
		'i_got',
		'skip_this_step',
		'your_name',
		'your_email',
		'share',
		'tweet',
		'pin',
		'email'
	);

	forEach ( $global_quiz_text_strings as $key => $value ) {
		if ( FCA_QC_PLUGIN_PACKAGE !== 'Free' || !in_array( $key, $premium_translations ) ){
			$fields['fca_qc_' . $key . '_translation'] = $key;
		}
	}

	forEach ( $fields as $key => $value ) {
		$settings[$value] = empty ( $_POST[$key] ) ? $global_quiz_text_strings[$key] : fca_qc_sanitize_text( $_POST[$key] );
	}

	update_post_meta ( $post_id, 'quiz_cat_translations', $settings );

}

//PREVIEW
function fca_qc_save_preview_redirect ( $location ) {
	global $post;
	if ( !empty($_POST['fca_qc_quiz_preview_url'] ) ) {
		// Flush rewrite rules
		global $wp_rewrite;
		$wp_rewrite->flush_rules(true);

		return $_POST['fca_qc_quiz_preview_url'];
	}

	return $location;
}
add_filter('redirect_post_location', 'fca_qc_save_preview_redirect');

function fca_qc_live_preview( $content ){
	if ( is_user_logged_in() && get_post_type() === 'fca_qc_quiz' && is_main_query() && !doing_action( 'wp_head' ) )  {
		return $content . do_shortcode("[quiz-cat id='" . get_the_ID() . "']");
	} else {
		return $content;
	}

}
add_filter( 'the_content', 'fca_qc_live_preview');
