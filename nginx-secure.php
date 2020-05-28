<?php
/*
Plugin Name: nginx-secure
Plugin URI: ''
Description: ''
Author: Jaap Marcus
Version: 4.2.8
Author URI: https://eris.nu
Text Domain: nginx-secure
Domain Path: /languages/
License: MIT
*/		
	
	class NginxSecure {
		private $options;
		
		function __construct(){
			
			add_filter('the_content', array($this,'content'),20);
			$this -> options = get_option('nginx-secure');
			if(empty($this -> options)){
				$this -> options = array('secret' => 'secret_key', 'ttl' => 3600,
					'extensions' => 'mp4,webm,ogg,ogv,mp3');
			}
		}
		
		function secure_url($url, $path){
	    		$expires = time() + $this -> options['ttl'];
	    		$secret = $this -> options['secret'];
			$md5 = md5("$expires$path $secret", true);
			$md5 = base64_encode($md5);
			$md5 = strtr($md5, '+/', '-_');
			$md5 = str_replace('=', '', $md5);
			
			//var_dump($url . $path . '?md5=' . $md5 . '&expires=' . $expires);
			return $url . $path . '?md5=' . $md5 . '&expires=' . $expires;
		
		}

		function convert($match){
			//var_dump($match);
			if(strpos($match[0], $_SERVER['HTTP_HOST'])){
				//we need to phrase it
				$url = parse_url($match[1].'.'.$match[2]);
				//echo $match[1].'.'.$match[2];
				
				return 'src="'.$this -> secure_url($url['scheme'].'://'.$url['host'], $url['path']).'"';				
			}else{
				return $match[0];
			}
		}

		function content($content){			
			$new_content = preg_replace_callback('/src="?([^ ]*)?\.('.implode('|',explode(',',$this -> options['extensions'])).')([^ ]*)"/', array($this, 'convert'), $content);
			return $new_content ;		
		}

	}
	
	$s = new NginxSecure();

	
	
	
	
	
Class NginxSecureSettings {
	private $settings;

	function __construct(){
		//add a new page the settings section
		add_action( 'admin_menu', array( $this, 'addPluginPage' ) );
		//register posible settings
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		
		add_action( 'plugins_loaded',  array( $this, 'textDomain' ));
		
		$this -> settings = get_option( 'nginx-secure' );
		
	}
	
	function textDomain(){
		load_plugin_textdomain( 'nginx-secure', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	}
	
	function addPluginPage(){
		add_options_page(
			__('Nginx Secure Link','nginx-secure'), // page_title
			__('Nginx Secure Link','nginx-secure'), // menu_title
			'manage_options', // capability
			'nginx-secure', // menu_slug
			array( $this, 'SettingsPage' ) // function
		);
	}
	
	function registerSettings(){
		register_setting('nginx-secure',
			'nginx-secure',
			array( $this, 'santize' )  
		);
		
		add_settings_section(
			'nginx-secure-settings', // id
			__('Settings','nginx-secure'), // title
			array( $this, 'info' ), // callback
			'nginx-secure-settings' // page
		);



		//options
		//enabled features

		add_settings_field(
			'secret', // id
			__('Secret Key','nginx-secure'), // title
				array( $this, 'secret' ), // callback
			'nginx-secure-settings',
			'nginx-secure-settings',
		);
		add_settings_field(
			'ttl', // id
			__('TTL','nginx-secure'), // title
				array( $this, 'ttl' ), // callback
			'nginx-secure-settings',
			'nginx-secure-settings',
		);
		add_settings_field(
			'extensions', // id
			__('Extensions','nginx-secure'), // title
				array( $this, 'extensions' ), // callback
			'nginx-secure-settings',
			'nginx-secure-settings',
		);
	
	}	
	
	function info(){
		?>
			<div>Hier komt wat tekst</div>
		<?php
	}
	
	function santize($input){

		$sanitary_values = array();
		if ( isset( $input['secret'] ) ) {
			$sanitary_values['secret'] = sanitize_text_field( $input['secret'] );			
		}
		if ( isset( $input['ttl'] ) ) {
			$sanitary_values['ttl'] = sanitize_text_field( $input['ttl'] );
		}
		if ( isset( $input['extensions'] ) ) {
			$sanitary_values['extensions'] = sanitize_text_field( $input['extensions'] );		
		}
			
		return $sanitary_values;
	}
	function secret(){
		printf(
			'<input class="regular-text" type="text" name="nginx-secure[secret]" id="secret" value="%s">',
			isset( $this->settings['secret'] ) ? esc_attr( $this->settings['secret']) : ''
		);
	}
	function ttl(){
		printf(
			'<input class="regular-text" type="text" name="nginx-secure[ttl]" id="ttl" value="%s">',
			isset( $this->settings['ttl'] ) ? esc_attr( $this->settings['ttl']) : ''
		);
	}
	function extensions(){
		printf(
			'<input class="regular-text" type="text" name="nginx-secure[extensions]" id="extensions" value="%s">',
			isset( $this->settings['extensions'] ) ? esc_attr( $this->settings['extensions']) : ''
		);
	}

	function SettingsPage(){
		?>
		<div class="">
			<h1 class = "wp-heading-inline"><?php _e('NGINX Secure Link Protection','nginx-secret');?></h1>
			
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'nginx-secure' );
					do_settings_sections( 'nginx-secure-settings' );
					submit_button();
				?>
			</form>	
		</div>
		<?php
	}
}

if(is_admin()){
	$nginx_secure = new NginxSecureSettings();
}	
	
?>