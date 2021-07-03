<!DOCTYPE HTML>
<html>

    <head>
        <?php
        session_start();
    if (!isset($_SESSION['session_name'])) {
        header("location:./login.php");
        exit();
    }
        $cacheBuster = '?v='.microtime(true);
        error_reporting(E_ALL & ~E_NOTICE);
        $pageName = "Upload";
        require "head.php";
        require "./xiSPEC_scripts.php";
    ?>
            <script type="text/javascript" src="./js/upload.js<?php echo $cacheBuster ?>"></script>
            <script type="text/javascript" src="./vendor/spin.js"></script>
            <script src="./vendor/jQueryFileUploadMin/jquery.ui.widget.js"></script>
            <script src="./vendor/jQueryFileUploadMin/jquery.iframe-transport.js"></script>
            <script src="./vendor/jQueryFileUploadMin/jquery.fileupload.js"></script>
            <link rel="stylesheet" href="./css/upload.css" />
    </head>

    <body>
        <!-- Sidebar -->
        <?php require "navigation.php";?>
        <!-- Main -->
        <div id="main">
            <div class="container">
                <h1 class="page-header"><?php echo($pageName); ?></h1>
                <!-- Intro -->
                <a href="https://player.vimeo.com/video/298348184" target="_blank">Data upload video tutorial</a>
                <hr class="wideDivider">
                <div id="jquery-fileupload">
                    <div id="fileUploadWrapper">
                        <input id="fileupload" type="file" name="files[]" accept=".mzid,.csv,.mzml,.mgf,.ms2,.zip,.gz,.fasta"
                            multiple data-url="vendor/jQueryFileUploadMin/fileUpload.php">
                        <label for="fileupload"><span class="uploadbox"></span><span class="btn">Choose file(s)</span></label>
                        <div id="uploadProgress">
                            <div class="file_upload_bar" style="width: 0%;">
                                <div class="file_upload_percent"></div>
                            </div>
                        </div>
                        <button id="startParsing" disabled="true" class="btn btn-2">Submit Data</button>
                    </div>
                    <div class="fileupload_info">
                        <table>
                            <tr id="mzid_fileBox">
                                <td style="text-align: center;">Identification file:</td>
                                <td>
                                    <span class="fileName">Select a mzIdentML or csv file to upload</span>
                                    <span class="statusBox" data-filetype="mzid"></span>
                                    <input class="uploadCheckbox" type="checkbox" id="mzid_checkbox" style="visibility: hidden;">
                                </td>
                            </tr>
                            <tr id="mzml_fileBox">
                                <td style="text-align: center;">Peak list file(s):</td>
                                <td>
                                    <span class="fileName">No peak list file(s) selected - spectra will be unavailable</span>
                                    <span class="statusBox" data-filetype="mzml"></span>
                                    <input class="uploadCheckbox" type="checkbox" id="mzml_checkbox" style="visibility: hidden;">
                                </td>
                            </tr>
                            <tr id="fasta_fileBox">
                                <td style="text-align: center;">Sequence file:</td>
                                <td>
                                    <span class="fileName">No FASTA file selected, protein identifiers must be UniprotKB accession numbers</span>
                                    <span class="statusBox" data-filetype="fasta"></span>
                                    <input class="uploadCheckbox" type="checkbox" id="fasta_checkbox" style="visibility: hidden;">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr class="wideDivider">
                <div class="image-link"><img alt="xiView workflow" class="image full" src="images/workflow.svg"></div>

                <p>xiView accepts three types of input data:</p>
                <ol>
                    <li><strong>Peptide Identifications (required)</strong>
                        <br/> Supported file formats:
                        <a title="HUPO-PSI: mzidentML" href="mzIdentML.php" target="blank">mzIdentML</a>
                        (file extension must be '.mzid') and
                        <a title="CSV formats" href="csvFormats.php" target="blank">Comma Seperated Values</a>
                        (file extension '.csv').
                    </li>
                    <li><strong>Peak Lists (optional)</strong><br/> Supported file formats:
                        <a title="HUPO-PSI: mzML" href="http://www.psidev.info/mzml" target="blank">mzML</a>,
                        <a title="Mascot Generic Format" href="http://www.matrixscience.com/help/data_file_help.html#GEN">mgf</a>,
                        and <a href="https://www.ncbi.nlm.nih.gov/pubmed/15317041" target="blank"> ms2</a>
                        (&amp; zip/gz archives of these). File extension must be '.mzML',
                        '.mgf', '.ms2' or '.zip'.
                        </br>

                        <div style="font-size: 0.8em; line-height: 1.7em; margin-top:0.5em;">
                            If peak list data is uploaded then it must be complete, i.e. all spectra identified must
                            be present, or the upload process will result in an error.<br>
                            mzML tip: Filter out MS1 spectra to reduce file size and upload/parsing
                            time. (e.g. 'MS level 2-' in <a title="Proteowizard download link"
                                href="http://proteowizard.sourceforge.net/downloads.shtml">MSconvert</a>)</br>
                        </div>
                        <!-- mzML: Make sure to use centroided MS2 data! (e.g. use 'Peak picking' for profile data in <a title="Proteowizard download link" href="http://proteowizard.sourceforge.net/downloads.shtml">MSconvert</a>)</br> -->
                    </li>
                    <li><strong>Protein Sequences (optional)</strong><br/> Supported file
                        formats:
                        <a title="FASTA" href="https://en.wikipedia.org/wiki/FASTA_format" target="blank">FASTA</a>
                        (file extension must be '.fasta'), sequences can also be contained
                        in mzIdentML files.</br>
                        <div style="font-size: 0.8em; line-height: 1.7em; margin-top:0.5em;">
                            If you do not provide a FASTA file, then your protein IDs must be valid UniProtKB accession
                            numbers.
                            </br>
                            If you do provide a FASTA file, then your protein IDs must all match identifiers in the
                            FASTA file.
                        </div>
                    </li>
                </ol>

                <br/>
                <ul>
                    <li>
                        <strong>Only the peptide identifications file is required</strong>,
                        but without uploading peak lists you won't be able to inspect
                        the supporting spectra using
                        <a href="http://spectrumviewer.org" target="_blank">xiSPEC</a>.
                    </li>
                    <li>
                        There is a 10GB size limit on uploaded files.
                    </li>
                    <!-- <li>
                    For mzIdentML files, the DBSequence elements must include their sequence within their Seq child element. FASTA files uploaded alongside mzIdentML files are currently ignored.
                    </li> -->
                </ul>
                <br/>
                <hr class="wideDivider">
                <strong>EXAMPLE DATA SETS</strong>
                <p>CSV Example (identification, peak lists, sequences):
                    <a href="examples/PolII_XiVersion1.6.742_PSM_xiFDR1.1.27.csv" target="_blank">CSV</a>
                    <a href="examples/Rappsilber_CLMS_PolII_MGFs.zip" target="_blank">MGFs</a>
                    <a href="examples/polII-uniprot.fasta" target="_blank">FASTA</a>
                    <!-- <a href="examples/test_HSA.csv" target="_blank">CSV</a>
                    <a href="examples/E180510_02_Orbi2_TD_IN_160_HSA_10kDa_10p.mzML" target="_blank">MZML</a>
                    <a href="examples/HSA-Active.FASTA" target="_blank">FASTA</a> -->
                </p>
                <p><a href="examples/SIM-XL_example.mzid" target="_blank">MzIdentML Example </a>(generated
                    by <a href="http://patternlabforproteomics.org/sim-xl/" target="_blank">SIM-XL</a>,
                    no peak list associated with this example).
                </p>
            </div>
        </div>





        <!-- Modals -->
        <div id="submitDataModal" role="dialog" class="modal" style="display: none;">
            <div id=submitDataInfo>
                <div id="submitDataTop">
                    <div id="errorInfo" style="display: none;">
                        <div id="errorMsg"></div>
                        <textarea class="form-control" id="errorLog" readonly></textarea>
                    </div>
                </div>
                <div id="ionsInfo" style="display: none;">
                    <div id="ionsMsg"></div>
                    <form id="ionsForm" method="post" action="php/updateIons.php">
                        <div class="multiSelect_dropdown" style="margin-right:2%;">
                            <input type="text" class="form-control btn-drop" id="ionSelectionSubmit" title="fragment ion types"
                                value="peptide, b, y" readonly>
                            <div class="multiSelect_dropdown-content mutliSelect">
                                <ul>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="peptide" checked id="PeptideIonSubmit" name="ions[]" />Peptide ion</label></li>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="a" id="AIonSubmit" name="ions[]" />A ion</label></li>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="b" checked id="BIonSubmit" name="ions[]" />B ion</label></li>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="c" id="CIonSubmit" name="ions[]" />C ion</label></li>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="x" id="XIonSubmit" name="ions[]" />X ion</label></li>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="y" checked id="YIonSubmit" name="ions[]" />Y ion</label></li>
                                    <li class="ionSelect">
                                        <label><input type="checkbox" class="ionSelectChkboxSubmit" value="z" id="ZIonSubmit" name="ions[]" />Z ion</label></li>
                                </ul>
                            </div>
                        </div>
                        <button type="submit" id="ionsFormSubmit" class="btn btn-2">update ions</button>
                        <div id="ionsUpdateMsg" style="font-size: 0.8em;display: inline;"></div>
                    </form>
                </div>
                <div id="modificationsInfo" style="display: none;">
                    <div id="modificationsMsg"></div>
                    <form id="csvModificationsForm" method="post" action="php/submitModDataForCSV.php"></form>
                </div>
                <div id="submitDataControls">
                    <button id="cancelUpload" class="btn btn-2" href="#">Cancel</button>
                    <a id="gitHubIssue" class="btn btn-1a" style="display:none;" href='https://github.com/Rappsilber-Laboratory/xiView_container/issues'>
                        <i class="fa fa-github" aria-hidden="true"></i>Create issue
                    </a>
                    <button id="continueToDB" class="btn btn-2" href="#">Continue</button>
                </div>
            </div>
            <div id="processDataInfo">
                <div class="spinnerWrapper"></div>
                <div id="processText" style="text-align: center; padding-top: 140px; margin:10px;"></div>
            </div>
        </div>
        <div class="overlay" style="z-index: -1; visibility: hidden;"></div>

    </body>

</html>
