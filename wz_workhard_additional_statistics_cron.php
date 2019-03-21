<?php

require_once(dirname(__FILE__).'/includes/Arrch.php');
require_once(dirname(__FILE__).'/wz_workhard_google_spreadsheet.php');

use Arrch\Arrch;

add_filter('cron_schedules', 'wz_cron_add_schedules');
add_action('wz_cron_additional_statistics_hourly', 'wz_cron_additional_statistics_callback');
add_action('wz_cron_additional_statistics_5seconds', 'wz_cron_additional_statistics_callback');


function wz_cron_add_schedules($schedules) {
    
    $schedules['5seconds'] = array(
        'interval'=>'5',
        'display'=>'5 Секунд'
    );
    
    return $schedules;
}

function wz_cron_additional_statistics_callback() {
    $options_google_spreadsheet = get_option('wz_options_google_spreadsheet');
    
    $enable_additional_statistics = $options_google_spreadsheet['enable_additional_statistics'];
    $range_columns = 'A:K';
    $document_id = $options_google_spreadsheet['id'];
    $sheetname = $options_google_spreadsheet['sheetname_additional_statistics'];
    
    // Если пользователь в настройка не ввел номер документа или название листа
    // прекратить дальнейшие действие скрипта
    if (empty($document_id) || empty($sheetname)) {
        wz_clear_scheduled_billing();
        return;
    }
    
    $json_data = json_decode($options_google_spreadsheet['json_auth'], true);
    $valid_json_data = wz_validate_google_json_auth($json_data);
    
    // Если отключена опция экспорта статистики или не валидный ключ гугл
    // то прекратить дальнейшее действие скрипта
    if (!$enable_additional_statistics && !$valid_json_data){
        wz_clear_scheduled_additional_statistics('hourly');
        wz_clear_scheduled_additional_statistics('5seconds');
    }
    
    $service = wz_google_create_service('Wordpress', $json_data);
    
    // Если нету записей в таблице, то вставить по умолчанию заголовки столбцов 
    // Иначе отключить скрипт
    $count_rows = wz_google_count_rows($service, $document_id,  $sheetname, $range_columns);
    
    if ($count_rows === 0) {
        
        $columns_names = array(
            'Номер задачи', 'Дата создания', 'Дата завершения', 'Название', 'Пользователь принявший статью', 'Пользователь создавший статью', 
            'Общая (итоговая) стоимость статьи', 'Итоговое количество символов', 'Фикс ставка за создание задачи',
            'Фикс ставка за создание плана статьи', 'Фикс ставка за 1000 знаков', 'Цена публикации'
        );
        wz_google_insert_row($service, $document_id, $sheetname, $range_columns, $columns_names);
    } elseif ($count_rows === null) {
        wz_clear_scheduled_additional_statistics('hourly');
        wz_clear_scheduled_additional_statistics('5seconds');
    }
    
    $tasks_ids_workhard = wz_fetch_tasks_ids_from_workhard();
    $tasks_ids_google_table =  wz_fetch_tasks_ids_from_google_table();
    
    // Если нету массива номеров задач с workhard или гугл таблицы
    // то прекратить дальнейшее действия скрипта
    if (!$tasks_ids_workhard || !$tasks_ids_google_table) {
        wz_clear_scheduled_additional_statistics('5seconds');
        return;
    }
    
    // Выбрать id задач которые не были вставлены в гугл таблицу 
    $diff_tasks_ids = array_diff($tasks_ids_workhard, $tasks_ids_google_table);
    
    $count_diff_tasks_ids = count($diff_tasks_ids);
    
    
    // Если задач больше 0, то включить расписание экспорта каждый 5 секунд и поменять статус в  works
    // если задач нет, то отключить экспорт каждых 5 секунд и поменять статус в ready
    if ($count_diff_tasks_ids > 0) {
        $export_tasks = get_option('export_tasks');
        if (!$export_tasks || $export_tasks['status'] == 'ready' || $export_tasks['status'] == 'preparation') {;
            update_option('export_tasks', array('status'=> 'works'));
            
            wz_run_schedule_addional_statistics('5seconds');
            return;
        }
    } elseif ($count_diff_tasks_ids == 0) {
        $export_tasks = get_option('export_tasks');
        if (!$export_tasks || $export_tasks['status'] == 'works' || $export_tasks['status'] == 'preparation') {
            update_option('export_tasks', array('status'=> 'ready'));
            
            wz_clear_scheduled_additional_statistics('5seconds');
        } 
        return;
    }
    
    // По сколько задач экспортировать за раз
    $step_export = 20;    
    if ($count_diff_tasks_ids < $step_export) {
        $step_export = count($diff_tasks_ids);
    }
    
    // Вырезать список id задач для экспорта
    $sliced_tasks_ids = array_slice($diff_tasks_ids, 0, $step_export);
        
    $options_workhard = get_option('wz_options_workhard');
    
    $token = $options_workhard['token'];
    
    
    foreach ($sliced_tasks_ids as $task_id) {
        
        $task =  wzz_fetch_task_by_id($token, $task_id)['response'];

        $task_logs = wzz_fetch_task_logs($token, $task_id)['response'];
                        
        if (!$task) {
            continue;
        }
        
        if ($task_logs) {
            $options = array(
                'where' => array(
                    array('type.value', '==', 1),
                )
            );

            $task_created = Arrch::find($task_logs, $options, 'first');

            $options = array(
                'where' => array(
                    array('type.value', '==', 15),
                )
            );
            $task_checked = Arrch::find($task_logs, $options, 'first');
            
            $user_checked = $task_checked['user']['name'];
            $user_created = $task_created['user']['name'];

            $dt = new DateTime();
            $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
            $date_creatiton = $dt->format('d.m.Y H:i:s');
            $dt->setTimestamp($task_created['created_at']);
            $date_creation = $dt->format('d.m.Y H:i:s');
            
            $dt->setTimestamp($task_checked['created_at']);
            $date_finish = $dt->format('d.m.Y H:i:s');
            
        } else {
            $user_checked = 'Нет данных';
            $user_created = 'Нет данных';
            $date_creation = 'Нет данных';
            $date_finish = 'Нет данных';
        }
        
        $total_price = $task['price_customer'] + $task['price_additional_customer'];
        
        // Заменить, где цены "." на ","
        $total_price = wz_mb_str_replace('.', ',', $total_price); 
        
        // Вставлять задачи в гугл таблицу
        $values = array(
            $task['id'], $date_creation, $date_finish, $task['name'], $user_checked, $user_created,
            $total_price, $task['symbols']['real'], $options_workhard['price_task_creation'], 
            $options_workhard['price_article_creation'], $options_workhard['price_thousand_characters'], 
            $options_workhard['price']
        );
        wz_google_insert_row($service, $document_id, $sheetname, $range_columns, $values);
    }
    
    
}

/*
 * Запустить крон выполнения экспорта статистики по задачам
 */
function wz_run_schedule_addional_statistics($schedule_interval) {
    if ($schedule_interval == 'hourly') {
        $schedule_name = 'wz_cron_additional_statistics_hourly';
    } elseif ($schedule_interval == '5seconds') {
        $schedule_name = 'wz_cron_additional_statistics_5seconds';
        $time = wp_next_scheduled($schedule_name);
        if ($time) {
            return;
        }
    }
    wp_schedule_event(time(), $schedule_interval, $schedule_name);
}

/*
 * Отключить крон выполнения экспорта статистики по задачам
 */
function wz_clear_scheduled_additional_statistics($schedule_interval){
    if ($schedule_interval == 'hourly') {
        $schedule_name = 'wz_cron_additional_statistics_hourly';
    } elseif ($schedule_interval == '5seconds') {
        $schedule_name = 'wz_cron_additional_statistics_5seconds';
    }
    $time = wp_next_scheduled($schedule_name);
    wp_unschedule_event($time, $schedule_name);
    wp_clear_scheduled_hook($schedule_name);
}

/*
 * Получить список номеров завершенных задач с workhard
 */
function wz_fetch_tasks_ids_from_workhard() {
    $token = get_option('wz_options_workhard')['token'];
    $folders = wzz_fetch_folders($token)['response'];
    $tasks_ids = array();

    foreach ($folders as $folder) {
        $options = array(
            'where' => array(
                array('status.alias', '==', 'Завершена')
            )
        );
        $tasks_in_folders = Arrch::find($folder['tasks'], $options);

        foreach ($tasks_in_folders as $task) {
            $tasks_ids[] = $task['id'];
        }
    }
    return $tasks_ids;
}

/*
 * Получить список номеров завершенных задач с гугл таблицы
 */
function wz_fetch_tasks_ids_from_google_table() {
    $options = get_option('wz_options_google_spreadsheet');
    
    $json_data = json_decode($options['json_auth'], true);
    $valid_json_data = wz_validate_google_json_auth($json_data);

    if (empty($options['id']) || empty($options['sheetname_additional_statistics']) || !$valid_json_data){
        return;
    }
            
    $service = wz_google_create_service('Wordpress', $json_data);
    $range_columns = 'A:K';
    $document_id = $options['id'];
    $sheetname = $options['sheetname_additional_statistics'];
    
    $google_values = wz_google_get_values($service, $document_id, $sheetname, $range_columns);
    
    if (!$google_values) {
        return;
    }

    
    $tasks_ids = array_map(function($item){
        return $item[0];
    }, $google_values);
        
    return $tasks_ids;
}

?>