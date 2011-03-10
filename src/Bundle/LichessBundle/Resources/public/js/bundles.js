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
            tolerance: 300,
            max: 5,
            refreshDelay: 200
        }, options);
        this.state = this.options.max;
        this.lastPingDate = new Date().getTime();
        this.$bars = this.element.find('span');
        this.start();
	};
	$.connectivity.prototype = {
        start: function() {
            var self = this;
            setInterval(function() { self.refresh() }, self.options.refreshDelay);
        },
        ping: function() {
            var self = this;
            // ping is not disconnected, show at least one bar
            self.state = Math.max(1, self.getStateForCurrentLatency());
            self.show();
            self.lastPingDate = new Date().getTime();
        },
        refresh: function() {
			var self = this;
            state = self.getStateForCurrentLatency();
            if (state < self.state) {
                self.state = state;
                self.show();
            }
        },
        getStateForCurrentLatency: function() {
            self = this;
            latency = new Date().getTime() - self.lastPingDate - self.options.delay;
            if (latency <= 0) {
                return self.options.max;
            }
            waitFactor = self.getLatencyFactor(latency);
            state = Math.max(0, self.options.max - waitFactor);
            return state;
        },
        getLatencyFactor: function(latency) {
            threshold = 0;
            for (factor=1;factor<=self.options.max;factor++) {
                threshold = factor + threshold;
                limit = threshold * self.options.tolerance;
                if (latency < limit) {
                    return factor -1;
                }
            }
            return self.options.max;
        },
        show: function() {
            this.$bars.removeClass('on').toggleClass('alert', 0 == this.state);
            for (i=0;i<this.state;i++) {
                $(this.$bars[i]).addClass('on');
            }
        }
	};
})(jQuery);
