var appSpreadSheetUsers = new Vue({
  el: '#app',
  data: {
    form: {
      selectedUser: null,
      tableName: null,
      price: null
    },
    rules: []
  },
  methods: {
    onSubmit: function () {
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_spreadsheet_users');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'insert_google_spreadsheet_user');
      params.append('user_id', this.form.selectedUser);
      params.append('table_name', this.form.tableName);           
      params.append('price', this.form.price);           

      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {
          if (response.data.status == 'error') {
            swal({title: 'Такой пользователь уже существует!', text: '', type: 'error'});
          }
        
          self.form.selectedUser = null;
          self.form.tableName = null;
          self.form.price = null;
          self.fetchRules();
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    fetchRules: function () {
      var self = this;
      var params = {
        action: 'wz_workhard_ajax_spreadsheet_users', 
        nonce: WORKHARD.nonce, 
        method: 'fetch_spreadsheet_users', 
      };
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          self.rules = response.data;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    removeRule: function(userID) {
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_spreadsheet_users');
      params.append('nonce',  WORKHARD.nonce);
      params.append('method', 'remove_google_spreadsheet_user');
      params.append('user_id', userID);       

      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {
          self.fetchRules();
        })
        .catch(function (error) {
          console.log(error);
        });
    }
  },
  computed: {
    validateForm: function () {
      return this.form.selectedUser && this.form.tableName && this.form.price;
    }
  },
  created: function() {
    this.fetchRules();
  }
});