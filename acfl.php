<?php
/*
Plugin Name: Advanced Custom Fields Limiter
Plugin URI: http://vinicius.soylocoporti.org.br/advanced-custom-fields-limiter-wordpress-plugin/
Description: Insert JavaScript character limiters in Advanced Custom Fields. You can set a limit for each field created in the 'Advanced Custom Fields' plugin, and a character counter will appear in the text input area of the admin interface.
Version: 0.02
Author: Vinicius Massuchetto
Author URI: http://vinicius.soylocoporti.org.br/
License: GPL
Copyright: Vinicius Massuchetto
*/

class Acfl {

    var $option_name;

    function Acfl() {
        global $acf;

        $this->option_name = 'acfl';

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_action_acfl_save', array( $this, 'admin_action_acfl_save' ) );
    }

	function admin_menu() {
		add_submenu_page( 'edit.php?post_type=acf', __('Advanced Custom Fields Limiters','acfl'), __('Limiters','acfl'), 'manage_options', 'acfl', array( $this, 'options_page' ) );
    }

    function admin_enqueue_scripts() {
        global $post;

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'acfl', plugins_url( 'acfl.js', __FILE__ ) );
        wp_localize_script( 'acfl', 'acfl', $this->get_limits_localization() );
        wp_enqueue_style( 'acfl', plugins_url( 'acfl.css', __FILE__ ) );

    }

    function get_limits_localization() {
        if ( !$fields = get_option( $this->option_name ) )
            return array();
        $limits = array();
        foreach( $fields as $field_id => $field_value ) {
            $limits['#acf-' . $field_id] = $field_value;
        }
        return $limits;
    }

    function get_character_field_types() {
        return array( 'text', 'textarea', 'number' );
    }

    function admin_action_acfl_save() {

        global $wpdb;

        if ( empty($_POST['fields'] ) )
            return false;

        $fields_option = array();
        foreach( $_POST['fields'] as $field_id => $field_value ) {
            if ( $field_value )
                $fields_option[ $field_id ] = preg_replace( '/[^0-9]/', '', $field_value );
        }
        if ( !empty( $fields_option ) )
            update_option( $this->option_name, $fields_option );

        header( 'Location:' . admin_url( 'edit.php?post_type=acf&page=acfl&updated' ) );

    }

    function options_page() {
        global $wpdb;
        ?>
        <div class="wrap">

            <?php if (isset($_GET['updated'])) : ?>
                <div class="updated">
                    <p><?php _e( 'Field limits successfully updated.', 'acfl' ); ?></p>
                </div>
            <?php endif; ?>

            <div class="icon32 icon32-posts-acf" id="icon-edit"><br></div>
            <h2><?php _e( 'Custom Fields Limiters', 'acfl' ); ?></h2>

            <p><?php _e( 'Set a character limit for each of the fields declared in Advanced Custom Fields. Leave it empty to not apply any limit.', 'acfl' ); ?></p>

            <form action="options.php" method="post">

            <input type="hidden" name="action" value="acfl_save" />

            <?php
                $character_fields = $this->get_character_field_types();
                $fields_option = get_option( $this->option_name );
                $groups = new WP_Query( array(
                    'post_type' => 'acf',
                    'posts_per_page' => -1
                ) );
            ?>

            <?php if ( $groups->have_posts() ) : ?>

                <?php while ( $groups->have_posts() ) : $groups->the_post(); ?>

                    <?php
                        $i = 0;
                        $sql = $wpdb->prepare ( "
                            SELECT
                                meta_key,
                                meta_value
                            FROM {$wpdb->postmeta}
                            WHERE 1=1
                                AND post_id = %d
                                AND meta_key LIKE %s
                        ", get_the_ID(), 'field\_%' );
                    ?>

                    <table class="form-table">

                    <?php foreach ( $wpdb->get_results( $sql ) as $field ) : ?>

                        <?php
                            $f = unserialize($field->meta_value);

                            if ( !in_array( $f['type'], $character_fields ) )
                                continue;

                            $field_limit = '';
                            if ( !empty( $fields_option[ $field->meta_key ] ) )
                                $field_limit = intval( $fields_option[ $field->meta_key ] );

                            $i++;
                        ?>

                        <?php if ( $i == 1 ) : ?>
                            <tr valig="top"><th colspan="2"><h3><?php the_title(); ?></h3></th></tr>
                        <?php endif; ?>

                        <tr valign="top">
                            <th scope="row"><label for="<?php echo $f['name']; ?>"><?php echo $f['label']; ?></label></th>
                            <td>
                                <input
                                    type="text"
                                    class="regular-text"
                                    id="<?php echo $f['name']; ?>"
                                    name="fields[<?php echo $field->meta_key; ?>]"
                                    value="<?php echo $field_limit; ?>" />
                                <?php if (!empty($f['instructions'])) : ?>
                                    <p class="description"><?php echo $f['instructions']; ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </table>

                <?php endwhile; ?>

            <?php else : ?>

                <p><?php _e('No custom fields defined.', 'acfl'); ?></p>

            <?php endif; ?>

            <?php submit_button(); ?>

            </form>

            <script type="text/javascript">

                jQuery('input[type="text"]').keyup(function(){
                    jQuery(this).val( jQuery(this).val().replace(/[^0-9]+/, '') );
                });

            </script>

        <?php
    }

}

function acfl_init() {
    $acfl = new Acfl();
}
add_action('plugins_loaded', 'acfl_init', 999);

?>
