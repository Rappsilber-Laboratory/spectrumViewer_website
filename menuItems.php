<?php if (session_status() === PHP_SESSION_NONE){session_start();} ?>
    <nav class="hover-effect">
                <a href="index.php" data-hover="Home" data-no-instant>Home</a><br/>
                <?php
                    if (!isset($_SESSION['session_name'])) {
                        echo '<a href="createAccount.php" data-hover="Create Account" data-no-instant>Create Account</a><br/>';
                        echo '<a href="login.php" data-hover="Sign in">Sign in</a><br/>';
                    } else {
                        echo '<a href="upload.php" data-hover="Upload new data set">Upload</a><br/>';
                        echo '<a href="../history/history.html" data-hover="Back to data set list" data-no-instant>My Data</a><br/>';
                        echo '<a href="./php/user/logout.php" data-hover="Sign out">Sign Out</a><br/>';
                    }
                ?>
                <br/>
                <a href="https://xiview.org/xi3/network.php?upload=547-90828-08990-28852-02225" data-hover="Demo" data-no-instant>Demo</a><br/>
                <a href="mzIdentML.php" data-hover="mzIdentML">mzIdentML</a><br/>
                <a href="csvFormats.php" data-hover="CSV Formats">CSV Formats</a><br/>
                <a href="privacy.php" data-hover="Privacy Statement">Privacy</a><br/>
                <a href="contact.php" data-hover="Contact">Contact</a><br/>
                <br/>


    </nav>
