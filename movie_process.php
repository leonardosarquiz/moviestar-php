<?php

require_once("globals.php");
require_once("db.php");
require_once("models/Movie.php");
require_once("models/Message.php");
require_once("dao/UserDAO.php");
require_once("dao/MovieDAO.php");

$message = new Message($BASE_URL);


// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");
$userDao = new UserDAO($conn, $BASE_URL);
$movieDao = new MovieDao($conn, $BASE_URL);



// Resgata dados do usuário
$userData = $userDao->verifyToken();

if ($type === 'create') {

  // Receber os dados dos inputs
  $title = filter_input(INPUT_POST, "title");
  $description = filter_input(INPUT_POST, "description");
  $trailer = filter_input(INPUT_POST, "trailer");
  $category = filter_input(INPUT_POST, "category");
  $length = filter_input(INPUT_POST, "length");


  $movie = new Movie();

  if (!empty($title) && !empty($description) && !empty($category)) {
    $movie->title = $title;
    $movie->description = $description;
    $movie->trailer = $trailer;
    $movie->category = $category;
    $movie->length = $length;
    $movie->users_id = $userData->id;


    //Upload de imagem do filme 

    if (isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {


      $image = $_FILES["image"];
      $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
      $jpgArray = ["image/jpeg", "image/jpg"];


      $ext = strtolower(substr($image['name'], -4));

      // Checagem de tipo de imagem
      if (in_array($image["type"], $imageTypes)) {

        // Checar se jpg
        if ($ext == ".jpg") {

          $imageFile = imagecreatefromjpeg($image["tmp_name"]);

          // Imagem é png
        } else if ($ext == ".png") {

          $imageFile = imagecreatefrompng($image["tmp_name"]);
        }

        $imageName = $movie->imageGenerateName($ext);

        imagejpeg($imageFile, "./img/movies/" . $imageName, 100);

        $movie->image = $imageName;
      } else {
        $message->setMessage("Tipo inválido de imagem, insira png ou jpg!", "error", "back");
      }
    }



    $movieDao->create($movie);
  } else {
    $message->setMessage("Você precisa adicionar pelo menos: título, descrição e categoria!", "error", "back");
  }
} else {
  $message->setMessage("Informações inválidas!", "error", "index.php");
}
