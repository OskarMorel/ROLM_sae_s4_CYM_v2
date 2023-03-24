<?php 
	// Authentification /////////////////////////////////////////////////////////////////////
	
	function authentification() {
		// fonction permettant de tester si la clé API est valide
		if (isset($_SERVER["HTTP_APIKEYDEMONAPPLI"])) {
			$cleAPI=$_SERVER["HTTP_APIKEYDEMONAPPLI"];
			// Test de la clé API fait en dur pour l'exemple mais devrait être fait avec la BD
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

	/*
		TODO On verifie si les logins correspondent à l'un stockée sur la base de données
			Si oui alors on vérifie si c'est sa première connection
				Si oui alors on créer la clé pour ce client et on la stock dans la BD
			Sinon on cherche son login et on entre la clé API pour le connecter
		Sinon on lui indique qu'il n'est pas inscrit et qu'il doit s'inscrire sur le site
	*/
	
	function verifLoginPassword($login, $password) {
		// fonction qui vérifie si le login et le password sont ok.
		// Si ok, on génère une clé API qui sera normalement stockée dans la BD
		// Et on la retourne au client
		if (verifLogin($login, $password)->num_rows > 0) {
			// Login et mot de passe correct, 
			// Genération de la clé, stockage en BD (non fait dans cet exemple)
			// Envoi de la clé au client.
			if (getAPIKEY($login)->num_rows == 1) {
				// La colonne contient la valeur recherchée
				creerAPIKEY($login, 0);
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