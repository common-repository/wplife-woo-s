<?php
/**
 * === Switcher for WooCommerce ===
 * Contributors: newterra
 * Plugin Name: Switcher for WooCommerce
 * Plugin URI: https://wplife.ru/plugins
 * Donate link: 
 * Tags: woo, woocommerce, switcher, setup, wc, wplife
 * Version: 1.1.1
 * Stable tag: trunk
 * Author: Александр Пархоменко
 * Description: Switcher for WooCommerce позволяет делать переключения для тонкой настройка плагина WooCommerce
 * Author URI: https://wplife.ru/
 * Requires at least: 3.0
 * Tested up to: 5.8
 * WC Tested up to: 5.6
 * Requires PHP: 5.6
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wplife-woo-s
 * Domain Path: /lang/
 */
/*
 * Префикс для функций wplife_wcs_
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Выходим из файла, если была попытка прямого доступа
}

//if ( class_exists( 'wooCommerce' ) ) {
  // code that requires WooCommerce

  /* Подключаем перевод плагина */
add_action( 'plugins_loaded', 'wplife_wcs_true_load_plugin_textdomain' );
function wplife_wcs_true_load_plugin_textdomain() {
	load_plugin_textdomain( 'wplife-woo-s', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' ); 
}
// создать меню пользовательских настроек плагина
add_action('admin_menu', 'woo_s_create_menu');
add_action( 'init', 'wplife_wcs_init' );		

function woo_s_create_menu() {
	// создать новое меню верхнего уровня
	add_options_page(__('Switcher for WooCommerce'), __('Switcher'), 'administrator', __FILE__, 'woo_s_settings_page');
	//функция настройки регистра вызовов
	add_action( 'admin_init', 'register_wplife_wcs_settings' );
}

/* Добавим ссылку на страницу настроек в таблицу плагинов */
function plugin_settings_link($links) { 
	$settings_link = '<a href="options-general.php?page=wplife-woo-s/wplife-woo-s.php">'.__('Настройки').'</a>'; 
	array_unshift( $links, $settings_link ); 
	return $links; 
}
$plugin_file = plugin_basename(__FILE__); 
add_filter( "plugin_action_links_$plugin_file", 'plugin_settings_link' );

/* Тело плагина */
function wplife_wcs_init() {

	// Добавить вариант сортировки: По названию А-Я
	if(get_option( 'add_sort_a' ) == true ){
		add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_name_args' );
		function custom_woocommerce_get_catalog_ordering_name_args( $args ) {
			$orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
			if ( 'title' == $orderby_value ) {
				$args['orderby'] = 'title';
				$args['order'] = 'ASC';
				$args['meta_key'] = '';
		}
		return $args;
		}
		add_filter( 'woocommerce_default_catalog_orderby_options', 'sort_catalog_name_title_asc' );
		add_filter( 'woocommerce_catalog_orderby', 'sort_catalog_name_title_asc', 1 );
		function sort_catalog_name_title_asc( $array ) {
			$array['title'] = __('По названию А-Я');
			return $array;
		}
	}

	// Добавить вариант сортировки: По названию Я-А
	if(get_option( 'add_sort_z' ) == true ){
		add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_name_args_Z' );
		function custom_woocommerce_get_catalog_ordering_name_args_Z( $args ) {
			$orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
			if ( 'titlez' == $orderby_value ) {
				$args['orderby'] = 'title';
				$args['order'] = 'DESC';
				$args['meta_key'] = '';
			}
		return $args;
		}
		add_filter( 'woocommerce_default_catalog_orderby_options', 'sort_catalog_name_title_desc' );
		add_filter( 'woocommerce_catalog_orderby', 'sort_catalog_name_title_desc', 1 );

		function sort_catalog_name_title_desc( $array ) {
			$array['titlez'] = __('По названию Я-А');
			return $array;
		}
	}
	
	// Добавить вариант сортировки: По артиклу
	if(get_option( 'add_sort_sku' ) == true ){
		add_filter( 'woocommerce_get_catalog_ordering_args', 'custom_woocommerce_get_catalog_ordering_sku' );
		function custom_woocommerce_get_catalog_ordering_sku( $args ) {
			$orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby_sku', get_option( 'woocommerce_default_catalog_orderby_sku' ) );
			if ( 'sku' == $orderby_value ) {
				$args['orderby'] = 'meta_value';
				$args['order'] = 'ASC';
				$args['meta_key'] = '_sku';
			}
			return $args;
		}
		add_filter( 'woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_name_orderby_sku' );
		add_filter( 'woocommerce_catalog_orderby', 'custom_woocommerce_catalog_name_orderby_sku', 1 );
		function custom_woocommerce_catalog_name_orderby_sku( $array ) {
			$array['sku'] = __('По артиклу');
			return $array;
		}
	}
	// Разрешить показывать пустые категории в магазине
	if(get_option( 'add_show_empty_cat' ) == true ){
		add_filter( 'woocommerce_product_subcategories_hide_empty', '__return_false' );
	}
	
	// Включить или выключить ваиант сортировки по дате
		
//	remove_action ( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
//	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 ); // Удаляем сортировку
//	add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 5 );

	function wplife_custom_woocommerce_catalog_orderby( $array ) {
		//unset($array["popularity"]);
		//unset($array["rating"]);
		if(get_option( 'add_sort_date' ) == true ){
			unset($array['date']);
		}
		//unset($array["price"]);
		//unset($array["price-desc"]);
		return $array;
	}
	add_filter( "woocommerce_catalog_orderby", "wplife_custom_woocommerce_catalog_orderby", 20 );
}

function register_wplife_wcs_settings() {
	// зарегистрировать настройки плагина
	register_setting( 'woo-s-settings-group', 'add_sort_a' );
	register_setting( 'woo-s-settings-group', 'add_sort_z' );
	register_setting( 'woo-s-settings-group', 'add_sort_sku' );
	register_setting( 'woo-s-settings-group', 'add_show_empty_cat' );
	register_setting( 'woo-s-settings-group', 'add_sort_date' );	
}

/* Страница настроек */
function woo_s_settings_page() {
?>
<style>
.table-woo-s th{
	text-align: start;
}
th {
	border-bottom:1px dashed #000;
}
</style>
<div class="wrap">
<h2><?php _e('Switcher for WooCommerce страница настроек', 'woo-s'); ?></h2>
<form method="post" action="options.php">
    <?php settings_fields( 'woo-s-settings-group' ); ?>
    
    <table class="table-woo-s">
        <tr valign="top">
<?php // Добавить вариант сортировки: По названию А-Я ?>
        <th scope="row">
			<?php _e('Добавить вариант сортировки: По названию А-Я', 'wplife-woo-s');?>
		</th>
        <td>
			<input type="checkbox" name="add_sort_a" value="1" <?php if (get_option('add_sort_a')==true) echo 'checked="checked" '; ?>" /></td>
        </tr>
		
<?php // Добавить вариант сортировки: По названию Я-А ?>
        <th scope="row">
			<?php _e('Добавить вариант сортировки: По названию Я-А', 'wplife-woo-s');?>
		</th>
        <td>
			<input type="checkbox" name="add_sort_z" value="1" <?php if (get_option('add_sort_z')==true) echo 'checked="checked" '; ?>" /></td>
        </tr>

<?php // Добавить вариант сортировки: По актиклу ?>
        <th scope="row">
			<?php _e('Добавить вариант сортировки: По актикулу', 'wplife-woo-s');?>
		</th>
        <td>
			<input type="checkbox" name="add_sort_sku" value="1" <?php if (get_option('add_sort_sku')==true) echo 'checked="checked" '; ?>" /></td>
        </tr>
		
<?php // Разрешить показывать пустые категории в магазине add_show_empty_cat ?>
		<th scope="row">
			<?php _e('Разрешить показывать пустые категории в магазине', 'wplife-woo-s');?>
		</th>
        <td>
			<input type="checkbox" name="add_show_empty_cat" value="1" <?php if (get_option('add_show_empty_cat')==true) echo 'checked="checked" '; ?>" /></td>
        </tr>

<?php // Удалить вариант сортировки по дате (date) ?>
		<th scope="row">
			<?php _e('Удалить вариант сортировки: по дате', 'wplife-woo-s');?>
		</th>
        <td>
			<input type="checkbox" name="add_sort_date" value="1" <?php if (get_option('add_sort_date')==true) echo 'checked="checked" '; ?>" /></td>
        </tr>

		
		
    </table>
	
    <?php submit_button(); ?>
</form>
</div>
<?php } //woo_s_settings_page
/*} else {
	// you don't appear to have WooCommerce activated
	function wplife_display_admin_notice() { ?>
		<div class="notice notice-error is-dismissible"><p><?php _e('Плагин Switcher for WooCommerce активирован, но не работает. Для его работы требуется плагин WooCommerce. Ативируйте плагин WooCommerce.');?></p></div>
	<?php
		}
	add_action( 'admin_notices', 'wplife_display_admin_notice' );
}*/
?>