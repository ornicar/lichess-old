(function($)
 {
     $.widget("lichess.clock", {
             _init: function()
     {
         var self = this;
         this.options.time = parseFloat(this.options.time) * 1000;
         $.extend(this.options, {
             duration: this.options.time,
             state: 'ready'
         });
     },

     start: function()
     {
         var self = this;
         self.options.state = 'running';
         self.element.addClass('running');
         var end_time = new Date().getTime() + self.options.time;
         var interval = setInterval(function() {
             if (self.options.state == 'running') {
                 var current_time = Math.round(end_time - new Date().getTime());
                 if (current_time <= 0) {
                     clearInterval(interval);
                     current_time = 0;
                 }

                 self.options.time = current_time;
                 self._show();

                 //If the timer completed, fire the buzzer callback
                 current_time == 0 && $.isFunction(self.options.buzzer) && self.options.buzzer(self.element);
             } else {
                 clearInterval(interval);
             }
         }, 1000);
     },

     setTime: function(time)
     {
         this.options.time = parseFloat(time) * 1000;
         this._show();
     },

     stop: function()
     {
         this.options.state = 'stop';
         this.element.removeClass('running');
     },

     _show: function()
     {
        this.element.text(this._formatDate(new Date(this.options.time)));
     },

     _formatDate: function(date)
     {
         minutes = date.getMinutes();
         if(minutes < 10) minutes = "0"+minutes;
         seconds = date.getSeconds();
         if(seconds < 10) seconds = "0"+seconds;
         return minutes+':'+seconds;
     }
         });
 })(jQuery);
