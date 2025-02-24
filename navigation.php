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
<style>
    .navigationsleiste_mainpage {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    height: 6vw;
    background-color: #F8F8F8;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    flex-wrap: wrap;
    
}
.home {
            display: flex;
            position: relative;
            left: 2.5vw;
            transition: transform 0.2s ease-in-out;
        }
        .home-icon {
            font-size: 3.5vw;
            color: black;
            margin-right: 30px;
            z-index:2000;
        }
        .home-icon:hover {
            transform: scale(1.1);
        }
    .link_navigationsleiste {
    display: flex;
    justify-content: center; /* Zentriert die Links */
    align-items: center;
    flex-grow: 1;
    gap: 30px; /* Reduziert den Abstand zwischen den Links */
    margin-left: -40px;

}
.box-link {
    padding: 10px 20px; /* Falls nötig, Padding anpassen */
    font-size: 25px; /* Optional: Schriftgröße für bessere Darstellung */
}
    .SucheProfil_navigationsleiste {
        display: flex;
        justify-content: center;
        align-items: center;

    }
    .fehlzeiten-box_navigationsleiste {
            display: flex;
            text-decoration: none;
            left: 12vw;
            
        }
        .fehlzeiten-icon_navigationsleiste {
            font-size: 50px;
            color: black;
            transition: all 0.3s ease-in-out;
            margin-right: 20px;
            margin-top: 12px;
        }
        .fehlzeiten-text_navigationsleiste {
            position: absolute;
            left: 65%;
            bottom: -10px;
            transform: translateX(-50%);
            background-color: black;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
        .fehlzeiten-box_navigationsleiste:hover .fehlzeiten-text_navigationsleiste {
            opacity: 1;
            visibility: visible;
        }
        


        
        
        .search-and-profile {
            display: flex;
            align-items: center;
        }
        .search-box {
            display: flex;
            align-items: center;
            background-color: #f4f4f4;
            padding: 0.6vw;
            border-radius: 20px;
            transition: all 0.3s ease-in-out;
            width: 23.5vw;
            overflow: hidden;
            border: solid black 3px;
        }
        .search-icon {
            font-size: 20px;
            margin-right: 10px;
            transition: all 0.3s ease-in-out;
        }
        .search-input {
            border: none;
            outline: none;
            padding: 5px 10px;
            width: 100%;
            background-color: transparent;
            font-size: 16px;
            transition: all 0.3s ease-in-out;
        }
        .profil-box {
            margin-left: 20px;
            margin-right: 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .profil-kreis {
            font-size: 30px;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 4vw;
            height: 4vw;
            border-radius: 50%;
            transition: all 0.3s ease-in-out;
        }
        .profil-box a {
            text-decoration: none;
            color: inherit;
        }
        
        
        .box-link {
            text-decoration: none;
            color: black;
            font-size: 25px;
            font-weight: bold;
            padding: 10px 15px;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
        }
        .box-link:hover {
            background-color: black;
            color: white;
        }

        .menu-icon {
            display: none;
            font-size: 2.5vw;
            cursor: pointer;
        }
        


</style>
