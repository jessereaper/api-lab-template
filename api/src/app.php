<?php
namespace jess\champions;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require './vendor/autoload.php';

class App{

  private $app;
  public function __construct($db) {

    $config['db']['host']   = 'localhost';
    $config['db']['user']   = 'root';
    $config['db']['pass']   = 'root';
    $config['db']['dbname'] = 'championdb';

    $app = new \Slim\App(['settings' => $config]);

    $container = $app->getContainer();
    $container['db'] = $db;
    //loggs
    $container['logger'] = function($c) {
        $logger = new \Monolog\Logger('my_logger');
        $file_handler = new \Monolog\Handler\StreamHandler('./logs/app.log');
        $logger->pushHandler($file_handler);
        return $logger;
    };
    //creates new log file in the directory and loggs entries

    // $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    //     $name = $args['name'];
    //     $this->logger->addInfo('get request to /hello/'.$name);
    //     $response->getBody()->write("Hello, $name");
    //
    //     return $response;
    // });

    //gets hole databse when u search for champions
    $app->get('/champions', function (Request $request, Response $res){
      $this->logger->addInfo("get /champions");
      $champions = $this->db->query('SELECT * from champions')->fetchAll();
      $jsonRes = $res->withJson($champions);
      return $jsonRes;
    });
    // go to http://192.168.33.10/champions ^
    $app->get('/champions/{id}', function (Request $request, Response $response, array $args) {
      $id = $args['id'];
      //makes the id into a $id (idk the technical terms)
      $this->logger->addInfo("get /champions".$id);
      //loggs
      $champions = $this->db->query('SELECT * from champions where id ='.$id)->fetch();
      //goes to db and fetches the champion by id
      //if else statment in case of failure
      if($champions){
        $response = $response->withJson($champions);
      } else {
        $errorData = array('status' => 404, 'message' => 'not found');
        $response = $response->withJson($errorData, 404);
      }
      return $response;
      //returns champion you search for with an id
      // go to http://192.168.33.10/champions/1 ext
    });
    // $app->put('/champions/{id}', function (Request $request, Response $response, array $args){
    //   $id = $args['id'];
    //   $this->logger->addInfo("GET /champions/".$id);
    //   $champions = $this->db->query('SELECT * from champions where id='.$id)->fetch();
    //
    //   if($champions){
    //     $response =  $response->withJson($champions);
    //   } else {
    //     $errorData = array('status' => 404, 'message' => 'not found');
    //     $response = $response->withJson($errorData, 404);
    //   }
    //   return $response;
    // });
    $app->post('/champions', function (Request $request, Response $response) {
        $this->logger->addInfo("POST /champions/");

        // check that champion exists
        //$champions = $this->db->query('SELECT * from champions where id='.$id)->fetch();
        // if(!$champions){
        //   $errorData = array('status' => 404, 'message' => 'not found');
        //   $response = $response->withJson($errorData, 404);
        //   return $response;
        // }

        $createString = "INSERT INTO champions ";
        $fields = $request->getParsedBody();
        $keysArray = array_keys($fields);
        $last_key = end($keysArray);
        $values = '(';
        $fieldNames = '(';
        foreach($fields as $field => $value) {
          $values = $values . "'"."$value"."'";
          $fieldNames = $fieldNames . "$field";
          if ($field != $last_key) {
            // conditionally add a comma to avoid sql syntax problems
            $values = $values . ", ";
            $fieldNames = $fieldNames . ", ";
          }
        }
        $values = $values . ')';
        $fieldNames = $fieldNames . ') VALUES ';
        $createString = $createString . $fieldNames . $values . ";";
        // execute query
        try {
          $this->db->exec($createString);
        } catch (\PDOException $e) {
          var_dump($e);
          $errorData = array('status' => 400, 'message' => 'Invalid data provided to create this champion');
          return $response->withJson($errorData, 400);
        }
        // return updated record
        $champions = $this->db->query('SELECT * from champions ORDER BY id desc LIMIT 1')->fetch();
        $jsonResponse = $response->withJson($champions);

        return $jsonResponse;
    });

    $app->put('/champions/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $this->logger->addInfo("PUT /champions/".$id);

        // check that peron exists
        $champions = $this->db->query('SELECT * from champions where id='.$id)->fetchAll();
        if(!$champions){
          $errorData = array('status' => 404, 'message' => 'not found');
          $response = $response->withJson($errorData, 404);
          return $response;
        }

        // build query string
        $updateString = "UPDATE champions SET ";
        $fields = $request->getParsedBody();
        $keysArray = array_keys($fields);
        $last_key = end($keysArray);
        foreach($fields as $field => $value) {
          $updateString = $updateString . "$field = '$value'";
          if ($field != $last_key) {
            // conditionally add a comma to avoid sql syntax problems
            $updateString = $updateString . ", ";
          }
        }
        $updateString = $updateString . " WHERE id = $id;";

        // execute query
        try {
          $this->db->exec($updateString);
        } catch (\PDOException $e) {
          $errorData = array('status' => 400, 'message' => 'Invalid data provided to update');
          return $response->withJson($errorData, 400);
        }
        // return updated record
        $champions = $this->db->query('SELECT * from champions where id='.$id)->fetch();
        $jsonResponse = $response->withJson($champions);

        return $jsonResponse;
    });
    $app->delete('/chmapions/{id}', function (Request $request, Response $response, array $args) {
      $id = $args['id'];
      //changes id to $id
      $this->logger->addInfo("DELETE /champions/".$id);
      //loggs the deletion
      $deleteSuccessful = $this->db->exec('DELETE FROM champions where id='.$id);
      //looks though db to find the champion and deletes from that part
      if($deleteSuccessful){
        $response = $response->withStatus(200);
        //on success
      } else {
        $errorData = array('status' => 404, 'message' => 'not found');
        $response = $response->withJson($errorData, 404);
      }
      //on error
      return $response;
    });

    $this->app = $app;
    }

    /**
    * Get an instance of the application.
    *
    * @return \Slim\App
    */
  public function get()
  {
    return $this->app;
  }
}
