<?php

return [
    'gsl_token' => [
        'title' => 'Ongeldig GSL-token!',
        'body_1' => 'Het lijkt erop dat je Gameserver Login Token (GSL-token) ongeldig of verlopen is.',
        'body_2' => 'Je kunt een nieuw token genereren en hieronder invoeren, of het veld leeg laten om het helemaal te verwijderen.',
        'field_label' => 'GSL-token',
        'field_description' => 'Ga naar https://steamcommunity.com/dev/managegameservers om een token te genereren.',
        'update_button' => 'GSL-token bijwerken',
    ],
    'hytale_oauth' => [
        'title' => 'Verificatie vereist',
        'body' => 'Je moet je verifiëren met je Hytale-account om serverbestanden te downloaden of bij te werken. Meld je aan om verder te gaan.',
        'cancel_button' => 'Annuleren',
        'login_button' => 'Aanmelden',
    ],
    'pid_limit' => [
        'admin_title' => 'Geheugen- of proceslimiet bereikt...',
        'admin_body_1' => 'Deze server heeft de maximale proces- of geheugenlimiet bereikt.',
        'admin_body_2' => 'Het verhogen van <0>container_pid_limit</0> in de wings-configuratie, <1>config.yml</1>, kan dit probleem helpen oplossen.',
        'admin_note' => 'Let op: Wings moet opnieuw worden gestart om de wijzigingen in het configuratiebestand door te voeren',
        'user_title' => 'Mogelijk resourcelimiet bereikt...',
        'user_body' => 'Deze server probeert meer resources te gebruiken dan zijn toegewezen. Neem contact op met de beheerder en geef hem of haar de onderstaande fout door.',
        'close_button' => 'Sluiten',
    ],
    'steam_disk_space' => [
        'title' => 'Geen schijfruimte meer beschikbaar...',
        'admin_body_1' => 'Deze server heeft geen beschikbare schijfruimte meer en kan de installatie of update niet voltooien.',
        'admin_body_2' => 'Zorg dat de machine genoeg schijfruimte heeft door <0>df -h</0> te typen op de machine die deze server host. Verwijder bestanden of vergroot de beschikbare schijfruimte om het probleem op te lossen.',
        'user_body' => 'Deze server heeft geen beschikbare schijfruimte meer en kan de installatie of update niet voltooien. Neem contact op met de beheerder(s) en meld het schijfruimteprobleem.',
        'close_button' => 'Sluiten',
    ],
    'eula' => [
        'title' => 'Minecraft®-EULA accepteren',
        'body_prefix' => 'Door hieronder op "Ik accepteer" te drukken, geef je aan akkoord te gaan met de',
        'body_suffix' => '.',
        'link_text' => 'Minecraft®-EULA',
        'cancel_button' => 'Annuleren',
        'accept_button' => 'Ik accepteer',
    ],
    'java_version' => [
        'title' => 'Niet-ondersteunde Java-versie',
        'body' => 'Deze server draait een niet-ondersteunde Java-versie en kan niet worden gestart.',
        'body_select_notice' => ' Selecteer een ondersteunde versie uit de onderstaande lijst om de server verder te starten.',
        'cancel_button' => 'Annuleren',
        'update_button' => 'Docker-image bijwerken',
    ],
];
