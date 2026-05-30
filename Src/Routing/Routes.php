<?php
// Routes du sites
return [
    /*
     * Erreur
     */
    'error404' => ['Base' => 'error404'],
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
    'addFirstUser' => ['User' => 'addFirstUser'],
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
    /*
     * Partie projet
     */
    'getProject' => ['Project' => 'getProject'],
    'createProject' => ['Project' => 'createProject'],
    'updateProject' => ['Project' => 'updateProject'],
    'deleteProject' => ['Project' => 'deleteProject'],
];