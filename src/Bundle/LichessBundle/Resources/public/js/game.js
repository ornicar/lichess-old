(function($)
{
  $.widget("lichess.game", {
  
    _init: function()
    {
      var self = this;
      self.pieceMoving = false
      self.$board = $("div.lichess_board", self.element);
      self.$table = $("div.lichess_table", self.element);
      self.initialTitle = document.title,
      
      self.indicateTurn();

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
              $.ajax({
                type: 'POST',
                dataType: "json",
                url: self.options.url.move,
                data: moveData,
                success: function(data)
                {
                  self.updateFromJson(data);
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
              ).fadeIn(500).find('div.lichess_piece').click(function()
              {
                moveData.options = {promotion: $(this).attr('rel')};
                sendMoveRequest(moveData);
                $choices.fadeOut(800, function() {$choices.remove();});
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
          if (!self.pieceMoving && self.isMyTurn() && (targets = self.options.possible_moves[$(this).parent().attr('id')]) && targets.length)
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
      
      self.restartBeat();

      if(!self.options.opponent.ai)
      {
        // update document title to show playing state
        setInterval(function()
        {
            document.title = (self.isMyTurn() && !self.options.game.finished)
            ? document.title = document.title.indexOf('/\\/') == 0
            ? '\\/\\ '+document.title.replace(/\/\\\/ /, '')
            : '/\\/ '+document.title.replace(/\\\/\\ /, '')
            : document.title;
        }, 500);
      }
      
      self.$table.find("a.lichess_resign").click(function()
      {
        if (confirm($(this).attr('title')+' ?')) 
        {
          $.ajax({
            dataType: "json",
            url: $(this).attr("href"),
            success: function(data)
            {
              self.updateFromJson(data);
            }
          });
        }

        return false;
      });

      self.$table.find("a.lichess_permalink_toggle").click(function()
      {
        self.$table.find('div.lichess_permalink').toggle(100);
      });

      self.$table.find("select.lichess_ai_level").change(function()
      {
        $.ajax({
          type: 'POST',
          url:  self.options.url.ai_level,
          data: {
            level:  $(this).val()
          }
        });
      });
    },
    updateFromJson: function(data)
    {
      var self = this;
      $("div.lcs.check", self.$board).removeClass("check");
      
      self.options.possible_moves = data.possible_moves;
      self.displayEvents(data.events);
      self.indicateTurn();
    },
    isMyTurn: function()
    {
      return this.options.possible_moves != null;
    },
    indicateTurn: function()
    {
      if (this.options.game.finished) 
      {
        document.title = this.translate('Game over');
      }
      else if (this.isMyTurn())
      {
        this.element.addClass("my_turn");
        document.title = this.translate('Your turn');
      }
      else 
      {
        this.element.removeClass("my_turn");
        document.title = this.translate('Waiting for opponent');
      }

      if (!this.$table.hasClass('finished'))
      {
        this.$table.find("div.lichess_current_player div.lichess_player." + (this.isMyTurn() ? this.options.opponent.color : this.options.player.color)).fadeOut(500);
        this.$table.find("div.lichess_current_player div.lichess_player." + (this.isMyTurn() ? this.options.player.color : this.options.opponent.color)).fadeIn(500);
      }
    },
    beat: function()
    {
      var self = this;

      if (self.options.game.finished)
      {
        return;
      }

      lichess_socket.connect(self.options.url.socket, function(data) {
        if (data)
        {
          self.updateFromJson(data);
        }
        self.restartBeat();
      });
    },
    movePiece: function(from, to, callback)
    {
      var $piece = $("div#"+from+" div.lichess_piece", this.$board);

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
      var animation = $piece.hasClass(self.options.player.color) ? 500 : 1000;
      
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
      $deads.append($("<div>"));
      var $tomb = $("div:last", $deads), tomb_offset = $tomb.offset();
      $('body').append($piece.css($square.offset()));
      $piece.css("opacity", 0).animate({
        top: tomb_offset.top,
        left: tomb_offset.left,
        opacity: 0.5
      }, 2000, function()
      {
        $tomb.append($piece.css({
          position: "relative",
          top: 0,
          left: 0
        }));
      });
    },
    displayEvents: function(events)
    {
      var self = this;
      // move first
      for (var i in events)
      {
          if(events[i].type == 'move')
          {
              var from = events[i].from, to = events[i].to;
                events.splice(i, 1);
            self.movePiece(from, to, function() {
                self.displayEvents(events);
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
          case "mate":
          case "resign":
            self.options.game.finished = true;
            document.title = self.translate('Game over');
            $.ajax({
              url: event.table_url,
              success: function(html)
              {
                $("div.lichess_table").replaceWith(html);
              }
            })
            $("div.ui-draggable").draggable("destroy");
            clearTimeout(self.options.beat.timeout);
            self.element.removeClass("my_turn");
        }
      }
    },
    restartBeat: function()
    {
      var self = this;
      if (self.options.beat.timeout) 
      {
        clearTimeout(self.options.beat.timeout);
      }
      self.options.beat.timeout = setTimeout(function()
      {
        self.beat();
      }, self.isMyTurn() ? self.options.beat.delay * 2 : self.options.beat.delay);
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
