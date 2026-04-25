<!DOCTYPE html>
<html lang="it" xml:lang="it">

    <head>

        <title>eXperience CORE Test</title>
        <!-- UIkit CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/css/uikit.min.css" />
        <!-- UIkit Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/css/uikit-icons.min.css" />


        <!-- UIkit JS -->
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/js/uikit.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/js/uikit-icons.min.js"></script>

    </head>

    <body>

        <nav class="uk-navbar-container">
            <div class="uk-container uk-container-expand">
                <div uk-navbar>
                    <a class="uk-navbar-item uk-logo" href="#" aria-label="Back to Home">eXperience CORE {$version} - Test application</a>';
                </div>
            </div>
        </nav>

        <div class='uk-container uk-margin-top'>
            <p>Questo è un file di test per eXperience CORE, non è destinato ad essere eseguito in produzione.</p>
            <hr>
            <p>Il test include:</p>
            <ul>
                <li>Informazioni di sistema</li>
                <li>Test del logger</li>
                <li>Test del gestore di configurazione</li>
            </ul>
            <hr>
            <p>
                Se si verificano errori, verrà mostrato un messaggio dettagliato con il codice di errore, il nome dell'eccezione, 
                il messaggio, il file e la linea in cui si è verificato e lo stack trace.
            </p>
            <hr>
            <h2 class='uk-heading-divider'>System information</h2>
            <b>Sistema operativo</b>: {$os}<br/>
            <b>Release</b>: {$release}<br/>
            <b>Versione</b>: {$version}<br/>
            <b>Server Web</b>: {$server}<br/>
            <b>Versione PHP</b>: {$phpversion} ({$phpbits} bit) <b><span uk-icon='icon: {$phpstatus}; ratio: 1' style='color: {$phpcolor};'></span></b><br/>
            <b>Versione eXperience CORE</b>: {$version}<br/>
            <b>Nome Host</b>: {$hostname}<br/>
            <b>Lib path</b>: {$experience_path}<hr>
            <b>Locale: {$locale}</b><br/>
            <b>Charset: {$charset}</b><br/>
            <b>Script path: {$script_path}</b><br/>
            <b>Language path: {$language_path}</b><br/>
            <b>Log path: {$log_path}</b><br/>
            <b>Configurazione: </b>
            <pre>
                {$cfg}
            </pre>
            <br/>
            <b>Start logger Test: {$logger_test}</b>
        </div>

    </body>

</html>
