<?php
	// Données
		
	function getPDO(){
		// Retourne un objet connexion à la BD
		$host='localhost';	// Serveur de BD
		$db='mezabi3';		// Nom de la BD
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
	

?>