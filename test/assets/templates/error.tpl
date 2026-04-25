<div class="uk-alert-danger" uk-alert>
    <h3 class='uk-heading-divider'><span uk-icon='icon: warning; ratio: 2'></span> ERRORE: {$code} - {$name}</h3>
    <div style='color: black; padding: 10px; border-radius: 5px;'>
        <b>Messaggio</b>: {$message}<br>
        <b>File</b>: {$file} <b>({ $line })</b><br/>
    </div>

    <h4 class='uk-heading-divider'>Stack trace:</h4>
        <table class='uk-table uk-table-divider uk-table-small uk-table-hover uk-table-responsive'>
        <tr class='uk-table-header'>
            <th>#</th>
            <th>File (Linea)</th>
            <th>Chiamata (Funzione/Metodo)</th>
        </tr>

        {loop="trace"} 
            <tr>
                <td>{$index}</td>
                <td>{$file} <b>({ $line })</b></td>
                <td>{$class}{$type}{$function}()</td>
            </tr>
        {/loop}
    </table>
</div>
