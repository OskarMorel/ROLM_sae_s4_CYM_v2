<?php 
	// Authentification /////////////////////////////////////////////////////////////////////
	
	function authentification() {
		// fonction permettant de tester si la clé API est valide
		if (isset($_SERVER["HTTP_APIKEYDEMONAPPLI"])) {
			$cleAPI=$_SERVER["HTTP_APIKEYDEMONAPPLI"];
			// Test de la clé API fait en dur pour l'exemple mais devrait être fait avec la BD
            return $cleAPI;
			if (!getAllAPIKEY($cleAPI)) {
				$infos['Statut']="KO";
				$infos['message']="APIKEY invalide.";
				sendJSON($infos, 403) ;
				die();
			}
		}else {
			// Pas de clé API envoyée, pas d'accès à l'Api
			$infos['Statut']="KO";
			$infos['message']="Authentification necessaire par APIKEY.";
			sendJSON($infos, 401) ;
			die();
		}
	}
	//////////////////////////////////////////////////////////////////////////////////////////

	
	function verifLoginPassword($login, $password) {
		
		if (verifLogin($login, $password)) {
			// Login et mot de passe correct, 
			// Genération de la clé, stockage en BD 
			// Envoi de la clé au client.
			
			if (verifNonAPIKEY($login)) {
				// La colonne ne contient pas la valeur recherchée
				creerAPIKEY($login);
			} 
			
			$infos['APIKEYDEMONAPPLI']=getAPIKEY($login);
			sendJSON($infos, 200) ;
		} else {
			// Login incorrect
			$infos['Statut']="KO";
			$infos['message']="Logins incorrects.";
			sendJSON($infos, 401) ;
			die();
		}
	}
	?>
