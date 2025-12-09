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
            <?php include __DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "packedstyle.css"; ?>
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
                $comp = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "packedlib.js");
                $comp = str_replace("XXX_PHP_AUTOFILL", $template->getHtmlAutofill(), $comp);
                echo $comp;
            ?>
        </script>
        <?= $template->getHtmlScripts(); ?>
    </body>
</html>
