<?php

namespace SmartGoblin\Slaves;

use SmartGoblin\Exceptions\BadImplementationException;
use SmartGoblin\Internal\Factory\SlaveFactory;
use SmartGoblin\Internal\Core\Kernel;
use SmartGoblin\Components\Core\Config;

use SmartGoblin\Components\Http\DataType;
use SmartGoblin\Components\Http\Response;

final class KernelSlave extends SlaveFactory {
    private Kernel $kernel;

    protected function __construct() {
        $this->kernel = new Kernel();
    }

    public function order(Config $config): void {
        $this->kernel->setup($config);
    }

    public function work(): void {
        $response = null;
        
        try {
            
            if(!$response) $this->kernel->processApi($response);
            if(!$response) $this->kernel->processView($response);

        } catch(BadImplementationException $e) {
            $response = Response::new(false, 500);
            $response->setBody($e->getMessage());
        }
        
        
        $this->kernel->packMetadata();
        if(!$response) {
            $response = Response::new(false, 301);
            $this->redirect($response);
        } else {
            $this->complete($response);
        }
    }

    private function complete(Response $response): void {
        http_response_code($response->getCode());
        header("Content-Type: ".$response->getType()->value."; charset=utf-8");

        if ($response->getType() == DataType::JSON) {
            echo json_encode([
                "status" => $response->getStatus(),
                "data" => $response->getData()
            ]);
        }
        
        exit(0);
    }

    private function redirect($response): void {
        http_response_code($response->getCode());
        header("Location: /".$this->kernel->getConfig()->getDefaultPathRedirect(), true);

        exit(0);
    }
}