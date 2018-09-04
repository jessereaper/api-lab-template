<?php
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Http\RequestBody;
require './vendor/autoload.php';

// empty class definitions for phpunit to mock.
class mockQuery {
  public function fetchAll(){}
  public function fetch(){}
};
class mockDb {
  public function query(){}
  public function exec(){}
}

class PeopleTest extends TestCase
{
    protected $app;
    protected $db;

    // execute setup code before each test is run
    public function setUp()
    {
      $this->db = $this->createMock('mockDb');
      $this->app = (new feather\firstSlim\App($this->db))->get();
    }

    // test the helloName endpoint
    public function testHelloName() {
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/hello/Joe',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;
      $response = $this->app->run(true);
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame("Hello, Joe", (string)$response->getBody());
    }

    // test the GET people endpoint
    public function testGetChampions() {

      // expected result string
      $resultString = '[{"id":"1","name":"irelia","role":"top","class":"bruiser"},{"id":"2","name":"poppy","role":"top","class":"bruiser"}]';


      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetchAll')
        ->willReturn(json_decode($resultString, true)
      );
       $this->db->method('query')
             ->willReturn($query);

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/people',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }

    public function testGetchampions() {

      // test successful request
      $resultString = '{"id":"1","name":"irelia","role":"top","class":"bruiser"}';
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(json_decode($resultString, true));
      $this->db->method('query')->willReturn($query);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }
    public function testGetChampionsFailed() {
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(false);
      $this->db->method('query')->willReturn($query);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'GET',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
    }

    public function testUpdateChampions() {
      // expected result string
      $resultString = '{"id":"1","name":"irelia","role":"top","class":"bruiser"}';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')
        ->willReturn(json_decode($resultString, true)
      );
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
             ->willReturn(true);

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["name" =>  "irelia", "role" => "top", "class" => "bruiser"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
      $this->assertSame($resultString, (string)$response->getBody());
    }

    // test person update failed due to invalid fields
    public function testUpdateChampionsFailed() {
      // expected result string
      $resultString = '{"id":"1","name":"irelia","role":"top","class":"bruiser"}';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')
        ->willReturn(json_decode($resultString, true)
      );
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
          ->will($this->throwException(new PDOException()));

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["name" =>  "irelia", "role" => "top", "class" => "bruiser"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(400, $response->getStatusCode());
      $this->assertSame('{"status":400,"message":"Invalid data provided to update"}', (string)$response->getBody());
    }

    // test person update failed due to persn not found
    public function testUpdateChampionsNotFound() {
      // expected result string
      $resultString = '{"id":"1","name":"irelia","role":"top","class":"bruiser"}';

      // mock the query class & fetchAll functions
      $query = $this->createMock('mockQuery');
      $query->method('fetch')->willReturn(false);
      $this->db->method('query')
            ->willReturn($query);
       $this->db->method('exec')
          ->will($this->throwException(new PDOException()));

      // mock the request environment.  (part of slim)
      $env = Environment::mock([
          'REQUEST_METHOD' => 'PUT',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $requestBody = ["name" =>  "irelia", "role" => "top", "class" => "bruiser"];
      $req =  $req->withParsedBody($requestBody);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());

    }


    public function testDeleteChampions() {
      $query = $this->createMock('mockQuery');
      $this->db->method('exec')->willReturn(true);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'DELETE',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(200, $response->getStatusCode());
    }

    // test person delete failed due to person not found
    public function testDeleteChampionsFailed() {
      $query = $this->createMock('mockQuery');
      $this->db->method('exec')->willReturn(false);
      $env = Environment::mock([
          'REQUEST_METHOD' => 'DELETE',
          'REQUEST_URI'    => '/champions/1',
          ]);
      $req = Request::createFromEnvironment($env);
      $this->app->getContainer()['request'] = $req;

      // actually run the request through the app.
      $response = $this->app->run(true);
      // assert expected status code and body
      $this->assertSame(404, $response->getStatusCode());
      $this->assertSame('{"status":404,"message":"not found"}', (string)$response->getBody());
    }
}
