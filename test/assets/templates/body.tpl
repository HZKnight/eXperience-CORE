
            <div uk-grid>

                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-small uk-card-body">
                        <h2 class="uk-card-title">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    System information
                                </div>
                                <div>
                                    <span uk-icon="icon: cog; ratio: 2" class="icon_title"></span>
                                </div>
                            </div>
                        </h2>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Sistema operativo</b>
                            </div>
                            <div>
                                {$os}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Release</b>
                            </div>
                            <div>
                                {$release}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Versione</b>
                            </div>
                            <div>
                                {$version}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Server Web</b>
                            </div>
                            <div>
                                {$server}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Versione PHP</b>
                            </div>
                            <div>
                                {$phpversion} ({$phpbits} bit) <b><span uk-icon='icon: {$phpstatus}; ratio: 1' style='color: {$phpcolor};'></span></b>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Nome Host</b>
                            </div>
                            <div>
                                {$hostname}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-small uk-card-body"> 
                        <h2 class="uk-card-title">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    eXperience CORE information
                                </div>
                                <div>
                                    <span uk-icon="icon: nut; ratio: 2" class="icon_title"></span>
                                </div>
                            </div>
                        </h2>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Versione eXperience CORE</b>
                            </div>
                            <div>
                                {$experience_version}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Lib path</b>
                            </div>
                            <div>
                                {$experience_path}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Locale</b>
                            </div>
                            <div>
                                {$locale}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Script path</b>
                            </div>
                            <div>
                                {$script_path}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Language path</b>
                            </div>
                            <div>
                                {$language_path}
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Log path</b>
                            </div>
                            <div>
                                {$log_path}
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div uk-grid>

                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-small uk-card-body"> 
                        <h2 class="uk-card-title">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    App configuration
                                </div>
                                <div>
                                    <span uk-icon="icon: settings; ratio: 2" class="icon_title"></span>
                                </div>
                            </div>
                        </h2>
                        <div class="uk-grid-small " uk-grid>
                            <div class="uk-width-expand header_title">
                                Parametro
                            </div>
                            <div class="header_title">
                                Valore
                            </div>
                        </div>

                        {loop="$cfg"}
                            {if="is_array($value)"}
                                <div class="uk-grid-small " uk-grid>
                                    <div class="uk-width-expand header_title">
                                        {$key}
                                    </div>
                                    <div class="header_title">
                                    </div>
                                </div>
                                {loop="$value"}
                                    <div class="uk-grid-small little_border_bt" uk-grid>
                                        <div class="uk-width-expand key-name">
                                            {$key}
                                        </div>
                                        <div>
                                            <ul>
                                                {$value}
                                            </ul>
                                        </div>
                                    </div>
                                {/loop}
                            {else}
                                <div class="uk-grid-small little_border_bt" uk-grid>
                                    <div class="uk-width-expand key-name">
                                        {$key}
                                    </div>
                                    <div>
                                        {$value}
                                    </div>
                                </div>
                            {/if}
                        {/loop}
                        
                    </div>
                </div>

                <div class="uk-width-1-2@m">
                    <div class="uk-card uk-card-default uk-card-small uk-card-body">
                        <h2 class="uk-card-title">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    Test results
                                </div>
                                <div>
                                    <span uk-icon="icon: check; ratio: 2" class="icon_title"></span>
                                </div>
                            </div>
                        </h2>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Config read</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$testCfgRead}; ratio: 1' style='color: green;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Config write</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$testCfgWrite}; ratio: 1' style='color: green;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Logger</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$logger_test}; ratio: 1' style='color: {$logger_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Mailer</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$mailer_test}; ratio: 1' style='color: {$mailer_color};'></span></b></br/>
                            </div>
                        </div>

                        <div class="uk-grid-small " uk-grid>
                            <div class="uk-width-expand header_title">
                                MySqli Native Driver Test
                            </div>
                            <div class="header_title">
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database driver loading</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_test}; ratio: 1' style='color: {$db_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database connection</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_conn_test}; ratio: 1' style='color: {$db_conn_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database insert query</b>
                            </div>
                            <div>
                                {if="isset($db_iquery_id)"}
                                    Last Insert ID: {$db_iquery_id} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_iquery_test}; ratio: 1' style='color: {$db_iquery_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database query</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_query_test}; ratio: 1' style='color: {$db_query_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database get table num rows</b>
                            </div>
                            <div>
                                {if="isset($db_num_rows)"}
                                    Num rows in test table: {$db_num_rows} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_num_rows_test}; ratio: 1' style='color: {$db_num_rows_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database get table subset of rows</b>
                            </div>
                            <div>
                                {if="isset($db_subset_rows)"}
                                    Subset of rows in test table: {$db_subset_rows} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_subset_test}; ratio: 1' style='color: {$db_subset_color};'></span></b></br/>
                            </div>
                        </div>

                        <div class="uk-grid-small " uk-grid>
                            <div class="uk-width-expand header_title">
                                SQLite Native Driver Test
                            </div>
                            <div class="header_title">
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test SQLite Database driver loading</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_sqlite_test}; ratio: 1' style='color: {$db_sqlite_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test SQLite Database connection</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_sqlite_conn_test}; ratio: 1' style='color: {$db_sqlite_conn_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test SQLite Database insert query</b>
                            </div>
                            <div>
                                {if="isset($db_sqlite_iquery_id)"}
                                    Last Insert ID: {$db_sqlite_iquery_id} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_sqlite_iquery_test}; ratio: 1' style='color: {$db_sqlite_iquery_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test SQLite Database query</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_sqlite_query_test}; ratio: 1' style='color: {$db_sqlite_query_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test SQLite Database get table num rows</b>
                            </div>
                            <div>
                                {if="isset($db_sqlite_num_rows)"}
                                    Num rows in test table: {$db_sqlite_num_rows} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_sqlite_num_rows_test}; ratio: 1' style='color: {$db_sqlite_num_rows_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test SQLite Database get table subset of rows</b>
                            </div>
                            <div>
                                {if="isset($db_sqlite_subset_rows)"}
                                    Subset of rows in test table: {$db_sqlite_subset_rows} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_sqlite_subset_test}; ratio: 1' style='color: {$db_sqlite_subset_color};'></span></b></br/>
                            </div>
                        </div>

                        <div class="uk-grid-small " uk-grid>
                            <div class="uk-width-expand header_title">
                                PDO Driver Test
                            </div>
                            <div class="header_title">
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test PDO MySQL Database driver loading</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_pdo_test}; ratio: 1' style='color: {$db_pdo_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test PDO MySQL Database connection</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_pdo_conn_test}; ratio: 1' style='color: {$db_pdo_conn_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test PDO MySQL Database insert query</b>
                            </div>
                            <div>
                                {if="isset($db_pdo_query_id)"}
                                    Last Insert ID: {$db_pdo_query_id} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_pdo_query_test}; ratio: 1' style='color: {$db_pdo_query_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test PDO MySQL Database query</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: {$db_pdo_query_test}; ratio: 1' style='color: {$db_pdo_query_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test PDO MySQL Database get table num rows</b>
                            </div>
                            <div>
                                {if="isset($db_pdo_num_rows)"}
                                    Num rows in test table: {$db_pdo_num_rows} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_pdo_num_rows_test}; ratio: 1' style='color: {$db_pdo_num_rows_color};'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test PDO MySQL Database get table subset of rows</b>
                            </div>
                            <div>
                                {if="isset($db_pdo_subset_rows)"}
                                    Subset of rows in test table: {$db_pdo_subset_rows} &nbsp;
                                {/if}
                                <b><span uk-icon='icon: {$db_pdo_subset_test}; ratio: 1' style='color: {$db_pdo_subset_color};'></span></b></br/>
                            </div>
                        </div>
                    </div>

                    <div class="uk-card uk-card-default uk-card-small uk-card-body" style="margin-top: 40px;">
                        <h2 class="uk-card-title">
                            <div class="uk-grid-small" uk-grid>
                                <div class="uk-width-expand">
                                    Errors
                                </div>
                                <div>
                                    <span uk-icon="icon: warning; ratio: 2" class="icon_title"></span>
                                </div>
                            </div>
                        </h2>
                        {if="isset($db_error)"}
                            <div class="uk-alert-danger" uk-alert>
                                <p><b>Database error:</b> {$db_error}</p>
                            </div>
                        {/if}
                        {if="isset($logger_error)"}
                            <div class="uk-alert-danger" uk-alert>
                                <p><b>Logger error:</b> {$logger_error}</p> 
                            </div>
                        {/if}
                        {if="isset($mailer_error)"}
                            <div class="uk-alert-danger" uk-alert>
                                <p><b>Mailer error:</b> {$mailer_error}</p> 
                            </div>
                        {/if}
                    </div>
                </div>

            </div>

            <br>
            <br>


