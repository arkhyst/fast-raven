<?php

namespace FastRaven\Components\Core;

use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;
use FastRaven\Workers\Bee;

final class Template {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $file = "";
        public function getFile(): string { return $this->file; }
        public function setFile(string $file): void { $this->file = $file; }
    private string $title = "";
        public function getTitle(): string { return $this->title; }
    private string $version = "";
        public function getVersion(): string { return $this->version; }
    private string $lang = "";
        public function getLang(): string { return $this->lang; }
    private string $favicon = "favicon.png";
        public function getFavicon(): string { return $this->favicon; }
        public function setFavicon(string $favicon): void { $this->favicon = $favicon; }
    private array $styles = [];
        public function getStyles(): array { return $this->styles; }
        public function addStyle(string $style): void { $this->styles[] = $style; }
    private array $scripts = [];
        public function getScripts(): array { return $this->scripts; }
        public function addScript(string $script): void { $this->scripts[] = $script; }
    private Collection $autofill;
        public function getAutofill(): Collection { return $this->autofill; }
        public function addAutofill(string $dom, string $api): void { $this->autofill->add(Item::new($dom, $api)); }
    private array $beforeFragments = [];
        public function getBeforeFragments(): array { return $this->beforeFragments; }
        public function setBeforeFragments(array $fragments): void { $this->beforeFragments = $fragments; }
    private array $afterFragments = [];
        public function getAfterFragments(): array { return $this->afterFragments; }
        public function setAfterFragments(array $fragments): void { $this->afterFragments = $fragments; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Template instance.
     *
     * @param string $title       The title of the page.
     * @param string $version     The version to use for resources.
     * @param string $lang        [optional] The language of the page. Default is "en".
     *
     * @return Template
     */
    public static function new(string $title, string $version, string $lang = "en", string $favicon = "favicon.png"): Template {
        return new Template($title, $version, $lang, $favicon);
    }

    /**
     * Creates a new Template instance using all available parameters in a single line.
     *
     * @param string $title       [optional] The title of the page.
     * @param string $version     [optional] The version to use for resources.
     * @param string $lang        [optional] The language of the page. Default is "en".
     * @param array  $styles      [optional] An array of style files to include.
     * @param array  $scripts     [optional] An array of script files to include.
     * @param Collection $autofill [optional] A collection of DOM elements to autofill with API data.
     *
     * @return Template
     */
    public static function flex(string $title = "", string $version = "", string $lang = "", string $favicon = "", array $styles = [], array $scripts = [], ?Collection $autofill = null): Template {
        return new Template($title, $version, $lang, $favicon, $styles, $scripts, $autofill);
    }

    private function  __construct(string $title, string $version, string $lang, string $favicon, array $styles = [], array $scripts = [], ?Collection $autofill = null) {
        $this->title = $title;
        $this->version = $version;
        $this->lang = $lang;
        $this->favicon = $favicon;
        $this->styles = $styles;
        $this->scripts = $scripts;
        $this->autofill = $autofill ?? Collection::new();
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    /**
     * Merges the given Template instance into this instance.
     *
     * This will overwrite any existing values with the values from the given Template instance.
     *
     * @param Template $template The Template instance to merge into this instance.
     */
    public function merge(Template $template): void {
        $this->title = $template->getTitle() ? $template->getTitle() : $this->title;
        $this->lang = $template->getLang() ? $template->getLang() : $this->lang;
        $this->favicon = $template->getFavicon() ? $template->getFavicon() : $this->favicon;
        $this->styles = array_merge($this->styles, $template->getStyles());
        $this->scripts = array_merge($this->scripts, $template->getScripts());
        $this->autofill->merge($template->getAutofill());
        $this->beforeFragments = array_merge($this->beforeFragments, $template->getBeforeFragments());
        $this->afterFragments = array_merge($this->afterFragments, $template->getAfterFragments());
    }

    public function sanitaze(): void {
        $this->favicon = Bee::normalizePath($this->favicon);
        $this->styles = array_map(fn($style) => Bee::normalizePath($style), $this->styles);
        $this->scripts = array_map(fn($script) => Bee::normalizePath($script), $this->scripts);
        $this->beforeFragments = array_map(fn($fragment) => Bee::normalizePath($fragment), $this->beforeFragments);
        $this->afterFragments = array_map(fn($fragment) => Bee::normalizePath($fragment), $this->afterFragments);
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
        return "<link rel=\"icon\" href=\"/public/assets/img/{$this->favicon}\" type=\"image/png\">";
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
            $html .= "<link rel=\"stylesheet\" href=\"/public/assets/css/$style?v=".$this->getVersion()."\">";
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
            $html .= "<script src=\"/public/assets/js/$script?v=".$this->getVersion()."\" type=\"text/javascript\"></script>";
        }

        return $html;
    }

    /**
     * Returns an array of DOM elements to autofill with API data.
     *
     * The JSON object contains a mapping of DOM elements to their corresponding API endpoints.
     *
     * @return string The JSON object containing the auto-fill information of the page.
     */
    public function getHtmlAutofill(): string { 
        $autofillList = [];
        foreach ($this->autofill->getAllKeys() as $dom) {
            $autofillList[] = [
                "dom" => $dom,
                "api" => $this->autofill->get($dom)->getValue()
            ];
        }
        return json_encode($autofillList, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    #/ METHODS
    #----------------------------------------------------------------------
}