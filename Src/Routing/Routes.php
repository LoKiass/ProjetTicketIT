<?php
// Routes du sites
return [
    /*
     * Partie login
     */
    'formLogin' => ['User' => 'formLogin'],
    ''      => ['User' => 'formLogin'],
    /*
     * Partie User
     */
    'getUser' => ['User' => 'getUser'],
    'updateUser' => ['User' => 'updateUser'],
    /*
     * Partie accueil
     */
    'formAccueil' => ['Accueil' => 'formAccueil'],
    /*
     * Partie Tech
     */
    'createTech' => ['Tech' => 'createTech'],
    'getTech'  => ['Tech' => 'getTech'],
    'updateTech' => ['Tech' => 'updateTech'],
    /*
     * Partie fonction
     */
    'getFonction' => ['Fonction' => 'getFonction'],
    'createFonction' => ['Fonction' => 'createFonction'],
    'updateFonction' => ['Fonction' => 'updateFonction'],
    'deleteFonction' => ['Fonction' => 'deleteFonction'],
    /*
     * Partie Jobs
     */
    'getJob' => ['Job' => 'getJob'],
    'createJob' => ['Job' => 'createJob'],
    'updateJob' => ['Job' => 'updateJob'],
    'deleteJob' => ['Job' => 'deleteJob'],
];