<?php
/*
 * Author: Kailey Hart
 * Date: 02-12-2021
 * Name: user_model.class.php
 * Description: Manages user data
*/

require_once('app/class.database.php');

class UserModel
{
  private $db;
  private $conn;

  function __construct()
  {
    $this->db = Database::getInstance();
    $this->conn = $this->db->getSQL();
  }

  //Delete user
  function delete()
  {
  }

  //Reset password?
  function reset()
  {
  }

  function register()
  {
  }

  //Registers user and shows confirmation page
  function register_confirm()
  {

    if (isset($_POST['submit'])) {

      $username = '';
      $password = '';
      $lastname = '';
      $firstname = '';

      $username = trim(filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING));
      $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));
      $firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING));
      $lastname = trim(filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_STRING));

      $image = $_FILES['image']['name'];
      $tempname = $_FILES["image"]["tmp_name"];
      $folder = "dist/images/" . $image;
      //encode the password
      $hash = password_hash($password, PASSWORD_DEFAULT);

      echo "First Name:" . $firstname . "<br>";
      echo "Last Name:" . $lastname . "<br>";
      echo "Username:" . $username . "<br>";
      echo "Password:" . $hash . "<br><br>";
      echo $image;

      $sql = "INSERT INTO " . $this->db->getUserTable() . " (username, password, first_name, last_name, user_img ) VALUES ('$username', '$hash', '$firstname', '$lastname', '$image')";
      echo "SQL:" . $sql . "<br>";
      echo "<hr>";

      if (move_uploaded_file($tempname, $folder)) {
        echo "Image uploaded successfully";
      } else {
        echo "Failed to upload image";
      }

      $result = $this->db->insert_user($sql);

      $user_id = mysqli_insert_id($this->conn);
      if ($user_id) {
      session_start();
        $_SESSION["pk_user_id"] = $user_id;
      }

      echo "result: " . $result;

      return $result;
    }
  }

  //Login Confirmation
  function login_confirm()
  {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = trim(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING));

    $sql = "SELECT password FROM " . $this->db->getUserTable() . " WHERE username='$username'";

    $result = $this->conn->query($sql);
    $row = mysqli_fetch_assoc($result);
    print_r($row['password']);

    $hash = $row['password'];

    if (password_verify($password, $hash)) {
      $correctPassword = true;
    } else {
      $correctPassword = false;
    }

    echo 'correct password: ' . $correctPassword;
    if ($correctPassword) {

      $sql = "SELECT * FROM " . $this->db->getUserTable() . " WHERE username='$username'";

      $result = $this->conn->query($sql);
      $row = mysqli_fetch_assoc($result);

      session_start();
      $_SESSION["pk_user_id"] = $row["pk_user_id"];
    }
  }

  function logout_confirm()
  {
    session_start();
    unset($_SESSION);
    session_destroy();
  }

  function profile()
  {
  }

  function add_gallery()
  {
  }

  function add_image_confirm()
  {
    session_start();

    if (isset($_POST['submit'])) {

      $galleryId = "";
      $image = "";
      $imageAlt = "";
      $tag = $_POST["tag"];

      $galleryId = $_GET["id"];
      $image = filter_input(INPUT_POST, "image", FILTER_SANITIZE_STRING);
      $imageAlt = filter_input(INPUT_POST, "imagealt", FILTER_SANITIZE_STRING);



      $image = $_FILES["galleryImage"]["name"];
      $tempname = $_FILES["galleryImage"]["tmp_name"];
      $folder = "dist/galleryImages/" . $image;

      echo "galleryID: " . $galleryId . "<br>";
      echo "image: " . $image . "<br>";
      echo "image alt: " . $imageAlt . "<br>";

      if (move_uploaded_file($tempname, $folder)) {
        echo "Image uploaded.";
      } else {
        echo "Failed.";
      }

      $sql =
        "INSERT INTO final_images (fk_gallery_id, img_path, img_alt ) VALUES ('$galleryId', '$folder', '$imageAlt')";
      // INSERT INTO final_tags (tag_name, fk_image_id) VALUES ('$tag', '$imageId');";
     
      
        $result = $this->db->insert_img($sql);

      $sql = "SELECT pk_img_id FROM final_images ORDER BY pk_img_id DESC LIMIT 1";

        $IDresult = $this->conn->query($sql);

        print_r($IDresult);

        $imageInfo = array();
        while ($row = $IDresult->fetch_assoc()) {
          $imageInfo[] = $row;
        }

        print_r($imageInfo);
       $imageID = $imageInfo[0]["pk_img_id"];

       $sql =
        "INSERT INTO final_img_tags (fk_img_id, fk_tag_id) VALUES ('$imageID', '$tag')";

        $result = $this->db->insert_tag($sql);

        print_r($result);
    }
  }


  function add_image()
  {  
  }

  function display_single_gallery_images() {
    session_start();

    $gallery_id = $_GET["id"];
    // $sql = "SELECT * FROM final_images WHERE fk_gallery_id = $gallery_id";
    $sql = "SELECT *
    FROM final_images i, final_tags t, final_img_tags it
    WHERE i.pk_img_id = it.fk_img_id
    AND t.pk_tag_id = it.fk_tag_id
    AND i.fk_gallery_id = $gallery_id";

    print_r($sql);

    $results = $this->conn->query($sql);

      $images = array();

      while ($row = $results->fetch_assoc()) {
        $images[] = $row;
      }
      return $images;
      print_r($images);
  }

  //Adds gallery
  function single_gallery_view()
  {
    $galleryName = '';
    $galleryName = trim(filter_input(INPUT_POST, "galleryName", FILTER_SANITIZE_STRING));

    session_start();
    if ($_GET["id"]) {
      $sql = "SELECT * FROM final_gallery WHERE fk_user_id = '" . $_SESSION['pk_user_id'] . "' AND pk_gallery_id = '" . $_GET["id"] . "'";
      echo "SQL:" . $sql . "<br>";
      echo "<hr>";
      $result = $this->conn->query($sql);

      $galleryInfo = array();

      while ($row = $result->fetch_assoc()) {
        $galleryInfo[] = $row;
      }
      return $galleryInfo;
    } else {
      $sql = "INSERT INTO " . $this->db->getGalleryTable() . " (fk_user_id, gallery_name) VALUES ('" . $_SESSION['pk_user_id'] . "','$galleryName')";
      echo "SQL:" . $sql . "<br>";

      $result = $this->db->insert_gallery($sql);
      return $result;
    }
  }

  //Get all of the usernames of all of the users
  function get_all_usernames()
  {
    $sql = "SELECT * FROM final_users";

    $usernames = $this->conn->query($sql);
    return $usernames;
  }

  //Returns the username of a user
  function get_last_username()
  {
    $user_id = $_SESSION["pk_user_id"];
    $sql = "SELECT * FROM final_users WHERE pk_user_id = $user_id";

    $username = $this->db->get_last_username($sql);

    return $username;
  }
  

 //Gets the gallery name for a single user
  function get_gallery_name()
  {
    session_start();

    $user_id = $_SESSION["pk_user_id"];
    $gallerySql = "SELECT * FROM final_gallery WHERE fk_user_id = $user_id";

    $results = $this->conn->query($gallerySql);

    if ($results === 0) {
      echo "No galleries were found. Please add one.";
    } else {

      foreach ($results as $result) {
        $galleryId = $result['pk_gallery_id'];
      }
    }

    $sql = "SELECT * FROM final_gallery WHERE pk_gallery_id = $galleryId";

    $galleryName = $this->db->get_gallery_name($sql);

    return $galleryName;

    
  }

  //Gets the profile image of a user
  function get_profile_img()
  {
    $user_id = $_SESSION["pk_user_id"];
    $sql = "SELECT * FROM final_users WHERE pk_user_id = $user_id";

    $image = $this->db->get_profile_img($sql);

    return $image;
  }

  //Gets all the galleries for one user
  function get_single_user_galleries()
  {
    session_start();

    $user_id = $_SESSION["pk_user_id"];
    $gallerySql = "SELECT * FROM final_gallery WHERE fk_user_id = $user_id";

    $results = $this->conn->query($gallerySql);

    return $results;
  }


  //Gets all images, tags, galleries, and users to display on Index
  function get_all_galleries()
  {
    session_start();

    //Retrieves info from images, tags, img_tags, gallery, and users table
    //Doesn't duplicate the information
    $sql = "SELECT *
    FROM final_images i, final_tags t, final_img_tags it, final_gallery g, final_users u
    WHERE i.pk_img_id = it.fk_img_id
    AND t.pk_tag_id = it.fk_tag_id
    AND g.pk_gallery_id = i.fk_gallery_id
    AND g.fk_user_id = u.pk_user_id";

    $results = $this->conn->query($sql);
    return $results;
  }
}
