<!DOCTYPE HTML>
<html>
	<head>
		<?php
		$pageName = "Help";
		include("head.php");
		include("xiSPEC_scripts.php");
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

				<h1 class="page-header accordionHead">
					<i class="fa fa-plus-square" aria-hidden="true"></i> Peptide identification csv column headings
				</h1>
				<div class="accordionContent" style="display: none;" id="csv">
					<table class="myTable" id="csvTable">
						<thead>
							<tr><th>column</th><th>required?</th><th>notes</th><th>default</th><th>example</th></tr>
						</thead>
						<tbody>
							<tr><td>PepSeq1</td><td>Yes</td><td>Peptide sequence for peptide 1 in one letter amino acid code (uppercase) with modifications following the amino acid and consisting of the following characters: a-z:0-9.()\-</td><td>-</td><td>LKECcmCcmEKPLLEK</td></tr>
							<tr><td>PepSeq2</td><td>Yes</td><td>Peptide sequence for peptide 2 in one letter amino acid code (uppercase) with modifications following the amino acid and consisting of the following characters: a-z:0-9.()\-</td><td>-</td><td>HPYFYAPELLFFAKR</td></tr>
							<tr><td>PepPos1</td><td>Yes</td><td>Position of peptide 1 in protein 1 (1-based)</td><td>-</td><td>10</td></tr>
							<tr><td>PepPos2</td><td>Yes</td><td>Position of peptide 2 in protein 2 (1-based)</td><td>-</td><td>20</td></tr>
							<tr><td>LinkPos1</td><td>Yes</td><td>Position of cross-linked residue for peptide 1 (1-based)</td><td>-</td><td>2</td></tr>
							<tr><td>LinkPos2</td><td>Yes</td><td>Position of cross-linked residue for peptide 2 (1-based)</td><td>-</td><td>14</td></tr>
							<tr><td>Protein1</td><td>Yes</td><td>Identifier for protein 1. Ambiguous results are represented by listing the alternative proteins separated by semi-colons</td><td>-</td><td>HSA</td></tr>
							<tr><td>Protein2</td><td>Yes</td><td>Identifier for protein 2. Ambiguous results are represented by listing the alternative proteins separated by semi-colons</td><td>-</td><td>P02768;P13645</td></tr>
							<!-- <tr><td>Decoy 1</td><td>No</td><td>Set to true if the peptide 1 is matched to a decoy sequence</td><td>FALSE</td><td>TRUE</td></tr>
							<tr><td>Decoy 2</td><td>No</td><td>Set to true if the peptide 2 is matched to a decoy sequence</td><td>FALSE</td><td>TRUE</td></tr> -->
							<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
							<tr><td>Rank</td><td>No*</td><td>Rank of the identification quality as scored by the search engine (1 is the top rank)</td><td>1</td><td>1</td></tr>
							<tr><td>Score</td><td>No</td><td>Confidence score for the identification (number)</td><td>0</td><td>10.5641</td></tr>
							<tr><td>PassThreshold</td><td>No</td><td>True if the identification has passed a given threshold or been validated as correct</td><td>TRUE</td><td>FALSE</td></tr>
							<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
							<tr><td>Charge</td><td>Yes</td><td>Precursor charge state</td><td>-</td><td>3</td></tr>
							<tr><td>CrossLinkerModMass</td><td>No**</td><td>Modification mass of the used cross-linker</td><td>0</td><td>138.06808</td></tr>
							<tr><td>ExpMz</td><td>No</td><td>The mass-to-charge value measured in the experiment in Daltons / charge.</td><td>-</td><td>985.4341</td></tr>
							<tr><td>CalcMz</td><td>No</td><td>The theoretical mass-to-charge value calculated for the peptide in Daltons / charge.</td><td>-</td><td>985.6531</td></tr>
							<tr><td>FragmentTolerance</td><td>No</td><td>MS2 tolerance for annotating fragment peaks (in ppm or Da)</td><td>10 ppm</td><td>0.2 Da</td></tr>
							<tr><td>IonTypes</td><td>No</td><td>Fragment ion types to be considered separated by semicolon</td><td>peptide;b;y</td><td>peptide;a;b;c;x;y;z</td></tr>
							<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>
							<tr><td>ScanId</td><td>Yes</td><td>mzML: 1-based scan number; MGF: 0-based index in file</td><td>-</td><td>2256</td></tr>
							<tr><td>PeakListFileName</td><td>Yes</td><td>File name of the associated peak list file that contains the scan</td><td>-</td><td>example_file.mzML</td></tr>

						</tbody>
					</table>
					<br/>
					<p style="font-size: small;line-height: 1.5em;">
						*required if there are multiple alternative explanations for the same spectrum.</br>
					</p>
				</div>

				<h1 class="page-header accordionHead">
					<i class="fa fa-plus-square" aria-hidden="true"></i> mzIdentML support
				</h1>
				<div class="accordionContent" style="display: none;" id="mzIdSupport">
					<table class="myTable" id="mzidSupportTable">
						please <a href="mailto:colin.combe@ed.ac.uk">contact us</a> if you are trying to upload mzIdentML, we still have some issues to be resolved
						<!-- HUPO PSI provide <a href="http://www.psidev.info/tools-implementing-mzidentml">a list of tools that support mzIdentML</a>.
						<br/>
						We tested output from the following tools and confirmed that they successfully worked in xiSPEC. <a href="mailto:lars.b.kolbowski@campus.tu-berlin.de" title="Contact us">Contact us</a> if you noticed a tool missing from the list and we will add it.
						<ul>
							<li>
								<a href="http://www.matrixscience.com/">Mascot (Matrix Science)</a>
							</li>
							<li>
								<a href="http://www.proteomesoftware.com/products/scaffold/">Scaffold</a>
							</li>
							<li>
								<a href="http://compomics.github.io/projects/peptide-shaker.html">PeptideShaker</a>
							</li>
							<li>
								<a href="https://www.thermofisher.com/order/catalog/product/OPTON-30795">Proteome Discoverer</a>
							</li>
							<li>
								<a href="https://github.com/Rappsilber-Laboratory/xiFDR">xiFDR</a>
							</li>
						</ul> -->
					</table>
				</div>
 			</div> <!-- CONTAINER -->
		</div> <!-- MAIN -->


	</body>
</html>
