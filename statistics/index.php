<?php
    session_start();
    
    if(isset($_POST['logout'])){
        session_destroy();
        header('location: /AdvisorHub/login');
    }

    if(isset($_POST['profile'])){
        header('location: /AdvisorHub/profile');
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Topic Statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="../Logo.png">
</head>
<body>
<nav>
        <div class="logo">
            <img src="../CSIT.png" alt="" width="250px">
        </div>
        <ul>
            <li><a href="/AdvisorHub/home">Home</a></li>
            
            <?php
                if(isset($_SESSION['username'])){
                    echo 
                    "
                    <li><a href='/AdvisorHub/advisor'>Advisor</a></li>
                    <li><a href='/AdvisorHub/inbox'>Inbox</a></li>
                    <li><a href='/AdvisorHub/thesis/thesis.php'>Thesis</a></li>
                    <li><a href='/AdvisorHub/statistics'>Statistics</a></li>
                    <li><a href='/AdvisorHub/thesis_resource_list/thesis_resource_list.php'>File</a></li>
                    ";
                }else{
                    echo "<li><a href='/AdvisorHub/login'>Login</a></li>";
                }
            ?>
        </ul>

        <div class="userProfile">
            <?php
                if(isset($_SESSION['username'])){
                    echo '<h2>'.$_SESSION['username'].'<h2/>';
                    echo "<i class='bx bxs-user-circle' ></i>";
                    echo "<div class='dropdown'>
                            <form action='' method='post'>
                                <button name='profile'>Profile</button>
                                <button name='logout'>Logout</button>
                            </form>
                        </div>";
                }
            ?>
        </div>
    </nav>
    <div class="container">
        <h1>Research Topic Statistics</h1>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th>Research Topic</th>
                    <th>Number of Studies</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Artificial Intelligence (AI)</td>
                    <td>120</td>
                </tr>
                <tr>
                    <td>Machine Learning</td>
                    <td>95</td>
                </tr>
                <tr>
                    <td>Data Science</td>
                    <td>80</td>
                </tr>
                <tr>
                    <td>Cybersecurity</td>
                    <td>60</td>
                </tr>
                <tr>
                    <td>Internet of Things (IoT)</td>
                    <td>50</td>
                </tr>
            </tbody>
        </table>

        <!-- Chart -->
        <div id="chart-container">
            <canvas id="researchChart"></canvas>
        </div>
    </div>

    <script>
        // Chart Data
        const data = {
            labels: ["AI", "Machine Learning", "Data Science", "Cybersecurity", "IoT"],
            datasets: [{
                label: "Number of Studies",
                data: [120, 95, 80, 60, 50],
                backgroundColor: [
                    "rgba(75, 192, 192, 0.6)",
                    "rgba(153, 102, 255, 0.6)",
                    "rgba(255, 159, 64, 0.6)",
                    "rgba(255, 99, 132, 0.6)",
                    "rgba(54, 162, 235, 0.6)"
                ],
                borderColor: [
                    "rgba(75, 192, 192, 1)",
                    "rgba(153, 102, 255, 1)",
                    "rgba(255, 159, 64, 1)",
                    "rgba(255, 99, 132, 1)",
                    "rgba(54, 162, 235, 1)"
                ],
                borderWidth: 1
            }]
        };

        // Chart Configuration
        const config = {
            type: "bar",
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Render Chart
        const ctx = document.getElementById("researchChart").getContext("2d");
        new Chart(ctx, config);
    </script>
</body>
</html>
