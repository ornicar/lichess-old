(function($){

    $.xqueue = function(name) {
        this.name = name;
        this.processing = false;
        this.count = 1;
    };

    $.xqueue.prototype.add = function(url, options) {
        var self = this;
        options.xqCount = self.count++;
        $.queue(document, self.name, function() {
            self.processing = true;
            $.isFunction(url) && (url = url());
            $.ajax(url, options).complete(function() {
                self.processing = false;
                $.dequeue(document, self.name);
            });
        });
        if(!self.processing){
            $.dequeue(document, self.name);
        }
    };

})(jQuery);
