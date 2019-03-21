function playSound(){
  var filepath = WORKHARD.filepath_sound
  var sound = new Howl({
    volume: 0.5,
    src: [filepath + '.mp3', filepath + '.ogg', filepath + '.wav']
  });
  sound.play();
}

function createPush(title, body){
  Push.create(title, {
    body: body,
    timeout: 10000,
    onClick: function () {
        window.focus();
        this.close();
    }
  });
}

Vue.filter('formatDate', function(timestampSecs) {
  if (timestampSecs) {
    var timestampMs = timestampSecs * 1000
    var date = new Date();
    date.setTime(timestampMs);
    
    var day = date.getDate();
    var month = date.getMonth() + 1;
    var year = date.getFullYear();
    var hours = date.getHours();
    var minutes = date.getMinutes();
    
    day = day > 9 ? day : '0' + day; 
    month = month > 9 ? month : '0' + month; 
    hours = hours > 9 ? hours : '0' + hours; 
    minutes = minutes > 9 ? minutes : '0' + minutes; 
    
    return day + '.' + month + '.' + year + ' ' + hours + ':' + minutes;
  }
});      

var PrivateMessagesPage = {
  template: document.getElementById('private-messages-page'),
  data: function() {
    return {
      privateMessages: [],
      notified: false,
      timer: null,
    }
  },
  methods: {
    fetchPrivateMessages: function (){
      var self = this;
      var params = {action: 'wz_workhard_ajax_dialog', nonce: WORKHARD.nonce, method: 'fetch_list_messages'};
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          self.privateMessages = response.data.response;
          
          if (self.hasNewMessage() && !self.notified) {
            playSound();
            createPush('Workhard', 'Новое сообщение');
            self.notified = true;
          }
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    hasNewMessage: function () {
      for (var i = 0; i < this.privateMessages.length; i++) {
        var isViewed = this.privateMessages[i].is_viewed == 0;
        if (isViewed) {
          return true;
        }
      }
    }
  },
  created: function () {
    var delay =  60 * 1000;
    
    Push.Permission.request();
    
    this.fetchPrivateMessages();
    this. timer = setInterval(function(){
      this.fetchPrivateMessages();
    }.bind(this), delay);
    window.scrollTo(0, 0);
  },
  destroyed: function () {
    clearInterval(this.timer);
  }
}

var DialogPage = {
  template: document.getElementById('dialog-page'),
  data: function () {
    return {
      dialogID: 0,
      dialog: [],
      form: {
        chatID: null,
        message: ''
      },
      timer: null
    }
  },
  methods: {
    fetchDialog: function () {
      var self = this;
      this.dialogID = this.$route.params.id;
      
      var params = {action: 'wz_workhard_ajax_dialog', nonce: WORKHARD.nonce, method: 'fetch_messages_by_id', id: this.dialogID};
      
      axios.get(WORKHARD.admin_post, {params:  params})
        .then(function (response) {
          self.dialog = response.data.response;
        })
        .catch(function (error) {
          console.log(error);
        });
    },
    initForm: function () {
      this.form.message = '';
    },
    onSubmit: function (event) {
      event.preventDefault();
      
      var self = this;
      
      
      var params = new URLSearchParams();
      params.append('action', 'wz_workhard_ajax_dialog'); 
      params.append('nonce', WORKHARD.nonce);
      params.append('method', 'send_message');
      params.append('id', this.dialogID);
      params.append('message', this.form.message);
      
      var headers = {'Content-Type': 'application/x-www-form-urlencoded;'};
      
      axios.post(WORKHARD.admin_post, params, {headers: headers})
        .then(function (response) {
          self.fetchDialog();
          self.initForm();
        })
        .catch(function (error) {
          console.log(error);
        });
    }
  },
  computed: {
    reverseMessages: function() {
      if ('messages' in this.dialog) {
        return this.dialog.messages.slice().reverse();
      }
    }  
  },
  created: function(){
    var delay =  60 * 1000;
    
    this.fetchDialog();
    this.timer = setInterval(function(){
      this.fetchDialog();
    }.bind(this), delay);
    
    this.initForm();
    window.scrollTo(0, 0);
  },
  destroyed: function () {
    clearInterval(this.timer);
  }
}

var routes = [
  {path: '/', component: PrivateMessagesPage},
  {path: '/dialog/:id', component: DialogPage},
];

var router = new VueRouter({
  routes: routes
});

var app = new Vue({
  el: '#app',
  data: {},
  router: router
})