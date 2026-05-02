<!DOCTYPE html>
<html lang="it" xml:lang="it">

    <head>

        <title>eXperience CORE Test</title>
        <!-- UIkit CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/css/uikit.min.css" />
        <!-- UIkit Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/css/uikit-icons.min.css" />
        <!-- Custom CSS -->
        <link rel="stylesheet" href="assets/templates/css/style.css" />


        <!-- UIkit JS -->
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/js/uikit.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/js/uikit-icons.min.js"></script>

    </head>

    <body>

        <nav class="uk-navbar-container">
            <div class="uk-container uk-container-expand">
                <div uk-navbar>
                    <a class="uk-navbar-item uk-logo" href="#" aria-label="Back to Home">eXperience CORE {$version} - Test application</a>
                </div>
            </div>
        </nav>

        <br>

        <div class="uk-container uk-container-expand">
            <div class="uk-alert-warning" uk-alert>
                <a href class="uk-alert-close" uk-close></a>
                <h3><span uk-icon="icon: warning; ratio: 2"></span> Attenzione</h3>
                <p>
                    Questo è un file di test per eXperience CORE, non è destinato ad essere eseguito in produzione. Se si verificano errori, verrà mostrato un messaggio dettagliato con il codice di errore, il nome dell'eccezione, 
                    il messaggio, il file e la linea in cui si è verificato e lo stack trace.
                </p>
                <p>
                    Il test include:
                    <ul>
                        <li>Informazioni di sistema</li>
                        <li>Test del logger</li>
                        <li>Test del gestore di configurazione</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class='uk-container uk-container-expand uk-margin-top'>

