<?php

require_once("models/Review.php");
require_once("models/Message.php");

require_once("dao/UserDAO.php");

class ReviewDao implements ReviewDAOInterface
{



  private $conn;
  private $url;
  private $message;



  public function __construct(PDO $conn, $url)
  {

    $this->conn = $conn;
    $this->url = $url;
    $this->message = new Message($url);
  }



  public function buildReview($data)
  {

    $reviewObject = new Review();

    $reviewObject->id = $data["id"];
    $reviewObject->rating = $data["rating"];
    $reviewObject->review = $data["review"];
    $reviewObject->users_id = $data["users_id"];
    $reviewObject->movies_id = $data["movies_id"];

    return $reviewObject;
  }
  public function create(Review $review)
  {
    $stmt = $this->conn->prepare('INSERT INTO review (rating, review, movies_id, users_id) VALUES (:rating, :review, :movies_id, :users_id)');

    $stmt->bindParam(":rating", $review->rating);
    $stmt->bindParam(":review", $review->review);
    $stmt->bindParam(":movies_id", $review->movies_id);
    $stmt->bindParam(":users_id", $review->users_id);



    $stmt->execute();

    // Redireciona para o perfil do usuario
    $this->message->setMessage("Crítica adicionado com sucesso!", "success", "index.php");
  }
  public function getMoviesReview($id)
  {
    $reviews = [];

    // encontrar as reviews na tabela de review
    $stmt = $this->conn->prepare("SELECT * FROM review WHERE movies_id = :id");

    $stmt->bindParam("id", $id);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {

      $userDao = new userDAO($this->conn, $this->url);

      $reviewsArray = $stmt->fetchAll();

      foreach ($reviewsArray as $review) {

        $reviewObject = $this->buildReview($review);

        // pegar dados do usuário
        $user = $userDao->findById($reviewObject->users_id);

        // Adiciona usuário na review
        $reviewObject->user = $user; // aqui esta o erro

        $reviews[] = $reviewObject;
      }
    }

    return $reviews;
  }
  public function hasAlreadyReviewed($id, $userId)
  {
    $stmt = $this->conn->prepare("SELECT * FROM review WHERE movies_id = :movies_id AND users_id = :users_id");

    $stmt->bindParam(":movies_id", $id);
    $stmt->bindParam(":users_id", $userId);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
      return true;
    } else {
      return false;
    }
  }
  public function getRatings($id)
  {
    $stmt = $this->conn->prepare("SELECT * FROM reviews WHERE movies_id = :movies_id");

    $stmt->bindParam(":movies_id", $id);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {

      $rating = 0;

      $reviews = $stmt->fetchAll();

      foreach ($reviews as $review) {
        $rating += $review["rating"];
      }

      $rating = $rating / count($reviews);
    } else {
      $rating = "Não avaliado";
    }
  }
}
