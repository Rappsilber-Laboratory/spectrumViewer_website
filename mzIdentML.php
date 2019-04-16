<!DOCTYPE HTML>
<html>
    <head>
    <?php
        $pageName = "mzIdentML Support";
        include("head.php");
    ?>
        <script type="text/javascript" src="./js/accordion.js"></script>
        <script type="text/javascript" src="./js/directURL.js"></script>
    </head>

    <body>
        <!-- Sidebar -->
        <?php include("navigation.php");?>

        <!-- Main -->
        <div id="main">
            <div class="container">
                <h1 class="page-header"><?php echo($pageName); ?></h1>

                <p>Crosslinking data is supported in <a href="http://www.psidev.info/mzidentml#mzid12" target="_blank">version 1.2 of the mzIdentML schema</a>.
                This is the best format to use when uploading data to xiView, for example, it will already contain the information on modification masses.</p>
                <br/>
                <p>Please <a href="mailto:colin.combe@ed.ac.uk">contact us</a> if you are having difficulties uploading MzIdentML.</p>
                <br/>
                <p>We have tested output from the following tools and confirmed that it works in xiVIEW:</p>
                <ul>
                    <li>
                    <a href="http://patternlabforproteomics.org/sim-xl/" target="_blank">SIM-XL</a>
                    </li>
                    <li>
                    <a href="https://github.com/Rappsilber-Laboratory/xiFDR">xiFDR</a>
                    </li>
                    <!--
                    <li>
                    <a href="https://www.thermofisher.com/order/catalog/product/OPTON-30795">Proteome Discoverer</a>
                    </li> -->
                </ul>
                HUPO PSI provide <a href="http://www.psidev.info/tools-implementing-mzidentml">a list of tools that support mzIdentML</a>.
            </div> <!-- CONTAINER -->
        </div> <!-- MAIN -->
    </body>
</html>
