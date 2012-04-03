/*
 * HACKED jQuery Web Sockets Plugin v0.0.1
 * https://github.com/kontinuity/jquery-websocket/blob/master/jquery.websocket-0.0.1.js
 */
$.extend({
	websocketSettings: {
		open: function(){},
		close: function(){},
		message: function(){},
		options: {},
		events: {}
	},
	websocket: function(url, s) {
		var ws = WebSocket ? new WebSocket( url ) : {
			send: function(m){ return false },
			close: function(){}
		};
		$.websocketSettings = $.extend($.websocketSettings, s);
		$(ws)
			.bind('open', $.websocketSettings.open)
			.bind('close', $.websocketSettings.close)
			.bind('message', $.websocketSettings.message)
			.bind('message', function(e){
				var m = $.parseJSON(e.originalEvent.data);
				var h = $.websocketSettings.events[m.t];
				if (h) h.call(this, m);
        else console.debug("ws: " + m.t + " not supported");
			});
		ws._settings = $.extend($.websocketSettings, s);
		ws._send = ws.send;
		ws.send = function(t, data) {
			var m = {t: t};
			m = $.extend(true, m, $.extend(true, {}, $.websocketSettings.options, m));
			if (data) m['data'] = data;
			return this._send($.toJSON(m));
		}
		$(window).unload(function(){ ws.close(); ws = null });
		return ws;
	}
});
