<?php

add_action('admin_menu', 'wz_workhard_admin_settings_users');
add_action('admin_init', 'wz_workhard_settings_users');

add_action('wp_ajax_wz_workhard_ajax_users', 'wz_workhard_ajax_users');

function wz_workhard_admin_settings_users() {
    $parent_slug = 'wz-workhard';
    $page_title = 'Настройка доступа к определенным папкам';
    $menu_title= 'Настройка доступа к определенным папкам'; 
    $capability = 'manage_options';
    $menu_slug = 'wz-options-workhard-settings-users';
    $callback = 'wz_workhard_settings_users_callback';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
}

function wz_workhard_settings_users_callback() {
    $users = get_users();
    $token = get_option('wz_options_workhard')['token'];
    $categories = get_categories(array('hide_empty' => 0));
    $folders = wzz_fetch_folders_array($token);
?>
    <div class="wrap">
        <div id="app">
            <h2>Настройка доступа к определенным папкам</h2>
            <form @submit="onSubmit">
                <p>Выберете автора, чтобы выдать доступ к определенным папкам:</p>
                <select v-model="selectedUser" @change="onChangeUser">
                    <option :value="null">Выберете пользователя</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user->ID ?>"><?php echo $user->display_name; ?></option>
                    <?php endforeach; ?>
                </select>
                <hr>

                <p>Добавьте правило к определенным папкам для пользователя:</p>
                <p>Из</p>
                <select v-model="selectedFromFolder">
                    <option :value="null">Выберете папку</option>
                    <?php foreach ($folders['response'] as $folder): ?>
                    <option :value="{folder_id: '<?php echo $folder['id']; ?>', folder_name: '<?php echo $folder['name']; ?>'}"><?php echo $folder['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <p>В</p>
                 <select v-model="selectedToFolder">
                    <option :value="null">Выберете папку</option>
                    <?php foreach ($folders['response'] as $folder): ?>
                    <option :value="{folder_id: '<?php echo $folder['id']; ?>', folder_name: '<?php echo $folder['name']; ?>'}"><?php echo $folder['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <p>Категория</p>
                 <select v-model="selectedCategory">
                    <option :value="null">Выберете рубрику</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category->cat_ID; ?>"><?php echo $category->name; ?></option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <button class="button button-primary" :disabled="!validateForm">Сохранить</button>
            </form>
            <hr>
            
            <p>Список правил:</p>
            <div v-for="item in permissionFolders" :key="item.id"><span class="wz_from_to">{{ item.from_folder_name }} -> {{ item.to_folder_name }} -> {{ item.category_name }}</span>
                <button @click="deletePermissionFolders(item.id)" class="wz_close_button">Удалить</button>
            </div>
        </div>
        
        <hr>
        
        <div>
            <h3>Настройка переноса Заголовка и описание в Yoast Seo</h3>
            <?php if (!is_plugin_active( 'wordpress-seo/wp-seo.php' )):?>
            <p class="wz-error">Плагин Yoast Seo не установлен или не активен.</p>
            <?php else: ?>
            <form method="POST" action="options.php" enctype="multipart/form-data">
                <?php settings_errors('wz-options-workhard-settings-users'); ?>
                <?php settings_fields('wz_options_group_users'); ?>
                <?php do_settings_sections('wz-options-workhard-settings-users'); ?>
                <?php submit_button(); ?>
            </form>
      
            <?php endif; ?>
        </div>
        
    </div>
<?php
}

function wz_workhard_settings_users(){
    $option_group = 'wz_options_group_users';
    $option_name = 'wz_options_users';
    $option_sanitize = 'wz_options_sanitize_users';
    register_setting($option_group, $option_name, $option_sanitize);
    
    $section_id = 'wz_section_users';
    $section_title = '';
    $section_callback = '';
    $section_page = 'wz-options-workhard-settings-users';
    add_settings_section($section_id, $section_title, $section_callback, $section_page);
    
    $field_token_id = 'wz_field_yoast_title';
    $field_token_title = 'Заголовок';
    $field_token_callback = 'wz_make_field_yoast_title';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_yoast_description';
    $field_token_title = 'Описание';
    $field_token_callback = 'wz_make_field_yoast_description';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
          
}

function wz_make_field_yoast_title() {
    $options = get_option('wz_options_users');
    $checked = $options['yoast_title'] ? 'checked' : '';
    
    $html_code = '<label for="wz_field_yoast_title">';
    $html_code .= '<input type="checkbox" name="wz_options_users[yoast_title]" id="wz_field_yoast_title" ' . $checked . ' value="'.esc_attr($options['yoast_title']).'" class="regular-text">';
    $html_code .= 'Включить перенос "Название заказа" в заголовок Yoast Seo</label>';
    print($html_code);
}

function wz_make_field_yoast_description() {
    $options = get_option('wz_options_users');
    $checked = $options['yoast_description'] ? 'checked' : '';
    
    $html_code = '<label for="wz_field_yoast_description">';
    $html_code .= '<input type="checkbox" name="wz_options_users[yoast_description]" id="wz_field_yoast_description" ' . $checked . ' value="'.esc_attr($options['yoast_description']).'" class="regular-text">';
    $html_code .= 'Включить перенос "Анонс" в метаописание Yoast Seo</label>';
    print($html_code);
}

function wz_options_sanitize_users($options){
    if ( isset($_POST['wz_options_users']['yoast_title']) ) {
        $options['yoast_title'] = 1;
    } else {
        $options['yoast_title'] = 0;
    }
    
    if ( isset($_POST['wz_options_users']['yoast_description']) ) {
        $options['yoast_description'] = 1;
    } else {
        $options['yoast_description'] = 0;
    }
    return $options;
}



function wz_workhard_ajax_users() {   
    if(!wp_verify_nonce($_REQUEST['nonce'], 'workhard_ajax')){
        echo( json_encode( array('status' => 'error' ) ) );
        wp_die();
    }
            
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (wzz_validate_params(array('method', 'user_id')) && $_GET['method'] == 'fetch_permission_folders') {
            echo json_encode(wz_fetch_permission_folders_db($_GET['user_id']));
            wp_die();
        } 
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (
            wzz_validate_params(
                array('method', 'user_id', 'from_folder_id', 'from_folder_name', 'to_folder_id', 'to_folder_name', 'category_id') 
            )
            && $_POST['method'] == 'insert_permission_folders'
            
        ) {
            $result = wz_insert_permission_folders_db(
                $_POST['user_id'], 
                $_POST['from_folder_id'], 
                $_POST['from_folder_name'], 
                $_POST['to_folder_id'],
                $_POST['to_folder_name']
            );
                        
            wz_insert_permission_categories_db($result['id'], $_POST['category_id']);
            
            echo json_encode($result);
            wp_die();
        } elseif (wzz_validate_params(array('method', 'id')) && $_POST['method'] == 'delete_permission_folders') {
            $result = wz_delete_permission_folders_db($_POST['id']);
            echo json_encode($result);
            wp_die();
        }
    }
}

