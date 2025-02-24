<div class="navigationsleiste_mainpage">
    <div class="menu-icon" onclick="toggleMenu()">
        <i class='bx bx-menu'></i>
    </div>
    <a href="mainpage.php" class="home">
        <div class="home-icon"><i class="bx bx-home"></i></div>
    </a>
    <div class="link_navigationsleiste">
        <a href="link1_navigationsleiste" class="box-link">Teams</a>
        <a href="link2_navigationsleiste" class="box-link">Chat</a>
        <a href="stundenplan.php" class="box-link">Stundenplan</a>
        <a href="link4_navigationsleiste" class="box-link">Meine Noten</a>
        <a href="link5_navigationsleiste" class="box-link">EDocs</a>
    </div>
    <a href="fehlzeiten.php" class="fehlzeiten-box_navigationsleiste">
        <div class="fehlzeiten-icon_navigationsleiste"><i class="bx bx-user-x"></i></div>
        <span class="fehlzeiten-text_navigationsleiste">Heute nicht da?</span>
    </a>
    <?php
    // Die Kürzel und Hintergrundfarbe aus der Session laden
    $namenkürzel = isset($_SESSION['profil_kürzel']) ? $_SESSION['profil_kürzel'] : '??';
    $backgroundColor = isset($_SESSION['profil_farbe']) ? $_SESSION['profil_farbe'] : '#7f8c8d';

    // Vorname und Nachname aus der Session (oder einer Datenbank) laden
    $vorname = isset($_SESSION['vorname']) ? $_SESSION['vorname'] : 'Maximilianalsjakshkjs';
    $nachname = isset($_SESSION['nachname']) ? $_SESSION['nachname'] : 'Mustermann';

    echo '<div class="SucheProfil_navigationsleiste">
        <div class="search-and-profile">
            <!-- Suchbox -->
            <div class="search-box">
                <div class="search-icon">
                    <i class="bx bx-search-alt"></i>
                </div>
                <input type="text" class="search-input" placeholder="Suchen...">
            </div>

            <!-- Profilbox -->
            <div class="profil-box">
                <a href="profil.php" class="profil-link">
                    <div class="profil-kreis" style="background-color: ' . $backgroundColor . ';">
                        ' . $namenkürzel . '
                    </div>
                </a>

                
            </div>
        </div>
    </div>';
    ?>
</div>
<script>
    function toggleMenu() {
        var menu = document.querySelector('.link_navigationsleiste');
        if (menu.style.display === 'flex' || menu.style.display === '') {
            menu.style.display = 'none';
        } else {
            menu.style.display = 'flex';
        }
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Document</title>
</head>
</html>