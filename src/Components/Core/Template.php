<?php

namespace SmartGoblin\Components\Core;

final class Template {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $file = "main";
        public function getFile(): string { return $this->file; }
        public function setFile(string $file): void { $this->file = $file; }
    private string $title = "Smart Goblin Framework";
        public function getTitle(): string { return $this->title; }
    private string $favicon = "favicon.png";
        public function getFavicon(): string { return $this->favicon; }
        public function setFavicon(string $favicon): void { $this->favicon = $favicon; }
    private array $styles = [];
        public function getStyles(): array { return $this->styles; }
        public function addStyle(string $style): void { $this->styles[] = $style; }
    private array $scripts = [];
        public function getScripts(): array { return $this->scripts; }
        public function addScript(string $script): void { $this->scripts[] = $script; }
    private array $autofill = [];
        public function getAutofill(): array { return $this->autofill; }
        public function addAutofill(string $dom, string $api): void { $this->autofill[$dom] = $api; }
    private string $version = "1.0.0";
        public function getVersion(): string { return $this->version; }
    private string $lang = "en";
        public function getLang(): string { return $this->lang; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    public static function new(string $title, string $version, string $lang = "en"): Template {
        return new Template($title, $version, $lang);
    }

    public static function oneline(string $title, string $version, string $lang = "en", array $styles = [], array $scripts = [], array $autofill = []): Template {
        return new Template($title, $version, $lang, $styles, $scripts, $autofill);
    }

    private function  __construct(string $title, string $version, string $lang = "en", array $styles = [], array $scripts = [], array $autofill = []) {
        $this->title = $title;
        $this->version = $version;
        $this->lang = $lang;
        $this->styles = $styles;
        $this->scripts = $scripts;
        $this->autofill = $autofill;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function merge(Template $template): void {
        $this->title = $template->getTitle();
        $this->favicon = $template->getFavicon();
        $this->styles = array_merge($this->styles, $template->getStyles());
        $this->scripts = array_merge($this->scripts, $template->getScripts());
        $this->autofill = array_merge($this->autofill, $template->getAutofill());
    }

    public function getHtmlTitle(): string { return "<title>{$this->title}</title>"; }
    
    public function getHtmlFavicon(): string { return "<link rel=\"icon\" href=\"/public/assets/{$this->favicon}\" type=\"image/png\">"; }

    public function getHtmlStyles(): string { 
        $html = "";
        foreach ($this->styles as $style) {
            $html .= "<link rel=\"stylesheet\" href=\"/public/resources/$style?v=".$this->getVersion()."\">";
        }

        return $html;
    }

    public function getHtmlScripts(): string { 
        $html = "";
        foreach ($this->scripts as $script) {
            $html .= "<script src=\"/public/resources/$script?v=".$this->getVersion()."\" type=\"text/javascript\"></script>";
        }

        return $html;
    }

    public function getHtmlAutofill(): string { 
        return json_encode(array_map(
        fn($k) => (object)["dom" => $k, "api" => $this->autofill[$k]],
        array_keys($this->autofill)
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 
    }

    #/ METHODS
    #----------------------------------------------------------------------
}