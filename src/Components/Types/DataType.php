<?php

namespace FastRaven\Components\Types;

/**
 * Enum DataType
 *
 * DataType is an enum that defines the type of data that is being sent or received.
 * MIME types organized by category, most commonly used first.
 */
enum DataType: string {
    // ─── Web Essentials ───────────────────────────────────────────────
    case HTML = "text/html";
    case CSS = "text/css";
    case JS = "text/javascript";
    case JSON = "application/json";
    case XML = "application/xml";
    case TEXT = "text/plain";

    // ─── Data Formats ─────────────────────────────────────────────────
    case CSV = "text/csv";
    case YAML = "text/yaml";
    case TOML = "application/toml";
    case FORM = "application/x-www-form-urlencoded";
    case MULTIPART = "multipart/form-data";

    // ─── Images ───────────────────────────────────────────────────────
    case PNG = "image/png";
    case JPG = "image/jpeg";
    case GIF = "image/gif";
    case WEBP = "image/webp";
    case SVG = "image/svg+xml";
    case ICO = "image/x-icon";
    case AVIF = "image/avif";
    case BMP = "image/bmp";
    case TIFF = "image/tiff";

    // ─── Audio ────────────────────────────────────────────────────────
    case MP3 = "audio/mpeg";
    case OGG_AUDIO = "audio/ogg";
    case WAV = "audio/wav";
    case FLAC = "audio/flac";
    case AAC = "audio/aac";
    case WEBM_AUDIO = "audio/webm";
    case M4A = "audio/mp4";

    // ─── Video ────────────────────────────────────────────────────────
    case MP4 = "video/mp4";
    case WEBM = "video/webm";
    case OGG_VIDEO = "video/ogg";
    case AVI = "video/x-msvideo";
    case MOV = "video/quicktime";
    case MKV = "video/x-matroska";
    case MPEG = "video/mpeg";

    // ─── Documents & Office ───────────────────────────────────────────
    case PDF = "application/pdf";
    case DOC = "application/msword";
    case DOCX = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
    case XLS = "application/vnd.ms-excel";
    case XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
    case PPT = "application/vnd.ms-powerpoint";
    case PPTX = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
    case ODT = "application/vnd.oasis.opendocument.text";
    case ODS = "application/vnd.oasis.opendocument.spreadsheet";
    case RTF = "application/rtf";
    case EPUB = "application/epub+zip";

    // ─── Fonts ────────────────────────────────────────────────────────
    case WOFF = "font/woff";
    case WOFF2 = "font/woff2";
    case TTF = "font/ttf";
    case OTF = "font/otf";
    case EOT = "application/vnd.ms-fontobject";

    // ─── Archives ─────────────────────────────────────────────────────
    case ZIP = "application/zip";
    case GZIP = "application/gzip";
    case TAR = "application/x-tar";
    case RAR = "application/vnd.rar";
    case SEVENZ = "application/x-7z-compressed";
    case BZIP2 = "application/x-bzip2";

    // ─── Miscellaneous ────────────────────────────────────────────────
    case BINARY = "application/octet-stream";
    case ICS = "text/calendar";
    case WASM = "application/wasm";
    case MANIFEST = "application/manifest+json";
}