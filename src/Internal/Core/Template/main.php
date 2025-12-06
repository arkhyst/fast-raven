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
            @import"https://fonts.googleapis.com/css2?family=Roboto&display=swap";*{box-sizing:border-box}html{font-size:18px;overflow:hidden;line-height:1.4}body{display:flex;flex-direction:column;justify-content:center;align-items:center;margin:0;padding:0;width:100vw;height:100vh;height:100vh;height:100svh;height:100lvh;height:100dvh;background:#060606;font-family:"Roboto";font-weight:500;color:#f3f3f3}main{width:100%;display:flex;flex-grow:1;flex-direction:column;justify-content:center;align-items:center;text-align:center}
        </style>

        <?= $template->getHtmlFavicon(); ?>
        <?= $template->getHtmlStyles(); ?>

        <link rel="preconnect" href="https://ajax.googleapis.com" crossorigin>
        <link rel="dns-prefetch" href="https://ajax.googleapis.com">
    </head>
    <body>
        <?php 
            foreach ($template->getPreDOMFiles() as $preFile) {
                include SITE_PATH . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . $preFile;
            }
        ?>
        <main>
            <?php include $template->getFile(); ?>
        </main>
        <?php 
            foreach ($template->getPostDOMFiles() as $postfile) {
                include SITE_PATH . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . $postfile;
            }
        ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script>
            <?php if (isset($_SESSION["csrf_token"])) { ?>
                const CSRF_TOKEN = "<?= $_SESSION["csrf_token"]; ?>";
            <?php } ?>
            <?php 
                $comp = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . "compiled.js");
                $comp = str_replace("XXX_PHP_AUTOFILL", $template->getHtmlAutofill(), $comp);
                echo $comp;
            ?>
        </script>
        <?= $template->getHtmlScripts(); ?>
    </body>
</html>
