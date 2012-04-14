$.websocketSettings = {
  open: function(){},
  close: function(){},
  message: function(m){},
  events: {},
  params: {
    uid: Math.random().toString(36).substring(5) // 8 chars
  },
  options: {
    reconnectDelay: 1000,
    //reconnectDelay: 0,
    debug: true,
    offlineDelay: 5000,
    offlineTag: $('#connection_lost'),
    nbPlayersTag: $('#nb_connected_players')
  }
};
$.websocket = function(url, version, settings) {
  this.url = url;
  this.version = version;
  this.settings = $.extend(true, $.websocketSettings, settings);
  this.options = this.settings.options;
  this.ws = null;
  this.fullUrl = null;
  this.offlineTimeout = null;
  this.reconnectTimeout = null;
  this.connect();
  $(window).unload(this._destroy);
}
$.websocket.prototype = {
  addEvent: function(name, fn) { this.settings.events[name] = fn; },
  send: function(t, d) { 
    this._debug({t: t, d: d});
    return this.ws.send($.toJSON({t: t, d: d})); 
  },
  connect: function() { var self = this;
    self._debug("WS connection attempt");
    self._destroy();
    self.fullUrl = self.url + "?" + $.param($.extend(self.settings.params, { version: self.version }));
    self.ws = new WebSocket(self.fullUrl); 
    $(self.ws)
      .bind('open', function() {
        self._debug("WS connected to " + self.fullUrl);
        if (self.offlineTimeout) clearTimeout(self.offlineTimeout);
        self.options.offlineTag.hide(); 
        self.settings.open();
      })
      .bind('close', function() {
        self._debug("WS disconnected");
        if (!self.offlineTimeout) self.offlineTimeout = setTimeout(function() { 
          self.options.offlineTag.show(); 
        }, self.options.offlineDelay);
        if (self.options.reconnectDelay) self.reconnectTimeout = setTimeout(function() {
          self.connect();
        }, self.options.reconnectDelay);
        self.settings.close();
      })
      .bind('message', function(e){
        var m = $.parseJSON(e.originalEvent.data);
        self._debug(m);
        if (m.v) self.version = m.v;
        var h = self.settings.events[m.t];
        if ($.isFunction(h)) h(m.d || null);
        else self._debug("WS " + m.t + " not supported");
        self.settings.message(m);
      });
  },
  disconnect: function() { 
    this.ws.close(); 
    clearTimeout(self.reconnectTimeout);
  },
  _debug: function(msg) { if (this.options.debug) console.debug(msg); },
  _destroy: function() { if (this.ws) { this.ws.close(); this.ws = null; } }
};
