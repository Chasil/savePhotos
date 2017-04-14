<?php
/*
Plugin Name: Save Photos
Plugin URI:  http://wtyczka.pandzia.pl
Description: Allow to download photos of products in basket
Version:     1.0
Author:      Mateusz Wojcik
Author URI:  http://portfolio.pandzia.pl
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/

/*
 * License
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	
class WC_SavePhotos {

	private $cart;
	

	public function __construct() {
        $this->chasil_registerHooks();
    }

	private function fillCart() {
		if(empty($this->cart)) {
			$this->cart = WC()->cart->get_cart();
		}
	}
	
	/*
	 * Rejestracja zaczepow
	 */
	public function chasil_registerHooks(){
		add_action('woocommerce_cart_item_remove_link', array($this, 'chasil_add_checkbox_to_product'), 2, 2);
		add_action('woocommerce_cart_contents', array($this, 'chasil_add_buttons'), 1);
		add_action('wp_loaded', array($this, 'chasil_savePhotos'));
		
		add_action('wp_enqueue_scripts', array($this, 'chasil_loadScripts'));
	}
	
	
	/*
	 * Dodanie checkbox-ow przy produkcie w koszyku
	 */
	public function chasil_add_checkbox_to_product($closeString, $productKey){
		$this->fillCart();
		?>
		<input type="checkbox" class="ch_cb" value="<? echo $this->cart[$productKey]['product_id']; ?>" name="photo[<? echo $this->cart[$productKey]['product_id']; ?>]">
		<?php

		return $closeString;
	}
	
	/*
	 * Dodanie przyciskow: ZAZNACZ WSZYSTKIE, ODZNACZ WSZYSTKIE, POBIERZ ZDJECIA
	 */
	public function chasil_add_buttons(){
		?>
		<input type="text" id="ch_select_all" class="checkout-button button alt wc-forward ch_selects" name="ch_select_all" value="<?php esc_attr_e( 'Zaznacz wszystkie', 'woocommerce' ); ?>" />
		<input style="margin-left: 5px;" type="text" id="ch_deselect_all" class="checkout-button button alt wc-forward ch_selects" name="ch_select_all" value="<?php esc_attr_e( 'Odznacz wszystkie', 'woocommerce' ); ?>" />
		<input type="submit" class="checkout-button button alt wc-forward savePhotos" name="save_photos" value="<?php esc_attr_e( 'Pobierz zdjęcia', 'woocommerce' ); ?>" />
		<?php
	}

	
	/*
	 * Zapis zdjec do ZIP
	 */
	public function chasil_savePhotos(){
		if(isset($_POST['save_photos']) && isset($_POST['photo'])) {
			$links = array();
			$zip = new ZipArchive();
			$tempFile = tempnam('zips', '');
			$zip->open($tempFile, ZIPARCHIVE::CREATE);
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ){
				if(in_array($cart_item['product_id'], $_POST['photo'])){
					$image_url = parse_url(get_the_post_thumbnail_url( $cart_item['product_id'], '/'));
					$zip->addFile(ABSPATH . $image_url['path'], basename($image_url['path']));
				}
			}

			$zip->close();

			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length: ".filesize($tempFile));
			header("Content-Disposition: attachment; filename=\"zdjecia.zip\"");
			readfile($tempFile); die();
		}
	}
	
	/*
	 * Dodanie styli
	 */
	
	public function chasil_loadScripts(){
		wp_enqueue_style('chasil_savePhotosStyle', plugins_url('/SavePhotos/css/savePhotos.css'));
		wp_enqueue_script('chasil_savePhotosJS', plugins_url('/SavePhotos/js/savePhotos.js'), array('jquery'));
	}
}

new WC_SavePhotos();