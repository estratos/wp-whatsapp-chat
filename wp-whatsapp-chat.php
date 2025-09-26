<?php
/**
 * Plugin Name: WP WhatsApp Chat
 * Plugin URI: https://github.com/tu-usuario/wp-whatsapp-chat
 * Description: Añade un botón flotante de WhatsApp para que los visitantes puedan contactarte fácilmente
 * Version: 1.0.0
 * Author: Tu Nombre
 * License: GPL v2 or later
 * Text Domain: wp-whatsapp-chat
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes
define('WPWHATSAPP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPWHATSAPP_PLUGIN_PATH', plugin_dir_path(__FILE__));

class WPWhatsAppChat {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Cargar traducciones
        load_plugin_textdomain('wp-whatsapp-chat', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Hook para el backend
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        
        // Hook para el frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'display_whatsapp_button'));
        
        // Shortcode
        add_shortcode('whatsapp', array($this, 'whatsapp_shortcode'));
    }
    
    public function activate() {
        // Valores por defecto
        $default_options = array(
            'phone_number' => '+1234567890',
            'message' => 'Hola, me interesa tu servicio',
            'position' => 'right',
            'button_text' => 'Chatear por WhatsApp',
            'show_on_desktop' => 'yes',
            'show_on_mobile' => 'yes',
            'button_color' => '#25D366',
            'text_color' => '#ffffff',
            'auto_open' => 'no',
            'delay' => '5'
        );
        
        if (!get_option('wpwhatsapp_settings')) {
            add_option('wpwhatsapp_settings', $default_options);
        }
    }
    
    public function deactivate() {
        // Limpiar si es necesario
    }
    
    // Menú de administración
    public function add_admin_menu() {
        add_options_page(
            'WP WhatsApp Chat',
            'WhatsApp Chat',
            'manage_options',
            'wp-whatsapp-chat',
            array($this, 'options_page')
        );
    }
    
    // Inicializar configuraciones
    public function settings_init() {
        register_setting('wpwhatsapp', 'wpwhatsapp_settings');
        
        // Sección principal
        add_settings_section(
            'wpwhatsapp_section',
            __('Configuración de WhatsApp Chat', 'wp-whatsapp-chat'),
            array($this, 'settings_section_callback'),
            'wpwhatsapp'
        );
        
        // Campos
        add_settings_field(
            'phone_number',
            __('Número de WhatsApp', 'wp-whatsapp-chat'),
            array($this, 'phone_number_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'message',
            __('Mensaje predeterminado', 'wp-whatsapp-chat'),
            array($this, 'message_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'button_text',
            __('Texto del botón', 'wp-whatsapp-chat'),
            array($this, 'button_text_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'position',
            __('Posición del botón', 'wp-whatsapp-chat'),
            array($this, 'position_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'button_color',
            __('Color del botón', 'wp-whatsapp-chat'),
            array($this, 'button_color_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'text_color',
            __('Color del texto', 'wp-whatsapp-chat'),
            array($this, 'text_color_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'show_on_desktop',
            __('Mostrar en desktop', 'wp-whatsapp-chat'),
            array($this, 'show_on_desktop_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
        
        add_settings_field(
            'show_on_mobile',
            __('Mostrar en móvil', 'wp-whatsapp-chat'),
            array($this, 'show_on_mobile_render'),
            'wpwhatsapp',
            'wpwhatsapp_section'
        );
    }
    
    // Callbacks para los campos
    public function settings_section_callback() {
        echo __('Configura los ajustes del botón de WhatsApp', 'wp-whatsapp-chat');
    }
    
    public function phone_number_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <input type="text" name="wpwhatsapp_settings[phone_number]" value="<?php echo esc_attr($options['phone_number']); ?>" class="regular-text">
        <p class="description"><?php _e('Ejemplo: +34123456789 (incluye código de país)', 'wp-whatsapp-chat'); ?></p>
        <?php
    }
    
    public function message_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <textarea name="wpwhatsapp_settings[message]" class="large-text" rows="3"><?php echo esc_textarea($options['message']); ?></textarea>
        <p class="description"><?php _e('Mensaje predeterminado que se enviará', 'wp-whatsapp-chat'); ?></p>
        <?php
    }
    
    public function button_text_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <input type="text" name="wpwhatsapp_settings[button_text]" value="<?php echo esc_attr($options['button_text']); ?>" class="regular-text">
        <?php
    }
    
    public function position_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <select name="wpwhatsapp_settings[position]">
            <option value="left" <?php selected($options['position'], 'left'); ?>><?php _e('Izquierda', 'wp-whatsapp-chat'); ?></option>
            <option value="right" <?php selected($options['position'], 'right'); ?>><?php _e('Derecha', 'wp-whatsapp-chat'); ?></option>
        </select>
        <?php
    }
    
    public function button_color_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <input type="color" name="wpwhatsapp_settings[button_color]" value="<?php echo esc_attr($options['button_color']); ?>">
        <?php
    }
    
    public function text_color_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <input type="color" name="wpwhatsapp_settings[text_color]" value="<?php echo esc_attr($options['text_color']); ?>">
        <?php
    }
    
    public function show_on_desktop_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <label>
            <input type="checkbox" name="wpwhatsapp_settings[show_on_desktop]" value="yes" <?php checked($options['show_on_desktop'], 'yes'); ?>>
            <?php _e('Mostrar botón en dispositivos desktop', 'wp-whatsapp-chat'); ?>
        </label>
        <?php
    }
    
    public function show_on_mobile_render() {
        $options = get_option('wpwhatsapp_settings');
        ?>
        <label>
            <input type="checkbox" name="wpwhatsapp_settings[show_on_mobile]" value="yes" <?php checked($options['show_on_mobile'], 'yes'); ?>>
            <?php _e('Mostrar botón en dispositivos móviles', 'wp-whatsapp-chat'); ?>
        </label>
        <?php
    }
    
    // Página de opciones
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('WP WhatsApp Chat', 'wp-whatsapp-chat'); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('wpwhatsapp');
                do_settings_sections('wpwhatsapp');
                submit_button();
                ?>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #46b450;">
                <h3><?php _e('Cómo usar el shortcode', 'wp-whatsapp-chat'); ?></h3>
                <p><?php _e('Puedes usar el shortcode [whatsapp] en cualquier página o post. Ejemplos:', 'wp-whatsapp-chat'); ?></p>
                <ul>
                    <li><code>[whatsapp]</code> - <?php _e('Botón con configuración global', 'wp-whatsapp-chat'); ?></li>
                    <li><code>[whatsapp phone="+123456789" message="Mensaje personalizado"]</code> - <?php _e('Botón personalizado', 'wp-whatsapp-chat'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    // Cargar scripts y estilos
    public function enqueue_scripts() {
        wp_enqueue_style('wpwhatsapp-style', WPWHATSAPP_PLUGIN_URL . 'assets/style.css', array(), '1.0.0');
        wp_enqueue_script('wpwhatsapp-script', WPWHATSAPP_PLUGIN_URL . 'assets/script.js', array('jquery'), '1.0.0', true);
    }
    
    // Mostrar botón de WhatsApp
    public function display_whatsapp_button() {
        $options = get_option('wpwhatsapp_settings');
        
        if (empty($options['phone_number'])) {
            return;
        }
        
        // Verificar visibilidad por dispositivo
        $is_mobile = wp_is_mobile();
        if (($is_mobile && $options['show_on_mobile'] !== 'yes') || 
            (!$is_mobile && $options['show_on_desktop'] !== 'yes')) {
            return;
        }
        
        $phone = $options['phone_number'];
        $message = urlencode($options['message']);
        $button_text = $options['button_text'];
        $position = $options['position'];
        $button_color = $options['button_color'];
        $text_color = $options['text_color'];
        
        $whatsapp_url = "https://wa.me/{$phone}?text={$message}";
        
        ?>
        <div id="wpwhatsapp-chat" class="wpwhatsapp-<?php echo esc_attr($position); ?>" style="display: none;">
            <a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener" class="wpwhatsapp-button" 
               style="background-color: <?php echo esc_attr($button_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
                <span class="wpwhatsapp-icon">💬</span>
                <span class="wpwhatsapp-text"><?php echo esc_html($button_text); ?></span>
            </a>
        </div>
        
        <style>
            .wpwhatsapp-button {
                background-color: <?php echo esc_attr($button_color); ?> !important;
                color: <?php echo esc_attr($text_color); ?> !important;
            }
        </style>
        <?php
    }
    
    // Shortcode
    public function whatsapp_shortcode($atts) {
        $options = get_option('wpwhatsapp_settings');
        $atts = shortcode_atts(array(
            'phone' => $options['phone_number'],
            'message' => $options['message'],
            'text' => $options['button_text'],
            'color' => $options['button_color'],
            'text_color' => $options['text_color']
        ), $atts);
        
        $phone = $atts['phone'];
        $message = urlencode($atts['message']);
        $button_text = $atts['text'];
        $button_color = $atts['color'];
        $text_color = $atts['text_color'];
        
        $whatsapp_url = "https://wa.me/{$phone}?text={$message}";
        
        return sprintf(
            '<a href="%s" target="_blank" rel="noopener" class="wpwhatsapp-shortcode-button" style="background-color: %s; color: %s; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block;">%s</a>',
            esc_url($whatsapp_url),
            esc_attr($button_color),
            esc_attr($text_color),
            esc_html($button_text)
        );
    }
}

// Inicializar el plugin
new WPWhatsAppChat();
?>
