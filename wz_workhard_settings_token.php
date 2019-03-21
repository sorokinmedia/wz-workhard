<?php

add_action('admin_menu', 'wz_workhard_admin_settings_token');
add_action('admin_init', 'wz_workhard_settings_token');


function wz_workhard_admin_settings_token() {
    $parent_slug = 'wz-workhard';
    $page_title = 'Общая настройка Workhard';
    $menu_title= 'Общая настройка Workhard'; 
    $capability = 'manage_options';
    $menu_slug = 'wz-options-workhard-settings-token';
    $callback = 'wz_workhard_settings_token_callback';
    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback );
}

/*
 * Настройка workhard
 */
function wz_workhard_settings_token_callback() {
?>
    <div class="wrap">
        <h2>Настройка Workhard</h2>
        <p>Для дальнейшей работы с плагином Workhard добавьте токен и цену по умолчанию</p>
        <form method="POST" action="options.php" enctype="multipart/form-data">
            <?php settings_errors('wz-options-workhard-settings-token'); ?>
            <?php settings_fields('wz_options_group_workhard'); ?>
            <?php do_settings_sections('wz-options-workhard-settings-token'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

function wz_workhard_settings_token(){
    $option_group = 'wz_options_group_workhard';
    $option_name = 'wz_options_workhard';
    $option_sanitize = 'wz_options_sanitize_workhard';
    register_setting($option_group, $option_name, $option_sanitize);
    
    $section_id = 'wz_section_workhard';
    $section_title = '';
    $section_callback = '';
    $section_page = 'wz-options-workhard-settings-token';
    add_settings_section($section_id, $section_title, $section_callback, $section_page);
    
    $field_token_id = 'wz_field_token_workhard';
    $field_token_title = 'Токен*';
    $field_token_callback = 'wz_make_field_token_workhard';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
  
    $field_token_id = 'wz_field_price_task_creation_workhard';
    $field_token_title = 'Фикс ставка за создание задачи';
    $field_token_callback = 'wz_make_field_price_task_creation_workhard';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_price_article_creation_workhard';
    $field_token_title = 'Фикс ставка за создание плана статьи';
    $field_token_callback = 'wz_make_field_price_article_creation_workhard';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
    
    $field_token_id = 'wz_field_price_thousand_characters_workhard';
    $field_token_title = 'Фикс ставка за 1000 знаков';
    $field_token_callback = 'wz_make_field_price_thousand_characters_workhard';
    $field_token_page = $section_page;
    $field_token_section = $section_id;    
    add_settings_field($field_token_id, $field_token_title, $field_token_callback, $field_token_page, $field_token_section, array('label_for'=>$field_token_id));
      
}

function wz_make_field_token_workhard() {
    $options = get_option('wz_options_workhard');
        
    $html_code = '<input type="text" name="wz_options_workhard[token]" id="wz_field_token_workhard" value="'.esc_attr($options['token']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_price_task_creation_workhard() {
    $options = get_option('wz_options_workhard');
        
    $html_code = '<input type="text" name="wz_options_workhard[price_task_creation]" id="wz_field_price_task_creation_workhard" value="'.esc_attr($options['price_task_creation']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_price_article_creation_workhard() {
    $options = get_option('wz_options_workhard');
        
    $html_code = '<input type="text" name="wz_options_workhard[price_article_creation]" id="wz_field_price_article_creation_workhard" value="'.esc_attr($options['price_article_creation']).'" class="regular-text">';
    print($html_code);
}

function wz_make_field_price_thousand_characters_workhard() {
    $options = get_option('wz_options_workhard');
        
    $html_code = '<input type="text" name="wz_options_workhard[price_thousand_characters]" id="wz_field_price_thousand_characters_workhard" value="'.esc_attr($options['price_thousand_characters']).'" class="regular-text">';
    print($html_code);
}

function wz_options_sanitize_workhard($options){
    //exit(print_r($options));
    $token = trim($options['token']);
    $price = trim($options['price']);
    if(!empty($token)){
        $message = 'Настройка успешно сохранена!';
        $type = 'updated';
    } else{
        $message = 'Поле token не должен быть пустым';
        $type = 'error';
    }
    
    $options['price'] = wz_mb_str_replace('.', ',', $options['price']);
    $options['price_task_creation'] = wz_mb_str_replace('.', ',', $options['price_task_creation']);
    $options['price_article_creation'] = wz_mb_str_replace('.', ',', $options['price_article_creation']);
    $options['price_thousand_characters'] = wz_mb_str_replace('.', ',', $options['price_thousand_characters']);
    
    add_settings_error('wz-options-workhard-settings-token', 'wz_workhard_notice', $message, $type);
    return $options;
}

?>