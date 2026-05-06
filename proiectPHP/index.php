<?php
    include_once "config/database.php";
    include_once "includes/header.php";

    $database = new Database();
    $db = $database->getConnection();

    $totalMembersQuery = "SELECT COUNT(*) AS total_members FROM members";
    $totalMembersStmt = $db->prepare($totalMembersQuery);
    $totalMembersStmt->execute();
    $totalMembers = $totalMembersStmt->fetch(PDO::FETCH_ASSOC)['total_members'];

    $professionDistributionQuery = "
        SELECT profession, COUNT(*) AS profession_count 
        FROM members 
        WHERE profession IS NOT NULL AND profession != '' 
        GROUP BY profession 
        ORDER BY profession_count DESC";
    $professionDistributionStmt = $db->prepare($professionDistributionQuery);
    $professionDistributionStmt->execute();
    $professionDistribution = $professionDistributionStmt->fetchAll(PDO::FETCH_ASSOC);

    $newMembersPerMonthQuery = "
        SELECT YEAR(created_at) AS year, MONTH(created_at) AS month, COUNT(*) AS new_members 
        FROM members 
        GROUP BY year, month 
        ORDER BY year DESC, month DESC";
    $newMembersPerMonthStmt = $db->prepare($newMembersPerMonthQuery);
    $newMembersPerMonthStmt->execute();
    $newMembersPerMonth = $newMembersPerMonthStmt->fetchAll(PDO::FETCH_ASSOC);

    $topCompaniesQuery = "
        SELECT company, COUNT(*) AS company_count 
        FROM members 
        WHERE company IS NOT NULL AND company != '' 
        GROUP BY company 
        ORDER BY company_count DESC 
        LIMIT 5";
    $topCompaniesStmt = $db->prepare($topCompaniesQuery);
    $topCompaniesStmt->execute();
    $topCompanies = $topCompaniesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="jumbotron">
    <h1 class="display-4">Welcome to our Women Tech Power Platform</h1>
    <p class="lead">Empowering women in technology through community and collaboration.</p>
    <hr class="my-4">
    <p>Join our community of professional women in Tech.</p>
    <a class="btn btn-primary btn-lg" href="add_member.php" role="button">Join Now</a>
</div>

<div class="jumbotron">
    <h2 class="text-center">Our Success Stories</h2>
    <div class="row">
        <!-- Story 1 -->
        <div class="col-md-4">
            <div class="card">
                <img src="images/success1.png" class="card-img-top" alt="Success Story 1">
                <div class="card-body">
                    <h5 class="card-title">Maria R.</h5>
                    <p class="card-text">
                        Maria joined our platform last year and successfully transitioned into a software engineering role at a top tech company. She credits the mentorship and resources available here for her success.
                    </p>
                </div>
            </div>
        </div>
        <!-- Story 2 -->
        <div class="col-md-4">
            <div class="card">
                <img src="images/success2.png" class="card-img-top" alt="Success Story 2">
                <div class="card-body">
                    <h5 class="card-title">Amelia C.</h5>
                    <p class="card-text">
                        Amelia leveraged our resources to start her own tech consultancy business. She now mentors others on the platform, empowering women to follow their dreams in the tech world.
                    </p>
                </div>
            </div>
        </div>
        <!-- Story 3 -->
        <div class="col-md-4">
            <div class="card">
                <img src="images/success3.png" class="card-img-top" alt="Success Story 3">
                <div class="card-body">
                    <h5 class="card-title">Sara J.</h5>
                    <p class="card-text">
                        Sara attended several platform events and gained the skills needed to excel in her career. She now leads a team of engineers at a renowned company.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="jumbotron">
            <h5>Total Members</h5>
            <p class="lead"><?php echo $totalMembers; ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="jumbotron">
            <h5>Profession Distribution</h5>
            <ul class="list-group">
                <?php foreach ($professionDistribution as $profession): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($profession['profession']); ?>: <?php echo $profession['profession_count']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-3">
        <div class="jumbotron">
            <h5>New Members Per Month</h5>
            <ul class="list-group">
                <?php foreach ($newMembersPerMonth as $monthData): ?>
                    <?php
                    $date = DateTime::createFromFormat('Y-m', $monthData['year'] . '-' . $monthData['month']);
                    $formattedDate = $date->format('M Y');
                    ?>
                    <li class="list-group-item">
                        <?php echo $formattedDate; ?>: <?php echo $monthData['new_members']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-3">
        <div class="jumbotron">
            <h5>Top Represented Companies</h5>
            <ul class="list-group">
                <?php foreach ($topCompanies as $company): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($company['company']); ?>: <?php echo $company['company_count']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
