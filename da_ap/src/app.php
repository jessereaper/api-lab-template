<?php
namespace jess\champions;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

require './vendor/autoload.php';

class App
{
   private $app;
   private const SCRIPT_INCLUDE = '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
   <script
     src="https://code.jquery.com/jquery-3.3.1.min.js"
     integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
     crossorigin="anonymous"></script>
   </head>
   <script src=".public/script.js"></script>';


   public function __construct() {

     $app = new \Slim\App(['settings' => $config]);

     $container = $app->getContainer();

     $container['logger'] = function($c) {
         $logger = new \Monolog\Logger('my_logger');
         $file_handler = new \Monolog\Handler\StreamHandler('./logs/app.log');
         $logger->pushHandler($file_handler);
         return $logger;
     };
     $container['renderer'] = new PhpRenderer("./templates");

     function makeApiRequest($path){
       $ch = curl_init();

       //Set the URL that you want to GET by using the CURLOPT_URL option.
       curl_setopt($ch, CURLOPT_URL, "http://localhost/api/$path");
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

       $response = curl_exec($ch);
       return json_decode($response, true);
     }
     $app->get('/champions', function (Request $request, Response $response, array $args) {
       $responseRecords = makeApiRequest('champions');
       $tableRows = "";
       foreach($responseRecords as $champions) {
         $tableRows = $tableRows . "<tr>";
         $tableRows = $tableRows . "<td>".$champions["name"]."</td><td>".$champions["role"]."</td><td>".$champions["class"]."</td>";
         $tableRows = $tableRows . "<td>
         <a href='http://localhost:8080/da_ap/champions/".$champions["id"]."' class='btn btn-primary'>View Details</a>
         <a href='http://localhost:8080/da_ap/champions/".$champions["id"]."/edit' class='btn btn-secondary'>Edit</a>
         <a data-id='".$champions["id"]."' class='btn btn-danger deletebtn'>Delete</a>

         </td>";
         $tableRows = $tableRows . "</tr>";
       }

       $templateVariables = [
           "title" => "champions",
           "tableRows" => $tableRows
       ];
       return $this->renderer->render($response, "/champions.html", $templateVariables);
     });
     $app->get('/champions/add', function(Request $request, Response $response) {
       $templateVariables = [
         "type" => "new",
         "title" => "Add a new champion :0"
       ];
       return $this->renderer->render($response, "/championsForm.html", $templateVariables);

     });

     $app->get('/champions/{id}', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         $responseRecords = makeApiRequest('champions/'.$id);
         $body = "<h1>Name: ".$responseRecords['name']."</h1>";
         $body = $body . "<h2>role: ".$responseRecords['role']."</h2>";
         $body = $body . "<h3>class: ".$responseRecords['class']."</h3>";
         $response->getBody()->write($body);
         return $response;
     });
     $app->get('/champions/{id}/edit', function (Request $request, Response $response, array $args) {
         $id = $args['id'];
         $responseRecord = makeApiRequest('champions/'.$id);
         $templateVariables = [
           "type" => "edit",
           "title" => "Edit a existing champion",
           "person" => $responseRecord
         ];
         return $this->renderer->render($response, "/championsEditForm.html", $templateVariables);

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
