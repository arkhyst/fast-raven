<?php

namespace FastRaven\Tests\Components\Core;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Core\Template;
use FastRaven\Components\Data\Collection;
use FastRaven\Components\Data\Item;

class TemplateTest extends TestCase
{
    public function testNewCreatesTemplateWithBasicParameters(): void
    {
        $template = Template::new('My Page', '1.0.0');

        $this->assertEquals('My Page', $template->getTitle());
        $this->assertEquals('1.0.0', $template->getVersion());
        $this->assertEquals('en', $template->getLang());
        $this->assertEquals('favicon.png', $template->getFavicon());
    }

    public function testNewWithCustomLangAndFavicon(): void
    {
        $template = Template::new('Page', '1.0', 'es', 'custom.ico');

        $this->assertEquals('es', $template->getLang());
        $this->assertEquals('custom.ico', $template->getFavicon());
    }

    public function testFlexCreatesTemplateWithAllParameters(): void
    {
        $template = Template::flex(
            'Title',
            '2.0',
            'fr',
            'icon.png',
            ['style1.css', 'style2.css'],
            ['script1.js', 'script2.js'],
            Collection::new([
                Item::new('#name', '/api/name'),
                Item::new('#email', '/api/email')
            ])
        );

        $this->assertEquals('Title', $template->getTitle());
        $this->assertEquals('2.0', $template->getVersion());
        $this->assertEquals('fr', $template->getLang());
        $this->assertEquals('icon.png', $template->getFavicon());
        $this->assertEquals(['style1.css', 'style2.css'], $template->getStyles());
        $this->assertEquals(['script1.js', 'script2.js'], $template->getScripts());
        $this->assertEquals(['script1.js', 'script2.js'], $template->getScripts());
        
        $autofill = $template->getAutofill();
        $this->assertEquals(['#name', '#email'], $autofill->getAllKeys());
        $this->assertEquals(['/api/name', '/api/email'], $autofill->getAllValues());
    }

    public function testFlexWithEmptyParameters(): void
    {
        $template = Template::flex();

        $this->assertEquals('', $template->getTitle());
        $this->assertEquals('', $template->getVersion());
        $this->assertEquals('', $template->getLang());
        $this->assertEquals('', $template->getFavicon());
        $this->assertEmpty($template->getStyles());
        $this->assertEmpty($template->getScripts());
        $this->assertEmpty($template->getScripts());
        $this->assertEmpty($template->getAutofill()->getRawData());
    }

    public function testSetFileAndGetFile(): void
    {
        $template = Template::new('Test', '1.0');

        $template->setFile('/path/to/file.php');

        $this->assertEquals('/path/to/file.php', $template->getFile());
    }

    public function testSetFaviconAndGetFavicon(): void
    {
        $template = Template::new('Test', '1.0');

        $template->setFavicon('new-icon.png');

        $this->assertEquals('new-icon.png', $template->getFavicon());
    }

    public function testAddStyleAppendsToStylesArray(): void
    {
        $template = Template::new('Test', '1.0');

        $template->addStyle('style1.css');
        $template->addStyle('style2.css');

        $this->assertEquals(['style1.css', 'style2.css'], $template->getStyles());
    }

    public function testAddScriptAppendsToScriptsArray(): void
    {
        $template = Template::new('Test', '1.0');

        $template->addScript('script1.js');
        $template->addScript('script2.js');

        $this->assertEquals(['script1.js', 'script2.js'], $template->getScripts());
    }

    public function testAddAutofillAddsToAutofillArray(): void
    {
        $template = Template::new('Test', '1.0');

        $template->addAutofill('#username', '/api/user');
        $template->addAutofill('#email', '/api/email');

        $expectedKeys = ['#username', '#email'];
        $expectedValues = ['/api/user', '/api/email'];
        
        $this->assertEquals($expectedKeys, $template->getAutofill()->getAllKeys());
        $this->assertEquals($expectedValues, $template->getAutofill()->getAllValues());
    }

    public function testMergeOverwritesTitleLangFavicon(): void
    {
        $template1 = Template::new('Original', '1.0', 'en', 'original.png');
        $template2 = Template::new('Updated', '2.0', 'fr', 'updated.png');

        $template1->merge($template2);

        $this->assertEquals('Updated', $template1->getTitle());
        $this->assertEquals('fr', $template1->getLang());
        $this->assertEquals('updated.png', $template1->getFavicon());
    }

    public function testMergeKeepsOriginalIfNewIsEmpty(): void
    {
        $template1 = Template::new('Original', '1.0', 'en', 'original.png');
        $template2 = Template::flex('', '1.0', '', '');

        $template1->merge($template2);

        $this->assertEquals('Original', $template1->getTitle());
        $this->assertEquals('en', $template1->getLang());
        $this->assertEquals('original.png', $template1->getFavicon());
    }

    public function testMergeCombinesStylesAndScripts(): void
    {
        $template1 = Template::flex('', '', '', '', ['style1.css'], ['script1.js']);
        $template2 = Template::flex('', '', '', '', ['style2.css'], ['script2.js']);

        $template1->merge($template2);

        $this->assertEquals(['style1.css', 'style2.css'], $template1->getStyles());
        $this->assertEquals(['script1.js', 'script2.js'], $template1->getScripts());
    }

    public function testMergeCombinesAutofill(): void
    {
        $template1 = Template::flex('', '', '', '', [], [], Collection::new([Item::new('#field1', '/api/1')]));
        $template2 = Template::flex('', '', '', '', [], [], Collection::new([Item::new('#field2', '/api/2')]));

        $template1->merge($template2);

        $expectedKeys = ['#field1', '#field2'];
        $expectedValues = ['/api/1', '/api/2'];
        
        $this->assertEquals($expectedKeys, $template1->getAutofill()->getAllKeys());
        $this->assertEquals($expectedValues, $template1->getAutofill()->getAllValues());
    }

    public function testGetHtmlTitleGeneratesCorrectTag(): void
    {
        $template = Template::new('My Page Title', '1.0');

        $html = $template->getHtmlTitle();

        $this->assertEquals('<title>My Page Title</title>', $html);
    }

    public function testGetHtmlFaviconGeneratesCorrectTag(): void
    {
        $template = Template::new('Test', '1.0', 'en', 'icon.png');

        $html = $template->getHtmlFavicon();

        $this->assertEquals('<link rel="icon" href="/public/assets/icon.png" type="image/png">', $html);
    }

    public function testGetHtmlStylesGeneratesLinkTags(): void
    {
        $template = Template::new('Test', '1.0');
        $template->addStyle('main.css');
        $template->addStyle('theme.css');

        $html = $template->getHtmlStyles();

        $this->assertStringContainsString('<link rel="stylesheet" href="/public/resources/main.css?v=1.0">', $html);
        $this->assertStringContainsString('<link rel="stylesheet" href="/public/resources/theme.css?v=1.0">', $html);
    }

    public function testGetHtmlScriptsGeneratesScriptTags(): void
    {
        $template = Template::new('Test', '1.0');
        $template->addScript('app.js');
        $template->addScript('utils.js');

        $html = $template->getHtmlScripts();

        $this->assertStringContainsString('<script src="/public/resources/app.js?v=1.0" type="text/javascript"></script>', $html);
        $this->assertStringContainsString('<script src="/public/resources/utils.js?v=1.0" type="text/javascript"></script>', $html);
    }

    public function testGetHtmlAutofillGeneratesJsonArray(): void
    {
        $template = Template::new('Test', '1.0');
        $template->addAutofill('#name', '/api/name');
        $template->addAutofill('#email', '/api/email');

        $json = $template->getHtmlAutofill();
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);

        // Check structure
        $this->assertEquals('#name', $decoded[0]['dom']);
        $this->assertEquals('/api/name', $decoded[0]['api']);
        $this->assertEquals('#email', $decoded[1]['dom']);
        $this->assertEquals('/api/email', $decoded[1]['api']);
    }

    public function testGetHtmlAutofillWithEmptyAutofill(): void
    {
        $template = Template::new('Test', '1.0');

        $json = $template->getHtmlAutofill();

        $this->assertEquals('[]', $json);
    }

    public function testVersionInResourceUrls(): void
    {
        $template = Template::new('Test', '3.2.1');
        $template->addStyle('style.css');
        $template->addScript('script.js');

        $styles = $template->getHtmlStyles();
        $scripts = $template->getHtmlScripts();

        $this->assertStringContainsString('?v=3.2.1', $styles);
        $this->assertStringContainsString('?v=3.2.1', $scripts);
    }

    public function testSetPreDOMFilesAndGetPreDOMFiles(): void
    {
        $template = Template::new('Test', '1.0');
        $files = ['header.php', 'nav.php'];

        $template->setPreDOMFiles($files);

        $this->assertEquals($files, $template->getPreDOMFiles());
    }

    public function testSetPostDOMFilesAndGetPostDOMFiles(): void
    {
        $template = Template::new('Test', '1.0');
        $files = ['footer.php', 'analytics.php'];

        $template->setPostDOMFiles($files);

        $this->assertEquals($files, $template->getPostDOMFiles());
    }
}
