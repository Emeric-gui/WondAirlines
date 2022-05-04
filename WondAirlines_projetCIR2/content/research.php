<!--
Research

Recherche de vol selon différents critères
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Wond'Airlines - Rechercher un vol </title>
    <?php include('head.php'); ?>
</head>
<body>
<?php include('navbar.php') ?>
<div class="alert alert-warning" role="alert">
    Connectez-vous avant toute reservation.
</div>
<div class="container">
    <h1>Rechercher un vol</h1> <hr class='h1hr'>
    <?php

    if(isset($_GET['error'])){
        if($_GET['error'] == 1){
            echo"<div class='alert alert-danger' role='alert'>
              			Entrez une date postérieure.
            			</div>";
        }else if($_GET['error'] == 2){
            echo"<div class='alert alert-danger' role='alert'>
              			Aucun vol trouvé.
            			</div>";
        }
    }

    if (isset($_POST['codeAO']) && isset($_POST['codeAA']) && isset($_POST['departureDate']) && isset($_POST['passengerNb'])){
        $str = $_POST['departureDate'];
        $timestampForm = strtotime($str);
        $timestampNow = time();

        if ($timestampNow > $timestampForm) {
            header('Location: research.php?error=1');
        }else{
            ?>
            <h2>Information des passagers</h2> <hr>
            <!-- Ensemble des critères de recherche -->
            <form method="post" action="results.php">
                <input type="hidden" name="codeAO" value="<?= $_POST['codeAO']?>">
                <input type="hidden" name="codeAA" value="<?= $_POST['codeAA']?>">
                <input type="hidden" name="departureDate" value="<?= $_POST['departureDate']?>">
                <input type="hidden" name="passengerNb" value="<?= $_POST['passengerNb']?>">
                <input type="hidden" name="plageDate" value="<?= $_POST['plageDate']?>">

                <div class="form-group">
                    <div class="container">
                        <?php
                        for($i = 1;$i<=$_POST['passengerNb'];$i++) {
                            echo "<div class='row'>
                                <div class='offset-md-2 col-md-8'>
                                   <h4>Passager n°".$i."</h4>
                                   <label for='nom".$i."'>Nom</label>
                                   <input type='text' id='nom".$i."' name='Lname".$i."' minlength='3' maxlength='50' class='form-control' required>
                                   <label for='prenom".$i."'>Prenom</label>
                                   <input type='text' id='prenom".$i."' name='Fname".$i."' minlength='3' maxlength='50' class='form-control' required>
                                   <label for='dateN".$i."'>Date de naissance</label>
                                   <input type='date' id='dateN".$i."' name='birthdate".$i."' class='form-control' required>
                                   <label for='mail'>Adresse Mail</label>
                                   <input type='email' minlength='3' maxlength='50' id='mail' name='userMail".$i."' required class='form-control'>
                                   <hr/>
                                </div>
                                </div>";
                        } ?>
                    </div> <br>
                    <button style="display: block; margin: auto" class="btn btn-secondary" type="submit">Envoyer</button>
                </div> <br>
            </form>
        <?php }
    } else { ?>
        <h2>Paramètres de recherche</h2> <hr>
        <form method="post" action="research.php">
            <div class="form-group">
                <label for="codeAO">Code Aéroport d'Origine</label>
                <input type="text" minlength='3' maxlength='50' id="codeAO" name="codeAO" class="form-control" list='airports' required>
                <label for="codeAA">Code Aéroport d'Arrivé</label>
                <input type="text" minlength='3' maxlength='50' id="codeAA" name="codeAA" class="form-control" list='airports' required>
                <!-- Liste de tous les aéroports existants -->
                <datalist id='airports'>
                    <?php
                    try{
                        $db = new PDO('mysql:host=localhost;dbname=project_db;', 'adminISENG4', 'adm@*18l');
                    }catch (Exception $e) {
                        die('Erreur : ' . $e->getMessage());
                    }

                    $requestAirport = $db->query('select airportCode, city from airport order by city');
                    while($dataAirport = $requestAirport->fetch()){
                        echo "<option value='".$dataAirport['airportCode']."'>".$dataAirport['city']." - ".$dataAirport['airportCode']."</option>";
                    }


                    ?>
                </datalist>
                <label for="dateD">Date de départ</label>
                <input type="date" id="dateD" name="departureDate" class="form-control" required>
                <label for="plageD">Plage de date (+- x jours)</label>
                <select id="plageD" name="plageDate" class="custom-select">
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
                <label for="nbpass">Nombre de passagers (9 maximum)</label>
                <input type="number"  id="nbpass" name="passengerNb" class="form-control" required min="1" max="9">
                <br>
                <!-- Options de prix (pas implémenté) -->
                <!--                    <label for="priceMin">Prix du Vol (HT) minimum</label>-->
                <!--                    <input class="form-control" type="number" min="1" max="2500" id="priceMin" name="minPrice">-->
                <!--                    <label for="priceMax">Prix du Vol (HT) maximum</label>-->
                <!--                    <input class="form-control" type="number" min="1" max="2500" id="priceMax" name="maxPrice">-->
                <br>
                <button type="submit" class="btn btn-secondary">Envoyer</button>
            </div>
        </form>
    <?php } ?>
</div>
<?php include('footer.php') ?>
</body>
</html>
