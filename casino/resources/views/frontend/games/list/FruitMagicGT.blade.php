<!DOCTYPE html>
<html>
<head>
    <title>{{ $game->title }}</title>
    <meta charset="utf-8">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">
      <style>
         body,
         html {
         position: fixed;
         } 
      </style>
   </head>

<body style="margin:0px;background-color:black;overflow:hidden">

    <script src="/games/FruitMagicGT/js/lib/pixi.min.js"></script>
    <script src="/games/FruitMagicGT/js/lib/createjs.min.js"></script>

    <script src="/games/FruitMagicGT/js/classes/GameButton.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameBack.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameUI.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameView.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameReels.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameLines.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameCounters.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/classes/GameRules.js" type="text/javascript"></script>
	
	@if ($slot->slotGamble)
		<script src="/games/FruitMagicGT/js/classes/GameGamble.js" type="text/javascript"></script>
	@endif
	
	@if ($slot->slotBonus)
		<script src="/games/FruitMagicGT/js/classes/GameBonus.js" type="text/javascript"></script>
	@endif
	
    <script src="/games/FruitMagicGT/js/classes/GameMessages.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/core.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/utils.js" type="text/javascript"></script>
    <script src="/games/FruitMagicGT/js/loader.js" type="text/javascript"></script>

    <script src="/games/FruitMagicGT/js/classes/Sounds.js" type="text/javascript"></script>


    <script>
        var isFontLoaded = false;

    if( !sessionStorage.getItem('sessionId') ){
        sessionStorage.setItem('sessionId', parseInt(Math.random() * 1000000));
    }

        window.WebFontConfig = {
            google: {
                families: ['Verdana Regular', 'Verdana Bold', 'Arial Regular', 'Arial Bold', 'Roboto Bold', 'Roboto', 'Roboto Light']
            },
            active: function() {
                isFontLoaded = true;
                InitializeGame();
            }
        };

        (function() {
            var wf = document.createElement('script');
            wf.src = '/games/FruitMagicGT/js/lib/webfont.js';
            wf.type = 'text/javascript';
            wf.async = 'false';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(wf, s);

        })();
    </script>
</body>

</html>