<?php

namespace FastRaven\Components\Core;

use FastRaven\Workers\AuthWorker;
use FastRaven\Workers\CacheWorker;
use FastRaven\Workers\Bee;

use FastRaven\Components\Data\Map;
use FastRaven\Components\Data\Pair;

use FastRaven\Types\ProjectFolderType;

final class Template {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $file = "";
        public function getFile(): string { return $this->file; }
        public function setFile(string $file): Template { $this->file = $file; return $this; }
    private string $title = "";
        public function getTitle(): string { return $this->title; }
        public function setTitle(string $title): Template { $this->title = $title; return $this; }
    private string $version = "";
        public function getVersion(): string { return $this->version; }
        public function setVersion(string $version): Template { $this->version = $version; return $this; }
    private string $faviconLight = "";
        public function getFaviconLight(): string { return $this->faviconLight; }
        public function setFaviconLight(string $faviconLight): Template { $this->faviconLight = $faviconLight; return $this; }
    private string $faviconDark = "";
        public function getFaviconDark(): string { return $this->faviconDark; }
        public function setFaviconDark(string $faviconDark): Template { $this->faviconDark = $faviconDark; return $this; }
    private string $langFile = "global";
        public function getLangFile(): string { return $this->langFile; }
        public function setLangFile(string $langFile): Template { $this->langFile = $langFile; return $this; }
    private string $defaultLang = "en";
        public function getDefaultLang(): string { return $this->defaultLang; }
        public function setDefaultLang(string $defaultLang): Template { $this->defaultLang = $defaultLang; return $this; }
    
    private array $styles = [];
        public function getStyles(): array { return $this->styles; }
        public function addStyle(string $style): Template { $this->styles[] = $style; return $this; }
    private array $scripts = [];
        public function getScripts(): array { return $this->scripts; }
        public function addScript(string $script): Template { $this->scripts[] = $script; return $this; }
    private array $beforeFragments = [];
        public function getBeforeFragments(): array { return $this->beforeFragments; }
        public function setBeforeFragments(array $fragments): Template { $this->beforeFragments = $fragments; return $this; }
    private array $afterFragments = [];
        public function getAfterFragments(): array { return $this->afterFragments; }
        public function setAfterFragments(array $fragments): Template { $this->afterFragments = $fragments; return $this; }
    
    private Map $data;
        public function hasData(string $key): bool { return $this->data->has($key); }
        public function getData(string $key): string { return $this->hasData($key) ? strval($this->data->get($key)) : ""; }
        public function addData(string $key, string $value): Template { $this->data->add($key, $value); return $this; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Template instance.
     * @param string $file        The file to use for the template. Relative to src/web/pages/.
     * @param string $title       The title of the page.
     * @param string $version     The version to use for resources.
     * 
     * @return Template
     */
    public static function new(string $file, string $title, string $version = ""): Template {
        return new Template($file, $title, $version);
    }

    private function  __construct(string $file, string $title, string $version = "") {
        $this->file = $file;
        $this->title = $title;
        $this->version = $version;
        $this->data = Map::new();
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function setFavicon(string $favicon): Template {
        $this->faviconLight = $favicon;
        $this->faviconDark = $favicon;
        return $this;
    }

    /**
     * Merges the given Template instance into this instance.
     *
     * This will overwrite any existing values with the values from the given Template instance.
     *
     * @param Template $template The Template instance to merge into this instance.
     */
    public function merge(?Template $template): Template {
        if($template) {
            $this->file = $template->getFile() ? $template->getFile() : $this->file;
            $this->title = $template->getTitle() ? $template->getTitle() : $this->title;
            $this->version = $template->getVersion() ? $template->getVersion() : $this->version;
            $this->faviconLight = $template->getFaviconLight() ? $template->getFaviconLight() : $this->faviconLight;
            $this->faviconDark = $template->getFaviconDark() ? $template->getFaviconDark() : $this->faviconDark;
            $this->styles = array_merge($this->styles, $template->getStyles());
            $this->scripts = array_merge($this->scripts, $template->getScripts());
            $this->beforeFragments = array_merge($this->beforeFragments, $template->getBeforeFragments());
            $this->afterFragments = array_merge($this->afterFragments, $template->getAfterFragments());
            $this->data->merge($template->data);
        }
        return $this;
    }

    /**
     * Returns the HTML title element containing the title of the page.
     *
     * @return string The HTML title element.
     */
    public function getHtmlTitle(): string {
        return "<title>{$this->title}</title>"; 
    }
    
    /**
     * Returns the HTML link element containing the favicon of the page.
     *
     * The favicon is retrieved from the public/assets directory.
     *
     * @return string The HTML link element containing the favicon of the page.
     */
    public function getHtmlFavicon(): string {
        $html = "<link rel=\"icon\" href=\"/public/assets/img/" . Bee::normalizePath($this->faviconLight) . "\" type=\"image/png\" media=\"(prefers-color-scheme: light)\">";
        $html .= "<link rel=\"icon\" href=\"/public/assets/img/" . Bee::normalizePath($this->faviconDark) . "\" type=\"image/png\" media=\"(prefers-color-scheme: dark)\">";
        return $html;
    }

    /**
     * Returns the HTML link elements containing the stylesheets of the page.
     *
     * The stylesheets are retrieved from the public/resources directory.
     *
     * @return string The HTML link elements containing the stylesheets of the page.
     */
    public function getHtmlStyles(): string { 
        $html = "";
        foreach ($this->styles as $style) {
            $html .= "<link rel=\"stylesheet\" href=\"/public/assets/css/" . Bee::normalizePath($style) . "?v=".$this->getVersion()."\">";
        }

        return $html;
    }

    /**
     * Returns the HTML script elements containing the JavaScript files of the page.
     *
     * The JavaScript files are retrieved from the public/resources directory.
     *
     * @return string The HTML script elements containing the JavaScript files of the page.
     */
    public function getHtmlScripts(): string { 
        $html = "";
        foreach ($this->scripts as $script) {
            $html .= "<script src=\"/public/assets/js/" . Bee::normalizePath($script) . "?v=".$this->getVersion()."\" type=\"text/javascript\"></script>";
        }

        return $html;
    }

    /**
     * Returns the HTML script element containing the language data of the page.
     * 
     * The language data is retrieved from the web/lang directory.
     *
     * @return string The HTML script element containing the language data of the page.
     */
    public function getHtmlLang(): string {
        $cacheKey = Bee::getCacheKey("lang", $this->langFile);
        
        $langData = CacheWorker::read($cacheKey);
        if ($langData === null) {
            $langData = Bee::parseCSV(Bee::buildProjectPath(
                ProjectFolderType::SRC_WEB_ASSETS_LANG,
                $this->langFile . ".csv"));
            CacheWorker::write($cacheKey, $langData, 3600);
        }
        
        return "<script>window.LANG = " . json_encode($langData, JSON_UNESCAPED_UNICODE) . ";</script>";
    }

    /**
     * Returns the HTML script element containing the CSRF token of the page.
     * 
     * The CSRF token is retrieved from the session.
     *
     * @return string The HTML script element containing the CSRF token of the page.
     */
    public function getHtmlCSRF(): string {
        if(AuthWorker::isAuthorized()) return "<script>window.CSRF_TOKEN = \"" . $_SESSION["sgas_csrf"] . "\";</script>";
        
        return "";
    }

    #/ METHODS
    #----------------------------------------------------------------------
}