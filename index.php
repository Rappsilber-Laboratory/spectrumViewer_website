<!DOCTYPE HTML>
<html lang="en">

<head>
    <title>xiVIEW | Home</title>
    <?php
    include("head.php");
    ?>
</head>

<body>

<!-- Sidebar -->
<?php include("navigation.php"); ?>
<!-- Main -->
<div id="main">
    <div class="container">
        <h1 class="page-header">Home</h1>
        <p>xiVIEW is a web-based visualisation tool for the analysis of cross-linking
            / mass spectrometry results, it is independent of the search software
            used. It provides multiple, linked views of the data, including:</p>
        <ul>
            <li>2D network (<a href="http://crosslinkviewer.org" target="_blank">xiNET</a> or circular)</li>
            <li>the supporting annotated spectra using <a href="http://spectrumviewer.org"
                                                          target="_blank">xiSPEC</a>.
            </li>
            <li>3D structure view using <a href="http://nglviewer.org/ngl" target="_blank">NGL</a>.</li>
        </ul>
        <div>
            <hr class="wideDivider">
            <p>The <a href="https://rappsilberlab.org/software/xiview/"
                      target="_blank">video tutorials</a> give an overview of xiVIEW's
                many features.</p>
        </div>
        <div>
            <hr class="wideDivider">
            <p>xiVIEW is an open source project on
                <a href="https://github.com/Rappsilber-Laboratory/xiView_container" target="_blank">GitHub</a>.
                Report issues and request features
                <a href="https://github.com/Rappsilber-Laboratory/xiView_container/issues" target="_blank">here</a>.
            </p>
        </div>
        <div>
            <hr class="wideDivider">
            When using xiVIEW please cite:
            <a target="_blank" href="http://biorxiv.org/cgi/content/short/561829v1">
                Graham, M., Combe, C. W., Kolbowski, L. &amp; Rappsilber, J. xiView: A common platform for the
                downstream analysis of
                Crosslinking Mass Spectrometry data. <i>doi: 10.1101/561829 </i>.
            </a>
        </div>
        <div>
            <div class="login">
                <div class="newUserSection" style="display:block;">
                    <hr class="wideDivider">
                    <h3>New User?</h3>
                    <form id="new_user_form" name="new_user_form" action="./createAccount.php">
                        <input name="Submit" value="Create New Account" type="submit" class="btn btn-1a"/>
                    </form>
                </div>
            </div>
            <br/>
            <div>
                <img style="display:block;margin:auto;" class="sliderImg"
                     alt="Automatic mapping of links onto 3D structures"
                     src="images/midScreenshot.png">
            </div>

        </div>

    </div>
    <!-- CONTAINER -->
</div>
<!-- MAIN -->

</body>

</html>
