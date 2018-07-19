<?php

/**
 *
 * @link              https://ivanbarreda.com
 * @since             1.0.1
 * @package           Shortener
 *
 * @wordpress-plugin
 * Plugin Name:       Shortener Simple
 * Plugin URI:        https://ivanbarreda.com/shortener
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Ivan Barreda
 * Author URI:        https://ivanbarreda.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       shortener
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'BZZ_SHORTENER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BZZ_SHORTENER__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Register Custom Post Type


class parametrosdelredirecMetabox {

	private $screen = array(
		'buzz_shortener'
	);

	private $meta_fields = array(
		array(
			'label' => 'URL de destino',
			'id' => 'buzz_dest_url',
			'type' => 'url',
		),
		array(
			'label' => 'HTTP Code (301 by default)',
			'id' => 'buzz_http_code',
			'type' => 'text',
			'default' => 301
		),
		array(
			'label' => 'Numbers views',
			'id' => 'buzz_views_counter',
			'type' => 'text',
			'disable' => true
		),
	);

	public function __construct() {
		add_action( 'init', array( $this, 'buzz_shortener_cpt'));

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_fields' ) );
	}
	public function buzz_shortener_cpt() {

		$labels = array(
			'name'                  => _x( 'Enlaces', 'Post Type General Name', 'buzz_shortener' ),
			'singular_name'         => _x( 'Enlace', 'Post Type Singular Name', 'buzz_shortener' ),
			'menu_name'             => __( 'Enlaces', 'buzz_shortener' ),
			'name_admin_bar'        => __( 'Enlace', 'buzz_shortener' ),
			'archives'              => __( 'Archivos de enlace', 'buzz_shortener' ),
			'attributes'            => __( 'Atributos enlace', 'buzz_shortener' ),
			'parent_item_colon'     => __( 'Enlace padre:', 'buzz_shortener' ),
			'all_items'             => __( 'Todos los enlaces', 'buzz_shortener' ),
			'add_new_item'          => __( 'Añadir nuevo enlace', 'buzz_shortener' ),
			'add_new'               => __( 'Añadir enlace', 'buzz_shortener' ),
			'new_item'              => __( 'Nuevo enlace', 'buzz_shortener' ),
			'edit_item'             => __( 'Editar enlace', 'buzz_shortener' ),
			'update_item'           => __( 'Actualizar enlace', 'buzz_shortener' ),
			'view_item'             => __( 'Ver enlace', 'buzz_shortener' ),
			'view_items'            => __( 'Ver enlaces', 'buzz_shortener' ),
			'search_items'          => __( 'Buscar enlace', 'buzz_shortener' ),
			'not_found'             => __( 'No encontrado', 'buzz_shortener' ),
			'not_found_in_trash'    => __( 'No encontrado en la papelera', 'buzz_shortener' ),
			'featured_image'        => __( 'QR del enlace', 'buzz_shortener' ),
			'set_featured_image'    => __( 'Asignar QR del enlace', 'buzz_shortener' ),
			'remove_featured_image' => __( 'Quitar QR del enlace', 'buzz_shortener' ),
			'use_featured_image'    => __( 'Usar como QR del enlace', 'buzz_shortener' ),
			'insert_into_item'      => __( 'Insertar en el enlace', 'buzz_shortener' ),
			'uploaded_to_this_item' => __( 'Subir a este enlace', 'buzz_shortener' ),
			'items_list'            => __( 'Lista enlaces', 'buzz_shortener' ),
			'items_list_navigation' => __( 'Lista navegación de enlaces', 'buzz_shortener' ),
			'filter_items_list'     => __( 'Filtrar lista de enlaces', 'buzz_shortener' ),
		);
		$args = array(
			'label'                 => __( 'Enlace', 'buzz_shortener' ),
			'description'           => __( 'Acortador y redireccionador de enlaces externos.', 'buzz_shortener' ),
			'labels'                => $labels,
			'supports'              => array( 'title','thumbnail', 'revisions', 'slug' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'rewrite' 				=> array('slug' => 'b','with_front' => false),
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
		);
		register_post_type( 'buzz_shortener', $args );
		flush_rewrite_rules( false );
	}

	public function add_meta_boxes() {
		foreach ( $this->screen as $single_screen ) {
			add_meta_box(
				'parametrosdelredirec',
				__( 'Parametros del redireccionamiento', 'buzz_shorter_link' ),
				array( $this, 'meta_box_callback' ),
				$single_screen,
				'normal',
				'high'
			);
		}
	}


	public function meta_box_callback( $post ) {

		wp_nonce_field( 'parametrosdelredirec_data', 'parametrosdelredirec_nonce' );
		echo 'Parametros del enlace';
		$this->field_generator( $post );
	}

	public function field_generator( $post ) {
		$output = '';
		foreach ( $this->meta_fields as $meta_field ) {
			$label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
			$meta_value = get_post_meta( $post->ID, $meta_field['id'], true );
			if ( empty( $meta_value ) ) {
				$meta_value = $meta_field['default']; 
			}
			switch ( $meta_field['type'] ) {
				default:
				$input = sprintf(
					'<input %s id="%s" name="%s" type="%s" %s value="%s">',
					$meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
					$meta_field['id'],
					$meta_field['id'],
					$meta_field['type'],
					$meta_field['disable'] == true ? 'disabled="disabled"' : '' , 
					$meta_value
				);
			}
			$output .= $this->format_rows( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}

	public function format_rows( $label, $input ) {
		return '<tr><th>'.$label.'</th><td>'.$input.'</td></tr>';
	}
	public function save_fields( $post_id ) {


		if ( ! isset( $_POST['parametrosdelredirec_nonce'] ) )
			return $post_id;

		$nonce = $_POST['parametrosdelredirec_nonce'];

		if ( !wp_verify_nonce( $nonce, 'parametrosdelredirec_data' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		foreach ( $this->meta_fields as $meta_field ) {

			if ( isset( $_POST[ $meta_field['id'] ] ) ) {

				switch ( $meta_field['type'] ) {

					case 'email':
					$_POST[ $meta_field['id'] ] = sanitize_email( $_POST[ $meta_field['id'] ] );
					break;
					case 'text':
					$_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
					break;
				}
				update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
			} else if ( $meta_field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, $meta_field['id'], '0' );
			}
		}
	}
}

if (class_exists('parametrosdelredirecMetabox')) {
	new parametrosdelredirecMetabox;
};



function buzz_shortener_entry_loop_func(){

	if (!is_singular('buzz_shortener'))
		return;

	if (empty(get_post_meta(get_the_ID(),'buzz_dest_url'))|| empty(get_post_meta(get_the_ID(),'buzz_dest_url')[0]))
		return;

	$views_counter = intval(get_post_meta(get_the_ID(),'buzz_views_counter')[0]);

	update_post_meta(get_the_ID(),'buzz_views_counter', $views_counter+1);


	$url = get_post_meta(get_the_ID(),'buzz_dest_url')[0];
	$http_code = get_post_meta(get_the_ID(),'buzz_http_code')[0] != '' ? get_post_meta(get_the_ID(),'buzz_http_code')[0] : 301;
	wp_redirect($url, $http_code);
	exit();

}

add_action('template_redirect', 'buzz_shortener_entry_loop_func');

//add our action
add_action( 'save_post', 'buzz_save_shortener', 10, 2 );



function buzz_save_shortener($post_id, $post){

	if (!is_singular('buzz_shortener'))
		return;

  //Comprobamos que el post no está en revisión
	if (wp_is_post_revision($post_id))
		return false;

	remove_action('save_post', 'buzz_save_shortener' );

	buzz_insert_imagen_qr($post);

	add_action('save_post', 'buzz_save_shortener' );

}

function buzz_insert_imagen_qr($post){

	require_once( BZZ_SHORTENER_PLUGIN_PATH . 'lib/phpqrcode/qrlib.php' );

	$nombre_imagen = md5($post->post_name).$post->post_name.'.png';

	$media_path = wp_upload_dir()['path'].'/'.$nombre_imagen;

	$imagen_destacada = QRcode::png(get_permalink(), $media_path);

	$upload = wp_upload_bits($imagen_destacada, null, $nombre_imagen);
	
	$post_id = $post->ID; //set post id to which you need to set post thumbnail

	$filename = $media_path;
	$wp_filetype = wp_check_filetype($filename, null );

	//var_dump($media_path);


	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => sanitize_file_name($nombre_imagen),
		'post_content' => '',
		'post_status' => 'inherit'
	);

	$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );

	require_once(ABSPATH . 'wp-admin/includes/image.php');

	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

	wp_update_attachment_metadata( $attach_id, $attach_data );

	set_post_thumbnail( $post_id, $attach_id );
}


