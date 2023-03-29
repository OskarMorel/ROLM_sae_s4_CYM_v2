package com.coursdevappli.rolm_cymdroid;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.WindowManager;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

public class Accueil extends AppCompatActivity {

    String cleApi;

    private TextView zoneResultat;

    private static String urlApi = "http://192.168.146.1/ROLM_sae_s4_CYM_v2/API_CheckYourMood/";

    private RequestQueue fileRequete;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.accueil);
        // Cacher la bar du haut
        this.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN,WindowManager.LayoutParams.FLAG_FULLSCREEN);
        getSupportActionBar().hide();

        // Récupération de la clé api, envoyée par l'autre activité
        cleApi = getIntent().getStringExtra("apiKey");

        zoneResultat = findViewById(R.id.zoneResultat);
        afficherHumeurs(zoneResultat);
    }

    public void seDeconnecter (View view) {
        Intent intent = new Intent(Accueil.this, MainActivity.class);
        // Démarrer l'activité de destination
        startActivity(intent);

        cleApi = "";
    }


    private RequestQueue getFileRequete() {
        if (fileRequete == null) {
            fileRequete = Volley.newRequestQueue(this);
        }
        // sinon
        return fileRequete;
    }

    public void afficherHumeurs(View view) {
        // le titre saisi par l'utilisateur est récupéré et encodé en UTF-8

        // le titre du film est inséré dans l'URL de recherche du film
        String url = urlApi + "humeursRecentes?cleApi=" + cleApi;
        /*
         * on crée une requête GET, paramètrée par l'url préparée ci-dessus,
         * Le résultat de cette requête sera un objet JSon, donc la requête est de type
         * JsonObjectRequest
         */
        JsonArrayRequest requeteVolley = new JsonArrayRequest(Request.Method.GET, url,
                null,
                // écouteur de la réponse renvoyée par la requête
                new Response.Listener<JSONArray>() {
                    @Override
                    public void onResponse(JSONArray reponse) {
                        setZoneResultatAvecObjetJson(reponse);
                    }
                },
                // écouteur du retour de la requête si aucun résultat n'est renvoyé
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError erreur) {
                        erreur.printStackTrace();
                        Toast.makeText(getApplication(), R.string.toast_erreur_reponse, Toast.LENGTH_LONG).show();
                    }
                });
        // la requête est placée dans la file d'attente des requêtes
        getFileRequete().add(requeteVolley);
    }

    public void setZoneResultatAvecObjetJson(JSONArray reponse){
        try {
            StringBuilder resultatFormate = new StringBuilder();
            /*
             * on extrait de l'objet Json reponse : le titre, l'année, les auteurs
             * On construit la chaine resultatFormate avec des libellés et le chaînes
             * extraites de l'objet Json
             */
            for (int i =0; i < reponse.length(); i++) {
                resultatFormate.append(reponse.getString(i));
            }
            // on affiche la chaîne formatée
            zoneResultat.setText(resultatFormate.toString());
        } catch(JSONException erreur) {
            /*
             * Exception levée si l'un des 3 champs recherché (Title, Year, Actors)
             * n'est pas présent
             * dans l'objet Json reponse, c'est-à-dire si le film n'a pas été trouvé
             * par le Web service
             */
            Toast.makeText(this, R.string.toast_erreur, Toast.LENGTH_LONG).show();
        }
    }


}
