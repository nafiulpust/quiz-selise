<?php

/**
 * Plugin Name: V Quiz
 * Plugin URI: https://version001.com
 * Description: Crushing it!
 * Text Domain: quiz-cat
 * Domain Path: /languages
 * Version:1.0.1
 * Author: Nafiul Islam
 * Author URI: http://version001.com
 */



// BASIC SECURITY
defined( 'ABSPATH' ) or die( 'Unauthorized Access!' );



if ( !defined ('FCA_QC_PLUGIN_DIR') ) {

	// DEFINE SOME USEFUL CONSTANTS
	define( 'FCA_QC_DEBUG', FALSE );
	if ( FCA_QC_DEBUG ) {
		define( 'FCA_QC_PLUGIN_VER', '1.0.' . time() );
	} else {
		define( 'FCA_QC_PLUGIN_VER', '1.0.1' );
	}
	define( 'FCA_QC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'FCA_QC_PLUGINS_URL', plugins_url( '', __FILE__ ) );
	define( 'FCA_QC_PLUGINS_BASENAME', plugin_basename(__FILE__) );
	define( 'FCA_QC_PLUGIN_FILE', __FILE__ );
	define( 'FCA_QC_PLUGIN_PACKAGE', 'Free' ); //DONT CHANGE THIS, IT WONT ADD FEATURES, ONLY BREAKS UPDATER AND LICENSE

	include_once( FCA_QC_PLUGIN_DIR . '/includes/functions.php' );
	include_once( FCA_QC_PLUGIN_DIR . '/includes/post-type.php' );
	include_once( FCA_QC_PLUGIN_DIR . '/includes/quiz/quiz.php' );
	include_once( FCA_QC_PLUGIN_DIR . '/includes/editor/editor.php' );	
	include_once( FCA_QC_PLUGIN_DIR . '/includes/block.php' );

	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/editor/sidebar.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/editor/sidebar.php' );
	}
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/premium/premium.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/premium/premium.php' );
	}
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/premium/optins.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/premium/optins.php' );
	}
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/premium/licensing.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/premium/licensing.php' );
	}	
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/premium/db.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/premium/db.php' );
	}
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/stats/stats.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/stats/stats.php' );
	}
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/upgrade.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/upgrade.php' );
	}
	if ( file_exists ( FCA_QC_PLUGIN_DIR . '/includes/notices/notices.php' ) ) {
		include_once( FCA_QC_PLUGIN_DIR . '/includes/notices/notices.php' );
	}	
	
	//FILTERABLE FRONT-END STRINGS
	$global_quiz_text_strings = array (
		'no_quiz_found' => esc_attr__('No Quiz found', 'quiz-cat'),
		'timedout' => esc_attr__('Timed out!', 'quiz-cat'),
		'time_taken' => esc_attr__('Total time taken:', 'quiz-cat'),
		'correct' => esc_attr__('Correct!', 'quiz-cat'),
		'wrong' => esc_attr__('Wrong!', 'quiz-cat'),
		'your_answer' => esc_attr__('Your answer:', 'quiz-cat'),
		'correct_answer' => esc_attr__('Correct answer:', 'quiz-cat'),
		'question' => esc_attr__('Question', 'quiz-cat'),
		'next' =>  esc_attr__('Next', 'quiz-cat'),
		'you_got' =>  esc_attr__('You got', 'quiz-cat'),
		'out_of' => esc_attr__('out of', 'quiz-cat'),
		'your_answers' =>  esc_attr__('Your Answers', 'quiz-cat'),
		'start_quiz' => esc_attr__('Start Quiz', 'quiz-cat'),
		'retake_quiz' => esc_attr__('Retake Quiz', 'quiz-cat'),
		'share_results' => esc_attr__('SHARE YOUR RESULTS', 'quiz-cat'),
		'i_got' => esc_attr__('I got', 'quiz-cat'),
		'skip_this_step' => esc_attr__('Skip this step', 'quiz-cat'),
		'your_name' => esc_attr__('Your Name', 'quiz-cat'),
		'your_email' => esc_attr__('Your Email', 'quiz-cat'),
		'share'  => esc_attr__('Share', 'quiz-cat'),
		'tweet'  =>  esc_attr__('Tweet', 'quiz-cat'),
		'pin'  =>  esc_attr__('Pin', 'quiz-cat'),
		'email'  =>  esc_attr__('Email', 'quiz-cat') 
	);
	
	function fca_qc_add_plugin_action_links( $links ) {
		
		$support_url = FCA_QC_PLUGIN_PACKAGE === 'Free' ? 'https://wordpress.org/support/plugin/quiz-cat' : 'https://fatcatapps.com/support';
		
		$new_links = array(
			'support' => "<a target='_blank' href='$support_url' >" . esc_attr__('Support', 'quiz-cat' ) . '</a>'
		);
		
		$links = array_merge( $new_links, $links );

		return $links;
		
	}
	add_filter( 'plugin_action_links_' . FCA_QC_PLUGINS_BASENAME, 'fca_qc_add_plugin_action_links' );

	/* Localization */
	function fca_qc_load_localization() {
		load_plugin_textdomain( 'quiz-cat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	add_action( 'init', 'fca_qc_load_localization' );
	

}