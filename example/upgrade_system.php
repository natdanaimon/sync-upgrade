<title>Upgrade System</title>
<meta charset="UTF-8">
<meta content="width=device-width, initial-scale=1" name="viewport" />
<link rel="shortcut icon" href="favicon.ico" /> </head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>



<style>
    .btn-circle {
        width: 30px;
        height: 30px;
        text-align: center;
        padding: 6px 0;
        font-size: 12px;
        line-height: 1.428571429;
        border-radius: 50%;
    }
    .btn-circle.btn-lg {
        width: 50px;
        height: 50px;
        padding: 10px 16px;
        font-size: 18px;
        line-height: 1.33;
    }
    .btn-circle.btn-xl {
        width: 70px;
        height: 70px;
        padding: 10px 16px;
        font-size: 24px;
        line-height: 1.33;
    }

</style>
<div class="container">

    <br/><br/><br/>

    <?php
    $listCheck = array('127.0.0.1', "::1", "localhost");

    $_config['folderMain'] = (in_array($_SERVER['REMOTE_ADDR'], $listCheck) ? "sync-upgrade/" : "");
    $_config['path'] = $_SERVER["DOCUMENT_ROOT"];
    $_config['rootPath'] = $_config['path'] . "/" . $_config['folderMain'];
    $_config['url'] = 'https://www.yourhost.com/version/';
    $_config['project'] = 'BankPullTransaction';

    $vServer = file_get_contents($_config['url'] . 'version.txt') or die('ERROR');
    $vClient = file_get_contents($_config['rootPath'] . 'version/version.txt') or die('ERROR');
    $lstServer = array_map('trim', explode("\n", $vServer));
    $lstClient = array_map('trim', explode("\n", $vClient));

    $latest_version = TRUE;
    $release_versionS = '';
    $release_versionC = '';
    foreach ($lstServer as $serverV) {
        if (in_array($serverV, $lstClient)) {
            $release_versionC = $serverV;
            continue;
        } else {
            $latest_version = FALSE;
            $release_versionS = $serverV;
        }
    }





//        $latest_version = TRUE;
//        $release_version = ''; $_GET['upgrade'] === "true"
    if (!$latest_version && $_GET['upgrade'] === "true") {
        $txt1 = 'System : ' . $_config['project'] . ' v.' . $release_versionC;
        $txt2 = 'The latest is version ' . $release_versionS;
        ?>
        <div class="jumbotron alert alert-info">

            <h2 style=""><?= $txt1 ?>   </h2> 
            <p class="lead" style="font-weight: bold"><?= $txt2 ?> </p>

            <?php
            foreach ($lstServer as $serverV) {
                if (in_array($serverV, $lstClient)) {
                    $release_versionC = $serverV;
                    continue;
                } else {
                    $latest_version = FALSE;
                    if (!is_dir($_config['rootPath'] . 'version/')) {
                        mkdir($_config['rootPath'] . 'version/');
                    }
                    echo '<hr>';
                    echo '<h3>Package Version ' . $serverV . '</h3>';

                    $pathZipServer = $_config['url'] . $serverV . '.zip';
                    $pathZipClient = $_config['rootPath'] . 'version/' . $serverV . '.zip';
                    file_put_contents($pathZipClient, fopen($pathZipServer, 'r'));
                    echo '<h5>Step 1 : Downloading Version ' . $serverV . ' Success.</h5>';
//                    echo '<hr>';
                    echo '<h5>Step 2 : Install Package Version ' . $serverV . ' List File.</h5>';


                    $zipHandle = zip_open($_config['rootPath'] . 'version/' . $serverV . '.zip');
                    echo '<ul>';
                    while ($aF = zip_read($zipHandle)) {
                        $thisFileName = zip_entry_name($aF);
                        $thisFileDir = dirname($thisFileName);


                        if (substr($thisFileName, -1, 1) == '/') {
                            //Make the directory if we need to...
                            if (!is_dir($_config['rootPath'] . $thisFileName)) {
                                mkdir($_config['rootPath'] . $thisFileName);
//                                echo '<li>Created Directory ' . $thisFileDir . '</li>';
                            }
                            continue;
                        }

                        //Make the directory if we need to...
                        if (!is_dir($_config['rootPath'] . $thisFileDir)) {
                            mkdir($_config['rootPath'] . '/' . $thisFileDir);
//                            echo '<li>Created Directory ' . $thisFileDir . '</li>';
                        }



                        //Overwrite the file
                        if (!is_dir($_config['rootPath'] . $thisFileName)) {
                            echo '<li>' . $thisFileName . '...........';
                            $contents = zip_entry_read($aF, zip_entry_filesize($aF));


                            $updateThis = '';
                            //If we need to run commands, then do it.
                            if ($thisFileName == 'version/database.sql') {
                                $upgradeExec = fopen($_config['rootPath'] . '/version/database.sql', 'w');
                                fwrite($upgradeExec, $contents);
                                fclose($upgradeExec);
                                include ($_config['rootPath'] . 'version/ExecuteSQL.php');
                                unlink($_config['rootPath'] . 'version/database.sql');
                                echo' EXECUTED</li>';
                            } else {
                                $updateThis = fopen($_config['rootPath'] . '/' . $thisFileName, 'w');
                                fwrite($updateThis, $contents);
                                fclose($updateThis);
                                unset($contents);
                                echo' UPDATED</li>';
                            }
                        }
                    }
                    echo '</ul>';
                    echo '<h5>Step 3 : Install Package Version ' . $serverV . ' Success.</h5>';
                }
            }
            ?>
            <hr>
            <p class="mb-0">
                <span>
                    Note :
                    <!--หมายเหตุ :--> 
                </span>
                The team would like to thank all customers for their support. If the system is upgraded, the screen will display a button. Press Upgrade Version.
                <!--ทางทีมงานขอขอบคุณลูกค้าทุกท่านที่ให้การสนับสนุน หากมีการอัพเกรดระบบหน้าจอจะแสดงปุ่มให้กดอัพเกรดเวอร์ชั่น-->
            </p>
        </div>

        <?php
    } else if ($latest_version) {
        $txt1 = 'System : ' . $_config['project'] . ' v.' . $release_versionC;
        $txt2 = 'Latest Version. ';
        ?>


        <div class="jumbotron alert alert-success">


            <h2 style=""><?= $txt1 ?></h2> 
            <p class="lead" style="font-weight: bold"><?= $txt2 ?></p>
            <hr>
            <p class="mb-0">
                <span>
                    Note :
                    <!--หมายเหตุ :--> 
                </span>
                The team would like to thank all customers for their support. If the system is upgraded, the screen will display a button. Press Upgrade Version.
                <!--ทางทีมงานขอขอบคุณลูกค้าทุกท่านที่ให้การสนับสนุน หากมีการอัพเกรดระบบหน้าจอจะแสดงปุ่มให้กดอัพเกรดเวอร์ชั่น-->
            </p>

        </div>
        <?php
    } else {
        $txt1 = 'System : ' . $_config['project'] . ' v.' . $release_versionC;
        $txt2 = 'The latest is version ' . $release_versionS;
        ?>

        <div class="jumbotron alert alert-info">


            <h2 style=""><?= $txt1 ?></h2>  
            <p class="lead" style="font-weight: bold"><?= $txt2 ?></p>

            <p><a class="btn btn-lg btn-info" href="upgrade_system.php?upgrade=true" role="button">Upgrade Version</a></p>
            <hr>
            <p class="mb-0">
                <span>
                    Note :
                    <!--หมายเหตุ :--> 
                </span>
                The team would like to thank all customers for their support. If the system is upgraded, the screen will display a button. Press Upgrade Version.
                <!--ทางทีมงานขอขอบคุณลูกค้าทุกท่านที่ให้การสนับสนุน หากมีการอัพเกรดระบบหน้าจอจะแสดงปุ่มให้กดอัพเกรดเวอร์ชั่น-->
            </p>

        </div>


        <?php
    }
    ?>


    <footer class="footer" align="center">
        <a href="upgrade_system.php" >
            <button type="button" class="btn btn-success btn-circle btn-xl" style="cursor:pointer"> 
                <span class="glyphicon glyphicon-refresh" style="font-size:11px">
                    Click
                    <br/>
                    Check
                    <br/>
                    Version
                </span>
            </button>
        </a>
    </footer>
    <br/>
    <footer class="footer" align="center">
        <p style="font-weight: bold;color: #848484">Copyright © 2017 NAGIEOS All rights reserved.</p>
    </footer>
</div>


