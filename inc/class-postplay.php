<?php

class PostPlay {

    private $__file;

    public function PostPlay($file) {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_head', array($this, 'custom_css'));
        add_shortcode('postplay-player', array($this, 'player_shortcode'));

        new PostPlayMetabox();
        $this->defineProperties($file);

        add_action('init', array(new PostPlayUploads(), 'downloadTheFile'));
        add_filter('the_content', array($this, 'post_content_filter'));
    }

    /*
     * 
     * Define properties
     * 
     */

    private function defineProperties($file) {
        define('POSTPLAY_LANG_SLUG', '');
        $this->__file = $file;
    }

    /*
     * 
     * Enqueue back end scripts
     * 
     */

    function enqueue_admin_styles() {
        wp_enqueue_style('postplay-admin', plugin_dir_url($this->__file) . 'inc/css/postplay-admin.css', false, '1.0.0');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('postplay-admin', plugins_url('inc/js/postplay-admin.js', $this->__file), array('wp-color-picker'), false, true);
    }

    /*
     * 
     * Enqueue front end scripts
     * 
     */

    function enqueue_styles() {
        wp_enqueue_style('postplay', plugin_dir_url($this->__file) . 'inc/css/postplay.css', false, '1.0.0');
    }

    /*
     * 
     * Append player to post
     * 
     */

    function post_content_filter($content) {

        // Skip if auto publish is OFF
        if (get_option('_postplay_autopublish') !== 'yes')
            return $content;

        $mods = $this->get_player_content();

        if (!$mods) {
            return $content;
        }
        $_postplay_player_position = get_option('_postplay_player_position', 'top');
        if ($_postplay_player_position == 'top') {
            return $mods . $content;
        }
        return $content . $mods;
    }
    
    /*
     * 
     * Generate player HTML
     * 
     */

    function get_player_content() {
        $current_data_str = get_post_meta(get_the_ID(), '_postplay_attachments', TRUE);
        $current_data = unserialize($current_data_str);
        $attachments_array = $current_data['attachments'];
        if (empty($attachments_array) || !is_array($attachments_array)) {
            return FALSE;
        }

        ob_start();
        ?>
        <ul class="postplay-audio-players">
            <?php
			$atta = end($attachments_array);
            //foreach ($attachments_array as $atta):
                $attachment_url = wp_get_attachment_url($atta);
                if ($attachment_url == false)
                    continue;
                echo '<li>';
                $attr = array(
                    'src' => $attachment_url,
                    'loop' => '',
                    'autoplay' => '',
                    'preload' => 'none'
                );
                echo wp_audio_shortcode($attr);
                echo '</li>';
            //endforeach;
            ?>
        </ul>
        <?php
        $mods = ob_get_clean();
        return $mods;
    }

    /*
     * 
     * Add custom CSS
     * 
     */

    function custom_css() {
        $_postplay_player_color = get_option('_postplay_player_color');
        if (empty($_postplay_player_color))
            return;
        ?>
        <style>
            .postplay-audio-players .mejs-container, .mejs-embed, .mejs-embed body, .postplay-audio-players .mejs-container .mejs-controls, .mejs-container, .mejs-embed, .mejs-embed body, .mejs-container .mejs-controls{<?php echo 'background: ' . $_postplay_player_color . ';'; ?>}
        </style>
        <?php
    }

    /*
     * 
     * Perform the shortcode
     * 
     */

    function player_shortcode($atts) {
        $a = shortcode_atts(array(
            'foo' => 'something',
            'bar' => 'something else',
                ), $atts);
        
        $player_c = $this->get_player_content();

        return $player_c;
    }

}
