<?php
include 'includes/inc_koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network IT Club</title>
    <link rel="icon" href="img/logo.png" type="image/x-icon">
    <!-- my css -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/material.css">

    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- font awesome -->
    <script src="https://kit.fontawesome.com/b3139f7467.js" crossorigin="anonymous"></script>



</head>

<body>
    <header>
        <div class="navbar-container">
            <div class="logo">
                <img src="img/logo.png" alt="NIC">
            </div>
            <div class="title">
                <h1>Network IT Club</h1>
                <p>SMKN 1 PUNGGING</p>
            </div>
        </div>
        <nav class="nav-list">
            <ul class="nav">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#about">About</a></li>
                <li><a href="index.php#class">Class</a></li>
                <li><a href="index.php#contact">Contact</a></li>
            </ul>
            <div class="nav-extra">
                 <a href="#" class="search-button"><i class="fa-solid fa-magnifying-glass fa-lg"></i></a>
                <a href="login.php"><i class="fa-solid fa-user fa-lg"></i></a>
                <a href="#" id="hamburger-menu"><i class="fa-solid fa-bars fa-lg"></i></a>
            </div>
        <div class="search-form">
            <div class="search-input-wrapper"> <input type="search" id="search-box" placeholder="search class here..." />
                <label for="search-box" ><a href="index.php#class"><i class="fa-solid fa-magnifying-glass fa-lg" id="search-btn"></i></a></label>
            </div>
            <div id="autocomplete-list" class="autocomplete-items"></div>
        </div>
        </nav>
    </header>