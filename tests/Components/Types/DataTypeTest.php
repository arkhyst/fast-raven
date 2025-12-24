<?php

namespace FastRaven\Tests\Components\Types;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Types\DataType;

class DataTypeTest extends TestCase
{
    // =====================================================================
    // Web Essentials
    // =====================================================================

    public function testHtmlType(): void
    {
        $this->assertEquals('text/html', DataType::HTML->value);
    }

    public function testJsonType(): void
    {
        $this->assertEquals('application/json', DataType::JSON->value);
    }

    public function testCssType(): void
    {
        $this->assertEquals('text/css', DataType::CSS->value);
    }

    public function testJsType(): void
    {
        $this->assertEquals('text/javascript', DataType::JS->value);
    }

    public function testXmlType(): void
    {
        $this->assertEquals('application/xml', DataType::XML->value);
    }

    public function testTextType(): void
    {
        $this->assertEquals('text/plain', DataType::TEXT->value);
    }

    // =====================================================================
    // Images
    // =====================================================================

    public function testPngType(): void
    {
        $this->assertEquals('image/png', DataType::PNG->value);
    }

    public function testJpgType(): void
    {
        $this->assertEquals('image/jpeg', DataType::JPG->value);
    }

    public function testWebpType(): void
    {
        $this->assertEquals('image/webp', DataType::WEBP->value);
    }

    public function testSvgType(): void
    {
        $this->assertEquals('image/svg+xml', DataType::SVG->value);
    }

    // =====================================================================
    // Media
    // =====================================================================

    public function testMp3Type(): void
    {
        $this->assertEquals('audio/mpeg', DataType::MP3->value);
    }

    public function testMp4Type(): void
    {
        $this->assertEquals('video/mp4', DataType::MP4->value);
    }

    // =====================================================================
    // Documents
    // =====================================================================

    public function testPdfType(): void
    {
        $this->assertEquals('application/pdf', DataType::PDF->value);
    }

    // =====================================================================
    // Utility
    // =====================================================================

    public function testBinaryType(): void
    {
        $this->assertEquals('application/octet-stream', DataType::BINARY->value);
    }

    public function testEnumIsStringBacked(): void
    {
        $this->assertIsString(DataType::HTML->value);
        $this->assertIsString(DataType::JSON->value);
        $this->assertIsString(DataType::PNG->value);
    }
}
