<!DOCTYPE html>
<html lang="<?= $template->getLang(); ?>">
    <head>
        <meta charset="UTF-8">
        <?= $template->getHtmlTitle(); ?>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-Content-Type-Options" content="nosniff">
        <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
        <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=(), interest-cohort=()">
        <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
        <meta name="color-scheme" content="light dark">
        <meta name="format-detection" content="telephone=no">

        <style>
            @import"https://fonts.googleapis.com/css2?family=Roboto&display=swap";*{box-sizing:border-box}html{font-size:18px;overflow:hidden;line-height:1.4}body{margin:0;padding:0;width:100vw;height:100vh;height:100vh;height:100svh;height:100lvh;height:100dvh;background:#060606;font-family:"Roboto";font-weight:500;color:#f3f3f3}#main{width:100%;height:100vh;height:100vh;height:100svh;height:100lvh;height:100dvh;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center}
        </style>

        <?= $template->getHtmlFavicon(); ?>
        <?= $template->getHtmlStyles(); ?>

        <link rel="preconnect" href="https://ajax.googleapis.com" crossorigin>
        <link rel="dns-prefetch" href="https://ajax.googleapis.com">
    </head>
    <body>
        <div id="main">
            <?php include $template->getFile(); ?>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>
            <?php if (isset($_SESSION["csrf_token"])) { ?>
                const CSRF_TOKEN = "<?= $_SESSION["csrf_token"]; ?>";
            <?php } ?>
            const a0_0x1fca85=a0_0x347c;function a0_0x347c(_0x4a0701,_0x531cfb){const _0x5ed819=a0_0x3f4f();return a0_0x347c=function(_0x435f7e,_0x2924fd){_0x435f7e=_0x435f7e-0x18f;let _0x3f4ffd=_0x5ed819[_0x435f7e];return _0x3f4ffd;},a0_0x347c(_0x4a0701,_0x531cfb);}(function(_0x578e1a,_0x3a7409){const _0xd28997=a0_0x347c,_0x1255c3=_0x578e1a();while(!![]){try{const _0x4d7e36=parseInt(_0xd28997(0x19f))/0x1*(-parseInt(_0xd28997(0x1a1))/0x2)+parseInt(_0xd28997(0x199))/0x3+-parseInt(_0xd28997(0x1a3))/0x4*(parseInt(_0xd28997(0x1a4))/0x5)+parseInt(_0xd28997(0x191))/0x6+-parseInt(_0xd28997(0x19d))/0x7+parseInt(_0xd28997(0x19e))/0x8*(parseInt(_0xd28997(0x196))/0x9)+parseInt(_0xd28997(0x195))/0xa;if(_0x4d7e36===_0x3a7409)break;else _0x1255c3['push'](_0x1255c3['shift']());}catch(_0x9154cc){_0x1255c3['push'](_0x1255c3['shift']());}}}(a0_0x3f4f,0x7819c));function request(_0x3d92fa,_0x1e4d90,_0x1567a3={}){const _0x2cdca2=(function(){let _0x3312e7=!![];return function(_0x54c28c,_0x7997b9){const _0x21b2c5=_0x3312e7?function(){const _0x3e1fdf=a0_0x347c;if(_0x7997b9){const _0x4181f9=_0x7997b9[_0x3e1fdf(0x1a5)](_0x54c28c,arguments);return _0x7997b9=null,_0x4181f9;}}:function(){};return _0x3312e7=![],_0x21b2c5;};}()),_0x272e03=_0x2cdca2(this,function(){const _0x349945=a0_0x347c;return _0x272e03['toString']()[_0x349945(0x19a)](_0x349945(0x1a2))[_0x349945(0x193)]()[_0x349945(0x19c)](_0x272e03)['search'](_0x349945(0x1a2));});return _0x272e03(),new Promise(function(_0x4d05c8,_0x860c23){const _0x3271ba=a0_0x347c;$[_0x3271ba(0x197)]({'url':_0x3d92fa,'method':_0x1e4d90,'data':JSON[_0x3271ba(0x194)](_0x1567a3),'headers':{'Content-Type':'application/json','X-CSRF-TOKEN':window[_0x3271ba(0x19b)]??''},'success':function(_0x5758e0){_0x4d05c8(_0x5758e0);},'error':function(_0x3e8195,_0x486330,_0x4f9bdd){_0x860c23(_0x4f9bdd);}});});}for(const req of <?= $template->getHtmlAutofill(); ?>){request(req[a0_0x1fca85(0x192)],a0_0x1fca85(0x1a0))['then'](_0x4bf9fe=>{const _0x5da04c=a0_0x1fca85,_0x33d70a=_0x4bf9fe[_0x5da04c(0x190)];$(req[_0x5da04c(0x18f)])[_0x5da04c(0x198)](_0x33d70a['toString']());});}function a0_0x3f4f(){const _0x59824a=['4ikAXmh','3113690vKtcBZ','apply','dom','data','4573848rOYmZR','api','toString','stringify','2716570vJDtjo','3814371KRZwmQ','ajax','html','2466111JyBEFp','search','CSRF_TOKEN','constructor','5426260dhFJTC','8KfvjgM','389971AFLtIE','GET','2GjHvie','(((.+)+)+)+$'];a0_0x3f4f=function(){return _0x59824a;};return a0_0x3f4f();}
        </script>
        <?= $template->getHtmlScripts(); ?>
    </body>
</html>
