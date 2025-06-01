<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des Heures Supplémentaires
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient les paramètres de configuration pour la gestion
    | des heures supplémentaires dans l'application.
    |
    */

    // Limites horaires
    'limits' => [
        'min_hours' => 0.5,                    // Minimum d'heures supplémentaires par demande
        'max_hours_per_day' => 12,             // Maximum d'heures supplémentaires par jour
        'max_hours_per_week' => 40,            // Maximum d'heures supplémentaires par semaine
        'max_hours_per_month' => 120,          // Maximum d'heures supplémentaires par mois
    ],

    // Taux de majoration
    'rates' => [
        'standard' => 1.25,                    // 125% - Heures supplémentaires standard
        'night_weekend' => 1.5,               // 150% - Nuit et weekend
        'holiday' => 2.0,                     // 200% - Jours fériés
    ],

    // Types d'heures supplémentaires
    'types' => [
        'planned' => [
            'label' => 'Planifiées',
            'description' => 'Heures supplémentaires prévues à l\'avance',
            'default_rate' => 1.25,
            'approval_required' => true,
        ],
        'urgent' => [
            'label' => 'Urgentes',
            'description' => 'Heures supplémentaires pour situations urgentes',
            'default_rate' => 1.5,
            'approval_required' => true,
        ],
        'project' => [
            'label' => 'Projet spécial',
            'description' => 'Heures supplémentaires pour projets importants',
            'default_rate' => 1.25,
            'approval_required' => true,
        ],
    ],

    // Statuts des demandes
    'statuses' => [
        'pending' => [
            'label' => 'En attente',
            'color' => 'warning',
            'description' => 'Demande en cours d\'examen',
        ],
        'approved' => [
            'label' => 'Approuvé',
            'color' => 'success',
            'description' => 'Demande approuvée par le responsable',
        ],
        'rejected' => [
            'label' => 'Rejeté',
            'color' => 'danger',
            'description' => 'Demande rejetée par le responsable',
        ],
    ],

    // Règles métier
    'business_rules' => [
        'advance_notice_days' => 7,            // Préavis minimum en jours
        'retroactive_days' => 7,              // Jours maximum pour demande rétroactive
        'auto_approve_threshold' => 2,         // Heures auto-approuvées si < seuil
        'approval_timeout_days' => 5,         // Délai maximum pour approbation
    ],

    // Heures de travail normal
    'work_hours' => [
        'start' => '08:00',                   // Début journée normale
        'end' => '17:00',                     // Fin journée normale
        'lunch_break' => [
            'start' => '12:00',
            'end' => '13:00',
        ],
    ],

    // Horaires considérés comme heures supplémentaires
    'overtime_periods' => [
        'before_work' => '08:00',             // Avant 8h
        'after_work' => '17:00',              // Après 17h
        'weekend' => ['saturday', 'sunday'],  // Weekend
        'night_start' => '22:00',             // Début nuit
        'night_end' => '06:00',               // Fin nuit
    ],

    // Notifications
    'notifications' => [
        'email_enabled' => true,              // Activer notifications email
        'sms_enabled' => false,               // Activer notifications SMS
        'push_enabled' => true,               // Activer notifications push
        
        // Destinataires des notifications
        'notify_on_submit' => [
            'department_head' => true,         // Notifier chef département
            'hr_admin' => false,              // Notifier RH
        ],
        
        'notify_on_approval' => [
            'employee' => true,               // Notifier employé
            'hr_admin' => true,               // Notifier RH
        ],
    ],

    // Calculs et rapports
    'calculations' => [
        'round_to_quarter_hour' => false,     // Arrondir au quart d'heure
        'include_break_time' => false,        // Inclure les pauses
        'weekend_multiplier' => 1.5,          // Multiplicateur weekend
        'holiday_multiplier' => 2.0,          // Multiplicateur jours fériés
    ],

    // Export et rapports
    'reports' => [
        'formats' => ['pdf', 'excel', 'csv'], // Formats d'export disponibles
        'default_period' => 'month',          // Période par défaut
        'max_export_records' => 1000,         // Limite d'enregistrements par export
    ],

    // Validation
    'validation' => [
        'max_description_length' => 500,      // Longueur max description
        'required_fields' => [
            'reason',
            'start_time',
            'end_time',
            'overtime_date',
        ],
    ],

    // Interface utilisateur
    'ui' => [
        'default_sort' => 'overtime_date',    // Tri par défaut
        'items_per_page' => 15,               // Éléments par page
        'show_charts' => true,                // Afficher graphiques
        'color_theme' => '#6f42c1',           // Couleur thème
    ],

    // Jours fériés (exemple pour la France)
    'holidays' => [
        '2024-01-01' => 'Jour de l\'An',
        '2024-04-01' => 'Lundi de Pâques',
        '2024-05-01' => 'Fête du Travail',
        '2024-05-08' => 'Victoire 1945',
        '2024-05-09' => 'Ascension',
        '2024-05-20' => 'Lundi de Pentecôte',
        '2024-07-14' => 'Fête Nationale',
        '2024-08-15' => 'Assomption',
        '2024-11-01' => 'Toussaint',
        '2024-11-11' => 'Armistice',
        '2024-12-25' => 'Noël',
        
        // 2025
        '2025-01-01' => 'Jour de l\'An',
        '2025-04-21' => 'Lundi de Pâques',
        '2025-05-01' => 'Fête du Travail',
        '2025-05-08' => 'Victoire 1945',
        '2025-05-29' => 'Ascension',
        '2025-06-09' => 'Lundi de Pentecôte',
        '2025-07-14' => 'Fête Nationale',
        '2025-08-15' => 'Assomption',
        '2025-11-01' => 'Toussaint',
        '2025-11-11' => 'Armistice',
        '2025-12-25' => 'Noël',
    ],
];