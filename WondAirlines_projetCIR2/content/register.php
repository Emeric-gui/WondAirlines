<!--
Register

Page d'inscription
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Wond'Airlines - Créer un compte</title>
    <?php include('head.php'); ?>
</head>
<body>
<?php include('navbar.php');

if(isset($_SESSION['id'])){
    header('Location: myaccount.php');
}else{

    if (isset($_GET['error'])) {
        if($_GET['error'] == 11){
            echo "<div class='alert alert-danger' role='alert'>
					Nom d'utilisateur refusé, liste des caracteres speciaux autorisés ->  _
					</div>";
        }else if($_GET['error'] == 12){
            echo "<div class='alert alert-danger' role='alert'>
					Nom d'utilisateur non disponible.
					</div>";
        }else if($_GET['error'] ==21){
            echo "<div class='alert alert-danger' role='alert'>
					Mot de passe refusé, il faut au moins 8 caractères dont 1 lettre majuscule, 1 lettre minuscule, 1 chiffre et 1 caractère spécial. 
					</div>";
        }else if($_GET['error'] == 22){
            echo "<div class='alert alert-danger' role='alert'>
      				Entrez un mot de passe identique au précédent.
    				</div> ";
        }else if($_GET['error'] == 3){
            echo "<div class='alert alert-danger' role='alert'>
					Adresse mail invalide. </div>";
        }else if($_GET['error'] == 4){
            echo "<div class='alert alert-danger' role='alert'>
					Une inscription / connexion est requise pour effectuer cette action </div>";
        }
    }
}
?>

<div class='container'>
    <h2> Créer un compte </h2> <hr>
    <form method='POST' action='controller.php?func=register'>
        <div class='form-group'>
            <label for='name'>Nom</label>
            <input type='text' minlength='3' maxlength='50' class='form-control' id='name' name='name' required>
        </div>
        <div class='form-group'>
            <label for='fname'>Prénom</label>
            <input type='text' minlength='3' maxlength='50' class='form-control' id='fname' name='fname' required>
        </div>
        <div class='form-group'>
            <label for='username'>Pseudonyme</label>
            <input type='text' minlength='3' maxlength='50' class='form-control' id='username' name='username' required>
        </div>
        <div class='form-group'>
            <label for='mail'>Mail</label>
            <input type='email' minlength='3' maxlength='50' class='form-control' id='mail' name='mail' required>
        </div>
        <div class='form-group'>
            <label for='password'>Mot de passe</label>
            <input type='password' minlength='3' maxlength='50' class='form-control' id='password' name='password' required>
        </div>
        <div class='form-group'>
            <label for='password2'>Mot de passe</label>
            <input type='password' minlength='3' maxlength='50' class='form-control' id='password2' name='password2' required>
        </div>
        <button type='submit' class='btn btn-secondary'>Créer mon compte</button>
    </form> <br>
    <a href='login.php'> Déjà inscrit ? </a>
</div>
<?php include('footer.php') ?>
</body>
</html>
