<div class="inner-wrapper">
    <section role="main" class="content-body">
        <header class="page-header">
            <!--<h2>Welcome</h2>

            --><?php /*include_once 'Components/header-home-button.php'; */?>
        </header>

        <!-- start: page -->
        <div class="row">
            <div class="col-md-12">
                <section class="card">
                    <header class="card-header">
                        <h2 class="card-title">Welcome, <span style="color: teal"><?php echo $loggedUserFullName; ?></span></h2>
                    </header>
                    <div class="card-body" style="text-align: center; height: 100vh">
                        <!-- <div><h3>Welcome to the project</h3></div>-->

                        <div><img src="img/bbs-logo.png" alt="QGDP Logo" height="300px"></div>

                        <div>
                            <b style="font-size: 17px; line-height: 2"><?php echo $projectDescription; ?></b>
                            <p style="font-size: 17px; line-height: 2"><?php echo $projectDescription2; ?></p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <!-- end: page -->
    </section>
</div>
