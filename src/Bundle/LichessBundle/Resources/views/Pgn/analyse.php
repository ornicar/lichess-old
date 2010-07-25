<?php $view->extend('LichessBundle::layout') ?>
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

    <textarea id="pgnText"><?php echo $pgn ?></textarea>
<center> 
<b><span id="GameWhite"></span>&nbsp;-&nbsp;<span id="GameBlack"></span>&nbsp;&nbsp;<span id="GameResult"></span></b> 
<p></p> 
<div id="GameBoard"></div> 
<p></p> 
<div id="GameButtons"></div> 
<p></p> 
</center>
</div>
