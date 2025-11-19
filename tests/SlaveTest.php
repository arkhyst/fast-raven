<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

use SmartGoblin\Slaves\KernelSlave;



final class SlaveTest extends TestCase
{
    public function testKernelSlave(): void {
        $slave = KernelSlave::call();

        $this->assertInstanceOf(KernelSlave::class, $slave);
    }
}