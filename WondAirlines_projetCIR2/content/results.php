<!--
Results

Affiche les résultats de la recherche avec la possibilité d'en réserver un.
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Wond'Airlines - Résultats de la recherche </title>
    <?php include('head.php'); ?>
</head>
<body>
<?php include('navbar.php') ?>
<div class="container">
    <h1>Résultats de votre recherche</h1> <hr class='h1hr'>
    <p class='subtitle'> <?=$date_fr = implode('-', array_reverse(explode('-', $_POST['departureDate'])))?> </p>
    <h2> Résultats de la recherche </h2> <hr>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Date de départ</th>
            <th>Départ</th>
            <th>Heure de départ</th>
            <th>Arrivée</th>
            <th>Heure d'arrivée</th>
            <th>Prix</th>
            <th></th>
        </tr>
        </thead>
        <tbody id="table">
        <?php
        try{
            $db = new PDO('mysql:host=localhost;dbname=project_db;', 'adminISENG4', 'adm@*18l');
        }catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        // Plage de date

        $departureDate = $_POST['departureDate'];
        $plageDate = $_POST['plageDate'];
        $codeAO = $_POST['codeAO'];
        $codeAA = $_POST['codeAA'];

        $str = $departureDate;
        $plage = $plageDate;
        $time = strtotime($str);
        $date = getDate($time);
        $maxTime = mktime(0, 0, 0, $date['mon']  , $date['mday']+$plageDate, $date['year']);
        $maxDate = getDate($maxTime);
        $minTime = mktime(0, 0, 0, $date['mon']  , $date['mday']-$plageDate, $date['year']);
        $minDate = getDate($minTime);
        if ($maxDate['mon'] >= 10) {
            if ($maxDate['mday'] >= 10) {
                $maxd = $maxDate['year']."-".$maxDate['mon']."-".$maxDate['mday'];
            }
            else {
                $maxd = $maxDate['year']."-".$maxDate['mon']."-0".$maxDate['mday'];
            }
        } else {
            if ($maxDate['mday'] >= 10) {
                $maxd = $maxDate['year']."-0".$maxDate['mon']."-".$maxDate['mday'];
            }
            else {
                $maxd = $maxDate['year']."-0".$maxDate['mon']."-0".$maxDate['mday'];
            }
        }

        if ($minDate['mon'] >= 10) {
            if ($minDate['mday'] >= 10) {
                $mind = $minDate['year']."-".$minDate['mon']."-".$minDate['mday'];
            }
            else {
                $mind = $minDate['year']."-".$minDate['mon']."-0".$minDate['mday'];
            }
        } else {
            if ($minDate['mday'] >= 10) {
                $mind = $minDate['year']."-0".$minDate['mon']."-".$minDate['mday'];
            }
            else {
                $mind = $minDate['year']."-0".$minDate['mon']."-0".$minDate['mday'];
            }
        }

        $requestFlight1 = $db->prepare('select * from dateFlight df, flight f where f.id_vol = df.id_vol and df.nbPassenger >:passengerNb and df.date between :dateMinus and :datePlus
                                                                       and f.originAirport= :codeAO and f.destinationAirport=:codeAA order by df.date');
        $requestFlight1->execute(array('passengerNb'=> $_POST['passengerNb'],
            'dateMinus'=>$mind,
            'datePlus'=> $maxd,
            'codeAO'=>(' '.$codeAO),
            'codeAA'=>(' '.$codeAA)));

        $reponseFlight = $requestFlight1->fetch()['id_vol'];

        if( $reponseFlight== null){//si aucun résultat trouvé
            header('Location: research.php?error=2');
            $requestFlight1->closeCursor();
        }else{
            // Affiche les résultats dynamiquement
            $requestFlight1->closeCursor();
            $requestFlight2 = $db->prepare('select * from dateFlight df, flight f where f.id_vol = df.id_vol and df.nbPassenger >:passengerNb and df.date between :dateMinus and :datePlus
                                                                       and f.originAirport= :codeAO and f.destinationAirport=:codeAA order by df.date');
            $requestFlight2->execute(array('passengerNb'=> $_POST['passengerNb'],
                'dateMinus'=>$mind,
                'datePlus'=> $maxd,
                'codeAO'=>(' '.$codeAO),
                'codeAA'=>(' '.$codeAA)));

            while($data5 = $requestFlight2->fetch()){
                echo "<tr><td>" . $data5['id_vol'] . "</td>
                            <td>".implode('-', array_reverse(explode('-', $data5['date'])))."</td>
                            <td> " . $data5['originAirport'] . " - " . $data5['originCity'] . "</td>
                            <td>" . $data5['departureTime'] . "</td>
                            <td>" . $data5['destinationAirport'] . " - " . $data5['destinationCity'] . "
                            <td>" . $data5['arrivalTime'] . "</td>
                            <td>".returnPrice($data5['id_vol'], $data5['date'])."€</td>";

                echo "<td><form method='post' action='";
                if(isset($_SESSION['id'])){
                    echo "booking.php";
                }else{
                    echo "register.php?error=4";
                }
                echo"'><input type='hidden' name='id_vol' value='".$data5['id_vol']."'>
                            <input type='hidden' name='dDate' value='".$data5['date']."'>
                            <input type='hidden' name='passengerNb' value='" . $_POST['passengerNb'] . "'>";

                for ($i = 1; $i <= $_POST['passengerNb']; $i++) {
                    echo "<input type='hidden' name='Lname" . $i . "' value='" . $_POST['Lname' . $i] . "'>
                                            <input type='hidden' name='Fname" . $i . "' value='" . $_POST['Fname' . $i] . "'>
                                            <input type='hidden' name='birthDate" . $i . "' value='" . $_POST['birthdate' . $i] . "'>
                                            <input type='hidden' name='userMail" . $i . "' value='" . $_POST['userMail' . $i] . "'>
                                    ";
                }
                echo "<button type=\"submit\" class=\"btn btn-secondary\">Choisir</button>
                                </form>
                            </td>";
            }
            $requestFlight2->closeCursor();
        }

        ?>
        </tbody>
    </table>
</div>
<?php include('footer.php'); ?>
</body>
</html>

<!-- Etapes de calcul du prix total -->

<?php
function returnPrice($id, $dDate){
    try{
        $db = new PDO('mysql:host=localhost;dbname=project_db;', 'adminISENG4', 'adm@*18l');
    }catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    // Informations vol général
    $request = $db->prepare('SELECT * FROM flight WHERE id_vol = :id');
    $request->execute(array('id'=>$id));
    $data = $request->fetch();

    // Week End ?
    $isWE = false;
    $time = strtotime($dDate);
    $date = getDate($time);
    $dayOfWeek = $date['wday'];
    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
        $isWE = true;
    }

    // Nb places restantes
    $request = $db->prepare('SELECT * FROM dateFlight df, flight f WHERE df.id_vol = :id and df.id_vol = f.id_vol AND df.date = :date');
    $request->execute(array('id'=>$id, 'date'=>$dDate));
    $data = $request->fetch();
    $nbPassagers = $data['nbPassenger'];



    // Nb de jours avant le départ
    $diff = abs(strtotime(date('Y-m-d')) - strtotime($dDate));
    $years = floor($diff / (365*60*60*24));
    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
    $daysBeforeFlight = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));


    // Récupération des tarifs
    $tab = array();
    for($i =0;$i<$_POST['passengerNb'];$i++){
        $data['route']= str_replace(' ', '', $data['route']);
        $request2 = $db->prepare('SELECT * FROM fare WHERE route = :route');
        $request2->execute(array('route'=>$data['route']));

        while ($data2 = $request2->fetch()) {
            $pourcent =(int) ((($data['nbPassenger'] - $i) / $data['flightSize'])*100);

            if ($isWE == true && $data2['weFlight'] == 1) { //-- weekend
                // Détail avec date et nb passagers
                if($daysBeforeFlight >= 21 && $data2['DateToDeparture']  == 21 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }else if((10<=$daysBeforeFlight && $daysBeforeFlight<21) && $data2['DateToDeparture']  == 10 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }else if((3<=$daysBeforeFlight && $daysBeforeFlight<10) && $data2['DateToDeparture']  == 3 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }else if((0<=$daysBeforeFlight && $daysBeforeFlight<3) && $data2['DateToDeparture']  == 0 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }

            } else if ($isWE == false && $data2['weFlight'] == 0) {// non weekend
                // Détail avec date et nb passagers
                if($daysBeforeFlight >= 21 && $data2['DateToDeparture']  == 21 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }else if((10<=$daysBeforeFlight && $daysBeforeFlight<21) && $data2['DateToDeparture']  == 10 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }else if((3<=$daysBeforeFlight && $daysBeforeFlight<10) && $data2['DateToDeparture']  == 3 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }else if((0<=$daysBeforeFlight && $daysBeforeFlight<3) && $data2['DateToDeparture']  == 0 && ($pourcent >= $data2['fillingRate'])){
                    $tab[$i] = $data2['fare'];
                }
            }
        }
    }



    // Application des taxes d'aéroport
    $data['originAirport']= str_replace(' ', '', $data['originAirport']);
    $request3 = $db->prepare('SELECT * FROM airport WHERE airportCode = :airportCode');
    $request3->execute(array('airportCode'=>$data['originAirport']));
    $originTaxe = $request3->fetch()['surcharge'];

    $data['destinationAirport']= str_replace(' ', '', $data['destinationAirport']);
    $request4 = $db->prepare('SELECT * FROM airport WHERE airportCode = :airportCode');
    $request4->execute(array('airportCode'=>$data['destinationAirport']));
    $destTaxe = $request4->fetch()['surcharge'];

    for($i =0; $i<$_POST['passengerNb'];$i++){
        $tab[$i] += ($originTaxe + $destTaxe);
    }

    // Réduction si -4 ans
    for($i =1; $i<=$_POST['passengerNb'];$i++){
        $str = $_POST['birthdate' . $i];
        $strflight = $data['date'];
        $datetime1 = date_create($str);
        $datetime2 = date_create($strflight);
        $interval = date_diff($datetime1, $datetime2);
        $annee =$interval->format('%Y');
        if($annee < 4){
            $tab[$i-1] /= 2;
        }
    }

    // Calcul prix total
    $prixTotal = 0;
    foreach ($tab as $value){
        $prixTotal += $value;
    }

    return $prixTotal;
}

?>
