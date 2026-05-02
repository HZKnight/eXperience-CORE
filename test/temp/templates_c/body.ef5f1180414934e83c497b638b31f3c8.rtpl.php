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
                                <?php echo $phpversion;?> (<?php echo $phpbits;?> bit) <b><span uk-icon='icon: <?php echo $phpstatus;?>; ratio: 1' style='color: <?php echo $phpcolor;?>;'></span></b></br/>
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
                        <pre>
                            <?php echo $cfg;?>

                        </pre>
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
                        <b>Start logger Test: <?php echo $logger_test;?></b>
                    </div>
                </div>

            </div>

            <br>


