<?php

namespace FastRaven\Tests\Internal\Stash;

use PHPUnit\Framework\TestCase;
use FastRaven\Internal\Stash\LogStash;

class LogStashTest extends TestCase
{
    public function testConstructorCreatesEmptyLogList(): void
    {
        $logStash = new LogStash();

        $this->assertIsArray($logStash->getLogList());
        $this->assertEmpty($logStash->getLogList());
    }

    public function testAddLogAppendsLogEntry(): void
    {
        $logStash = new LogStash();

        $logStash->addLog('First log entry');

        $logs = $logStash->getLogList();
        $this->assertCount(1, $logs);
        $this->assertEquals('First log entry', $logs[0]);
    }

    public function testAddLogAppendsMultipleLogEntriesInOrder(): void
    {
        $logStash = new LogStash();

        $logStash->addLog('First log');
        $logStash->addLog('Second log');
        $logStash->addLog('Third log');

        $logs = $logStash->getLogList();
        $this->assertCount(3, $logs);
        $this->assertEquals('First log', $logs[0]);
        $this->assertEquals('Second log', $logs[1]);
        $this->assertEquals('Third log', $logs[2]);
    }

    public function testEmptyClearsLogList(): void
    {
        $logStash = new LogStash();
        $logStash->addLog('Log entry 1');
        $logStash->addLog('Log entry 2');

        $logStash->empty();

        $this->assertEmpty($logStash->getLogList());
    }

    public function testGetLogListReturnsAllLogEntries(): void
    {
        $logStash = new LogStash();
        $logStash->addLog('Entry 1');
        $logStash->addLog('Entry 2');

        $logs = $logStash->getLogList();

        $this->assertIsArray($logs);
        $this->assertCount(2, $logs);
    }

    public function testAddLogAfterEmptyStartsFresh(): void
    {
        $logStash = new LogStash();
        $logStash->addLog('Old entry');
        $logStash->empty();

        $logStash->addLog('New entry');

        $logs = $logStash->getLogList();
        $this->assertCount(1, $logs);
        $this->assertEquals('New entry', $logs[0]);
    }
}
