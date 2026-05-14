<?php if(!class_exists('RainTPL\RainTPL', false)){exit;}?>

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
                                <?php echo $os;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Release</b>
                            </div>
                            <div>
                                <?php echo $release;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Versione</b>
                            </div>
                            <div>
                                <?php echo $version;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Server Web</b>
                            </div>
                            <div>
                                <?php echo $server;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Versione PHP</b>
                            </div>
                            <div>
                                <?php echo $phpversion;?> (<?php echo $phpbits;?> bit) <b><span uk-icon='icon: <?php echo $phpstatus;?>; ratio: 1' style='color: <?php echo $phpcolor;?>;'></span></b>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Nome Host</b>
                            </div>
                            <div>
                                <?php echo $hostname;?>

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
                                <?php echo $experience_version;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Lib path</b>
                            </div>
                            <div>
                                <?php echo $experience_path;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Locale</b>
                            </div>
                            <div>
                                <?php echo $locale;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Script path</b>
                            </div>
                            <div>
                                <?php echo $script_path;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Language path</b>
                            </div>
                            <div>
                                <?php echo $language_path;?>

                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Log path</b>
                            </div>
                            <div>
                                <?php echo $log_path;?>

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

                        <?php $counter1=-1; if( isset($cfg) && is_array($cfg) && sizeof($cfg) ) foreach( $cfg as $key1 => $value1 ){ $counter1++; ?>

                            <?php if( is_array($value1) ){ ?>

                                <div class="uk-grid-small " uk-grid>
                                    <div class="uk-width-expand header_title">
                                        <b><?php echo $key1;?></b>
                                    </div>
                                    <div class="header_title">
                                    </div>
                                </div>
                                <?php $counter2=-1; if( isset($value1) && is_array($value1) && sizeof($value1) ) foreach( $value1 as $key2 => $value2 ){ $counter2++; ?>

                                    <div class="uk-grid-small little_border_bt" uk-grid>
                                        <div class="uk-width-expand key-name">
                                            <?php echo $key2;?>

                                        </div>
                                        <div>
                                            <ul>
                                                <?php echo $value2;?>

                                            </ul>
                                        </div>
                                    </div>
                                <?php } ?>

                            <?php }else{ ?>

                                <div class="uk-grid-small little_border_bt" uk-grid>
                                    <div class="uk-width-expand key-name">
                                        <?php echo $key1;?>

                                    </div>
                                    <div>
                                        <?php echo $value1;?>

                                    </div>
                                </div>
                            <?php } ?>

                        <?php } ?>

                        
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
                                <b><span uk-icon='icon: <?php echo $testCfgRead;?>; ratio: 1' style='color: green;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Config write</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: <?php echo $testCfgWrite;?>; ratio: 1' style='color: green;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Logger</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: <?php echo $logger_test;?>; ratio: 1' style='color: <?php echo $logger_color;?>;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Mailer</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: <?php echo $mailer_test;?>; ratio: 1' style='color: <?php echo $mailer_color;?>;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database driver loading</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: <?php echo $db_test;?>; ratio: 1' style='color: <?php echo $db_color;?>;'></span></b></br/>
                            </div>
                        </div>
                        <div class="uk-grid-small little_border_bt" uk-grid>
                            <div class="uk-width-expand">
                                <b>Test Database connection</b>
                            </div>
                            <div>
                                <b><span uk-icon='icon: <?php echo $db_conn_test;?>; ratio: 1' style='color: <?php echo $db_conn_color;?>;'></span></b></br/>
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
                        <?php if( isset($db_error) ){ ?>

                            <div class="uk-alert-danger" uk-alert>
                                <p><b>Database error:</b> <?php echo $db_error;?></p>
                            </div>
                        <?php } ?>

                        <?php if( isset($logger_error) ){ ?>

                            <div class="uk-alert-danger" uk-alert>
                                <p><b>Logger error:</b> <?php echo $logger_error;?></p> 
                            </div>
                        <?php } ?>

                        <?php if( isset($mailer_error) ){ ?>

                            <div class="uk-alert-danger" uk-alert>
                                <p><b>Mailer error:</b> <?php echo $mailer_error;?></p> 
                            </div>
                        <?php } ?>

                    </div>
                </div>

            </div>

            <br>
            <br>


