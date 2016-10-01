<?php

class PostPlayUploads {

    public function upload_dir($dirs) {
        $dirs['subdir'] = '/postplay';
        $dirs['path'] = $dirs['basedir'] . '/postplay';
        $dirs['url'] = $dirs['baseurl'] . '/postplay';

        return $dirs;
    }

    private function deleteCallbackKey($post_id, $key) {
        delete_post_meta($post_id, '_postplay_callback_key', $key);
    }

    public function downloadTheFile() {

        if (empty($_REQUEST['postplay_callback']) || $_REQUEST['postplay_callback'] != 'run')
            return;
        
        if(empty($_REQUEST['post_id']) || empty($_REQUEST['files'])){
            wp_send_json(array('status' => 'Bad Request'));
        }

        $post_id = $_REQUEST['post_id'];
        $key = get_post_meta($post_id, '_postplay_callback_key', TRUE);

        $return_obj = array('status' => 'success');

        if (empty($key)) {
            wp_send_json(array('status' => 'Callback key cannot be found!'));
        }

        if (!function_exists('media_handle_upload')) {
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        }

        $files = $_REQUEST['files'];
        foreach ($files as $the_file) {
            $file_return = array('status' => 'success');
            $file = $the_file['file_url'];
            $file_name = $the_file['file_name'];

            $url_with_key = sprintf(stripslashes($file), $key);

            $tmp = download_url($url_with_key . '/');
            $file_array = array(
                'name' => $file_name,
                'tmp_name' => $tmp
            );


            // Check for download errors
            if (is_wp_error($tmp)) {
                @unlink($file_array['tmp_name']);
                //wp_send_json(array('status' => 'error2', 'messages' => json_encode($tmp->get_error_message())));
                $file_return['status'] = 'error';
                $file_return['message'] = $tmp->get_error_message();
                $return_obj[] = $file_return;
                continue;
            }

            add_filter('upload_dir', array($this, 'upload_dir'));
            $att_id = media_handle_sideload($file_array, $post_id);
            remove_filter('upload_dir', array($this, 'upload_dir'));

            // Check for handle sideload errors.
            if (is_wp_error($att_id)) {
                @unlink($file_array['tmp_name']);
                //wp_send_json(array('status' => 'error3', 'messages' => json_encode($tmp->get_error_message())));
                $file_return['status'] = 'error';
                $file_return['message'] = $tmp->get_error_message();
                $return_obj[] = $file_return;
                continue;
            }

            if ($this->addAudioFiletoPost($post_id, $att_id)) {
                //$this->deleteCallbackKey($post_id, $key);
            }

            $return_obj[] = $file_return;
        }
        
        // Do whatever you have to here
        wp_send_json($return_obj);
    }

    private function addAudioFiletoPost($post_id, $attachment_id) {
        $current_data_str = get_post_meta($post_id, '_postplay_attachments', TRUE);
        if (empty($current_data_str))
            $current_data_str = serialize(array('attachments' => array()));

        $current_data = unserialize($current_data_str);

        $attachments_array = $current_data['attachments'];
        if (!in_array($attachment_id, $attachments_array)) {
            $attachments_array[] = intval($attachment_id);
        }

        $current_data['attachments'] = $attachments_array;

        if (update_post_meta($post_id, '_postplay_attachments', serialize($current_data))) {
            return TRUE;
        }
        return FALSE;
    }

}
