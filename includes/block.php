<?php
/*
GUTENBERG BLOCK INTEGRATION
*/


function fca_qc_gutenblock() {
	
	wp_register_script(
		'fca_qc_gutenblock_script',
		FCA_QC_PLUGINS_URL . '/includes/block.min.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor' ),
		FCA_QC_PLUGIN_VER
	);

	wp_register_style( 'fca_qc_quiz_stylesheet', FCA_QC_PLUGINS_URL . '/includes/quiz/quiz.min.css', array(), FCA_QC_PLUGIN_VER );

	if ( function_exists( 'register_block_type' ) ) {
		register_block_type( 'quiz-cat/gutenblock',
			array(
				'editor_script' => array( 'fca_qc_gutenblock_script' ),
				'editor_style' => 'fca_qc_quiz_stylesheet',
				'render_callback' => 'fca_qc_gutenblock_render',
				'attributes' => array( 
					'post_id' => array( 
						'type' => 'string',
						'default' => '0'				
					)
				)
			)
		);
	}	
}
add_action( 'init', 'fca_qc_gutenblock' );


function fca_qc_gutenblock_enqueue() {

	$posts = get_posts( array(
		'post_type' => 'fca_qc_quiz',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids'
	));
	
	$quiz_list = array( 
		array(
			'value' => 0,
			'label' => 'Select a quiz',
		) 
	);
	
	forEach ( $posts as $p ) {
		$title =  get_the_title( $p );
		if ( empty( $title ) ) {
			$title = esc_attr__("(no title)", 'quiz-cat' );
		}
		$quiz_list[] = array(
			'value' => $p,
			'label' => html_entity_decode( $title ),
		);
	
	}
	
	wp_localize_script( 'fca_qc_gutenblock_script', 'fca_qc_gutenblock_script_data', array( 'quizzes' => $quiz_list, 'editurl' => admin_url( 'post.php' ), 'newurl' => admin_url( 'post-new.php' )  ) );	
}
add_action( 'enqueue_block_editor_assets', 'fca_qc_gutenblock_enqueue' );


function fca_qc_gutenblock_render( $attributes ) {

	$id = empty( $attributes['post_id'] ) ? 0 : $attributes['post_id'];
	if ( $id ) {		
		return do_shortcode( "[quiz-cat id='$id']" );
	}
	return '<p>' . esc_attr__( 'Click here and select a quiz from the menu above.', 'quiz-cat' ) . '</p>';
}