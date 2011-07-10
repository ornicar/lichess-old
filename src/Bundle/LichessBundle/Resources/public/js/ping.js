$.ping = function(url, options) {
    this.url = url;
    this.options = $.extend({
        delay: 2000,
        dataType: 'json',
        timeout: 10000,
        data: {},
        callback: function() {}
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
                self.options.callback(data);
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
    setData: function(key, value) { var self = this;
        self.options.data[key] = value;
    }
};
