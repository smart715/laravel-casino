<html>
   <head>
      <title>{{ $game->title }}</title>
      <meta charset="utf-8">
      <meta name="apple-mobile-web-app-capable" content="yes" />
      <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">
      <link href='/games/SecretForest/css/fonts.css' rel='stylesheet' type='text/css'>
      <script src="/games/SecretForest/js/lib/createjs-2015.11.26.min.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameButton.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameBack.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameUI.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameView.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameReels.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameLines.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameCounters.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/GameRules.js" type="text/javascript"></script>
	
	@if ($slot->slotGamble)
      <script src="/games/SecretForest/js/classes/GameGamble.js" type="text/javascript"></script>
	@endif
	
	@if ($slot->slotBonus)
      <script src="/games/SecretForest/js/classes/GameBonus.js" type="text/javascript"></script>
	@endif
      <script src="/games/SecretForest/js/classes/GameMessages.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/utils.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/loader.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/core.js" type="text/javascript"></script>
      <script src="/games/SecretForest/js/classes/Sounds.js" type="text/javascript"></script>
	<script>

    if( !sessionStorage.getItem('sessionId') ){
        sessionStorage.setItem('sessionId', parseInt(Math.random() * 1000000));
    }
	
	</script>
         <style>
         body,
         html {
         position: fixed;
         } 
      </style>
   </head>
   <body onload="InitializeGame()" style="margin:0px;background-color:black">
      <canvas id="game" width="750" height="630" cstyle="position: absolute;"></canvas>
   </body>
</html>