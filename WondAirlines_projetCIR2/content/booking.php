<!--
Booking

Confirmation du vol sélectionné avec tous les détails du vol
-->

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title> Wond'Airlines - Booker un vol </title>
    <?php include('head.php'); ?>
</head>
<body>
<?php include('navbar.php') ?>
<div class='container'>
    <h1> Confirmer votre réservation </h1> <hr class='h1hr'>
    <h2> Informations du vol </h2> <hr>
    <?php

    // Connexion BDD
    try{
        $db = new PDO('mysql:host=localhost;dbname=project_db;', 'adminISENG4', 'adm@*18l');
    }catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }

    // Informations vol général
    $request = $db->prepare('SELECT * FROM flight WHERE id_vol = :id');
    $request->execute(array('id'=>$_POST['id_vol']));
    $data = $request->fetch();

    // Week End ?
    $isWE = false;
    $time = strtotime($_POST['dDate']);
    $date = getDate($time);
    $dayOfWeek = $date['wday'];
    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
        $isWE = true;
    }

    // Nb places restantes
    $request = $db->prepare('SELECT * FROM dateFlight df, flight f WHERE df.id_vol = :id and df.id_vol = f.id_vol AND df.date = :date');
    $request->execute(array('id'=>$_POST['id_vol'], 'date'=>$_POST['dDate']));
    $data = $request->fetch();
    $nbPassagers = $data['nbPassenger'];

    // Nb de jours avant le départ
    $diff = abs(strtotime(date('Y-m-d')) - strtotime($_POST['dDate']));
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

            if ($isWE == true && $data2['weFlight'] == 1) { // Weekend
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

            } else if ($isWE == false && $data2['weFlight'] == 0) { // Non weekend
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
        $str = $_POST['birthDate' . $i];
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

    // Affichage des informations
    echo "<p>
					<b> ID : </b> ".$_POST['id_vol']." <br>
					<b> Jour de départ : </b>".$date_fr = implode('-', array_reverse(explode('-', $_POST['dDate'])))."<br>
					<b> Départ : </b>".$data['originAirport']." ".$data['originCity']."<br>
					<b> Arrivée : </b>".$data['destinationAirport']." ".$data['destinationCity']."<br>
					<b> Heure de départ : </b>".$data['departureTime']."<br>
					<b> Heure d'arrivée : </b>".$data['arrivalTime']."<br>
					<b> Nombre de passagers : </b> ".$_POST['passengerNb']." <br>
					<b> Coût total : </b> ".$prixTotal." €
				</p>
				<h2> Informations sur les passagers </h2> <hr>
				<table class='table'>
					<thead>
						<tr>
							<th scope='col'>Nom</th>
							<th scope='col'>Prenom</th>
							<th scope='col'>E-mail</th>
							<th scope='col'>Date de naissance</th>
							<th scope='col'>Prix TTC</th>
						</tr>
					</thead>
					<tbody>";
    for($i=1;$i<=$_POST['passengerNb'];$i++){
        echo "<tr>
								<td>".$_POST['Lname'.$i]."</td>
								<td>".$_POST['Fname'.$i]."</td>
								<td>".$_POST['userMail'.$i]."</td>
								<td>".$date_fr = implode('-', array_reverse(explode('-', $_POST['birthDate'.$i])))."</td>
								<td>".$tab[$i-1]." €</td>
								</tr>";
    }
    echo "</tbody>
				</table>
				<form method='post' action='controller.php?func=book'>
					<input type='hidden' name='id_vol' value='".$_POST['id_vol']."'>
					<input type='hidden' name='ttlprice' value='".$prixTotal."'>
					<input type='hidden' name='dateFlight' value='".$_POST['dDate']."'>
					<input type='hidden' name='nbPassenger' value='".$_POST['passengerNb']."'>
					<input type='hidden' name='id_vol' value='".$_POST['id_vol']."'>";
    for ($i = 1; $i <= $_POST['passengerNb']; $i++) {
        echo "<input type='hidden' name='Lname" . $i . "' value='" . $_POST['Lname' . $i] . "'>
												<input type='hidden' name='Fname" . $i . "' value='" . $_POST['Fname' . $i] . "'>
												<input type='hidden' name='birthDate" . $i . "' value='" . $_POST['birthDate' . $i] . "'>
												<input type='hidden' name='userMail" . $i . "' value='" . $_POST['userMail' . $i] . "'>
										";
    }
    echo "<button type='submit' class='btn btn-secondary'>Confirmer</button>
				</form>";
    ?>
</div>
<?php include('footer.php') ?>
</body>
</html>