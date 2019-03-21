var ArticlesListPage = {
  template: document.getElementById('articles-list-page'),
  data: function () {
    return {
      selectedPermissionFolders: null,
      articles: [],
      selectedArticles: [],
      statusButtonExport: true,
      price: 0
    }
  },  
  methods: {
    /* Получить список статей */
    fetchArticles: function () {
      if (!this.selectedPermissionFolders) {
        this.articles = [];
      }
      
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_articles', 
        nonce: WORKHARD.nonce, 
        method: 'fetch_articles', 
        from_folder_id: this.selectedPermissionFolders.fromFolderID
      };
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          self.articles = response.data;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /* Выделить все статьи */
    checkAll: function (event) {
      event.preventDefault();
      this.selectedArticles = _.map(this.articles, function(item){ return item.id; });
    },
    /* Снять все галочки */
    uncheckAll: function (event) {
      event.preventDefault();
      this.selectedArticles = [];
    },
    /* Экспортировать выбранные записи */
    exportArticles: function () {
      var totalCountSelectedArticles = this.selectedArticles.length;
      
      if (!totalCountSelectedArticles) {
        swal({
          title: "Нет выбранных позиций для экспорта",
          text: "",
          type: "error"
        });
        return;
      }
      
      var progressCountSelectedArticles = 0;
      this.statusButtonExport = false;
      
      this.displayRefElements('selectArticle', 'none');
      this.displayRefElements('articleEdit', 'none');
      
      var self = this;
      
      // Отправлять каждый пост с интервалом 3 секунды
      var timer = setInterval(function() {
        var firstSelectedArticle = this.selectedArticles.shift();
        
        var article = this.fetchArticle(firstSelectedArticle, function (article) {
          self.insertPost(firstSelectedArticle, article);
        });
        
        this.articles = _.filter(this.articles, function (item) {
          return item.id != firstSelectedArticle;
        });
                
        progressCountSelectedArticles += 1;
        this.updateProgressbar(progressCountSelectedArticles, totalCountSelectedArticles);
        
        var hasSelectedArticles = totalCountSelectedArticles - progressCountSelectedArticles > 0;
                
        if (!hasSelectedArticles) {
          this.statusButtonExport = true;
          this.displayRefElements('selectArticle', 'block');
          this.displayRefElements('articleEdit', 'block');
          clearInterval(timer);
        }
        
      }.bind(this), 3000);
    },
    /* Получить Заголовок, название заказа, крактое описание и текст */
    fetchArticle: function(taskID, callback) {
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_articles', 
        nonce: WORKHARD.nonce, 
        method: 'fetch_article', 
        task_id: taskID
      };
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          callback(response.data);
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /*
     * - Вставить новую статью в черновик 
     * - Переместить статью в другую папку в workhard
     * - Оставить отчет в гугл таблице
     */
    insertPost: function (taskID, article) {
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_articles');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'insert_post');
      params.append('order_name', article.order_name);
      params.append('title', article.title);
      params.append('description', article.description);
      params.append('text', article.text);
      params.append('category_id', self.selectedPermissionFolders.categoryID);
           

      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {
          if (!response.data.post_id) {
            return;
          }
          var toFolderId = self.selectedPermissionFolders.toFolderID;
          var folderName = self.selectedPermissionFolders.toFolderName;
          var postUrl = response.data.post_url;
        
          self.moveTaskToFolder(self.selectedPermissionFolders.toFolderID, taskID);

          self.insertSpreadsheetUser(folderName, article.order_name, postUrl, self.price, article.total_price);
        })
        .catch(function (error) {
          console.log(error);
        });
    },
     /* Переместить статью в workhard в другую папку */
    moveTaskToFolder: function (toFolderID, taskID) {
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_articles');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'move_task_to_folder');
      params.append('folder_id', toFolderID);
      params.append('task_id', taskID);

      axios.post(WORKHARD.admin_post, params);
    },
    /* Вставить отчет в google таблицу пользователей вп */
    insertSpreadsheetUser: function (folderName, orderName, postUrl, price, totalPrice) {
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_articles');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'insert_spreadsheet_user');
      params.append('folder_name', folderName);
      params.append('order_name', orderName);
      params.append('post_url', postUrl);
      params.append('price', price);
      params.append('total_price', totalPrice);

      axios.post(WORKHARD.admin_post, params);
    },
    /* Изменить стиль display для ref элементов. Например: displayCheckbox('none'); displayCheckbox('block') */
    displayRefElements: function (refElement, display) {
      _.each(this.$refs[refElement], function (element) {
        element.style.display = display;
      });
    },
    /* ProgressBar */
    updateProgressbar: function (currentCount, totalCount) {
      var percent = (currentCount / totalCount * 100);
      this.$refs.bar.style.width = percent + '%';
      this.$refs.bar.innerHTML = percent.toFixed(2) + '%';
    }
  },
  mounted: function () {
    var price = this.$refs.priceHidden.value;
    this.price = price;
  }
}

var ArticleEditPage = {
  template: document.getElementById('article-edit-page'),
  data: function () {
    return {
      article: {},
      price: null
    }
  },
  methods: {
    /* Получить Заголовок, название заказа, крактое описание и текст */
    fetchArticle: function(taskID) {
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_articles', 
        nonce: WORKHARD.nonce, 
        method: 'fetch_article', 
        task_id: taskID
      };
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          self.article = response.data;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /*
     * - Вставить новую статью в черновик 
     * - Переместить статью в другую папку в workhard
     * - Оставить отчет в гугл таблице
     */
    insertPost: function () {
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_articles');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'insert_post');
      params.append('order_name', this.article.order_name);
      params.append('title', this.article.title);
      params.append('description', this.article.description);
      params.append('text', this.article.text);
      params.append('category_id', this.$route.params.category_id);
      

      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {
          if (!response.data.post_id) {
            return;
          }
        
          self.moveTaskToFolder();
          self.insertSpreadsheetUser(response.data.post_url);
        
          swal({
              title: "Запись успешно добавлена!",
              text: "Перейти к записи?",
              type: "success",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Перейти",
              cancelButtonText: "Отмена",
              closeOnConfirm: false,
              closeOnCancel: false
            }, function(isConfirm){
              if (isConfirm) {
                window.location.href = 'post.php?post=' + response.data.post_id + '&action=edit';
              } else {
                self.$router.push({name: "articles-list-page"});
                swal.close();
              } 
            });
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    /* Переместить статью в workhard в другую папку */
    moveTaskToFolder: function () {
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_articles');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'move_task_to_folder');
      params.append('folder_id', this.$route.params.folder_id);
      params.append('task_id', this.$route.params.task_id);

      axios.post(WORKHARD.admin_post, params);
    },
    /* Вставить отчет в google таблицу пользователей вп */
    insertSpreadsheetUser: function ($postUrl) {
       var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_articles');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'insert_spreadsheet_user');
      params.append('folder_name', this.$route.params.folder_name);
      params.append('order_name', this.article.order_name);
      params.append('post_url', $postUrl);
      params.append('price', this.price);
      params.append('total_price', this.article.total_price);

      axios.post(WORKHARD.admin_post, params);
    }
  },
  mounted: function () {
    var taskID =  this.$route.params.task_id;
    this.fetchArticle(taskID);
    var price = this.$refs.priceHidden.value;
    this.price = price;
  }
}

var routes = [
  {name: 'articles-list-page', path: '/', component: ArticlesListPage},
  {path: '/article-edit/:folder_id/:folder_name/:task_id/:category_id', component: ArticleEditPage},
];

var router = new VueRouter({
  routes: routes
});

var app = new Vue({
  el: '#app',
  data: {},
  router: router
})