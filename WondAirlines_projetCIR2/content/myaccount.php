<!--
My account

Vue globale sur le compte
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Wond'Airlines - Mon compte </title>
    <?php include('head.php');
    ?>
</head>
<body>
<!-- Gestion des erreurs -->
<?php include('navbar.php');
if(!isset($_SESSION['id'])){
    header('Location: ../index.php');
}
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
					Le mot de passe pour confirmer est invalide.
					</div>";
    }else if($_GET['error'] == 3){
        echo "<div class='alert alert-danger' role='alert'>
					Adresse mail invalide. </div>";
    }
}
?>

<div class='container'>
    <h1> Mon compte </h1> <hr class='h1hr'>
    <p class='subtitle'> <?= $_SESSION['username'] ?> </p>
    <h2> Informations générales </h2> <hr>
    <p> <b> Nom </b> <?= $_SESSION['name']?></p>
    <p> <b> Prénom </b> <?= $_SESSION['fname']?></p>
    <p> <b> Email </b> <?= $_SESSION['email'] ?> </p>
    <h2> Mes vols </h2> <hr>
    <table class="table">
        <thead>
        <tr>
            <th scope="col">ID</th>
            <th scope="col">Date de départ</th>
            <th scope="col">Aéroport de départ</th>
            <th scope="col">Heure de départ</th>
            <th scope="col">Aéroport d'arrivée</th>
            <th scope="col">Heure d'arrivée</th>
            <th scope="col">Nombre personnes</th>
            <th scope="col">Prix</th>
            <th scope="col">Annuler</th>
        </tr>
        </thead>
        <tbody>

        <!-- Récupération d'informations -->

        <?php
        // Connection à la BDD
        try{
            $db = new PDO('mysql:host=localhost;dbname=project_db;', 'root', '');
        }catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        // Récupération de toutes les informations des vols que l'utilisateur a réservé

        $requestBooking = $db->prepare('select * from booking where id_user = :id_user');
        $requestBooking->execute(array('id_user'=>$_SESSION['id']));

        while($data = $requestBooking->fetch()){
            $requestDate = $db->prepare('select * from dateFlight df, flight f where df.id_vol = f.id_vol and df.id_dateFlight=:id_dateFlight');
            $requestDate->execute(array('id_dateFlight'=>$data['id_dateFlight']));
            $data2 = $requestDate->fetch();
            echo "<tr>
            <th scope=\"row\">".$data2['id_vol']."</th>
            <td>".implode('-', array_reverse(explode('-', $data2['date'])))."</td>
            <td> " . $data2['originAirport'] . " - " . $data2['originCity'] . "</td>
            <td>" . $data2['departureTime'] . "</td>
            <td>" . $data2['destinationAirport'] . " - " . $data2['destinationCity'] . "
            <td>" . $data2['arrivalTime'] . "</td>
            <td>".$data['nbPerson']."</td>
            <td>".$data['totalPrice']."€</td>
            <td><a role='button' class='btn btn-secondary' href='controller.php?func=unBook&id_booking=".$data['id_booking']."'>Annuler</a></td>
        </tr>";
        }

        ?>

        </tbody>
    </table>

    <h2> Modifier mes informations personnelles </h2> <hr>
    <form method='POST' action='controller.php?func=modifAccount'>
        <div class='form-group'>
            <label for='name'>Nom</label>
            <input type='text' minlength='3' maxlength='50' class='form-control' id='name' name='name' value='<?= $_SESSION['name']?>' required>
        </div>
        <div class='form-group'>
            <label for='fname'>Prénom</label>
            <input type='text' minlength='3' maxlength='50' class='form-control' id='fname' name='fname' value='<?= $_SESSION['fname']?>' required>
        </div>
        <div class='form-group'>
            <label for='username'>Pseudonyme</label>
            <input type='text' minlength='3' maxlength='50'class='form-control' id='username' name='username' value='<?= $_SESSION['username']?>' required>
        </div>
        <div class='form-group'>
            <label for='mail'>Mail</label>
            <input type='email' minlength='3' maxlength='50' class='form-control' id='mail' name='mail' value='<?= $_SESSION['email']?>' required>
        </div>
        <div class='form-group'>
            <label for='passwd'>Mot de passe</label>
            <input type='password' minlength='3' maxlength='50' class='form-control' id='passwd' name='passwd' required>
        </div>
        <div class='form-group'>
            <label for='password'> <hr> Mot de passe</label>
            <input type='password' minlength='3' maxlength='50' class='form-control' id='password' name='password' placeholder='Entrez votre mot de passe pour confirmer les changements' required>
        </div>
        <button type='submit' class='btn btn-secondary'>Modifier mes changements</button>
    </form>
    <br>
    <a href='controller.php?func=logout' role='button' class='btn btn-secondary'>Me déconnecter</a>
</div>
<?php include('footer.php') ?>
</body>
</html>
