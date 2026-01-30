<?php

use FastRaven\Bee;
use FastRaven\Types\ProjectFolderType;

?>

<!DOCTYPE html>
<html>
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
            $fragmentsPath = Bee::buildProjectPath(ProjectFolderType::SRC_WEB_TEMPLATES_FRAGMENTS);
            foreach ($template->getBeforeFragments() as $beforeFragment) {
                include $fragmentsPath . $beforeFragment;
            }
        ?>
        <main>
            <?php include Bee::buildProjectPath(ProjectFolderType::SRC_WEB_TEMPLATES_PAGES, $template->getFile()); ?>
        </main>
        <?php 
            foreach ($template->getAfterFragments() as $afterFragment) {
                include $fragmentsPath . $afterFragment;
            }
        ?>
        <script>
            <?php include __DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "jquery.min.js"; ?>
        </script>
        <?= $template->getHtmlLang(); ?>
        <?= $template->getHtmlCSRF(); ?>
        <script>
            window.currentLanguage = localStorage.getItem("activeLang") || "<?= $template->getDefaultLang(); ?>";
            <?php include __DIR__ . DIRECTORY_SEPARATOR . "compiled" . DIRECTORY_SEPARATOR . "packedlib.js"; ?>
        </script>
        <?= $template->getHtmlScripts(); ?>
    </body>
</html>
