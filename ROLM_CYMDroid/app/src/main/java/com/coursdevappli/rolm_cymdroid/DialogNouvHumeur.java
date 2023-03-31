package com.coursdevappli.rolm_cymdroid;

import android.app.Dialog;
import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;

import androidx.annotation.NonNull;

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

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

public class DialogNouvHumeur extends Dialog {

    Button ajouterHumeur;
    private Spinner typeHumeur;
    private EditText dateHeure, informations;
    String cleApi = Accueil.cleApi;

    private static final String urlApi = "http://192.168.146.1/ROLM_sae_s4_CYM_v2/API_CheckYourMood/";

    private RequestQueue fileRequete;

    public DialogNouvHumeur(@NonNull Context context) {
        super(context);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.nouvelle_humeur);

        ajouterHumeur = findViewById(R.id.btnAjoutHumeur);
        dateHeure = findViewById(R.id.dateHeure);
        informations = findViewById(R.id.informations);
        String date = new SimpleDateFormat("yyyy-MM-dd HH:mm", Locale.getDefault()).format(new Date());
        System.out.println(date);
        dateHeure.setText(date);
        typeHumeur = findViewById(R.id.typeHumeurs);

        afficherTypesHumeurs();
        ajouterHumeur.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String infoAEnvoyer = informations.getText().toString();
                String dateHeureAEnvoyer = dateHeure.getText().toString();
                int codeHumAEnvoyer = typeHumeur.getSelectedItemPosition() + 1;
                postAjoutHumeur(codeHumAEnvoyer,dateHeureAEnvoyer,infoAEnvoyer);
            }
        });
    }



    private RequestQueue getFileRequete() {
        if (fileRequete == null) {
            fileRequete = Volley.newRequestQueue(getContext());
        }
        // sinon
        return fileRequete;
    }

    public void afficherTypesHumeurs() {
        // le titre saisi par l'utilisateur est récupéré et encodé en UTF-8

        // le titre du film est inséré dans l'URL de recherche du film
        String url = urlApi + "typesHumeurs";
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
                        JSONObject objetTypesHumeurs;
                        List<String> spinnerArray = new ArrayList<String>();
                        for (int i = 0; i < reponse.length(); i++) {
                            try {
                                objetTypesHumeurs = reponse.getJSONObject(i);
                                spinnerArray.add(objetTypesHumeurs.getString("Libelle"));
                                ArrayAdapter<String> adapter = new ArrayAdapter<String>(getContext(), android.R.layout.simple_spinner_item, spinnerArray);
                                adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
                                Spinner sItems = (Spinner) findViewById(R.id.typeHumeurs);
                                sItems.setAdapter(adapter);
                            } catch (JSONException e) {
                                throw new RuntimeException(e);
                            }

                        }
                    }
                },
                // écouteur du retour de la requête si aucun résultat n'est renvoyé
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError erreur) {
                        erreur.printStackTrace();
                        //Toast.makeText(getApplication(), R.string.toast_erreur_reponse, Toast.LENGTH_LONG).show();
                    }
                });
        // la requête est placée dans la file d'attente des requêtes
        getFileRequete().add(requeteVolley);
    }

// ******************************* AJOUT HUMEUR *******************************

    private void postAjoutHumeur(int codeHumeur, String dateHumeur, String informations) {
        String url = urlApi + "ajoutHumeur?cleApi=" + cleApi;
        boolean toutOk;
        /*
         * préparation du nouveau client, à ajouter, en tant qu'objet Json
         * Les informations le concernant sont renseignées avec des valeurs par défaut,
         * sauf le nom du magasin qui est celui renseigné par l'utilisateur
         */
        toutOk = true;
        JSONObject objetAEnvoyer = new JSONObject();
        try {
            objetAEnvoyer.put("Code_hum", codeHumeur);
            objetAEnvoyer.put("Date_Hum", dateHumeur);
            objetAEnvoyer.put("Date_Ajout", dateHumeur);
            objetAEnvoyer.put("Informations", informations);

        } catch (JSONException e) {
            // l'exception ne doit pas se produire
            toutOk = false;
        }
        if (toutOk) {
            JsonObjectRequest requeteVolley = new JsonObjectRequest(Request.Method.POST, url, objetAEnvoyer,
                    // écouteur de la réponse renvoyée par la requête
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject reponse) {
                            Toast.makeText(getContext(), R.string.toast_validation_nv_humeur, Toast.LENGTH_LONG).show();
                        }
                    },
                    // écouteur du retour de la requête si aucun résultat n'est renvoyé
                    new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError erreur) {
                            Toast.makeText(getContext(), R.string.toast_erreur_reponse, Toast.LENGTH_LONG).show();
                        }
                    });
            // la requête est placée dans la file d'attente des requêtes
            getFileRequete().add(requeteVolley);
        }

    }
}
