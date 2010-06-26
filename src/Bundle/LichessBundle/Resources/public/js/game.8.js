(function($)
{
  $.widget("lichess.game", {
  
    _init: function()
    {
      var self = this;
      self.pieceMoving = false
      self.$board = self.element.find("div.lichess_board");
      self.$table = self.element.find("div.lichess_table");
      self.$chat = $("div.lichess_chat");
      self.initialTitle = document.title,
      self.animate = null;
      
      if(self.options.game.started)
      {
        self.indicateTurn();
        self.initSquaresAndPieces();
        self.initChat();  
        self.initTable();
        self.initOptions();
      }

      if(!self.options.opponent.ai)
      {
        // synchronize with game
        setTimeout(self.syncPlayer = function()
        {
            $.ajaxQueue({
                type:       'GET',
                cache:      false,
                url:        lichess_data.url.sync.replace(/0/, self.options.player.version),
                success:    function(data)
                {
                    self.syncSuccess(data);
                    setTimeout(self.syncPlayer, self.options.sync_delay);
                }
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
    syncSuccess: function(data)
    {
        var self = this;
        self.options.player.version = data.v;
        self.applyEvents(data.e);
        if(self.options.opponent.connected != data.o) {
            self.options.opponent.connected = data.o;
            $.ajaxQueue({
                type: 'GET',
                cache: false,
                url: self.options.url.opponent,
                success: function(html)
                {
                    self.$table.find('div.lichess_opponent').html(html);
                }
            });
        }
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

      if (!this.$table.hasClass('finished'))
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
          case "message":
            self.$chat.find('ol.lichess_messages').append(event.html)[0].scrollTop = 9999999;
            break;
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
          case "possible_moves":
              self.options.possible_moves = event.possible_moves;
              self.indicateTurn();
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
            $.ajaxQueue({
              cache: false,
              url: self.options.url.table,
              success: function(html)
              {
                self.$table.replaceWith(html);
              }
            })
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
                    $.ajaxQueue({
                        type: 'POST',
                        dataType: "json",
                        url: function() { return self.options.url.move.replace(/0/, self.options.player.version); },
                        data: moveData,
                        success: function(data)
                        {
                            self.syncSuccess(data);
                            if(self.options.opponent.ai) {
                                $.ajaxQueue({
                                    type:       'GET',
                                    cache:      false,
                                    url: function() { return self.options.url.sync.replace(/0/, self.options.player.version); },
                                    success:    function(data)
                                    {
                                        self.syncSuccess(data);
                                    }
                                });
                            }
                        }
                    });
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
            var $messages = self.$chat.find('.lichess_messages');
            $messages[0].scrollTop = 9999999;
            var $form = self.$chat.find('form');
            var $input = self.$chat.find('input').one("focus", function()
            {
                $input.val('').removeClass('lichess_hint');
            });

            // send a message
            $form.submit(function()
            {
                text = $.trim($input.val());
                if(!text) return;
                if(text.length > 140) {
                    alert('Max length: 140 chars. '+text.length+' chars used.');
                    return false;
                }
                $input.val('');
                $.ajaxQueue({
                    type: 'POST',
                    dataType: "json",
                    url: function() { return self.options.url.say.replace(/0/, self.options.player.version); },
                    data: {message: text},
                    success: function(data)
                    {
                        self.syncSuccess(data);
                    }
                });
                return false;
            });

            self.$table.find('label.lichess_enable_chat input').change(function()
            {
                if($(this).attr('checked'))
                {
                    $messages.show(); $form.show();
                }
                else
                {
                    $messages.hide(); $form.hide();
                }
            }).trigger('change');
        }
        else
        {
            self.$table.find('label.lichess_enable_chat').hide();
        }
    },
    initTable: function()
    {
        var self = this;
        self.$table.find("a.lichess_resign").click(function()
        {
            if (confirm($(this).attr('title')+' ?')) 
            {
                $.ajaxQueue({
                    cache: false,
                    dataType: "json",
                    url: $(this).attr("href"),
                    success: function(data)
                    {
                        self.options.player.version = data.version;
                        self.applyEvents(data.events);
                    }
                });
            }

            return false;
        });

        self.$table.find("select.lichess_ai_level").change(function()
        {
            $.ajaxQueue({
            type: 'POST',
            url:  self.options.url.ai_level,
            data: {
                level:  $(this).val()
            }
            });
        });
    },
    initOptions: function()
    {
        var self = this;
        self.element.find('div.lichess_control .lichess_enable_animation input').change(function()
        {
            self.animate = $(this).attr('checked');
            $('div.lcs.ui-droppable').droppable('option', 'activeClass', self.animate ? 'droppable-active' : '');
        }).trigger('change');
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
