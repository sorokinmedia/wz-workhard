// Статистика задач и биллинг
var appStatAndBilling = new Vue({
  el: '#app-stat-and-billing',
  data: {
    infoExportTasks: '',
    infoExportBilling: ''
  },
  methods: {
    getInfoExportTasks: function () {
      var intevalExportTasks = setInterval(function(){
        var self = this;
        var params = {
          action: 'wz_workhard_ajax_statistics', 
          nonce: WORKHARD.nonce, 
          method: 'get_info_export_tasks'
        };
        
        axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          if (response.data.status == 'ready') {
            self.infoExportTasks =  'Готов для работы экспорта данных';
          } else if (response.data.status == 'works') {
            self.infoExportTasks = 'Выполняется экспорт данных';
          } else if (response.data.status == 'preparation') {
            self.infoExportTasks = 'Выполняется подготовка для экспорта данных';
          }
        })
        .catch(function (error) {
          console.log(error);
        });
      }.bind(this), 5000);
    },
    getInfoExportBilling: function () {
      var intevalExportBilling = setInterval(function(){
        var self = this;
        var params = {
          action: 'wz_workhard_ajax_statistics', 
          nonce: WORKHARD.nonce, 
          method: 'get_info_export_billing'
        };
        
        axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          if (response.data.status == 'ready') {
            self.infoExportBilling =  'Готов для работы экспорта данных';
          } else if (response.data.status == 'works') {
            self.infoExportBilling = 'Выполняется экспорт данных';
          } else if (response.data.status == 'preparation') {
            self.infoExportBilling = 'Выполняется подготовка для экспорта данных';
          }
        })
        .catch(function (error) {
          console.log(error);
        });
      }.bind(this), 5000);
    },
    runExportTasks: function () {
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_statistics', 
        nonce: WORKHARD.nonce, 
        method: 'run_export_tasks'
      };

      axios.get(WORKHARD.admin_post, {params:  params})
      .then(function (response) {
        self.getInfoExportTasks();
      })
      .catch(function (error) {
        console.log(error);
      });
    },
    runExportBilling: function () {
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_statistics', 
        nonce: WORKHARD.nonce, 
        method: 'run_export_billing'
      };

      axios.get(WORKHARD.admin_post, {params:  params})
      .then(function (response) {
        self.getInfoExportTasks();
      })
      .catch(function (error) {
        console.log(error);
      });
    }
  },
  created: function() {
    this.getInfoExportTasks();
    this.getInfoExportBilling();
  }
});

// Список расходов
var appCosts = new Vue({
  el: '#app-costs',
  data: {
    form: {
      site: '',
      costsName: '',
      note: '',
      sum: ''
    },
    tableCosts: []
  },
  methods: {
    fetchTableCosts: function () {
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_statistics', 
        nonce: WORKHARD.nonce, 
        method: 'fetch_table_costs'
      };

      axios.get(WORKHARD.admin_post, {params:  params})
      .then(function (response) {
        self.tableCosts = response.data;
      })
      .catch(function (error) {
        console.log(error);
      });
    },
    addRow: function (event) {
      event.preventDefault();
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_statistics');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'add_row_costs');
      params.append('site', this.form.site);
      params.append('costs_name', this.form.costsName);
      params.append('note', this.form.note);
      params.append('sum', this.form.sum);
      

      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {  
          self.fetchTableCosts();
          self.clearForm();
        })
        .catch(function (error) {
          console.log(error);
        });
      
    },
    clearForm: function () {
      this.form.site = '';
      this.form.costsName = '';
      this.form.note = '';
      this.form.sum = '';
    },
    validateForm: function () {
      return this.form.site !== '' && this.form.costsName !== '' && this.form.sum !== '';
    }
  },
  created: function () {
    this.fetchTableCosts();
  }
});
