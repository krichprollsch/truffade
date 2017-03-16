<?php
namespace Krichprollsch\MockServer;

use Rxnet\Httpd\Httpd;
use Rxnet\Httpd\HttpdRequest as Request;
use Rxnet\Httpd\HttpdResponse as Response;
use Rxnet\Exceptions\InvalidJsonException;

class Server
{
    private $mockPort;
    private $mockServer;

    private $adminPort;
    private $adminServer;

    private $mocks;
    private $next;

    public function __construct(int $mockPort = 8080, int $adminPort = 8081)
    {
        $this->mockPort = $mockPort;
        $this->adminPort = $adminPort;

        $this->mockServer = $this->configureMockServer();
        $this->adminServer = $this->configureAdminServer();
    }

    public function start() :void
    {
        $this->reset();
        // mock server catch all the requests a return the $next configured response
        $this->mockServer->listen($this->mockPort);

        // start the admin server
        $this->adminServer->listen($this->adminPort);
    }

    public function reset() :void
    {
        $this->mocks = [];
        $this->next = 0;
    }

    public function resetAction(Request $request, Response $response) :void
    {
        $this->reset();
        $response->json(['msg' => 'ok']);
    }

    public function mockAction(Request $request, Response $response) :void
    {
        if (!isset($this->mocks[$this->next])) {
            $response->json(['err' => 'not configured yet'], 500);
            return;
        }

        try {
            $json = $request->getJson();
        } catch (InvalidJsonException $e) {
            $json = null;
        }


        $this->mocks[$this->next]['request'] = [
            'method' => $request->getMethod(),
            'path' => $request->getPath(),
            'query' => $request->getQuery(),
            'request' => $request->getRequest(),
            'headers' => $request->getHeaders(),
            'body' => $request->getBody(),
            'json' => $json,
        ];
        $response->json($this->mocks[$this->next++]['response']);
    }

    public function getMocksAction(Request $request, Response $response) :void
    {
        $response->json($this->mocks);
    }

    public function getMockAction(Request $request, Response $response) :void
    {
        $idx = $request->getRouteParam('idx');

        if (!isset($this->mocks[$idx])) {
            $response->json(['err' => 'bad index given'], 400);
            return;
        }

        $response->json($this->mocks[$idx]);
    }

    public function setMockAction(Request $request, Response $response) :void
    {
        try {
            $body = $request->getJson();
        } catch (InvalidJsonException $e) {
            $body = $request->getBody();
        }

        $this->mocks[] = [
            'response' => $body,
            'request' => null,
        ];

        $response->json([
            'total' => count($this->mocks),
            'next' => $this->next,
        ]);
    }

    private function configureMockServer() :Httpd
    {
        // returns the $nex configured response and store the request
        $mockServer = new Httpd();
        $mockServer->route('GET', '/{all:.*}', [$this, 'mockAction']);
        $mockServer->route('POST', '/{all:.*}', [$this, 'mockAction']);
        $mockServer->route('PUT', '/{all:.*}', [$this, 'mockAction']);
        $mockServer->route('PATCH', '/{all:.*}', [$this, 'mockAction']);
        $mockServer->route('DELETE', '/{all:.*}', [$this, 'mockAction']);

        return $mockServer;
    }

    private function configureAdminServer() :Httpd
    {
        $adminServer = new Httpd();
        // returns all the mocks configured
        $adminServer->route('GET', '/', [$this, 'getMocksAction']);
        // returns a specific mock
        $adminServer->route('GET', '/{idx:\d+}', [$this, 'getMockAction']);
        // reset all the server
        $adminServer->route('DELETE', '/', [$this, 'resetAction']);
        // configure a mock
        $adminServer->route('POST', '/', [$this, 'setMockAction']);

        return $adminServer;
    }
}
