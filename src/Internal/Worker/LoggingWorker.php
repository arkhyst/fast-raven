<?php

namespace SmartGoblin\Internal\Worker;

use SmartGoblin\Components\Http\Request;
use SmartGoblin\Internal\Stash\LoggingStash;

class LoggingWorker {
    public static function dump(LoggingStash $stash): void {
        $textBlock = "";
        foreach($stash->getLogList() as $obj) $textBlock .= "[".$obj["time"]."] ".$obj["value"]."\n";
        self::writeIntoFile($textBlock);
    }

    private static function writeIntoFile(string $text): void {
        $dir = SITE_PATH . DIRECTORY_SEPARATOR . "logs" . DIRECTORY_SEPARATOR;
        if(!is_dir($dir)) mkdir($dir, 0755, true);

        file_put_contents($dir . date("dmY") . ".log", $text, FILE_APPEND | LOCK_EX);
    }

    public static function addOpenLogs(LoggingStash $stash, Request $request): void {
        $type = $request->isApi() ? "API" : "VIEW";
        $stash->addLog("# OPEN {$type} REQUEST({$request->getInternalID()})");
        $stash->addLog("--- Complex Path: {$request->getComplexPath()}");
        $stash->addLog("--- Remote Address: {$request->getOriginInfo()["IP"]}");
    }

    public static function addCloseLogs(LoggingStash $stash, Request $request, float $elapsedTime): void {
        $stash->addLog("--- Request time: " . $elapsedTime . "ms");
        $stash->addLog("# CLOSE REQUEST({$request->getInternalID()})");
        $stash->addLog("=======================================================");
    }
}