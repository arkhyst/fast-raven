<?php

namespace FastRaven\Tests\Components\Routing;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Routing\Middleware;

class MiddlewareTest extends TestCase
{
    public function testNewCreatesEmptyMiddleware(): void
    {
        $middleware = Middleware::new();

        $this->assertNull($middleware->get('nonexistent'));
    }

    public function testAddMiddlewareFunction(): void
    {
        $middleware = Middleware::new();
        
        $callback = function($request): bool {
            return true;
        };

        $middleware->add('testMiddleware', $callback);

        $this->assertSame($callback, $middleware->get('testMiddleware'));
    }

    public function testAddMultipleMiddlewareFunctions(): void
    {
        $middleware = Middleware::new();
        
        $callback1 = function($request): bool { return true; };
        $callback2 = function($request): bool { return false; };

        $middleware->add('middleware1', $callback1);
        $middleware->add('middleware2', $callback2);

        $this->assertSame($callback1, $middleware->get('middleware1'));
        $this->assertSame($callback2, $middleware->get('middleware2'));
    }

    public function testGetNonExistentMiddlewareReturnsNull(): void
    {
        $middleware = Middleware::new();

        $this->assertNull($middleware->get('nonexistent'));
    }

    public function testMethodChaining(): void
    {
        $callback1 = function($request): bool { return true; };
        $callback2 = function($request): bool { return true; };

        $middleware = Middleware::new()
            ->add('first', $callback1)
            ->add('second', $callback2);

        $this->assertSame($callback1, $middleware->get('first'));
        $this->assertSame($callback2, $middleware->get('second'));
    }

    public function testOverwriteMiddleware(): void
    {
        $middleware = Middleware::new();
        
        $callback1 = function($request): bool { return true; };
        $callback2 = function($request): bool { return false; };

        $middleware->add('test', $callback1);
        $middleware->add('test', $callback2);

        // Should have the second callback
        $this->assertSame($callback2, $middleware->get('test'));
    }

    public function testMiddlewareCallableIsCallable(): void
    {
        $middleware = Middleware::new();
        
        $callback = function($arg): bool {
            return $arg === 'test';
        };

        $middleware->add('test', $callback);
        
        $callable = $middleware->get('test');
        
        $this->assertIsCallable($callable);
        $this->assertTrue($callable('test'));
        $this->assertFalse($callable('other'));
    }

    public function testMiddlewareReturningFalse(): void
    {
        $middleware = Middleware::new();
        
        $callback = function($arg): bool {
            return false;
        };

        $middleware->add('deny', $callback);
        
        $callable = $middleware->get('deny');
        $result = $callable(null);

        $this->assertFalse($result);
    }
}
