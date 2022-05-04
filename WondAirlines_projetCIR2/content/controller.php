<?php

session_start();
function dbConnect(){
    try{
        $db = new PDO('mysql:host=localhost;dbname=project_db;', 'root', '');
    }catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    }
    return $db;
}

// S'enregistrer

function register() {
    $db = dbConnect();
    $erreur = 0;
    // Username disponible ?
    $reqUser = $db->prepare('select count(login) as nb from user where login = :username');
    $reqUser->execute(array('username'=>htmlspecialchars($_POST['username'])));

    $username = htmlspecialchars($_POST['username']);
    //seul le caractère underscore est utilisable + lettres de l'alphabet --> Regex username
    $usernameRegex = '#^[a-z0-9A-Z_]+$#';

    if(preg_match($usernameRegex, $_POST['username']) ===0){//erreur login 11
    //si la value ne respecte pas la regex

        header('Location: register.php?error=11');
        $erreur =1;
    }

    if(($reqUser->fetch()['nb'] != 0) && $erreur == 0){//erreur login 12
        //si le pseudonyme est deja pris

        header('Location: register.php?error=12');
        $erreur =1;

    }else if($erreur == 0){//verification mail
        //Regex mail
        if(preg_match("#^[a-z0-9_.-]+@[a-z0-9_.-]{2,}\.[a-z]{2,4}$#", $_POST['mail']) === 0){//erreur dans le mail 3
            //si la value ne respecte pas la regex

            header('Location: register.php?error=3');
            $erreur =1;

        }else if($erreur == 0){//verif password

            if (htmlspecialchars($_POST['password']) == htmlspecialchars($_POST['password2'])) {
                //regex password
                $passwordRegex = "#^[a-zA-Z0-9_@*?!\#-]{8,}$#";
                if(preg_match($passwordRegex, $_POST['password']) === 0){//erreur password 21

                    //si le mot de passe ne respecte pas les conditions

                    header('Location: register.php?error=21');
                    $erreur = 1;
                }else if($erreur == 0) {
                    $password = password_hash(htmlspecialchars($_POST['password']), PASSWORD_DEFAULT);
                    $request = $db->prepare('INSERT INTO user(nom, prenom, login, password, mail) VALUES(:name, :fname, :login, :password, :mail)');
                    $request->execute(array('name' => htmlspecialchars($_POST['name']),
                        'fname' => htmlspecialchars($_POST['fname']),
                        'login' => htmlspecialchars($_POST['username']),
                        'password' => $password,
                        'mail' => htmlspecialchars($_POST['mail'])));
                    header('Location: login.php');
                }
            }else {//erreur mot de passe 22
                header('Location: register.php?error=22');
                //si les 2 mots de passe envoyés ne correspondent pas
                $erreur = 1;
            }
        }
    }
}

// Se connecter

function login() {
    // Début de session si les mots de passe sont équivalents
    $db = dbConnect();
    $passwordreq = $db->prepare('SELECT * FROM user WHERE login=:login');
    $passwordreq->execute(array('login'=>htmlspecialchars($_POST['username'])));
    $data = $passwordreq->fetch();
    $password = $data['password'];
    $isvalid = password_verify(htmlspecialchars($_POST['password']), $password);
    if ($isvalid) {
        session_start();
        $_SESSION['id'] = $data['id_user'];
        $_SESSION['name'] = $data['nom'];
        $_SESSION['username'] = $data['login'];
        $_SESSION['email'] = $data['mail'];
        $_SESSION['fname'] = $data['prenom'];
        header('Location: myaccount.php');
    } else {
        header('Location: login.php?error=1');
    }
}

// Se déconnecter

function logout() {
    session_destroy();
    header('Location: ../index.php');
}

// Modification des informations du compte

function modifAccount() {

    // Utilisation des regex

    $usernameRegex = '#^[a-z0-9A-Z_]+$#';
    $erreur = 0;

    if(preg_match($usernameRegex, $_POST['username']) === 0){//erreur login 11
        $erreur = 1;
        header('Location: myaccount.php?error=11');
    }else if($erreur == 0){
        $db = dbConnect();
        $requestUser = $db->prepare('select id_user, count(id_user) as nb from user where login = :username');
        $requestUser->execute(array('username'=>$_POST['username']));
        $dataAccount = $requestUser->fetch();
        if(($dataAccount['nb'] != 0) && $erreur == 0 && $dataAccount['id_user'] != $_SESSION['id']){//erreur login 12
//            echo $requestUser-
            header('Location: myaccount.php?error=12');
            $erreur =1;

        }else if($erreur == 0){// erreur mail 3
            if(preg_match("#^[a-z0-9_.-]+@[a-z0-9_.-]{2,}\.[a-z]{2,4}$#", $_POST['mail']) === 0){
                //erreur dans le mail 3
                header('Location: myaccount.php?error=3');
                $erreur =1;

            }else if($erreur == 0) {

                // Vérification du mot de passe

                $passwordreq = $db->prepare('SELECT * FROM user WHERE login=:login');
                $passwordreq->execute(array('login'=>htmlspecialchars($_SESSION['username'])));
                $data = $passwordreq->fetch();
                $password = $data['password'];
                $id = $data['id_user'];
                $isvalid = password_verify(htmlspecialchars($_POST['password']), $password);
                if ($isvalid) {
                    $passwordRegex = "#^[a-zA-Z0-9_@*?!\#-]{8,}$#";
                    if(preg_match($passwordRegex, $_POST['passwd']) === 0){
                        $erreur =1;
                        header('Location: myaccount.php?error=21');
                    }else if($erreur == 0){

                        // Update des informations

                        $request = $db->prepare('UPDATE user SET nom = :nom, prenom = :prenom, login = :login, mail = :mail, password = :password WHERE id_user = :id');
                        $request->execute(array('nom'=>htmlspecialchars($_POST['name']),
                            'prenom'=>htmlspecialchars($_POST['fname']),
                            'login'=>htmlspecialchars($_POST['username']),
                            'mail'=>htmlspecialchars($_POST['mail']),
                            'password'=>(password_hash(htmlspecialchars($_POST['passwd']),PASSWORD_DEFAULT)),
                            'id' => $id));
                        session_destroy();
                        header('Location: login.php?error=0');
                    }
                } else {
                    header('Location: myaccount.php?error=22');
                }
            }

        }
    }
}

// Réserver un vol

function book(){
    $db = dbConnect();


    $request = $db->prepare('select * from dateFlight where id_vol=:id and date= :date ');
    $request->execute(array('id'=>$_POST['id_vol'], 'date'=>$_POST['dateFlight']));
    $dataR = $request->fetch();

    // Ajout des passagers

    for ($i = 1; $i <= $_POST['nbPassenger']; $i++) {
        $req = $db->prepare('INSERT INTO passenger(id_user, id_dateFlight, nom, prenom, mail, age) VALUES(:id_user, :id_vol, :nom, :prenom, :mail, :age)');
        $req->execute(array('id_user' => $_SESSION['id'],
            'id_vol'=> $dataR['id_dateFlight'],
            'nom' => $_POST['Lname'.$i],
            'prenom' => $_POST['Fname'.$i],
            'mail' => $_POST['userMail'.$i],
            'age'=> $_POST['birthDate'.$i]
        ));
    }

    // Ajout dans booking

    $req2 = $db->prepare('INSERT INTO booking(totalPrice, nbPerson, id_dateFlight, id_user) VALUES(:totalPrice, :nbPerson, :id_dateflight, :id_user)');
    $req2->execute(array('totalPrice'=>$_POST['ttlprice'],
        'nbPerson'=>$_POST['nbPassenger'],
        'id_dateflight'=>$dataR['id_dateFlight'],
        'id_user'=>$_SESSION['id']
    ));

    // Enlever des places

    $req3 = $db->prepare('SELECT * FROM dateFlight WHERE id_dateFlight = :id_dateflight');
    $req3->execute(array('id_dateflight'=> $dataR['id_dateFlight']));
    $nbPass = $req3->fetch()['nbPassenger'] - $_POST['nbPassenger'];
    echo $nbPass;

    $req4 = $db->prepare('UPDATE dateFlight SET nbPassenger = :nbPassenger WHERE id_dateFlight = :id_dateflight');
    $req4->execute(array('nbPassenger'=>$nbPass,
        'id_dateflight'=> $dataR['id_dateFlight']));


    header('Location: myaccount.php');
}

// Annuler un vol

function unBook(){
    $db = dbConnect();

    // Récupérations des éléments

    $requestBooking = $db->prepare('select * from booking where id_booking = :id_booking');
    $requestBooking->execute(array('id_booking'=>$_GET['id_booking']));
    $data = $requestBooking->fetch();

    $reqNbPerson = $db->prepare('SELECT * FROM dateFlight WHERE id_dateFlight = :id_dateflight');
    $reqNbPerson->execute(array('id_dateflight'=> $data['id_dateFlight']));
    $nbPass = $reqNbPerson->fetch()['nbPassenger'] + $data['nbPerson'];

    // Libérer les places et Supprimer le vol de la table booking

    $reqUpdate = $db->prepare('UPDATE dateFlight SET nbPassenger = :nbPassenger WHERE id_dateFlight = :id_dateflight');
    $reqUpdate->execute(array('nbPassenger'=>$nbPass,
        'id_dateflight'=> $data['id_dateFlight']));

    $requestDelete = $db->prepare('delete from booking where id_booking = :id_booking');
    $requestDelete->execute(array('id_booking'=>$_GET['id_booking']));

    header('Location: myaccount.php');
}

function getTime(){
	$date = new DateTime(date('Y-m-d H:i:sP'), new DateTimeZone('America/Vancouver'));
	
	$date->setTimezone(new DateTimeZone('America/Vancouver'));
	$vancouver = $date->format('d-m-Y H:i:s');

	$date->setTimezone(new DateTimeZone('America/Toronto'));
	$toronto = $date->format('d-m-Y H:i:s');
	
	$date->setTimezone(new DateTimeZone('Europe/Paris'));
	
	$array = array('vancouver'=>$vancouver, 'toronto'=>$toronto);
	$json = json_encode($array);
	print_r($json);
}


if ($_GET['func'] == 'register') {
    register();
} else if ($_GET['func'] == 'login') {
    login();
} else if ($_GET['func'] == 'logout') {
    logout();
}else if($_GET['func'] == 'modifAccount'){
    modifAccount();
}else if($_GET['func'] == 'book'){
    book();
}else if($_GET['func'] == 'unBook'){
    unBook();
}else if($_GET['func'] == 'getTime'){
    getTime();
}
?>
