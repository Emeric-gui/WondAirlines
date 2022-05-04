<!--
Login

Page de connexion
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Wond'Airlines - Se connecter </title>
    <?php include('head.php'); ?>
</head>
<body>
<?php include('navbar.php');
if(isset($_SESSION['id'])){
    header('Location: myaccount.php');
}else{

    if(isset($_GET['error'])){

        if ($_GET['error'] == 1) {
            echo"<div class='alert alert-danger' role='alert'>
              			Le mot de passe ou le nom d'utilisateur est incorrect.
            			</div>";
        } else if ($_GET['error'] == 0) {
            echo"<div class='alert alert-danger' role='alert'>
          		    	Vos changements ont bien été effectués, reconnectez vous pour voir les changements.
            			</div>";
        }
    }
}
?>
<div class='container'>
    <h2> Se connecter </h2> <hr>
    <form method='POST' action='controller.php?func=login'>
        <div class='form-group'>
            <label for='username'>Pseudonyme</label>
            <input type='text'minlength='3' maxlength='50' class='form-control' id='username' name='username' required>
        </div>
        <div class='form-group'>
            <label for='password'>Mot de passe</label>
            <input type='password' class='form-control' minlength='3' maxlength='50' id='password' name='password' required>
        </div>
        <button type='submit' class='btn btn-secondary'>S'identifier</button>
    </form> <br>
    <a href='register.php'> Pas encore inscrit ? </a>
</div>
<?php include('footer.php') ?>
</body>
</html>
