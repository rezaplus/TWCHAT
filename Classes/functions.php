<?php
/**
 * Default Data and install
 * Plugins page actions
 * Admin menu
 * Float display on front-end
 * Admin bar
 * include woocommerce
 * check user Accessibility
 */

if ( ! defined( 'ABSPATH' ) ) exit;

DTWP_MainFunctions::get_instance();


class DTWP_MainFunctions{
    
    static $instance = null;
    public $DTWP_general;
    
    public function __construct(){
        //set default data and get general data
        $this->defaultData();
        //plugins page action.
        add_filter( 'plugin_action_links_'.DTWP_BASE_NAME, array($this,'plugin_actions') );
        //display admin menu
        add_action('admin_menu',array($this,'admin_menu'),99);
        //include Float widget
        add_action( 'wp_footer', array($this,'floatWidget'));
        //admin bar send message button
        add_action('admin_bar_menu', array($this,'admin_bar_DTWP'), 90);
        //include woocommerce class
        add_action('admin_init',array($this,'woocommerce'));
    }
    function defaultData(){
        //get general data
        $this->DTWP_general = get_option('DTWP_General_Option');
        
        //default general data
        if(empty($this->DTWP_general)){
            require_once  DTWP_DIR_path.'Classes/install.php';
        }  
    }
    /**
     * display plugin links on plugins page.
     */
    function plugin_actions( $actions ) {
        $actions[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=DTWP_settings') ) .'">'.__('Settings','DTWPLANG').'</a>';
        return $actions;
    }
    /**
     *display pages on admin menu. 
     */
    function admin_menu() {
        add_menu_page('Send Message', esc_html__('DTWhatsapp', 'DTWPLANG') , 'manage_options', 'DTWP_menu', 'sendmessage', 'dashicons-whatsapp', 150);
        if($this->check_user_Accessibility('DTWP_Accessibility_WC'))
            add_submenu_page('DTWP_menu', esc_html__('Send Message', 'DTWPLANG') , esc_html__('Send Message', 'DTWPLANG') , 'manage_options', "sendmessage", function(){require_once  DTWP_DIR_path.'Pages/sendMessage.php';}, 0);
        if($this->check_user_Accessibility('DTWP_Accessibility_settings'))
            add_submenu_page('DTWP_menu', esc_html__('DTWhatsapp settings') , esc_html__('Settings', 'DTWPLANG') , 'manage_options', "DTWP_settings", array($this,'DTWP_settings_page'), 1);
        remove_submenu_page('DTWP_menu', 'DTWP_menu');
    }
    /**
     * display settings page.
     * check update database.
     */
    public function DTWP_settings_page(){
        require_once  DTWP_DIR_path.'Classes/update.php';
        require_once DTWP_DIR_path.'Pages/Settings.php'; 
    }
    /**
     * check user Accessibility.
     * called from admin_menu function.
     * called from woocommerce function.
     */
    function check_user_Accessibility($Type){
        $user = wp_get_current_user();  
        $settings_roles = get_option($Type);
        if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
            foreach ( $user->roles as $role )
                if(in_array($role,$settings_roles) or $role=='administrator'){ return true;} 
            return false;
        }
        return false;
    }
    /**
     * require woocommerce class.
     * check accessibility.
     */
    function woocommerce(){
        if(class_exists( 'WooCommerce' ) && $this->check_user_Accessibility('DTWP_Accessibility_WC')){
            require_once  DTWP_DIR_path.'Classes/woocommerce.php';
        }
    }
    /**
     * display float on front-end
     */
    function floatWidget(){
        if($this->DTWP_general['float_is_enable']=='true') {
            wp_enqueue_style( 'Float', DTWP_assets . 'floatStyle.css',true,DTWP_plugin_version);
            require_once DTWP_DIR_path.'Pages/Float.php';
        }
    }
    /**
     * display send message button on admin bar.
     */
    function admin_bar_DTWP($wp_admin_bar)
    {
        $args = array(
            'id' => 'sendmessage',
            'title' => '<span class="ab-icon dashicons dashicons-whatsapp" style="font-size: large;line-height: 23px;"></span>',
            'href' => admin_url() . 'admin.php?page=sendmessage'
        );
        $wp_admin_bar->add_node($args);
    }
    public static function get_instance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
    

