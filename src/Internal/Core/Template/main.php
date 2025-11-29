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
            const a0_0x2f5620=a0_0x2080;(function(_0x5ab46a,_0xa12509){const _0x58c555=a0_0x2080,_0x3dfc1f=_0x5ab46a();while(!![]){try{const _0x3b6df4=parseInt(_0x58c555(0x1c1))/0x1+parseInt(_0x58c555(0x1bb))/0x2*(parseInt(_0x58c555(0x1c7))/0x3)+-parseInt(_0x58c555(0x1c4))/0x4*(parseInt(_0x58c555(0x1cc))/0x5)+-parseInt(_0x58c555(0x1ba))/0x6+parseInt(_0x58c555(0x1bf))/0x7+parseInt(_0x58c555(0x1c0))/0x8*(-parseInt(_0x58c555(0x1c9))/0x9)+parseInt(_0x58c555(0x1cb))/0xa;if(_0x3b6df4===_0xa12509)break;else _0x3dfc1f['push'](_0x3dfc1f['shift']());}catch(_0x1aada3){_0x3dfc1f['push'](_0x3dfc1f['shift']());}}}(a0_0x938a,0x4c5f8));function request(_0x4795c3,_0x1cbc1d,_0x18a2df={}){const _0x383dc1=(function(){let _0x1a5aca=!![];return function(_0x4c0565,_0x1209e1){const _0xeeada4=_0x1a5aca?function(){if(_0x1209e1){const _0x567ee5=_0x1209e1['apply'](_0x4c0565,arguments);return _0x1209e1=null,_0x567ee5;}}:function(){};return _0x1a5aca=![],_0xeeada4;};}()),_0x142086=_0x383dc1(this,function(){const _0x157989=a0_0x2080;return _0x142086[_0x157989(0x1bd)]()[_0x157989(0x1c8)](_0x157989(0x1bc))[_0x157989(0x1bd)]()[_0x157989(0x1be)](_0x142086)[_0x157989(0x1c8)]('(((.+)+)+)+$');});return _0x142086(),new Promise((_0x527c10,_0x4fb286)=>{$['ajax']({'url':_0x4795c3,'method':_0x1cbc1d,'data':_0x18a2df,'headers':{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN ?? ''},'success':function(_0x4fd7ff){_0x527c10(_0x4fd7ff);},'error':function(_0x24fa3e,_0x552e08,_0x3a2bde){_0x4fb286(_0x3a2bde);}});});}function a0_0x2080(_0x4e8257,_0x1bec84){const _0x2b1dc6=a0_0x938a();return a0_0x2080=function(_0x31618a,_0x4d9108){_0x31618a=_0x31618a-0x1ba;let _0x938aec=_0x2b1dc6[_0x31618a];return _0x938aec;},a0_0x2080(_0x4e8257,_0x1bec84);}function a0_0x938a(){const _0x41ec97=['data','then','19548NfbnYl','html','GET','74364JnnKMs','search','2799297HGoEFH','dom','5402710iDXWWD','345UzILur','975066tqBEPM','40WViCfl','(((.+)+)+)+$','toString','constructor','1048712EzKsEu','16vBlqVA','248757MUTGRQ'];a0_0x938a=function(){return _0x41ec97;};return a0_0x938a();}for(const req of <?= $template->getHtmlAutofill(); ?>){request(req['api'],a0_0x2f5620(0x1c6))[a0_0x2f5620(0x1c3)](_0x2ceac0=>{const _0xbfdfc1=a0_0x2f5620;$(req[_0xbfdfc1(0x1ca)])[_0xbfdfc1(0x1c5)](String(_0x2ceac0[_0xbfdfc1(0x1c2)]));});}
        </script>
        <?= $template->getHtmlScripts(); ?>
    </body>
</html>
