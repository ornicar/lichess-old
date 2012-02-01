$.ping = function(url, options) {
    this.url = url;
    this.options = $.extend({
        delay: 2000,
        dataType: 'json',
        timeout: 10000,
        data: {},
        callbacks: []
    }, options);
    this.queue();
};
$.ping.prototype = {
    queue: function() { var self = this;
        setTimeout(function() {
            self.send();
        }, self.options.delay);
    },
    send: function() { var self = this;
        $.ajax(self.url, {
            success: function(data) {
                for (var i in self.options.callbacks) {
                    self.options.callbacks[i](data);
                }
            },
            complete: function() {
                self.queue();
            },
            dataType: self.options.dataType,
            data: self.options.data,
            type: "GET",
            cache: false,
            timeout: self.options.timeout
        });
    },
    setData: function(key, value) { 
        this.options.data[key] = value;
    },
    pushCallback: function(callback) {
        this.options.callbacks.push(callback);
    }
};
