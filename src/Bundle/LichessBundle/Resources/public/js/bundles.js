/* Ajax queue implementation */
(function($) {

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
		if (!self.processing) {
			$.dequeue(document, self.name);
		}
	};

})(jQuery);

/* connectivity indicator */
(function($) {

	$.connectivity = function(element, options) {
		this.element = element;
        this.options = $.extend({
            delay: 5000,
            tolerance: 1000,
            max: 5
        }, options);
        this.state = this.options.max;
        this.timeout = null;
        this.lastPingDate = new Date().getTime();
        this.$bars = this.element.find('span');
	};
	$.connectivity.prototype = {
        ping: function() {
            var self = this;
            self.timeout && clearTimeout(self.timeout);
            self.setLatency(new Date().getTime() - self.lastPingDate - self.options.delay);
            self.lastPingDate = now;
            if (self.state > 0) {
                self.timeout = setTimeout(function() { self.decrease(); }, timeSincePreviousPing);
            }
        },
        setLatency: function(latency) {
            var self = this;
            waitFactor = parseInt(latency / self.options.tolerance);
            self.state = Math.max(0, self.options.max - waitFactor);
            self.show();
        },
        decrease: function() {
			var self = this;
            self.state--;
            self.show();
            if (self.state > 0) {
                self.timeout = setTimeout(function() { self.decrease(); }, self.options.tolerance);
            }
        },
        show: function() {
            this.$bars.removeClass('on').toggleClass('alert', 0 == this.state);
            for (i=0;i<this.state;i++) {
                $(this.$bars[i]).addClass('on');
            }
        }
	};
})(jQuery);
