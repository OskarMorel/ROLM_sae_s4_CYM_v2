package com.coursdevappli.rolm_cymdroid;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

public class MainActivity extends AppCompatActivity {

    /** Etiquette pour les messages de log */
    private static final String TAG_LOG = "ACCES WEB";

    private static String urlApi = "http://192.168.146.1/ROLM_sae_s4_CYM_v2/API_CheckYourMood/login?login=";

    private EditText editTextLogin, editTextPwd;

    private RequestQueue fileRequete;

    String apiKey;

    // Intent pour le liens avec une autre activité


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        editTextLogin = findViewById(R.id.login);
        editTextPwd = findViewById(R.id.pwd);
    }

    private RequestQueue getFileRequete() {
        if (fileRequete == null) {
            fileRequete = Volley.newRequestQueue(this);
        }
        // sinon
        return fileRequete;
    }

    public void seConnecter (View view) {
        try {
            // le titre saisi par l'utilisateur est récupéré et encodé en UTF-8
            String login = URLEncoder.encode(editTextLogin.getText().toString(), "UTF-8");
            String pwd = URLEncoder.encode(editTextPwd.getText().toString(), "UTF-8");

            // le titre du film est inséré dans l'URL de recherche du film
            String url = urlApi + login + "&pwd=" + pwd;
            /*
             * on crée une requête GET, paramètrée par l'url préparée ci-dessus,
             * Le résultat de cette requête sera un objet JSon, donc la requête est de type
             * JsonObjectRequest
             */
            JsonObjectRequest requeteVolley = new JsonObjectRequest(Request.Method.GET, url,
                    null,
                    // écouteur de la réponse renvoyée par la requête
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject reponse) {
                            StringBuilder apiKey = new StringBuilder();
                            try {
                                apiKey.append(reponse.getString("APIKEY"));
                            } catch (JSONException e) {
                                throw new RuntimeException(e);
                            }
                            Intent intent = new Intent(MainActivity.this, Accueil.class);
                            // Démarrer l'activité de destination
                            startActivity(intent);
                        }
                    },
                    // écouteur du retour de la requête si aucun résultat n'est renvoyé
                    new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError erreur) {
                            erreur.printStackTrace();
                            Toast.makeText(getApplication(), R.string.toast_erreur_login, Toast.LENGTH_LONG).show();
                        }
                    });
            // la requête est placée dans la file d'attente des requêtes
            getFileRequete().add(requeteVolley);
        } catch(UnsupportedEncodingException erreur) {
            // problème lors de l'encodage de la chaîne titre
            Toast.makeText(this, R.string.toast_erreur, Toast.LENGTH_LONG).show();
        }
    }
}