<?php

////////////////////////////
// SET UP POST TYPE
////////////////////////////

//REGISTER CPT
function fca_qc_register_post_type()
{

    $labels = array(
        'name'               => _x('Quizzes', 'quiz-cat'),
        'singular_name'      => _x('Quiz', 'quiz-cat'),
        'add_new'            => _x('Add New', 'quiz-cat'),
        'all_items'          => _x('All Quizzes', 'quiz-cat'),
        'add_new_item'       => _x('Add New Quiz', 'quiz-cat'),
        'edit_item'          => _x('Edit Quiz', 'quiz-cat'),
        'new_item'           => _x('New Quiz', 'quiz-cat'),
        'view_item'          => _x('View Quiz', 'quiz-cat'),
        'search_items'       => _x('Search Quizzes', 'quiz-cat'),
        'not_found'          => _x('Quiz not found', 'quiz-cat'),
        'not_found_in_trash' => _x('No Quizzes found in trash', 'quiz-cat'),
        'parent_item_colon'  => _x('Parent Quiz:', 'quiz-cat'),
        'menu_name'          => _x('Quiz Cat', 'quiz-cat')
    );

    $args = array(
        'labels'              => $labels,
        'description'         => "",
        'public'              => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_nav_menus'   => false,
        'show_in_menu'        => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 117,
        'menu_icon'           => FCA_QC_PLUGINS_URL . '/assets/icon.png',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title'),
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => true,
        'can_export'          => true
    );

    register_post_type('fca_qc_quiz', $args);
}

add_action('init', 'fca_qc_register_post_type');

//CHANGE CUSTOM 'UPDATED' MESSAGES FOR OUR CPT
function fca_qc_post_updated_messages($messages)
{

    $post = get_post();

    $messages['fca_qc_quiz'] = array(
        0  => '', // Unused. Messages start at index 1.
        1  => esc_attr__('Quiz updated.', 'quiz-cat'),
        2  => esc_attr__('Quiz updated.', 'quiz-cat'),
        3  => esc_attr__('Quiz deleted.', 'quiz-cat'),
        4  => esc_attr__('Quiz updated.', 'quiz-cat'),
        /* translators: %s: date and time of the revision */
        5  => isset($_GET['revision']) ? sprintf(esc_attr__('Quiz restored to revision from %s', 'quiz-cat'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
        6  => esc_attr__('Quiz published.', 'quiz-cat'),
        7  => esc_attr__('Quiz saved.', 'quiz-cat'),
        8  => esc_attr__('Quiz submitted.', 'quiz-cat'),
        9  => sprintf(
            esc_attr__('Quiz scheduled for: <strong>%1$s</strong>.', 'quiz-cat'),
            // translators: Publish box date format, see http://php.net/date
            date_i18n(esc_attr__('M j, Y @ G:i'), strtotime($post->post_date))
        ),
        10 => esc_attr__('Quiz draft updated.', 'quiz-cat'),
    );

    return $messages;
}

add_filter('post_updated_messages', 'fca_qc_post_updated_messages');
function fca_qc_remove_screen_options_tab($show_screen, $screen)
{
    if ($screen->id == 'fca_qc_quiz') {
        return false;
    }
    return $show_screen;
}

add_filter('screen_options_show_screen', 'fca_qc_remove_screen_options_tab', 10, 2);

// set metabox order in Settings tab
function fca_qc_settings_metabox_order($order)
{
    return array(
        'normal' => join(
            ",",
            array(
                'fca_qc_quiz_settings_meta_box',
                'fca_qc_quiz_timer_meta_box',
                'fca_qc_social_sharing_meta_box',
                'fca_qc_email_optin_meta_box'
            )
        ),
    );
}

add_filter('get_user_option_meta-box-order_fca_qc_quiz', 'fca_qc_settings_metabox_order');

// ADD OUR MENU, MAYBE REMOVE LEGACY WP ADMIN MENU ITEMS
function fca_qc_admin_menu()
{
    global $submenu;

    add_submenu_page('edit.php?post_type=fca_qc_quiz', __('All Quizzes', 'quiz-cat'), __('All Quizzes', 'quiz-cat'), 'manage_options', 'fca-qc-list', 'fca_qc_render_post_list', 0);
    add_submenu_page('edit.php?post_type=fca_qc_quiz', __('Add New', 'quiz-cat'), __('Add New', 'quiz-cat'), 'manage_options', 'fca-qc-new', 'fca_qc_render_add_new', 1);

    unset($submenu['edit.php?post_type=fca_qc_quiz'][2]);
    unset($submenu['edit.php?post_type=fca_qc_quiz'][3]);
}

add_action('admin_menu', 'fca_qc_admin_menu');

function fca_qc_render_add_new()
{
    echo "<script>window.location='" . admin_url('edit.php') . "?post_type=fca_qc_quiz&page=fca-qc-list&add_new=1" . "'</script>";
}

//SUPPRESS POST TITLES ON OUR CUSTOM POST TYPE
function fca_qc_suppress_post_title()
{
    global $post;
    if (empty ($post)) {
        return false;
    }
    if ($post->post_type == 'fca_qc_quiz' && is_main_query()) {
        echo "<style id='fca_qc_suppress_post_title'>.entry-title,.wp-block-post-title{display:none;}</style>";
    }
}

add_action('wp_head', 'fca_qc_suppress_post_title');

function fca_qc_render_post_list()
{

    $add_new = !empty($_GET['add_new']);
    if (!class_exists('QuizCat_List_Table')) {
        require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    }

    include(FCA_QC_PLUGIN_DIR . 'includes/list/post-list-table.php');
    wp_enqueue_style('fca_qc_post_list_css', FCA_QC_PLUGINS_URL . '/includes/list/post-list.min.css');
    wp_enqueue_script('fca_qc_post_list_js', FCA_QC_PLUGINS_URL . '/includes/list/post-list.min.js', false, FCA_QC_PLUGIN_VER, true);

    $mc_link = admin_url('post-new.php?post_type=fca_qc_quiz&quiz_type=mc');
    $pt_link = in_array(FCA_QC_PLUGIN_PACKAGE, array('Free')) ? 'https://fatcatapps.com/quizcat/' : admin_url('post-new.php?post_type=fca_qc_quiz&quiz_type=pt');
    $pt_atts = in_array(FCA_QC_PLUGIN_PACKAGE, array('Free')) ? 'target="_blank"' : '';
    $wq_link = in_array(FCA_QC_PLUGIN_PACKAGE, array('Free', 'Personal')) ? 'https://fatcatapps.com/quizcat/' : admin_url('post-new.php?post_type=fca_qc_quiz&quiz_type=wq');
    $wq_atts = in_array(FCA_QC_PLUGIN_PACKAGE, array('Free', 'Personal')) ? 'target="_blank"' : '';

    $modal_style = $add_new ? 'style="display:block;"' : ''
    ?>

    <form method="post">
        <div class="wrap">
            <h2>Quiz Cat
                <a href="#" id="fca-qc-add-new-button" class="page-title-action"><?php esc_html_e('Add New', 'quiz-cat') ?></a>
            </h2>

            <?php
            $listTable = new QuizCat_List_Table();
            $listTable->prepare_items();
            $listTable->display();
            ?>
        </div>
    </form>
    <div id='fca-quiz-select' class="fca-qc-modal" <?= $modal_style ?> >
        <div class="fca-qc-modal-inner">
            <span href="#" class="fca-qc-modal-close"><?php esc_html_e('Close', 'quiz-cat') ?></span>
            <h2><?php esc_html_e('Choose an Option', 'quiz-cat') ?></h2>
            <div class="fca-qc-modal-list">
                <a id='fca-quiz-select-multiplechoice' href="<?php echo $mc_link ?>" class="fca-qc-modal-list-group fca-qc-color1">
                    <span class="dashicons dashicons-clipboard"></span>
                    <h3><?php esc_html_e('Multiple Choice', 'quiz-cat') ?></h3>
                    <p>
                        <?php esc_html_e("Start Quiz", 'quiz-cat') ?><br>
                        <?php esc_html_e("Chose the correct answer", 'quiz-cat') ?>
                    </p>
                    <br>
                    <p class='fca_qc_info_span'> <?php esc_html_e("Examples include:", 'quiz-cat') ?> <br>
                        <?php esc_html_e("City Of Bangladesh", 'quiz-cat') ?><br>
                    </p>

                </a>
            </div>
        </div>
    </div>
    <?php
}
