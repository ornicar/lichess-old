(function($){

    $.xqueue = function(name) {
        this.name = name;
        this.processing = false;
        this.count = 1;
    };

    $.xqueue.prototype.add = function(url, options) {
        var self = this;
        options.xqCount = self.count++;
        self.debug('+ '+options.xqCount);
        $.queue(document, self.name, function() {
            self.debug('= '+options.xqCount);
            self.processing = true;
            $.isFunction(url) && (url = url());
            $.ajax(url, options).complete(function() {
                self.debug('- '+options.xqCount);
                self.processing = false;
                $.dequeue(document, self.name);
            });
        });
        if(!self.processing){
            $.dequeue(document, self.name);
        }
    };

    $.xqueue.prototype.debug = function(message) {
        //console.debug(message);
    };

})(jQuery);
