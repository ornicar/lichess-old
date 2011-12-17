/* connectivity indicator */
$.connectivity = function(element, options) {
    this.element = element;
    this.options = $.extend({
        delay: 2000,
        tolerance: 300,
        max: 5,
        refreshDelay: 300
    },
    options);
    var html = '';
    for (var i = 1; i <= 5; i++) {
        html += '<span style="height:'+(i*4)+'px;margin-top:'+(20-i*4)+'px;"></span>';
    }
    this.element.html(html);
    this.state = this.options.max;
    this.lastPingDate = new Date().getTime();
    this.$bars = this.element.find('span');
    this.start();
};
$.connectivity.prototype = {
    start: function() {
        var self = this;
        setInterval(function() {
            self.refresh()
        },
        self.options.refreshDelay);
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
        var self = this;
        latency = new Date().getTime() - self.lastPingDate - self.options.delay;
        if (latency <= 0) {
            return self.options.max;
        }
        waitFactor = self.getLatencyFactor(latency);
        state = Math.max(0, self.options.max - waitFactor);
        return state;
    },
    getLatencyFactor: function(latency) {
        var self = this;
        threshold = 0;
        for (factor = 1; factor <= self.options.max; factor++) {
            threshold = factor + threshold;
            limit = threshold * self.options.tolerance;
            if (latency < limit) {
                return factor - 1;
            }
        }
        return self.options.max;
    },
    show: function() {
        this.$bars.removeClass('on').toggleClass('alert', 0 == this.state);
        for (i = 0; i < this.state; i++) {
            $(this.$bars[i]).addClass('on');
        }
        $('#connection_lost').toggle(0 == this.state);
    }
};
