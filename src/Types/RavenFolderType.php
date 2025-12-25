<?php

namespace FastRaven\Types;

/**
 * Enum RavenFolderType
 *
 * RavenFolderType is an enum that defines the different folders required to run a FastRaven project.
 */
enum RavenFolderType: string {
    case CONFIG = "config";
    case CONFIG_ENV = self::CONFIG . DIRECTORY_SEPARATOR . "env";
    case CONFIG_ROUTER = self::CONFIG . DIRECTORY_SEPARATOR . "router";
    case PUBLIC = "public";
    case PUBLIC_ASSETS = self::PUBLIC . DIRECTORY_SEPARATOR . "assets";
    case PUBLIC_ASSETS_CSS = self::PUBLIC_ASSETS . DIRECTORY_SEPARATOR . "css";
    case PUBLIC_ASSETS_JS = self::PUBLIC_ASSETS . DIRECTORY_SEPARATOR . "js";
    case PUBLIC_ASSETS_IMG = self::PUBLIC_ASSETS . DIRECTORY_SEPARATOR . "img";
    case PUBLIC_ASSETS_FONTS = self::PUBLIC_ASSETS . DIRECTORY_SEPARATOR . "fonts";
    case SRC = "src";
    case SRC_WEB = self::SRC . DIRECTORY_SEPARATOR . "web";
    case SRC_WEB_VIEWS = self::SRC_WEB . DIRECTORY_SEPARATOR . "views";
    case SRC_WEB_VIEWS_FRAGMENTS = self::SRC_WEB_VIEWS . DIRECTORY_SEPARATOR . "fragments";
    case SRC_WEB_VIEWS_MAILS = self::SRC_WEB_VIEWS . DIRECTORY_SEPARATOR . "mails";
    case SRC_WEB_VIEWS_PAGES = self::SRC_WEB_VIEWS . DIRECTORY_SEPARATOR . "pages";
    case SRC_WEB_ASSETS = self::SRC_WEB . DIRECTORY_SEPARATOR . "assets";
    case SRC_WEB_ASSETS_SCSS = self::SRC_WEB_ASSETS . DIRECTORY_SEPARATOR . "scss";
    case SRC_WEB_ASSETS_JS = self::SRC_WEB_ASSETS . DIRECTORY_SEPARATOR . "js";
    case SRC_API = self::SRC . DIRECTORY_SEPARATOR . "api";
    case SRC_CDN = self::SRC . DIRECTORY_SEPARATOR . "cdn";
    case STORAGE = "storage";
    case STORAGE_CACHE = self::STORAGE . DIRECTORY_SEPARATOR . "cache";
    case STORAGE_LOGS = self::STORAGE . DIRECTORY_SEPARATOR . "logs";
    case STORAGE_UPLOADS = self::STORAGE . DIRECTORY_SEPARATOR . "uploads";
}