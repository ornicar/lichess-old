<?php $view->extend('LichessBundle::layout.php') ?>
<script src="/bundle/lichess/vendor/pgn4web/pgn4web.js" type="text/javascript"></script>
<script type="text/javascript">
   SetImagePath ("/bundle/lichess/vendor/pgn4web/alpha/48"); // use "" path if images are in the same folder as this javascript file
   SetImageType("png");
// SetHighlightOption(true); // true or false
   SetGameSelectorOptions("Select a KK game...", false, 0, 8, 0, 0, 0, 0, 10); // (head, num, chEvent, chSite, chRound, chWhite, chBlack, chResult, chDate);
// SetCommentsIntoMoveText(true);
   SetCommentsOnSeparateLines(true);
   SetAutoplayDelay(2500); // milliseconds
   SetAutostartAutoplay(true);
   SetAutoplayNextGame(true); // if set, move to the next game at the end of the current game during autoplay
// SetInitialGame(1); // number of game to be shown at load, from 1 (default); values (keep the quotes) of "first", "last", "random" are also accepted
// SetInitialHalfmove(0,false); // halfmove number to be shown at load, 0 (default) for start position; values (keep the quotes) of "start", "end", "random" and "comment" (go to first comment) are also accepted. Second parameter if true applies the setting to every selected game instead of startup only (default)
   SetShortcutKeysEnabled(false);
</script>

<div class="lichess_box">
    <h1 class="lichess_title">Pgn test</h1>

    <textarea id="pgnText">
[White "Spassky, Boris"]
[Black "Fischer, Robert"]
[Result "0-1"]
[Date "1972"]
[Event "World Championship"]
[Site "Reykjavik"]
[Round "13"]

1. e4 Nf6 2.e5 Nd5 3. d4 d6 4. Nf3 g6 5. Bc4 Nb6 6. Bb3 Bg7  7. Nbd2 O-O  8. h3 a5 9. a4 dxe5 10. dxe5 Na6 11. O-O Nc5 12. Qe2 Qe8  13. Ne4 Nbxa4  14. Bxa4 Nxa4 15. Re1 Nb6 16. Bd2 a4 17. Bg5 h6 18. Bh4 Bf5  19. g4 Be6  20. Nd4 Bc4 21. Qd2 Qd7 22. Rad1 Rfe8 23. f4 Bd5 24. Nc5 Qc8  25. Qc3 e6  26. Kh2 Nd7 27. Nd3 c5 28. Nb5 Qc6 29. Nd6 Qxd6 30. exd6 Bxc3  31. bxc3 f6  32. g5 hxg5 33. fxg5 f5 34. Bg3 Kf7 35. Ne5+ Nxe5 36. Bxe5 b5  37. Rf1 Rh8  38. Bf6 a3 39. Rf4 a2 40. c4 Bxc4 41. d7 Bd5 42. Kg3 Ra3+  43. c3 Rha8  44. Rh4 e5 45. Rh7+ Ke6 46. Re7+ Kd6 47. Rxe5 Rxc3+ 48. Kf2 Rc2+  49. Ke1 Kxd7 50. Rexd5+ Kc6 51. Rd6+ Kb7 52. Rd7+ Ka6 53. R7d2 Rxd2  54. Kxd2 b4 55. h4 Kb5 56. h5 c4 57. Ra1 gxh5 58. g6 h4 59. g7 h3 60. Be7 Rg8  61. Bf8 h2 62. Kc2 Kc6 63. Rd1 b3+ 64. Kc3 h1=Q 65. Rxh1 Kd5 66. Kb2 f4  67. Rd1+ Ke4 68. Rc1 Kd3 69. Rd1+ Ke2 70. Rc1 f3 71. Bc5 Rxg7 72. Rxc4 Rd7  73. Re4+ Kf1 74. Bd4 f2 0-1
</textarea>
<center> 
<b><span id="GameWhite"></span>&nbsp;-&nbsp;<span id="GameBlack"></span>&nbsp;&nbsp;<span id="GameResult"></span></b> 
<p></p> 
<div id="GameBoard"></div> 
<p></p> 
<div id="GameButtons"></div> 
<p></p> 
</center>
</div>
