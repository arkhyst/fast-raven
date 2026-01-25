<?php

namespace FastRaven\Tests\Components\Core;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Core\File;
use FastRaven\Types\DataType;

class FileTest extends TestCase
{
    private File $file;
    private string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempPath = sys_get_temp_dir() . '/test_file_' . uniqid() . '.jpg';
        
        // Create a dummy image file for type detection
        // JPEG magic bytes: FF D8 FF
        file_put_contents($this->tempPath, hex2bin('FFD8FFE0') . str_repeat("\0", 100));
        
        $this->file = File::new('test_image.jpg', $this->tempPath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempPath)) {
            unlink($this->tempPath);
        }
        parent::tearDown();
    }

    public function testNewCreatesFileInstance(): void
    {
        $this->assertInstanceOf(File::class, $this->file);
    }

    public function testGettersReturnCorrectValues(): void
    {
        $this->assertEquals('test_image.jpg', $this->file->getName());
        $this->assertEquals($this->tempPath, $this->file->getPath());
    }

    public function testGetExtensionReturnsCorrectExtension(): void
    {
        // Extension is derived from the path, not the name argument
        $this->assertEquals('jpg', $this->file->getExtension());
    }

    public function testGetTypeReturnsCorrectDataType(): void
    {
        // Should detect as IMAGE because we wrote JPEG magic bytes
        $this->assertEquals(DataType::JPG, $this->file->getType());
    }

    public function testGetExtensionHandlesNoExtension(): void
    {
        $noExtPath = sys_get_temp_dir() . '/test_no_ext_' . uniqid();
        touch($noExtPath);
        
        $file = File::new('README', $noExtPath);
        $this->assertEquals('', $file->getExtension());
        
        unlink($noExtPath);
    }

    public function testGetExtensionHandlesMultipleDots(): void
    {
        $multiDotPath = sys_get_temp_dir() . '/test_archive.tar.gz';
        touch($multiDotPath);
        
        $file = File::new('archive.tar.gz', $multiDotPath);
        $this->assertEquals('gz', $file->getExtension());
        
        unlink($multiDotPath);
    }
}
