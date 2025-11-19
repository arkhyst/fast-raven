<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

use SmartGoblin\Components\Core\Config;
use SmartGoblin\Components\Http\DataType;
use SmartGoblin\Components\Http\Request;
use SmartGoblin\Components\Http\Response;
use SmartGoblin\Components\Router\Endpoint;



final class ComponentTest extends TestCase
{
    public function testCoreConfig(): void {
        $config = Config::new("/home/user/project/main", "main", false, "/login", "public");
        $config->configureAllowedHosts(["www.mydomain.net"]);
        $config->configureApi([
            Endpoint::new(false, "POST", "/sayHello", "SayHello")
        ]);
        $config->configureViews([
            Endpoint::new(false, "GET", "/", "main")
        ]);

        $this->assertInstanceOf(Config::class, $config);

    }

    public function testHttpRequest(): void
    {
        $data = [
            "key" => "hello"
        ];
        $request = new Request("/sayHello/", "POST", json_encode($data));
        
        $this->assertSame("/sayHello#POST", $request->getComplexPath());
        $this->assertSame("hello", $request->getDataItem("key"));
    }

    public function testHttpResponse(): void
    {
        $response = Response::new(true, 200, DataType::JSON);
        $response->setBody("Everything went alright!",[
            "ping" => "pong"
        ]);
        
        $this->assertInstanceOf(Response::class, $response);

        $this->assertSame("OK", $response->getStatus());
        $this->assertSame(200, $response->getCode());
        $this->assertSame("pong", $response->getData()["ping"]);
        $this->assertSame(DataType::JSON, $response->getType());
    }

    public function testRouterEndpoint(): void
    {
        $endpoint = Endpoint::new(true, "POST", "/sayHello", "SayHello.php");

        $this->assertInstanceOf(Endpoint::class, $endpoint);
        
        $this->assertTrue($endpoint->getRestricted());
        $this->assertSame('/sayHello#POST', $endpoint->getComplexPath());
        $this->assertSame('SayHello', $endpoint->getFile());
    }
}