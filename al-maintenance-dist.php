<?php
/**
 * Plugin Name: WP maintenance by AzzLabs
 * Description: Attivare e disattivare la modalità manutenzione su wordpress
 * Version: 1.0
 * Author: azzari.dev
 * Author URI: https://azzari.dev
 */
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

function simple_maintenace_mode() {
    if (get_option('alwp_enabled')) {
        if (!is_user_logged_in() && !is_wplogin()) {
            $css = sprintf('html { background-color: %s; } body { color: %s; background: %s; border-color: %s }',
                get_option('alwp_color_html'), get_option('alwp_color_text'), get_option('alwp_color_box'), get_option('alwp_color_borderbox'));
            $css = sprintf('<style>%s</style>', $css);
            wp_die($css . get_option('alwp_page_content'), 'In manutenzione! - ' . get_bloginfo('name'));
        }
    }
}
add_action('init', 'simple_maintenace_mode');

function alwp_add_menu_entry() { 
    // Registra la voce menu
    add_submenu_page('tools.php', 'Modalità manutenzione', 'Manutenzione', 'administrator', 'alwp-maintenance', 'alwp_settings_page');
    // Registra i campi delle impostazioni di WP
    add_action('admin_init', 'alwp_register_settings');
    // Aggiunge i custom script e stili
    add_action('admin_enqueue_scripts', 'alwp_enqueue_scripts');
}

function alwp_register_settings() {
    $def_content = '<h1>Il sito web è attualmente in manutenzione<h1><p>Torna tra qualche minuto!</p>';
	register_setting('alwp_settings_group', 'alwp_page_content', ['type' => 'string', 'default' => $def_content]);
	register_setting('alwp_settings_group', 'alwp_enabled', ['type' => 'boolean', 'default' => false, 'sanitize_callback' => 'alwp_sanitize_cb']);
	register_setting('alwp_settings_group', 'alwp_color_html', ['type' => 'string', 'default' => '#f1f1f1']);
	register_setting('alwp_settings_group', 'alwp_color_text', ['type' => 'string', 'default' => '#444']);
	register_setting('alwp_settings_group', 'alwp_color_box', ['type' => 'string', 'default' => '#ffffff']);
	register_setting('alwp_settings_group', 'alwp_color_borderbox', ['type' => 'string', 'default' => '#ccd0d4']);
}
function alwp_sanitize_cb($string) {
    return $string == 'true';
}

add_action('admin_menu', 'alwp_add_menu_entry');

function alwp_settings_page() { ?>
<div class="wrap">
    <h1>Modalità manutenzione</h1>

    <form method="post" action="options.php">
        <?php settings_fields('alwp_settings_group'); ?>
        <?php do_settings_sections('alwp_settings_group'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo __('Stato modalità manutanzione', 'mcit') ?></th>
                <td>
                    <input type="hidden" name="alwp_enabled" value="false">
                    <input type="checkbox" name="alwp_enabled" id="mcit_cbinfo" value="true" <?= get_option('alwp_enabled') ? 'checked' : ''; ?> />
                    <label for="mcit_cbinfo">
                        <?php echo __('Abilita modalità manutenzione', 'mcit') ?>
                    </label>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo __('Contenuto pagina manutanzione', 'mcit') ?></th>
                <td>
                    <?php 
                        $css = sprintf('html { background-color: %s; } body { color: %s; background: %s; border-color: %s }',
                            get_option('alwp_color_html'), get_option('alwp_color_text'), get_option('alwp_color_box'), get_option('alwp_color_borderbox'));
                        wp_editor(get_option('alwp_page_content'), 'alwp_content_editor', [
                            'textarea_name' => 'alwp_page_content',
                            'tinymce' => ['content_css' => plugin_dir_url(__FILE__) . '/default-style.css', 'content_style' => $css]] );
                    ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Colori della pagina', 'mcit') ?></th>
                <td>
                    <p class="description"><?php _e('Sfondo della pagina', 'mcit') ?></p>
                    <input class="color_field" type="text" name="alwp_color_html" value="<?= get_option('alwp_color_html') ?>" /> 
                    <p class="description"><?php _e('Colore del testo', 'mcit') ?></p>
                    <input class="color_field" type="text" name="alwp_color_text" value="<?= get_option('alwp_color_text') ?>" /> 
                    <p class="description"><?php _e('Sfondo del box', 'mcit') ?></p>
                    <input class="color_field" type="text" name="alwp_color_box" value="<?= get_option('alwp_color_box') ?>" /> 
                    <p class="description"><?php _e('Bordo del box', 'mcit') ?></p>
                    <input class="color_field" type="text" name="alwp_color_borderbox" value="<?= get_option('alwp_color_borderbox') ?>" /> 
                </td>
            </tr>
        </table>
        
        <script>
            jQuery(document).ready(function($){
                $('.color_field').wpColorPicker();
            });
        </script>

        <?php submit_button(__('Salva la configurazione', 'mcit')); ?>
    </form>
</div>
<?php } 

function alwp_enqueue_scripts($hook) {
    if ($hook == 'tools_page_alwp-maintenance') {
        // Aggiunge il color picker di WordPress
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
}

function is_wplogin(){
    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || (isset($_SERVER['REQUEST_URI']) && ($_SERVER['REQUEST_URI'] === '/wp-login.php' || $_SERVER['REQUEST_URI'] === '/login')) || $_SERVER['PHP_SELF']== '/wp-login.php' || is_page('login'));
}

?>
