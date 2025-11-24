<?php

namespace SmartGoblin\Slaves;

use SmartGoblin\Exceptions\BadImplementationException;
use SmartGoblin\Exceptions\EndpointFileDoesNotExist;

use SmartGoblin\Internal\Factory\SlaveFactory;
use SmartGoblin\Internal\Core\Kernel;

use SmartGoblin\Components\Core\Config;
use SmartGoblin\Components\Http\Response;

final class KernelSlave extends SlaveFactory {
    private Kernel $kernel;
    private bool $ready = false;

    protected function __construct() {
        $this->kernel = new Kernel();
    }

    public function order(Config $config): void {
        $this->kernel->setConfig($config);
        $this->ready = true;
    }

    public function work(): void {
        $response = null;
        
        if ($this->ready) {
            $this->kernel->open();

            try {
                
                $response = $this->kernel->isApiRequest() ? $this->kernel->processApi() : $this->kernel->processView();

            } catch(BadImplementationException | EndpointFileDoesNotExist $e) {
                $response = Response::new(false, 500);
                $response->setBody($e->getMessage());
            }
            
            $this->kernel->close($response);
        } else {
            http_response_code(500);
        }
        
        exit(0);
    }
}