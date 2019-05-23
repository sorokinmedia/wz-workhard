<?php

add_action('admin_menu', 'wz_workhard_admin_menu');

add_action('wp_ajax_wz_workhard_ajax_dialog', 'wz_workhard_ajax_dialog');

/*
 * Создание меню
 */
function wz_workhard_admin_menu(){
    $page_title = 'Workhard';
    $menu_title = 'Workhard';
    $capability = 'edit_published_posts';
    $callback = 'wz_workhard_callback'; 
    $parent_slug = 'wz-workhard';
    add_menu_page($page_title, $menu_title, $capability, $parent_slug);
    
    $page_title = 'Workhard сообщения';
    $menu_title = 'Workhard сообщения';
    
    add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $parent_slug, $callback );
}

/*
 * Отображение и отправка сообщейний в workhard
 */
function wz_workhard_callback(){
?>
<div class="wrap">
    <h2>Workhard - сообщения</h2>
    <div id="app">
      <router-view></router-view>
    </div>
  </div> 
  
  <div id="private-messages-page">
    <div class="messages">
      <div class="item-message" v-for="item in privateMessages" v-bind:key="item.id" v-bind:class="{not_viewed: !item.is_viewed}">
        <div>
          <h3>{{ item.user.username }} - {{ item.created_at | formatDate }}</h3>
          {{ item.message }}
        </div>
        <router-link tag="button" class="btn btn-primary" :to="'/dialog/' + item.dialog_id">Перейти к Диалогу</router-link>
      </div>
    </div>
  </div>
  
  <div id="dialog-page">
    <div>
      <router-link to="/">Перейти к списку собщений</router-link>
      <hr>
      <form id="form-dialog" @submit="onSubmit">
         <div class="form-group">
          <label for="message">Введите ваше сообщение:</label>
          <textarea class="large-text" v-model="form.message" rows="5" id="message"></textarea>
        </div> 
        <button class="btn btn-primary" :disabled="form.message === ''">Отправить сообщение</button>
      </form>
      <div class="item-message" v-for="item in reverseMessages" v-bind:key="item.id">
        <div>
          <h3>{{ item.user.username }} - {{ item.created_at | formatDate }}</h3>
          {{ item.message }}
        </div>
      </div>
    </div>
</div>
<?php
}

function wz_workhard_ajax_dialog() {   
    if(!wp_verify_nonce($_REQUEST['nonce'], 'workhard_ajax')){
        echo( json_encode( array('status' => 'error' ) ) );
        wp_die();
    }
    
    $token = get_option('wz_options_workhard')['token'];
        
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (wzz_validate_params(array('method')) && $_GET['method'] === 'fetch_list_messages') {
            echo json_encode(wzz_fetch_list_messages($token));
            wp_die();
        } else if (wzz_validate_params(array('method', 'id')) && $_GET['method'] === 'fetch_messages_by_id') {
            echo json_encode(wzz_fetch_messages_by_id($token, $_GET['id']));
            wp_die();
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (wzz_validate_params(array('method', 'id', 'message')) && $_POST['method'] === 'send_message') {
            $response = wzz_send_message($token, $_POST['id'], $_POST['message']);
            echo( json_encode( $response ) );
            wp_die();
        }
    }
}



?>
