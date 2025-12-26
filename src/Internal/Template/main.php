<!DOCTYPE html>
<html lang="<?= $template->getLang(); ?>">
    <head>
        <meta charset="UTF-8">
        <?= $template->getHtmlTitle(); ?>

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="format-detection" content="telephone=no">

        <style>
            <?php include __DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "packedstyle.css"; ?>
        </style>

        <?= $template->getHtmlFavicon(); ?>
        <?= $template->getHtmlStyles(); ?>
    </head>
    <body>
        <?php
            $fragmentsPath = \FastRaven\Workers\Bee::buildProjectPath(\FastRaven\Types\ProjectFolderType::SRC_WEB_VIEWS_FRAGMENTS);
            foreach ($template->getBeforeFragments() as $beforeFragment) {
                include $fragmentsPath . $beforeFragment;
            }
        ?>
        <main>
            <?php include $template->getFile(); ?>
        </main>
        <?php 
            foreach ($template->getAfterFragments() as $afterFragment) {
                include $fragmentsPath . $afterFragment;
            }
        ?>
        <script>
            <?php include __DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "jquery.min.js"; ?>
        </script>
        <script>
            <?php if (isset($_SESSION["sgas_csrf"])) { ?>
                const CSRF_TOKEN = "<?= $_SESSION["sgas_csrf"]; ?>";
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
