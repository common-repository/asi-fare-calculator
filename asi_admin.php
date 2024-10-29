<?php
      global $wpdb;
      if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['addfares']) ) 
        {  
             $mile=sanitize_text_field($_POST['mile']);
             $mile=filter_var( $mile, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );           
             $stop=sanitize_text_field($_POST['stop']);
             $stop=filter_var( $stop, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
             $seat=sanitize_text_field($_POST['seat']);
             $seat=filter_var( $seat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
             $minute=sanitize_text_field($_POST['minute']);
             $minute=filter_var( $minute, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
             $diskmmile=sanitize_text_field($_POST['diskmmile']);
             $curr=sanitize_text_field($_POST['curr']);
             $bcolor=sanitize_text_field($_POST['bcolor']);
             $table_name = $wpdb->prefix."fare";
             $wpdb->query($wpdb->prepare("UPDATE $table_name SET mile=%s,stop=%s,seat=%s,minute=%s,diskmmile=%d,curr=%s,color=%s WHERE fare_id=%d",$mile,$stop,$seat,$minute,$diskmmile,$curr,$bcolor,1));
        }
         if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($_POST['addcar']) ) 
        { 
             $cartype=sanitize_text_field($_POST['cartype']);
             $carfare=sanitize_text_field($_POST['carfare']);
             $carfare=filter_var( $carfare, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
             $table_name = $wpdb->prefix."cartypes";
             $wpdb->query($wpdb->prepare("INSERT INTO $table_name(name,fare) VALUES(%s,%s)",array($cartype,$carfare)));
        }
    class asi_plugin_admin
    {
         /** verbingo_plugin father class */
            private $asi_settings_key = 'asi_setting';
            private $addcar_settings_key = 'asi_addcar';
            private $plugin_options_key = 'asi_options';
            private $plugin_settings_tabs = array();
            private $localleft = 'left';
            function __construct() 
            {
                add_action( 'init', array( &$this, 'asi_scripts_admin'));
                add_action( 'init', array( &$this, 'load_settings'));
                add_action( 'admin_init', array( &$this, 'register_asi_fare_settings' ));
                add_action( 'admin_init', array( &$this, 'register_addcar_settings' ));
                add_action( 'admin_init', array( &$this, 'Get_selected_car' ));
                add_action( 'admin_init', array( &$this, 'Get_selected_fare' ));
                add_action( 'admin_menu', array( &$this, 'add_admin_menus' ));
            }  
            function Get_selected_car()
            {
                global $wpdb;
                $table_name = $wpdb->prefix."cartypes";
                $cartypes = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
                return $cartypes;
            }
            function Get_selected_fare()
            {
                global $wpdb;
                $table_name = $wpdb->prefix."fare";
                $fares = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
                return $fares;
            }
            /** UTILITY FUNCTIONS * */
            private function sections($head, $text = '') {
                echo '<h2>' . $head . '</h2>';
                echo '<div class="col-wrap">';
                if ($text) echo '<p>' . $text . '</p>';
            }
            private function sectiontop() {
                echo '</div>';
            }
             private function header($head) 
             {
                 echo '<h3>'.$head.' </h3>';
             }
            function asi_scripts_admin()
            {
                $google_map_api = 'https://maps.google.com/maps/api/js?sensor=true&libraries=places&language=en-AU';
                wp_enqueue_script('google-places', $google_map_api);
                wp_register_style('asi_style', plugins_url('css/asi_style.css',__FILE__));
                wp_enqueue_style('asi_style');
                wp_register_script('asi_script', plugins_url('js/asi_script.js', __FILE__ ),array('jquery'));
                wp_enqueue_script('asi_script'); 	
            }
            // Load Settings
            function load_settings() 
            {
                $this->general_settings = (array) get_option( $this->asi_settings_key );
                $this->advanced_settings = (array) get_option( $this->addcar_settings_key );            
                // Merge with defaults
                $this->general_settings = array_merge( array(
                    'general_option' => 'General value'
                ), $this->general_settings );
                $this->advanced_settings = array_merge( array(
                    'advanced_option' => 'Advanced value'
                ), $this->advanced_settings );
           }  
            // Register Language Tab Setting
            function register_asi_fare_settings() 
            {
                $this->plugin_settings_tabs[$this->asi_settings_key] = 'Settings';
                register_setting( $this->asi_settings_key, $this->asi_settings_key );
                add_settings_section( 'section_fare', '', array( &$this, 'section_asi_fare_desc' ), $this->asi_settings_key );
            } 
            // Call Language Setting Page
            function section_asi_fare_desc() 
            { 
                $fares=$this->Get_selected_fare();
                if($fares[0]['diskmmile']==0){ $kmselected="";$milesselected="selected";}else{$milesselected="";$kmselected="selected";}
                $selectdistance='<select name="diskmmile" id="diskmmile" style="width:105px;">';
                $selectdistance.='<option value="0" '.$milesselected.'>Miles</option><option value="1" '.$kmselected.'>KM</option></select>';
                $this->sections(__('Fare Settings','asi'));
                echo '<br><form name="addfare"><table name="instfare">';
                echo '<tr><td>Fare per mile</td><td><input value="'.$fares[0]['mile'].'" type="text" name="mile" style="width:105px;" ></td></tr>';
                echo '<tr><td>Fare per stop</td><td><input value="'.$fares[0]['stop'].'" type="text" name="stop" style="width:105px;" ></td></tr>';
                echo '<tr><td>Fare per seat</td><td><input value="'.$fares[0]['seat'].'" type="text" name="seat" style="width:105px;" ></td></tr>';
                echo '<tr><td>Fare per minute</td><td><input value="'.$fares[0]['minute'].'" type="text" name="minute" style="width:105px;"></td></tr>';
                 echo '<tr><td>Type of distance</td><td>'.$selectdistance.'</td></tr>'; 
                echo '<tr><td>Currency Type</td><td><input value="'.$fares[0]['curr'].'" type="text" name="curr" style="width:105px;" ></td></tr>';
                echo '<tr><td>Background color</td><td><input value="'.$fares[0]['color'].'" type="text" name="bcolor" style="width:105px;"></td></tr>';
                echo '<tr><td colspan="3"><input type="submit" id="faresubmit" value="Save Changes" class="button-primary" name="addfares" style="margin-top:40px;width:105px;z-index:2147483647; padding: 0px;"/></td></tr>';
                echo '</table></form>';
                echo '<br>';
                $this->sectiontop();
            }
                // Register Advance Settings
                function register_addcar_settings() 
                {
                $this->plugin_settings_tabs[$this->addcar_settings_key] = 'Car Type';
                register_setting( $this->addcar_settings_key, $this->addcar_settings_key );
                add_settings_section( 'section_addcar', 'Add Car Type', array( &$this, 'section_addcar_desc' ), $this->addcar_settings_key );
        }
    function section_addcar_desc() 
    { 
        $cartypes=$this->Get_selected_car();
        echo '<br><table class="displayrecord">';
        $i=1;
        echo '<thead><tr class="home"><th>S.No</th><th>Car Type</th><th>Car Fee</th><th>Action</th></tr></thead><tbody>';
        foreach($cartypes as $car)
        {
           echo '<tr><td>'.$i.'</td><td>'.$car['name'].'</td><td>'.$car['fare'].'</td><td><div class="actions"><a href="" ><img  class="rem" title="Remove" alt="Delete" src="'.plugins_url("img/", __FILE__).'/delete.png" content="'.$car['c_id'].'">
           </a></div></td></tr>';
           $i++;
        }
        echo '<form name="addcar" ><table name="addcar" class="displayrecord">';
        echo '<tr><td>Car Type</td><td><input type="text" name="cartype" style="width:90%"></td></tr>';
        echo '<tr><td>Car Fee</td><td><input type="text" name="carfare" style="width:90%"></td></tr>';
        echo '<tr><td colspan="3" style="border:none !important;"><input type="submit" id="carsubmit" value="Save Changes" class="button-primary" name="addcar" style="margin-top:40px;width:105px;z-index:2147483647; padding: 0px;"/></td></tr>';
        echo '</tbody></table></form>';
    }
    // Add Menu Here
    function add_admin_menus() {
    add_menu_page('asi_dashboard', 'Fare Calculator', 'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ),''.plugins_url("img/", __FILE__).'asiimg.png');
    }
    function plugin_options_page() {
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->asi_settings_key; ?>
    <div class="wrap">
        <?php $this->plugin_options_tabs(); ?>
        <form method="post" action="options.php">
            <?php wp_nonce_field( 'update-options' ); ?>
            <?php settings_fields( $tab ); ?>
            <?php do_settings_sections( $tab ); ?>
            <?php //submit_button(); ?>
        </form>
    </div>
    <?php
        }
    function plugin_options_tabs() {
    $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->asi_settings_key;
    $scren=screen_icon();
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
        $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
        echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
    }
    echo '</h2>';
}
}
add_action( 'plugins_loaded', create_function( '', '$asi_admin_side = new asi_plugin_admin;' ) );
?>