<?php 
	

	require_once("json.php");
	require_once("donnees.php");
	require_once("authentification.php");

	//
	$request_method = $_SERVER["REQUEST_METHOD"];  // GET / POST / DELETE / PUT
	switch($_SERVER["REQUEST_METHOD"]) {
		case "GET" :
			if (!empty($_GET['demande'])) {
				// $encode=urlencode($_GET['demande']);
				// $decode=urldecode($encode);
				// décomposition URL par les / et  FILTER_SANITIZE_URL-> Supprime les caractères illégaux des URL
				$url = explode("/", filter_var($_GET['demande'],FILTER_SANITIZE_URL));
				switch($url[0]) {
					case 'login' :
						// Retournera une clé API si le login et password sont OK
						// La clé API sera utilisée pour les prochaines requetes.
						if (isset($_GET['login'])) {$login=$_GET['login'];} else {$login="";}
						if (isset($_GET['pwd'])) {$password=$_GET['pwd'];} else {$password="";}
						$password = md5($password);
						
						verifLoginPassword($login,$password);  // retourne l'apiKey si les logins / pwd sont ok
					break;
					case 'humeursRecentes' :
						// Retourne les types de clients
						//authentification(); // Test si on est bien authenfifié pour l'API
                        if (isset($_GET['cleApi'])) {$apiKey=$_GET['cleApi'];} else {$apiKey="";}
						getLast5Humors($apiKey);
						break;
					case 'typesHumeurs' :
						// Retourne les types de clients
						//authentification(); // Test si on est bien authenfifié pour l'API
						getTypeHumeur();
						break;
					default : 
						$infos['Statut']="KO";
						$infos['message']=$url[0]." inexistant";
						sendJSON($infos, 404) ;
				}
			} else {
				$infos['Statut']="KO";
				$infos['message']="URL non valide";
				sendJSON($infos, 404) ;
			}
			break ;
		case "POST" :
			if (!empty($_GET['demande'])) {
				// Ajout d'un client / type de client
				// Récupération des données envoyées
				$url = explode("/", filter_var($_GET['demande'],FILTER_SANITIZE_URL));
				switch($url[0]) {
					case 'ajoutHumeur' : 
						// Ajout d'un client
						//authentification(); // Test si on est bien authenfifié pour l'API
						if (isset($_GET['cleApi'])) {$apiKey=$_GET['cleApi'];} else {$apiKey="";}
						
						
						addHumor($apiKey);
						break ;
					default : 
						$infos['Statut']="KO";
						$infos['message']="'".$url[0]."' inexistant";
						sendJSON($infos, 404) ;
				}	
			} else {
				$infos['Statut']="KO";
				$infos['message']="URL non valide";
				sendJSON($infos, 404) ;
			}
			break;
		
	}
	
?>
