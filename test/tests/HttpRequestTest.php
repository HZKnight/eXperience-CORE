<?php

namespace Experience\Tests\Core\Io\Net\Http;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Net\Http\HttpRequest;

class HttpRequestTest extends TestCase
{
    private array $backupGet;
    private array $backupPost;
    private array $backupCookie;
    private array $backupServer;

    protected function setUp(): void
    {
        parent::setUp();

        // Backup delle superglobali
        $this->backupGet = $_GET;
        $this->backupPost = $_POST;
        $this->backupCookie = $_COOKIE;
        $this->backupServer = $_SERVER;

        // Inizializzazione superglobali per i test
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    protected function tearDown(): void
    {
        // Ripristino delle superglobali
        $_GET = $this->backupGet;
        $_POST = $this->backupPost;
        $_COOKIE = $this->backupCookie;
        $_SERVER = $this->backupServer;

        parent::tearDown();
    }

    /**
     * Test della cattura corretta delle superglobali nel costruttore.
     */
    public function testConstructorCapturesSuperglobalsAndMethod(): void
    {
        $_GET = ['id' => 10];
        $_POST = ['username' => 'lucliscio'];
        $_COOKIE = ['session_id' => 'abc123xyz'];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new HttpRequest();

        $this->assertEquals('post', $request->getRequestMethod());
        $this->assertEquals([
            'get' => ['id' => 10],
            'post' => ['username' => 'lucliscio'],
            'cookie' => ['session_id' => 'abc123xyz']
        ], $request->getRequest());
    }

    /**
     * Test della priorità del metodo corrente quando un parametro è presente sia in GET che in POST.
     */
    public function testGetParamPrioritizesCurrentRequestMethod(): void
    {
        $_GET = ['action' => 'from_get'];
        $_POST = ['action' => 'from_post'];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new HttpRequest();

        // Deve restituire il valore di POST dato che REQUEST_METHOD è POST
        $this->assertEquals('from_post', $request->getParam('action'));
        $this->assertEquals('post', $request->getParamRequestMethod('action'));
    }

    /**
     * Test del recupero di parametri da altri bag (es. GET quando il metodo è POST).
     */
    public function testGetParamFallbackToOtherBags(): void
    {
        $_GET = ['page' => 2];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = new HttpRequest();

        $this->assertEquals(2, $request->getParam('page'));
        $this->assertEquals('get', $request->getParamRequestMethod('page'));
    }

    /**
     * Test del comportamento quando un parametro non esiste (deve restituire stringa vuota '').
     */
    public function testGetParamReturnsEmptyStringWhenNotFound(): void
    {
        $request = new HttpRequest();

        $this->assertEquals('', $request->getParam('non_existing_key'));
        $this->assertEquals('', $request->getParamRequestMethod('non_existing_key'));
    }

    /**
     * Test del metodo has() con chiavi presenti e assenti.
     */
    public function testHasChecksParameterExistence(): void
    {
        $_COOKIE = ['theme' => 'dark'];

        $request = new HttpRequest();

        $this->assertTrue($request->has('theme'));
        $this->assertFalse($request->has('unknown_key'));
    }

    /**
     * Test del metodo setParam() per la modifica/aggiunta dinamica dei parametri a runtime.
     */
    public function testSetParamUpdatesRequestParams(): void
    {
        $request = new HttpRequest();

        $request->setParam('token', 'post', 'secret123');

        $this->assertTrue($request->has('token'));
        $this->assertEquals('secret123', $request->getParam('token'));
        $this->assertEquals('post', $request->getParamRequestMethod('token'));
    }
}
