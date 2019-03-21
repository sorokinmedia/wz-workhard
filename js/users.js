var app = new Vue({
  el: '#app',
  data: {
    selectedUser: null,
    selectedFromFolder: null,
    selectedToFolder: null,
    selectedCategory: null,
    permissionFolders: [],
  },  
  methods: {
    fetchPermissionFolders: function (userID) {
      var self = this;
      var params = {action: 'wz_workhard_ajax_users', nonce: WORKHARD.nonce, method: 'fetch_permission_folders', user_id: self.selectedUser};
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          self.permissionFolders = response.data;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    onChangeUser: function () {
      this.fetchPermissionFolders(this.selectedUser);
    },
    onSubmit : function (event) {
      event.preventDefault();
      
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_users');
      params.append('nonce', WORKHARD.nonce);
      params.append('method', 'insert_permission_folders');
      params.append('user_id', self.selectedUser);
      params.append('from_folder_id', self.selectedFromFolder.folder_id);
      params.append('from_folder_name', self.selectedFromFolder.folder_name);
      params.append('to_folder_id', self.selectedToFolder.folder_id);
      params.append('to_folder_name', self.selectedToFolder.folder_name);
      params.append('category_id', self.selectedCategory);
      
      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {
          self.fetchPermissionFolders();
          self.selectedFromFolder = null;
          self.selectedToFolder = null;
          self.selectedCategory = null;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    deletePermissionFolders: function (id) {
      var self = this;
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_users');
      params.append('nonce', WORKHARD.nonce);
      params.append('method', 'delete_permission_folders');
      params.append('id', id);
      
      axios.post(WORKHARD.admin_post, params)
        .then(function (response) {
          self.fetchPermissionFolders();
        })
        .catch(function (error) {
          console.log(error);
        });
    }
  },
  computed: {
    validateForm: function () {
      return this.selectedUser && this.selectedFromFolder && this.selectedToFolder && this.selectedCategory;
    }
  },
  created: function () {

  },
})