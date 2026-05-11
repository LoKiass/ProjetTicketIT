<?php
// Routes du sites
return [
    /*
     * Partie login
     */
    'formLogin' => ['User' => 'formLogin'],
    ''      => ['User' => 'formLogin'],
    'test'  => ['Test' => 'index'],
    /*
     * Partie User
     */
    'listUser' => ['User' => 'listUser'],
    'updateUser' => ['User' => 'updateUser'],
    /*
     * Partie accueil
     */
    'formAccueil' => ['Accueil' => 'formAccueil'],
    'formTech'   => ['Tech' => 'formTech'],
    'formUser' => ['User' => 'formUser'],
    /*
     * Partie Tech
     */
    'createTech' => ['Tech' => 'createTech'],
    'getTech'  => ['Tech' => 'getTech'],
    'updateTech' => ['Tech' => 'updateTech'],
];