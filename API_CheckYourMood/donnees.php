<?php
	// Données
	//?cleApi=t1hhVWy8zLtWydfnGEdHSQ==
		
	function getPDO(){
		// Retourne un objet connexion à la BD
		$host='localhost';	// Serveur de BD
		$db='checkyourmood';// Nom de la BD
		$user='root';		// User 
		$pass='root';		// Mot de passe
		$charset='utf8mb4';	// charset utilisé
		
		// Constitution variable DSN
		$dsn="mysql:host=$host;dbname=$db;charset=$charset";
		
		// Réglage des options
		$options=[																				 
			PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES=>false];
		
		try{	// Bloc try bd injoignable ou si erreur SQL
			$pdo=new PDO($dsn,$user,$pass,$options);
			return $pdo ;			
		} catch(PDOException $e){
			//Il y a eu une erreur de connexion
			$infos['Statut']="KO";
			$infos['message']="Problème connexion base de données";
			sendJSON($infos, 500) ;
			die();
		}
	}
	
	function getLast5Humors($api_key) {
		// Retourne la liste des catégories des clients
		try {
			$pdo=getPDO();
            // TODO Vérifier si la LIMIT marche bien ou pas
			$maRequete='SELECT Libelle, Date_Hum, Informations, Emoji FROM historique JOIN compte ON Code_Compte = ID_Compte JOIN humeur ON Code_hum = ID_Hum  WHERE APIKEY = :api LIMIT 5' ;
			
			$stmt = $pdo->prepare($maRequete);
            // Préparation de la requête
			$stmt->bindParam("api", $api_key);
            $stmt->execute();

            $humors=$stmt->fetchALL();
			$stmt->closeCursor();

			$stmt=null;
			$pdo=null;

			sendJSON($humors, 200) ;
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}
	}

	function getTypeHumeur(){

		try {
			$pdo=getPDO();
            // TODO Vérifier si la LIMIT marche bien ou pas
			$maRequete='SELECT Libelle FROM humeur';
			
			$stmt = $pdo->prepare($maRequete);										// Préparation de la requête
			$stmt->execute();	
				
			$humeur=$stmt ->fetchALL();
			$stmt->closeCursor();
			$stmt=null;
			$pdo=null;

			sendJSON($humeur, 200) ;

			sendJSON($humors, 200) ;
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}
	
	function addHumor($donneesJson, $api_key) {
		if(!empty($donneesJson['ID_Histo'])
			&& !empty($donneesJson['Code_Compte'])
			&& !empty($donneesJson['Code_hum'])
			&& !empty($donneesJson['Date_Hum'])
			&& !empty($donneesJson['Date_Ajout'])
			&& !empty($donneesJson['Informations'])
		  ){
			  // Données remplies, on insère dans la table client
			try {
				$pdo=getPDO();

				$requeteRecupCompte ='SELECT ID_Compte FROM compte WHERE APIKEY = :apikey';
				$stmt = $pdo->prepare($requeteRecupCompte);
				$stmt->bindParam("apikey", $api_key);
				$stmt->execute();
				$code_compte=$stmt->fetch();
				$stmt->closeCursor();
			    $stmt=null;

				$maRequete='INSERT INTO historique(Code_Compte, Code_hum, Date_Hum, Date_Ajout, Informations) VALUES (:Code_Compte, :Code_hum, :Date_Hum, :Date_Ajout, :Informations)';
				$stmt = $pdo->prepare($maRequete);						// Préparation de la requête
				$stmt->bindParam("Code_Compte", $code_compte);
				$stmt->bindParam("Code_hum", $donneesJson['Code_hum']);
				$stmt->bindParam("Date_Hum", $donneesJson['Date_Hum']);
				$stmt->bindParam("Date_Ajout", $donneesJson['Date_Ajout']);
				$stmt->bindParam("Informations", $donneesJson['Informations']);

				$stmt->execute();	
				
				$IdInsere=$pdo->lastInsertId() ;
					
				$stmt=null;
				$pdo=null;
				
				// Retour des informations au client (statut + id créé)
				$infos['Statut']="OK";
				$infos['ID']=$IdInsere;

				sendJSON($infos, 201) ;
			} catch(PDOException $e){
				// Retour des informations au client 
				$infos['Statut']="KO";
				$infos['message']=$e->getMessage();

				sendJSON($infos, 500) ;
			}
		} else {
			// Données manquantes, Retour des informations au client 
			$infos['Statut']="KO";
			$infos['message']="Données incomplètes";
			sendJSON($infos, 400) ;
		}
	}

	function getAPIKEY($login){

		try {
			$pdo=getPDO();

			$maRequete='SELECT APIKEY FROM compte WHERE Email = :login' ;
			
			$stmt = $pdo->prepare($maRequete);										// Préparation de la requête
			$stmt->bindParam("login", $login);
            $stmt->execute();

            $connexion=$stmt->fetch();


			$stmt->closeCursor();

			$stmt=null;
			$pdo=null;

			sendJSON($connexion, 200) ;
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}

	function getAllAPIKEY($value){

		try {
			$pdo=getPDO();

			$maRequete="SELECT APIKEY FROM compte WHERE APIKEY = :value" ;
			
			$stmt = $pdo->prepare($maRequete);	
			$stmt->bindParam("value", $value);	
			$result = $stmt->execute();
			$nb = $stmt->rowCount();

			$connexion=$stmt->fetchALL();
			$stmt->closeCursor();
			$stmt=null;
			$pdo=null;

			if ($nb>=1) {
				return true;
			} else {
				return false;
			}
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}

	function verifLogin($login, $mdp){

		try {
			$pdo=getPDO();

			$maRequete='SELECT Email FROM compte WHERE Email = :login AND Mot_de_passe  = :mdp' ;
			
			$stmt = $pdo->prepare($maRequete);										// Préparation de la requête
			$stmt->bindParam("login", $login);
			$stmt->bindParam("mdp", $mdp);
            $stmt->execute();
			$nb = $stmt->rowCount();

            $connexion=$stmt->fetchALL();
			$stmt->closeCursor();
			$stmt=null;
			$pdo=null;

			if ($nb==1) {
				return true;
			} else {
				return false;
			}
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}

	function verifNonAPIKEY($login){

		try {
			$pdo=getPDO();

			$maRequete='SELECT APIKEY FROM compte WHERE Email = :login' ;
			
			$stmt = $pdo->prepare($maRequete);										// Préparation de la requête
			$stmt->bindParam("login", $login);
            $stmt->execute();
			$connexion=$stmt->fetch();
			

			if (is_null($connexion['APIKEY'])) {
				return true;
			} else {
				return false;
			}
			$stmt->closeCursor();
			$stmt=null;
			$pdo=null;

		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}

	function creerAPIKEY($login){

		try {
			$pdo=getPDO();

			$maRequete='UPDATE compte SET APIKEY = :APIKEY WHERE Email = :login' ;
			
			$length = 16; // longueur de la clé d'API
			$api_key = bin2hex(random_bytes(8));

			
			if (getAllAPIKEY($api_key)) {
				// La colonne contient la valeur recherchée
				creerAPIKEY($login);
			}
			
			$stmt = $pdo->prepare($maRequete);	
			$stmt->bindParam("APIKEY", $api_key);									// Préparation de la requête
			$stmt->bindParam("login", $login);
            $stmt->execute();

			$stmt->closeCursor();

			$stmt=null;
			$pdo=null;

		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		} catch (Exception $e) {
            echo("Erreur");
        }

    }
	

?>