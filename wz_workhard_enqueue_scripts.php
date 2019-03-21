<?php

add_action( 'admin_enqueue_scripts', 'wz_workhard_enqueue_scripts' );

function wz_workhard_enqueue_scripts( $hook_suffix ){
	if ($hook_suffix == 'toplevel_page_wz-workhard') {
        
        wp_register_style( 'wz_workhard_style_dialog', plugins_url('css/dialog.css', __FILE__) );
        wp_enqueue_style('wz_workhard_style_dialog');
        
        wp_register_script('wz_workhard_axios', 'https://unpkg.com/axios@0.18.0/dist/axios.min.js', false, true);
        wp_enqueue_script('wz_workhard_axios');
        wp_register_script('wz_workhard_vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.5.17', false, true);
        wp_enqueue_script('wz_workhard_vuejs');
        
        wp_register_script('wz_workhard_vue_router', 'https://unpkg.com/vue-router@3.0.1/dist/vue-router.min.js', false, true);
        wp_enqueue_script('wz_workhard_vue_router');
        
        wp_register_script('wz_workhard_howler', 'https://cdnjs.cloudflare.com/ajax/libs/howler/2.0.3/howler.min.js', false, true);
        wp_enqueue_script('wz_workhard_howler');
        
        wp_register_script('wz_workhard_push', 'https://cdnjs.cloudflare.com/ajax/libs/push.js/0.0.13/push.min.js', false, true);
        wp_enqueue_script('wz_workhard_push');
                        
        wp_register_script('wz_workhard_dialog', plugins_url('js/dialog.js', __FILE__), array(
            'wz_workhard_axios', 
            'wz_workhard_vuejs', 
            'wz_workhard_vue_router',
            'wz_workhard_howler',
            'wz_workhard_push'
        ), 3, true);
        wp_enqueue_script('wz_workhard_dialog');
        
        $workhard_vars = array(
            'admin_post' => esc_url( admin_url('admin-ajax.php') ),
            'filepath_sound' => plugins_url('audio/alarmwatch', __FILE__),
            'nonce' => wp_create_nonce('workhard_ajax')
        );

        wp_localize_script( 'wz_workhard_dialog', 'WORKHARD', $workhard_vars );
        
    } elseif ($hook_suffix == 'workhard_page_wz-options-workhard-settings-users') {
        wp_register_style( 'wz_workhard_style_users', plugins_url('css/users.css', __FILE__) );
        wp_enqueue_style('wz_workhard_style_users');
        
        wp_register_script('wz_workhard_axios', 'https://unpkg.com/axios@0.18.0/dist/axios.min.js', false, true);
        wp_enqueue_script('wz_workhard_axios');
        
        wp_register_script('wz_workhard_vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.5.17', false, true);
        wp_enqueue_script('wz_workhard_vuejs');
                                
        wp_register_script('wz_workhard_users', plugins_url('js/users.js', __FILE__), array(
            'wz_workhard_axios', 
            'wz_workhard_vuejs', 
        ), 5, true);
        wp_enqueue_script('wz_workhard_users');
        
        $workhard_vars = array(
            'admin_post' => esc_url( admin_url('admin-ajax.php') ),
            'nonce' => wp_create_nonce('workhard_ajax')
        );

        wp_localize_script( 'wz_workhard_users', 'WORKHARD', $workhard_vars );
        
    } elseif ($hook_suffix == 'workhard_page_wz-options-workhard-articles') {
        
        wp_register_style( 'wz_workhard_style_swal', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css' );
        wp_enqueue_style('wz_workhard_style_swal');
        
        wp_register_style( 'wz_workhard_style_articles', plugins_url('css/articles.css', __FILE__) );
        wp_enqueue_style('wz_workhard_style_articles');
        
        wp_register_script('wz_workhard_axios', 'https://unpkg.com/axios@0.18.0/dist/axios.min.js', false, true);
        wp_enqueue_script('wz_workhard_axios');
                
        wp_register_script('wz_workhard_vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.5.17', false, true);
        wp_enqueue_script('wz_workhard_vuejs');
        
        wp_register_script('wz_workhard_vue_router', 'https://unpkg.com/vue-router@3.0.1/dist/vue-router.min.js', false, true);
        wp_enqueue_script('wz_workhard_vue_router');
        
        wp_register_script('wz_workhard_swal', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js', false, true);
        wp_enqueue_script('wz_workhard_swal');
        
        wp_register_script('wz_workhard_underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js', false, true);
        wp_enqueue_script('wz_workhard_underscore');
                                
        wp_register_script('wz_workhard_articles', plugins_url('js/articles.js', __FILE__), array(
            'wz_workhard_axios', 
            'wz_workhard_vue_router', 
            'wz_workhard_vuejs', 
            'wz_workhard_swal',
            'wz_workhard_underscore'
        ), 8, true);
        wp_enqueue_script('wz_workhard_articles');
        
        $workhard_vars = array(
            'admin_post' => esc_url( admin_url('admin-ajax.php') ),
            'nonce' => wp_create_nonce('workhard_ajax')
        );
        wp_localize_script( 'wz_workhard_articles', 'WORKHARD', $workhard_vars );
        
    } elseif ($hook_suffix == 'workhard_page_wz-options-workhard-statistics') {
        
        wp_register_style( 'wz_workhard_style_swal', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css' );
        wp_enqueue_style('wz_workhard_style_swal');
        
        wp_register_style( 'wz_workhard_style_statistics', plugins_url('css/statistics.css', __FILE__) );
        wp_enqueue_style('wz_workhard_style_statistics');
        
        wp_register_script('wz_workhard_axios', 'https://unpkg.com/axios@0.18.0/dist/axios.min.js', false, true);
        wp_enqueue_script('wz_workhard_axios');
        
        wp_register_script('wz_workhard_vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.5.17', false, true);
        wp_enqueue_script('wz_workhard_vuejs');
        
        wp_register_script('wz_workhard_underscore', 'https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js', false, true);
        wp_enqueue_script('wz_workhard_underscore');
        
        wp_register_script('wz_workhard_swal', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js', false, true);
        wp_enqueue_script('wz_workhard_swal');

        wp_register_script('wz_workhard_statistics', plugins_url('js/gl-st.js', __FILE__), array(
            'wz_workhard_axios', 'wz_workhard_vuejs', 'wz_workhard_underscore', 'wz_workhard_swal'
        ), 4, true);
        wp_enqueue_script('wz_workhard_statistics');
        
        $workhard_vars = array(
            'admin_post' => esc_url( admin_url('admin-ajax.php') ),
            'nonce' => wp_create_nonce('workhard_ajax')
        );
        wp_localize_script( 'wz_workhard_statistics', 'WORKHARD', $workhard_vars );
    } elseif ($hook_suffix == 'workhard_page_wz-options-workhard-spreadsheet-users') {
        wp_register_style( 'wz_workhard_style_swal', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css' );
        wp_enqueue_style('wz_workhard_style_swal');
        
        wp_register_style( 'wz_workhard_style_spreadsheet_users', plugins_url('css/spreadsheet_users.css', __FILE__) );
        wp_enqueue_style('wz_workhard_style_spreadsheet_users');
        
        wp_register_script('wz_workhard_axios', 'https://unpkg.com/axios@0.18.0/dist/axios.min.js', false, true);
        wp_enqueue_script('wz_workhard_axios');
        
        wp_register_script('wz_workhard_vuejs', 'https://cdn.jsdelivr.net/npm/vue@2.5.17', false, true);
        wp_enqueue_script('wz_workhard_vuejs');
        
        wp_register_script('wz_workhard_swal', 'https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js', false, true);
        wp_enqueue_script('wz_workhard_swal');
                
        wp_register_script('wz_workhard_spreadsheet_users', plugins_url('js/spreadsheet_users.js', __FILE__), array(
            'wz_workhard_axios', 'wz_workhard_vuejs', 'wz_workhard_swal'
        ), 4, true);
        wp_enqueue_script('wz_workhard_spreadsheet_users');
        
        $workhard_vars = array(
            'admin_post' => esc_url( admin_url('admin-ajax.php') ),
            'nonce' => wp_create_nonce('workhard_ajax')
        );
        wp_localize_script( 'wz_workhard_spreadsheet_users', 'WORKHARD', $workhard_vars );
    }
}

