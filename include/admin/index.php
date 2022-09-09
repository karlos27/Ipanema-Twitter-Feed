<?php
/**
 * File Name: index.php
 * Folder Path: /admin
 * Plugin Name : Ipanema Twitter Feed
 * 
 **/

if ( !defined( 'is_ipanema_tf_admin' ) ) {
	exit;
}


// Assign function to be called when admin page starts being displayed
add_action( 'admin_init', 'itf_admin_page_init' ); 

// Register function to be called when user submits options
function itf_admin_page_init() { 
  add_action( 'admin_post_save_itf_options', 'process_itf_feed_options' ); 
}

// Function to process user data submission
function process_itf_feed_options() { 
    // Check that user has proper security level 
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( 'Not allowed' ); 
    }
     
    // Check that nonce field is present 
    check_admin_referer( 'itf_form_data' ); 
	
    // Check if option_id field was present  
    if ( isset( $_GET['option_id'] ) ) {
        $option_id = intval( $_GET['option_id'] ); 
    } elseif ( isset( $_POST['option_id'] ) ) {
	    $option_id = intval( $_POST['option_id'] ); 
	} else {
        $option_id = 1;
    }
	
    // Build option name and retrieve options 
    $options = itf_gt_database_options( $option_id ); 
         
    // Cycle through all text fields and store their values 
    foreach ( array( 'tw_user_name', 'tw_setting_name', 'tw_lang' ) as $param_name ) { 
        if ( isset( $_POST[$param_name] ) ) { 
            $options[$param_name] = sanitize_text_field( $_POST[$param_name] ); 
        } 
    } 
         
    // Cycle through all numeric fields, convert to int and store
    foreach ( array( 'tw_width', 'tw_height', 'tw_number_of_tweets' ) as $param_name ) { 
        if ( isset( $_POST[$param_name] ) ) { 
            $options[$param_name] = intval( $_POST[$param_name] ); 
        } 
    }

    // Cycle through all check box form fields and set the options
    // array to true or false values based on presence of variables
    foreach ( array( 'tw_theme' ) as $param_name ) { 
        if ( isset( $_POST[$param_name] ) ) { 
            $options[$param_name] = true; 
        } else {
            $options[$param_name] = false; 
        }
    } 
             
    // Store updated options array to database 
	$options_name = 'itf_gt_database_options_' . $option_id;
    update_option( $options_name, $options ); 
	 
    $cleanaddress = 
        add_query_arg( array( 'message' => 1,  
                              'option_id' => $option_id, 
                              'page' => 'itf-admin-menu' ), 
                       admin_url( 'options-general.php' ) ); 
    wp_redirect( $cleanaddress ); 
    exit; 
}


// Administration page menu item
add_action( 'admin_menu', 'itf_sttngs_administration_menu_ittem', 1 );

function itf_sttngs_administration_menu_ittem() {
    $options_page =
        add_options_page( 
            esc_html__( 'Ipanema Twitter Feed Configuration', 'ipanema-twitter-feed' ),
            esc_html__( 'Ipanema Twitter Feed', 'ipanema-twitter-feed' ), 
            'manage_options',
            'itf-admin-menu', 
            'itf_sttngs_config_page' );
    
    if ( !empty( $options_page ) ) {
        add_action( 'load-' . $options_page, 'ifr_sttngs_help_tabs' );
    }
}


// Function to display options page content
function itf_sttngs_config_page() {
    // Retrieve plugin configuration options from database 
    if ( isset( $_GET['option_id'] ) ) {
        $option_id = intval( $_GET['option_id'] ); 
    } else {
        $option_id = 1;
    }
 
    $options = itf_gt_database_options( $option_id ); 
    
    ?>
    <div id="itf-general" class="wrap">
        <h2><?php esc_html_e( 'Ipanema Twitter Feed Configuration', 'ipanema-twitter-feed' ); ?></h2><br>

        <!-- Display message when settings are saved -->
        <?php if ( isset( $_GET['message'] ) && $_GET['message'] == '1' ) { ?>
            <div id='message' class='updated fade'>
                <p><strong><?php esc_html_e( 'Settings saved successfully', 'ipanema-twitter-feed' ) ?></strong></p>
            </div>
        <?php } ?>

        <!-- Option selector -->
        <div id="icon-themes" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper">
        <?php for ( $counter = 1; $counter <= 5; $counter++ ) {
            $temp_options = itf_gt_database_options( $counter ); 
            $class = ( $counter == $option_id ) ? ' nav-tab-active' : '';?>
            <a class="nav-tab<?php esc_html_e( $class, 'ipanema-twitter-feed' ); ?>" 
                href="<?php echo esc_url( add_query_arg( array( 'page' => 'itf-admin-menu', 'option_id' => $counter ) ), 
                admin_url( 'options-general.php' ) ); ?>">
                <?php esc_html_e( $counter, 'ipanema-twitter-feed' ); ?>
                <?php if ( $temp_options !== false ) esc_html_e( ' (' . $temp_options['tw_setting_name'] . ')', 'ipanema-twitter-feed' ); 
                else esc_html_e( ' (Empty)', 'ipanema-twitter-feed' ); ?></a>
        <?php } ?>
        </h2><br />

        <!-- Main options form --> 
        <form name="itf_tw_options_form" method="post" action="admin-post.php"> 
        
            <input type="hidden" name="action" value="save_itf_options" /> 
            <input type="hidden" name="option_id" value="<?php esc_html_e( $option_id, 'ipanema-twitter-feed' ); ?>" /> 
            <?php wp_nonce_field( 'itf_form_data' ); ?> 
        
            <table> 
                <tr> 
                    <td><?php esc_html_e( 'Setting name', 'ipanema-twitter-feed'); ?></td> 
                    <td><input class="tw_fields" type="text" name="tw_setting_name" 
                        value="<?php esc_html_e( $options['tw_setting_name'], 'ipanema-twitter-feed' ); ?>"/></td> 
                </tr>
                <tr> 
                    <td><?php esc_html_e( 'Username', 'ipanema-twitter-feed'); ?></td> 
                    <td><input class="tw_fields" type="text" name="tw_user_name" 
                        value="<?php esc_html_e( $options['tw_user_name'], 'ipanema-twitter-feed' ); ?>"/></td> 
                </tr> 
                <tr> 
                    <td><?php esc_html_e( 'Width', 'ipanema-twitter-feed'); ?></td> 
                    <td><input class="tw_fields" type="number" name="tw_width"
                        value="<?php esc_html_e( intval( $options['tw_width'] ), 'ipanema-twitter-feed' ); ?>"/></td> 
                </tr>
                <tr> 
                    <td><?php esc_html_e( 'Height', 'ipanema-twitter-feed'); ?></td> 
                    <td><input class="tw_fields" type="number" name="tw_height" 
                        value="<?php esc_html_e( intval( $options['tw_height'] ), 'ipanema-twitter-feed' ); ?>"/></td> 
                </tr> 
                <tr> 
                    <td><?php esc_html_e( 'Number of tweets to display', 'ipanema-twitter-feed'); ?></td> 
                    <td><input class="tw_fields" type="number" name="tw_number_of_tweets" 
                        value="<?php esc_html_e( intval( $options['tw_number_of_tweets'] ), 'ipanema-twitter-feed' ); ?>"/></td> 
                </tr>
                <tr>
                    <td><?php esc_html_e( 'Select language', 'ipanema-twitter-feed'); ?></td>
                    <td>
                        <select class="tw_fields" name="tw_lang">
                        <?php
                            $tw_lang = esc_html__( $options['tw_lang'], 'ipanema-twitter-feed' );
                            $langs   = array( 'en' => 'English (Default)', 'ar' => 'Arabic', 'bn' => 'Bengali', 'cs' => 'Czech', 'da' => 'Danish', 'de' => 'German', 'el' => 'Greek', 'es' => 'Spanish', 'fa' => 'Persian', 'fi' => 'Finnish', 'fil' => 'Filipino', 'fr' => 'French', 'he' => 'Hebrew', 'hi' => 'Hindi', 'hu' => 'Hungarian', 'id' => 'Indonesian', 'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean', 'msa' => 'Malay', 'nl' => 'Dutch', 'no' => 'Norwegian', 'pl' => 'Polish', 'pt' => 'Portuguese', 'ro' => 'Romanian', 'ru' => 'Russian', 'sv' => 'Swedish', 'th' => 'Thai', 'tr' => 'Turkish', 'uk' => 'Ukrainian', 'ur' => 'Urdu', 'vi'=> 'Vietnamese', 'zh-cn' => 'Chinese (Simplified)', 'zh-tw' => 'Chinese (Traditional)' );
                            foreach ( $langs as $lang => $languages ) { ?>
                                <option value="<?php esc_html_e( $lang, 'ipanema-twitter-feed' ); ?>" <?php selected( $lang, $tw_lang ); ?>><?php esc_html_e( $languages, 'ipanema-twitter-feed' ); ?> 
                              <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr> 
                    <td><?php esc_html_e( 'Dark mode', 'ipanema-twitter-feed'); ?></td>
                    <td><input class="tw_check" type="checkbox" name="tw_theme" <?php checked( $options['tw_theme'] ); ?>/></td> 
                </tr>
            </table><br /> 
            <input type="submit" value="<?php esc_html_e( 'Submit', 'ipanema-twitter-feed'); ?>" class="button-primary" /> 
        </form>
    </div>
<?php
}

// Custom help tabs in Ipanema settings page
function ifr_sttngs_help_tabs() {
    $screen = get_current_screen();

	$screen->add_help_tab( array(
		'id'       => 'itf-help-instructions',
		'title'    => esc_html__( 'Instructions', 'ipanema-twitter-feed' ),
		'callback' => 'ifr_sttngs_help_instructions',
	) );

	$screen->add_help_tab( array(
		'id'       => 'itf-help-faq',
		'title'    => esc_html__( 'FAQ', 'ipanema-twitter-feed' ),
		'callback' => 'ifr_sttngs_help_faq',
	) );

    $pluginsName    = esc_html__( 'Ipanema Twitter Feed ', 'ipanema-twitter-feed' );
    $pluginsObject  = esc_html__( 'plugin lets you add an awesome twitter feed/s into your WordPress site.', 'ipanema-twitter-feed' );

	$screen->set_help_sidebar( '<p><strong>' . $pluginsName . '</strong>' . $pluginsObject . '</p>' );

}

function ifr_sttngs_help_instructions() { 
    $darkside_url   = sanitize_url( 'https://en.wikipedia.org/wiki/Dark_side' );
    $moon_url       = sanitize_url( 'https://www.youtube.com/watch?v=NN-qxUZj7I8' );
    ?>
	<h3><?php esc_html_e( 'Follow the instructions: ', 'ipanema-twitter-feed' ) ?></h3>
	<dl>
  		<dt><strong><?php esc_html_e( 'Managing multiple sets in Ipanema Twitter Feed.', 'ipanema-twitter-feed' ) ?></strong></dt>
		<dd><?php esc_html_e( 'Specify the characteristics for each set of configuration.', 'ipanema-twitter-feed' ) ?></dd>
		<dd>
            <strong><?php esc_html_e( 'Setting name:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'Name (or title) for the configuration set.', 'ipanema-twitter-feed' ) ?>
                </dd>
            </dl>
        </dd>
        <dd>
            <strong><?php esc_html_e( 'Username:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'Your (or other) twitter username. By default, WordPress (@WordPress).', 'ipanema-twitter-feed' ) ?>
                </dd>
            </dl>
        </dd>
        <dd>
            <strong><?php esc_html_e( 'Width:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'The width property sets the width desired for the feed (length unit in \'px\'). By default, 560.', 'ipanema-twitter-feed' ) ?>
                </dd>
            </dl>
        </dd>
        <dd>
            <strong><?php esc_html_e( 'Height:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'The height property sets the height desired for the feed (length unit in \'px\'). Minimum value, 200. Related with \'Number of tweets to display\' configuration property.', 'ipanema-twitter-feed' ) ?>
                </dd>
            </dl>
        </dd>
        <dd>
            <strong><?php esc_html_e( 'Number of tweets to display:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'Number of tweets that you want to display in the feed (related with \'height\' configuration property).', 'ipanema-twitter-feed' ) ?>
                </dd>
            </dl>
        </dd>
        <dd>
            <strong><?php esc_html_e( 'Select language:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'Select desired twitter feed language.', 'ipanema-twitter-feed' ) ?>
                </dd>
            </dl>
        </dd>
        <dd>
            <strong><?php esc_html_e( 'Dark mode:', 'ipanema-twitter-feed' ) ?></strong>
            <dl>
                <dd>
                    <?php esc_html_e( 'Also known as \'Dark side\'. ', 'ipanema-twitter-feed' ) ?>
                </dd>
                <dd>
                    <a target="_blank" href="<?php esc_html_e( $darkside_url ) ?>"><?php esc_html_e( 'Read more', 'ipanema-twitter-feed' ) ?></a>
                </dd>
                <dd>
                <a target="_blank" href="<?php esc_html_e( $moon_url ) ?>"><?php esc_html_e( 'Related to', 'ipanema-twitter-feed' ) ?></a>
                </dd>
            </dl>
        </dd>
        <dt>
            <strong>
                <?php esc_html_e( 'Add the shortcode in page or post.', 'ipanema-twitter-feed' ) ?>
            </strong>
        </dt>
        <dd>
            <?php esc_html_e( 'To add the desired Twitter feed configuration set, you must add the shortcode \'twitterfeed\' plus the number set configuration in the \'option_id\'. See the example below.', 'ipanema-twitter-feed' ) ?>
        </dd>
        <dl>
            <dd><strong><?php esc_html_e( '[twitterfeed option_id="4"]', 'ipanema-twitter-feed' ) ?></strong></dd>
        </dl>
	</dl>
<?php }

function ifr_sttngs_help_faq() { ?>
	
	<h3><?php esc_html_e( 'FAQ:', 'ipanema-twitter-feed' ) ?></h3>
	<dl>
		<dt>
            <strong>
                <?php esc_html_e( 'How can I add the twitter feed in a page or post?', 'ipanema-twitter-feed' ) ?>
            </strong>
        </dt>
		<dd>
            <?php esc_html_e( 'You just need to add your preferred configuration\'s shortcode at in any page or post. Change the \'option_id\' parameter in the shortcode to render a specific \'Twitter feed configuration\'. For example, if you want to show the feed related to \'configuration one\', you just have to add:', 'ipanema-twitter-feed' ) ?>
        </dd>
        <dd>
            <strong>
                <?php esc_html_e( '[twitterfeed option_id="1"]', 'ipanema-twitter-feed' ) ?>
            </strong>
        </dd>
                    
	</dl>
<?php }