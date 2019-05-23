<?php

add_action('admin_menu', 'wz_workhard_admin_google_spreadsheet');
add_action('admin_init', 'wz_workhard_settings_google_spreadsheet');


function wz_workhard_admin_google_spreadsheet() {
    $parent_slug = 'wz-workhard';
    $page_title = 'Настройка гугл таблицы';
    $menu_title= 'Настройка гугл таблицы'; 
    $capability = 'manage_options';
    $menu_slug = 'wz-options-workhard-settings-google-spreadsheet';
    $callback = 'wz_workhard_settings_google_spreadsheet_callback';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
}

function wz_workhard_settings_google_spreadsheet_callback() {
?>
    <div class="wrap">
        <h2>Настройка Гугл таблицы</h2>
        <p>Заполните данные для работы с гугл таблицей</p>
        <form method="POST" action="options.php" enctype="multipart/form-data">
            <?php settings_errors('wz-options-workhard-settings-google-spreadsheet'); ?>
            <?php settings_fields('wz_options_group_google_spreadsheet'); ?>
            <?php do_settings_sections('wz-options-workhard-settings-google-spreadsheet'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function wz_workhard_settings_google_spreadsheet(){
    $option_group = 'wz_options_group_google_spreadsheet';
    $option_name = 'wz_options_google_spreadsheet';
    $option_sanitize = 'wz_options_sanitize_google_spreadsheet';
    register_setting($option_group, $option_name, $option_sanitize);
    
    $section_id = 'wz_section_google_spreadsheet';
    $section_title = '';
    $section_callback = '';
    $section_page = 'wz-options-workhard-settings-google-spreadsheet';
    add_settings_section($section_id, $section_title, $section_callback, $section_page);
    
    $field_token_id = 'wz_field_id_google_spreadsheet';
    $field_token_title = 'Введите id документа*';
    $field_token_callback = 'wz_make_field_id_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_sheetname_google_spreadsheet';
    $field_token_title = 'Введите название таблицы (для отчета опубликованных статей)*';
    $field_token_callback = 'wz_make_field_sheetname_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_enable_additional_statistics_google_spreadsheet';
    $field_token_title = 'Включить подгрузку дополнительной статистики*';
    $field_token_callback = 'wz_make_field_enable_additional_statistics_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_sheetname_additional_statistics_google_spreadsheet';
    $field_token_title = 'Введите название таблицы (дополнительной статистики)';
    $field_token_callback = 'wz_make_field_sheetname_additional_statistics_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_sheetname_billing_google_spreadsheet';
    $field_token_title = 'Введите название таблицы (Биллинга)';
    $field_token_callback = 'wz_make_field_sheetname_billing_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_sheetname_cost_entry_google_spreadsheet';
    $field_token_title = 'Введите название таблицы (Занесение расходов)';
    $field_token_callback = 'wz_make_field_sheetname_cost_entry_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    
    $field_token_id = 'wz_field_json_auth_google_spreadsheet';
    $field_token_title = 'Загрузите json файл';
    $field_token_callback = 'wz_make_field_json_auth_google_spreadsheet';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
      
}

function wz_make_field_id_google_spreadsheet() {
    $options = get_option('wz_options_google_spreadsheet');
        
    $html_code = '<input type="text" name="wz_options_google_spreadsheet[id]" id="wz_field_id_google_spreadsheet" value="'.esc_attr($options['id']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_sheetname_google_spreadsheet() {
    $options = get_option('wz_options_google_spreadsheet');
        
    $html_code = '<input type="text" name="wz_options_google_spreadsheet[sheetname]" id="wz_field_sheetname_google_spreadsheet" value="'.esc_attr($options['sheetname']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_enable_additional_statistics_google_spreadsheet() {
    $options = get_option('wz_options_google_spreadsheet');
    $checked = $options['enable_additional_statistics'] ? 'checked' : '';
    
    $html_code = '<label for="wz_field_enable_additional_statistics_google_spreadsheet">';
    $html_code .= '<input name="wz_options_google_spreadsheet[enable_additional_statistics]" id="wz_field_enable_additional_statistics_google_spreadsheet" ' . $checked . ' value="1" type="checkbox">';
    $html_code .= 'Включить дополнительную статистику экспорта с интервалом каждый час</label>';
    print($html_code);
}

function wz_make_field_sheetname_additional_statistics_google_spreadsheet() {
    $options = get_option('wz_options_google_spreadsheet');
        
    $html_code = '<input type="text" name="wz_options_google_spreadsheet[sheetname_additional_statistics]" id="wz_field_sheetname_additional_statistics_google_spreadsheet" value="'.esc_attr($options['sheetname_additional_statistics']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_sheetname_billing_google_spreadsheet() {
     $options = get_option('wz_options_google_spreadsheet');
        
    $html_code = '<input type="text" name="wz_options_google_spreadsheet[sheetname_billing]" id="wz_field_sheetname_billing_google_spreadsheet" value="'.esc_attr($options['sheetname_billing']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_sheetname_cost_entry_google_spreadsheet() {
    $options = get_option('wz_options_google_spreadsheet');
        
    $html_code = '<input type="text" name="wz_options_google_spreadsheet[sheetname_cost_entry]" id="wz_field_sheetname_cost_entry_google_spreadsheet" value="'.esc_attr($options['sheetname_cost_entry']).'" class="regular-text">';
    print($html_code);
}


function wz_make_field_json_auth_google_spreadsheet() {
    $options = get_option('wz_options_google_spreadsheet');
        
    $html_code = '<input type="file" name="json_auth" id="wz_field_json_auth_google_spreadsheet" class="regular-text">';
    $html_code .= '<br>';
    
    if (!empty($options['json_auth'])) {
        $json_data = json_decode($options['json_auth'], true);
        
        $html_code .= '<ul>';
        foreach($json_data as $key => $value) {
            $html_code .= '<li>' . $key . ': ' . $value;
        }
        $html_code .= '</ul>';
        
    } else {
        $html_code .= 'JSON файл не загружен';
    }
    
    print($html_code);
}

function wz_options_sanitize_google_spreadsheet($options){
    $old_options = get_option('wz_options_google_spreadsheet');
    
    // Загрузка данных с json файла
    $upfile_type = $_FILES['json_auth']['type'];
    $valid_json_data = true;
    if(!empty($_FILES['json_auth']) && $upfile_type == 'application/json'){
        $uploaded_file = file_get_contents($_FILES['json_auth']['tmp_name']);
        $json_data = json_decode($uploaded_file, true);
        $valid_json_data = wz_validate_google_json_auth($json_data);
        if ($valid_json_data) {
            $options['json_auth'] = $uploaded_file;
        }
    } else {
        $options['json_auth'] = $old_options['json_auth'];
    }
    
    // Присваивание опции вкл/выкл доп статистики
    // При включении доп статистики запустить крон для автовыгрузки новых записей
    // При выключении отключить доп статистику
    if ( isset($_POST['wz_options_google_spreadsheet']['enable_additional_statistics']) ) {
        $options['enable_additional_statistics'] = 1;
        
        wz_run_schedule_addional_statistics('hourly');
    } else {
        $options['enable_additional_statistics'] = 0;
        wz_clear_scheduled_additional_statistics('hourly');
        wz_clear_scheduled_additional_statistics('5seconds');
    }
    
    $id = trim($options['id']);
    $sheetname = trim($options['sheetname']);

    if(empty($id) || empty($sheetname) || !$valid_json_data){
        $type = 'error';
        if (empty($id)) {
            $message = 'Поле "id документа" не должен быть пустым.';
            add_settings_error('wz-options-workhard-settings-google-spreadsheet', 'wz_workhard_notice', $message, $type);
        }
        
        if (empty($sheetname)) {
            $message = 'Поле "название таблицы" не должен быть пустыми.';
            add_settings_error('wz-options-workhard-settings-google-spreadsheet', 'wz_workhard_notice', $message, $type);
        }
        
        if (!$valid_json_data) {
            $message = 'Загруженный json файл не валиден!';
            add_settings_error('wz-options-workhard-settings-google-spreadsheet', 'wz_workhard_notice', $message, $type);
        }
    } else{        
        $message = 'Настройка успешно сохранена!';
        $type = 'updated';
        add_settings_error('wz-options-workhard-settings-google-spreadsheet', 'wz_workhard_notice', $message, $type);
    }
    
    
    return $options;
}

?>
