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
		var settings = $.extend($.websocketSettings, s);
		$(ws)
			.bind('open', settings.open)
			.bind('close', settings.close)
			.bind('message', function(e){
				var m = $.parseJSON(e.originalEvent.data);
				var h = settings.events[m.t];
				if (h) h.call(this, m);
        else console.debug("ws: " + m.t + " not supported");
        settings.message(m);
			});
		ws._settings = settings;
		ws._send = ws.send;
		ws.send = function(t, data) {
			var m = {t: t};
			m = $.extend(true, m, $.extend(true, {}, settings.options, m));
			if (data) m['data'] = data;
			return this._send($.toJSON(m));
		}
		$(window).unload(function(){ ws.close(); ws = null });
		return ws;
	}
});
