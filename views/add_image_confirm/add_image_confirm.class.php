<?php
/*
 * Author: Kailey Hart
 * Date: 03-04-2021
 * Name: add_image_confirm.php
 * Description: Image Added Confirmation
*/

class AddImageConfirm extends MainView
{
    function show($result)
    {
        parent::header();

        if (!isset($_SESSION["pk_user_id"])) {
            echo '<h3>You are not logged in. Check your credentials and try again.</h3>
            <a href="index.php?action=register"><button class="btn btn-dark mt-5">Try Again</button></a>';
        }

        print_r($result);
?>



        <div class="top-row">
            <h2>You successfully uploaded an image!</h2>


        </div>




<?php
        parent::footer();
    }
}
