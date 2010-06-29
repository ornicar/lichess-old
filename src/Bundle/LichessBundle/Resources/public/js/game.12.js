(function($)
{
  $.widget("lichess.game", {
  
    _init: function()
    {
      var self = this;
      self.pieceMoving = false
      self.$board = self.element.find("div.lichess_board");
      self.$table = self.element.find("div.lichess_table_wrap");
      self.$chat = $("div.lichess_chat");
      self.initialTitle = document.title,
      self.animate = null;
      self.ajaxManager = $.manageAjax.create('lichess_sync', { manageType: "queue", maxReq: 1});
      
      if(self.options.game.started)
      {
        self.indicateTurn();
        self.initSquaresAndPieces();
        self.initChat();  
        self.initTable();
      }

      if(!self.options.opponent.ai)
      {
        // synchronize with game
        setTimeout(self.syncPlayer = function()
        {
            self.syncUrl(self.options.url.sync, function()
            {
                setTimeout(self.syncPlayer, self.options.sync_delay);
            });
        }, self.options.sync_delay);

        // update document title to show playing state
        setTimeout(self.updateTitle = function()
        {
            document.title = (self.isMyTurn() && !self.options.game.finished)
            ? document.title = document.title.indexOf('/\\/') == 0
            ? '\\/\\ '+document.title.replace(/\/\\\/ /, '')
            : '/\\/ '+document.title.replace(/\\\/\\ /, '')
            : document.title;
            setTimeout(self.updateTitle, 400);
        }, 400);
      }
    },
    syncUrl: function(url, callback, postData)
    {
        var self = this;
        self.ajaxManager.add({
            type: 'POST',
            dataType: 'json',
            data: postData || {},
            url: function() { return url.replace(/0$/, self.options.player.version); },
            success: function(data) {
                if(!data) return;
                if(self.options.opponent.connected != data.o && self.options.game.started) {
                    self.options.opponent.connected = data.o;
                    $.ajax({
                        type: 'GET',
                        cache: false,
                        url: self.options.url.opponent,
                        success: function(html)
                        {
                            self.$table.find('div.lichess_opponent').html(html);
                        }
                    });
                }
                if(data.v && data.v != self.options.player.version) {
                    self.options.player.version = data.v;
                    self.applyEvents(data.e);
                }
            },
            complete: function()
            {
                $.isFunction(callback) && callback();
            }
        });
    },
    isMyTurn: function()
    {
      return this.options.possible_moves != null;
    },
    getAnimationSpeed: function()
    {
        return this.animate ? this.options.animation_delay : 1;
    },
    changeTitle: function(text)
    {
        document.title = text+" - "+this.initialTitle;
    },
    indicateTurn: function()
    {
      if (this.options.game.finished) 
      {
        this.changeTitle(this.translate('Game over'));
      }
      else if (this.isMyTurn())
      {
        this.element.addClass("my_turn");
        this.changeTitle(this.translate('Your turn'));
      }
      else 
      {
        this.element.removeClass("my_turn");
        this.changeTitle(this.translate('Waiting'));
      }

      if (!this.$table.find('>div').hasClass('finished'))
      {
        this.$table.find("div.lichess_current_player div.lichess_player." + (this.isMyTurn() ? this.options.opponent.color : this.options.player.color)).fadeOut(this.getAnimationSpeed());
        this.$table.find("div.lichess_current_player div.lichess_player." + (this.isMyTurn() ? this.options.player.color : this.options.opponent.color)).fadeIn(this.getAnimationSpeed());
      }
    },
    movePiece: function(from, to, callback)
    {
      var $piece = this.$board.find("div#"+from+" div.lichess_piece");

      if (!$piece.length)
      {
        $.isFunction(callback || null) && callback();
        // already moved
        return;
      }

      var self = this;
      $("div.lcs.moved", self.$board).removeClass("moved");
      var $from = $("div#" + from, self.$board).addClass("moved"), from_offset = $from.offset();
      var $to = $("div#" + to, self.$board).addClass("moved"), to_offset = $to.offset();
      var animation = self.getAnimationSpeed()*($piece.hasClass(self.options.player.color) ? 1 : 2);
      
      $("body").append($piece.css({
        top: from_offset.top,
        left: from_offset.left
      }));
      $piece.animate({
        top: to_offset.top,
        left: to_offset.left
      }, animation, function()
      {
        $killed = $to.find("div.lichess_piece");
        if ($killed.length) 
        {
          self.killPiece($killed);
        }
        $to.append($piece.css({
          top: 0,
          left: 0
        }));
        $.isFunction(callback || null) && callback();
      });
    },
    killPiece: function($piece)
    {
      $piece.draggable("destroy");
      var self = this, $deads = $piece.hasClass("white") ? $("div.lichess_cemetery.white", self.element) : $("div.lichess_cemetery.black", self.element), $square = $piece.parent();
      $deads.append($("<div>").addClass('lichess_tomb'));
      var $tomb = $("div.lichess_tomb:last", $deads), tomb_offset = $tomb.offset();
      $('body').append($piece.css($square.offset()));
      $piece.css("opacity", 0).animate({
        top: tomb_offset.top,
        left: tomb_offset.left,
        opacity: 0.5
      }, self.getAnimationSpeed()*3, function()
      {
        $tomb.append($piece.css({
          position: "relative",
          top: 0,
          left: 0
        }));
      });
    },
    applyEvents: function(events)
    {
      var self = this;
      var actionEvents = [];

      // apply and overwrite possible_moves and messages
      for (var i in events)
      {
          if(events[i].type == 'possible_moves') {
              self.options.possible_moves = events[i].possible_moves;
              self.indicateTurn();
          }
          else if(events[i].type == 'message') {
              self.$chat.find('ol.lichess_messages').append(events[i].html)[0].scrollTop = 9999999;
          }
          else {
              actionEvents.push(events[i]);
          }
      }
      events = actionEvents;

      // move first
      for (var i in events)
      {
          if(events[i].type == 'move')
          {
            self.$board.find("div.lcs.check").removeClass("check");
            var from = events[i].from, to = events[i].to;
            events.splice(i, 1);
            self.movePiece(from, to, function() {
                self.applyEvents(events);
            });
            return;
          }
      }

      for (var i in events) 
      {
        var event = events[i];
        switch (event.type)
        {
          case "promotion":
            $("div#"+event.key+" div.lichess_piece")
            .addClass(event.pieceClass)
            .removeClass("pawn");
            break;
          case "castling":
            $("div#" + event.to, self.$board).append($("div#" + event.from + " div.lichess_piece", self.$board));
            break;
          case "enpassant":
            self.killPiece($("div#" + event.killed + " div.lichess_piece", self.$board));
            break;
          case "check":
            $("div#" + event.key, self.$board).addClass("check");
            break;
          case "redirect":
              window.location.href=event.url;
              break;
          case "end":
            self.options.game.finished = true;
            self.changeTitle(self.translate('Game over'));
            self.element.find("div.ui-draggable").draggable("destroy");
            self.element.removeClass("my_turn");
            // don't break here, we also want to reload the table
          case "reload_table":
            $.ajax({
              cache: false,
              url: self.options.url.table,
              success: function(html)
              {
                self.$table.html(html);
                self.initTable();
              }
            });
            break;
          default:
            break;
        }
      }
    },
    initSquaresAndPieces: function()
    {
        var self = this;
        // init squares
        $("div.lcs", self.$board).each(function()
        {
            var squareId = $(this).attr('id');
            $(this).droppable({
            accept: function(draggable)
            {
                return self.isMyTurn() && self.inArray(squareId, self.options.possible_moves[draggable.parent().attr('id')]);
            },
            drop: function(ev, ui)
            {
                var $piece  = ui.draggable,
                $oldSquare  = $piece.parent(),
                squareId    = $(this).attr("id"),
                moveData    = {
                from:    $oldSquare.attr("id"),
                to:   squareId
                };

                self.$board.find("div.droppable-active").removeClass("droppable-active");
                self.options.possible_moves = null;
                self.movePiece($oldSquare.attr("id"), squareId);

                function sendMoveRequest(moveData)
                {
                    self.syncUrl(self.options.url.move, function()
                    {
                        if(self.options.opponent.ai) {
                            setTimeout(function() {self.syncUrl(self.options.url.sync);}, self.getAnimationSpeed()*3);
                        }
                    }, moveData);
                }

                var color = self.options.player.color;
                // promotion
                if($piece.hasClass('pawn') && ((color == "white" && squareId[1] == 8) || (color == "black" && squareId[1] == 1)))
                {
                var $choices = $('<div class="lichess_promotion_choice">').appendTo(self.$board).html('\
                    <div rel="queen" class="lichess_piece queen '+color+'"></div>\
                    <div rel="knight" class="lichess_piece knight '+color+'"></div>\
                    <div rel="rook" class="lichess_piece rook '+color+'"></div>\
                    <div rel="bishop" class="lichess_piece bishop '+color+'"></div>'
                ).fadeIn(self.getAnimationSpeed()).find('div.lichess_piece').click(function()
                {
                    moveData.options = {promotion: $(this).attr('rel')};
                    sendMoveRequest(moveData);
                    $choices.fadeOut(self.getAnimationSpeed(), function() {$choices.remove();});
                }).end();
                }
                else
                {
                    sendMoveRequest(moveData);
                }
            },
            activeClass: 'droppable-active',
            hoverClass: 'droppable-hover'
            });
        });
        
        // init pieces
        $("div.lichess_piece." + self.options.player.color, self.$board).each(function()
        {
            $(this).draggable({
            //distance: 10,
            containment: self.$board,
            helper: function()
            {
                return $('<div>')
                .attr("class", $(this).attr("class"))
                .attr('data-key', $(this).parent().attr('id'))
                .appendTo(self.$board);
            },
            start: function()
            {
                self.pieceMoving = true;
                $(this).addClass("moving").parent().addClass("droppable-active")
            },
            stop: function()
            {
                self.pieceMoving = false;
                $(this).removeClass("moving").parent().removeClass("droppable-active")
            }
            })
            .hover(function()
            {
            if (self.animate && !self.pieceMoving && self.isMyTurn() && (targets = self.options.possible_moves[$(this).parent().attr('id')]) && targets.length)
            {
                $("#" + targets.join(", #")).addClass("droppable-active");
            }
            }, function()
            {
            if (!self.pieceMoving)
            {
                self.$board.find("div.droppable-active").removeClass("droppable-active");
            }
            });
        });
    },
    initChat: function()
    {
        var self = this;
        if(self.$chat.length)
        {
            self.$chat.find('ol.lichess_messages')[0].scrollTop = 9999999;
            var $input = self.$chat.find('input').one("focus", function()
            {
                $input.val('').removeClass('lichess_hint');
            });

            // send a message
            self.$chat.find('form').submit(function()
            {
                text = $.trim($input.val());
                if(!text) return;
                if(text.length > 140) {
                    alert('Max length: 140 chars. '+text.length+' chars used.');
                    return false;
                }
                $input.val('');
                self.syncUrl(self.options.url.say, null, {message: text});
                return false;
            });
        }
    },
    initTable: function()
    {
        var self = this;
        self.$table.find("a.lichess_resign").click(function()
        {
            if (confirm($(this).attr('title')+' ?')) 
            {
                self.syncUrl(self.options.url.resign);
            }
            return false;
        });

        self.$table.find("select.lichess_ai_level").change(function()
        {
            $.ajax({
                type: 'POST',
                url:  self.options.url.ai_level,
                data: { level:  $(this).val() }
            });
        });

        self.$table.find('label.lichess_enable_animation input').change(function()
        {
            self.animate = $(this).attr('checked');
            $('div.lcs.ui-droppable').droppable('option', 'activeClass', self.animate ? 'droppable-active' : '');
        }).trigger('change');

        self.$table.find('label.lichess_enable_chat input').change(function()
        {
            var $chatElements = $('div.lichess_chat').find('ol.lichess_messages, form');
            if($(this).attr('checked'))
            {
                $chatElements.show();
            }
            else
            {
                $chatElements.hide();
            }
        }).trigger('change');

        if(self.options.opponent.ai)
        {
            self.$table.find('label.lichess_enable_chat').hide();
        }
    },
    translate: function(message)
    {
      return this.options.i18n[message] || message;
    },
    inArray: function(needle, haystack)
    {
      for (var i in haystack)
      {
        if (haystack[i] == needle) {
          return true;
        }
      }
      return false;
    }
  });

})(jQuery);
