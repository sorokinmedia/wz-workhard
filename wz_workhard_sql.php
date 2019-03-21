<?php

/*
 * Создать таблицу для доступа пользователям вп к папкам workhard
 *
 * @return void
 */
function wz_create_table_folders() {
    global $wpdb;
    $query = "CREATE TABLE IF NOT EXISTS wz_workhard_permission_folders (";
    $query .= " `id` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL";
    $query .= ", `user_id` INT(11) NOT NULL";
    $query .= ", `from_folder_name` VARCHAR(255) NOT NULL";
    $query .= ", `to_folder_name` VARCHAR(255) NOT NULL";
    $query .= ", `from_folder_id` INT(11) NOT NULL";
    $query .= ", `to_folder_id` INT(11) NOT NULL";
    $query .= ") ENGINE = InnoDB;";
    $wpdb->query($query);
    
    $query = "CREATE TABLE IF NOT EXISTS wz_workhard_permission_categories (";
    $query .= "`id` INT(11) AUTO_INCREMENT PRIMARY KEY NOT NULL, ";
    $query .= "`permission_folder_id` INT(11) NOT NULL,";
    $query .= "`category_id` INT(11) NOT NULL";
    $query .= ") ENGINE = InnoDB;";
    $wpdb->query($query);
}

/*
 * Получить доступы к папкам workhard в котором пользователю вп разрешено работать
 *
 * @param int $user_id
 * @return array
 */
function wz_fetch_permission_folders_db($user_id) {
    global $wpdb;
    
    $subquery_category_name = "(SELECT {$wpdb->prefix}terms.name";
    $subquery_category_name .= " FROM {$wpdb->prefix}terms";
    $subquery_category_name .= " WHERE {$wpdb->prefix}terms.term_id = wz_workhard_permission_categories.category_id";
    $subquery_category_name .= " LIMIT 1) as category_name";

    
    $query = "SELECT wz_workhard_permission_folders.id, wz_workhard_permission_folders.user_id, wz_workhard_permission_folders.from_folder_name, wz_workhard_permission_folders.to_folder_name, wz_workhard_permission_folders.from_folder_id, wz_workhard_permission_folders.to_folder_id, wz_workhard_permission_categories.category_id,  {$subquery_category_name}";
    $query .= ' FROM wz_workhard_permission_folders';
    $query .= " LEFT JOIN wz_workhard_permission_categories";
    $query .= " ON wz_workhard_permission_folders.id = wz_workhard_permission_categories.permission_folder_id";
    $query .= " WHERE wz_workhard_permission_folders.user_id = %d;";
    return $wpdb->get_results($wpdb->prepare($query, $user_id));
}

/*
 * Вставить правило доступа к папкам workhard в котором пользователю вп разрешено работать
 *
 * @param int $user_id
 * @param int $from_folder_id
 * @param int $to_folder_id
 * @param int $from_folder_name
 * @param int $to_folder_name
 * @return array
 */
function wz_insert_permission_folders_db($user_id, $from_folder_id, $from_folder_name, $to_folder_id, $to_folder_name) {
    global $wpdb;
    
    $wpdb->insert(
        'wz_workhard_permission_folders', 
        array(
            'user_id'=>$user_id, 
            'from_folder_id'=>$from_folder_id, 
            'from_folder_name'=>$from_folder_name, 
            'to_folder_id'=>$to_folder_id,
            'to_folder_name'=>$to_folder_name
        )
    );
    return array('id'=> $wpdb->insert_id);
}

/*
 * Вставить правило доступа к рубрикам
 *
 * @param int permission_folder_id - id таблицы wz_workhard_permission_folders
 * @param int category_id - id Рубрики WP
 * @return array
 */
function wz_insert_permission_categories_db($permission_folder_id, $category_id) {
    global $wpdb;
    
    $wpdb->insert(
        'wz_workhard_permission_categories', 
        array(
            'permission_folder_id'=>$permission_folder_id, 
            'category_id'=>$category_id, 
        )
    );
    return array('id'=> $wpdb->insert_id);
}

/*
 * Удалить правило доступа к папкам workhard в котором пользователю вп разрешено работать
 *
 * @param int $id номер записи
 * @return array
 */
function wz_delete_permission_folders_db($id) {
    global $wpdb;    
    $affected_rows =  $wpdb->delete('wz_workhard_permission_folders', array('id'=>$id), array('%d'));
    $wpdb->delete('wz_workhard_permission_categories', array('permission_folder_id'=>$id), array('%d'));
    return array('affected_rows' => $affected_rows);
}

/*
 * Получить сгенерированный url адрес статьи
 *
 * @param int $post_id
 * @return string
 */
function wz_get_url_from_wp_posts_db($post_id) {
    global $wpdb;
    
    $query = $wpdb->prepare( "SELECT guid FROM {$wpdb->prefix}posts WHERE id=%d", array($post_id) );
    return $wpdb->get_var($query);
}

/*
 * Вставить заголовок в yoast_seo
 * @param int $post_id
 * @param string title
 * @param return array
 */
function wz_add_title_to_yoast($post_id, $title) {
    global $wpdb;
    
    $wpdb->insert(
        "{$wpdb->prefix}postmeta",
        array(
            'post_id' => $post_id, 
            'meta_key' => '_yoast_wpseo_title',
            'meta_value' => $title
        )
    );
    return array('meta_id'=> $wpdb->insert_id);
}

/*
 * Вставить Описание в yoast_seo
 * @param int $post_id
 * @param string description
 * @param return array
 */
function wz_add_description_to_yoast($post_id, $description) {
    global $wpdb;
    
    $wpdb->insert(
        "{$wpdb->prefix}postmeta", 
        array(
            'post_id' => $post_id, 
            'meta_key' => '_yoast_wpseo_metadesc',
            'meta_value' => $description
        )
    );
    return array('meta_id'=> $wpdb->insert_id);
}