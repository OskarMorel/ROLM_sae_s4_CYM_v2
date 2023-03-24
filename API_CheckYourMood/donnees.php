<?php
	// Données
		
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
	
	function getLast5Humors($id) {
		// Retourne la liste des catégories des clients
		try {
			$pdo=getPDO();
            // TODO Vérifier si la LIMIT marche bien ou pas
			$maRequete='SELECT * FROM historique WHERE Code_Compte = :id LIMIT 5' ;
			
			$stmt = $pdo->prepare($maRequete);										// Préparation de la requête
			$stmt = $pdo->prepare("Code_Compte", $id);
            $stmt->execute();

            $humors=$stmt ->fetchALL();
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
	
	function addHumor($donneesJson) {
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
				$maRequete='INSERT INTO historique(Code_Compte, Code_hum, Date_Hum, Date_Ajout, Informations) VALUES (:Code_Compte, :Code_hum, :Date_Hum, :Date_Ajout, :Informations)';
				$stmt = $pdo->prepare($maRequete);						// Préparation de la requête
				$stmt->bindParam("Code_Compte", $donneesJson['Code_Compte']);
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
			$stmt = $pdo->prepare("Email", $login);
            $stmt->execute();

            $connexion=$stmt ->fetchALL();
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
			$stmt = $pdo->prepare("APIKEY", $value);	
            
			$result = $stmt->execute();;

			

			sendJSON($result, 200) ;
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}

	function verifLogin($login, $mdp){

		try {
			$pdo=getPDO();

			$maRequete='SELECT Email = :login FROM compte WHERE Email = :login AND Mot_de_passe  = :mdp' ;
			
			$stmt = $pdo->prepare($maRequete);										// Préparation de la requête
			$stmt = $pdo->prepare("Email", $login);
			$stmt = $pdo->prepare("Mot_de_passe", $mdp);
            $stmt->execute();

            $connexion=$stmt ->fetchALL();
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

	function creerAPIKEY($login, $APIKEY){

		try {
			$pdo=getPDO();

			$maRequete='UPDATE compte SET APIKEY = :APIKEY WHERE Email = :login' ;
			
			$length = 32; // longueur de la clé d'API
			$api_key = base64_encode(random_bytes($length));

			
			if (getAllAPIKEY($api_key)->num_rows > 0) {
				// La colonne contient la valeur recherchée
				creerAPIKEY($login, $APIKEY);
			} else {
				// La colonne ne contient pas la valeur recherchée
				$APIKEY = $api_key;
			}

			$stmt = $pdo->prepare($maRequete);	
			$stmt = $pdo->prepare("APIKEY", $APIKEY);									// Préparation de la requête
			$stmt = $pdo->prepare("Email", $login);
            $stmt->execute();

            $IdInsere=$pdo ->lastInsertID();
			$stmt->closeCursor();

			$stmt=null;
			$pdo=null;

			sendJSON($IdInsere, 200) ;
		} catch(PDOException $e){
			$infos['Statut']="KO";
			$infos['message']=$e->getMessage();
			sendJSON($infos, 500) ;
		}

	}
	

?>