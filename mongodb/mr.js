var g = db.game2.findOne();

printjson(g.players[0].user['$id']);
var map = function() {
  [0, 1].forEach(function(i) {
    if (this.players[i].user['$id'])
      emit(this.players[i].user['$id'], {count: 1});
  });
};

var reduce = function(key, values) {
  var result = {count: 0};

  values.forEach(function(value) {
    result.count += value.count;
  });

  return result;
};

var mr = db.game2.mapReduce(map, reduce, {limit: 1000, out: {inline: 1}});

printjson(mr);
