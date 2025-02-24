<?php
SESSION_start();
 
$conn = new Mysqli('localhost', 'root', '', 'educonnect');
mysqli_set_charset($conn, "utf8mb4");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
$wochentag = date('w', strtotime('-2 day')); 
$aktuelles_datum = date('Y-m-d', strtotime('-2 day')); 
 
$_SESSION['rang'] = 'lehrer';     //################################################################################
$_SESSION['user_id'] = 2;          //################################################################################
$_SESSION['schule_id'] = 1;       //################################################################################
$_SESSION['stufe_id'] = 13;
$_SESSION['letzte_seite'] = 'mainpage.php';
unset($_SESSION['news_kategorie']);
 
$rang = $_SESSION['rang'];

if ($rang === 'admin' || $rang === 'schulleiter') {
    // Admins und Schulleiter sind in der "lehrer"-Tabelle
    $id_feld = "lehrer_id";
    $rang_tabelle = "lehrer";
} else {
    // Schüler oder Lehrer haben ihre eigene Tabelle
    $id_feld = $rang . "_id"; // Ergibt "schueler_id" oder "lehrer_id"
    $rang_tabelle = $rang; // Tabelle ist "schueler" oder "lehrer"
}
 
$user_id = $_SESSION['user_id'];
$schule_id = $_SESSION['schule_id'];
$letzte_seite = $_SESSION['letzte_seite'];
 
 
 
$schueler_query = "SELECT `vorname`, `nachname` FROM $rang_tabelle WHERE `$id_feld` = '$user_id'";
 
$schueler = mysqli_query($conn, $schueler_query);
 
if ($schueler) {
    $row = mysqli_fetch_assoc($schueler);
 
    if ($row) {
        $vorname = $row['vorname'];
        $nachname = $row['nachname'];
 
        $_SESSION['vorname'] = $vorname;
        $_SESSION['nachname'] = $nachname;
 
        $firstLetter_vorname = strtoupper($vorname[0]);
        $firstLetter_nachname = strtoupper($nachname[0]);
 
        $namenkürzel = $firstLetter_vorname . $firstLetter_nachname;
 
        // Hintergrundfarbe bestimmen
        $firstLetter = strtoupper($firstLetter_vorname);
 
        if ($firstLetter >= 'A' && $firstLetter <= 'B') {
            $backgroundColor = "#3498db"; // Blau
        } elseif ($firstLetter >= 'C' && $firstLetter <= 'D') {
            $backgroundColor = "#e74c3c"; // Rot
        } elseif ($firstLetter >= 'E' && $firstLetter <= 'F') {
            $backgroundColor = "#2ecc71"; // Grün
        } elseif ($firstLetter >= 'G' && $firstLetter <= 'H') {
            $backgroundColor = "#f1c40f"; // Gelb
        } elseif ($firstLetter >= 'I' && $firstLetter <= 'J') {
            $backgroundColor = "#9b59b6"; // Lila
        } elseif ($firstLetter >= 'K' && $firstLetter <= 'L') {
            $backgroundColor = "#1abc9c"; // Türkis
        } elseif ($firstLetter >= 'M' && $firstLetter <= 'N') {
            $backgroundColor = "#ff5733"; // Orange
        } elseif ($firstLetter >= 'O' && $firstLetter <= 'P') {
            $backgroundColor = "#8e44ad"; // Dunkellila
        } elseif ($firstLetter >= 'Q' && $firstLetter <= 'R') {
            $backgroundColor = "#2c3e50"; // Dunkelblau
        } elseif ($firstLetter >= 'S' && $firstLetter <= 'T') {
            $backgroundColor = "#d35400"; // Dunkelorange
        } elseif ($firstLetter >= 'U' && $firstLetter <= 'V') {
            $backgroundColor = "#16a085"; // Dunkeltürkis
        } elseif ($firstLetter >= 'W' && $firstLetter <= 'X') {
            $backgroundColor = "#c0392b"; // Dunkelrot
        } else {
            $backgroundColor = "#7f8c8d"; // Grau für Y und Z
        }
    }}
   
    $_SESSION['profil_kürzel'] = $namenkürzel;
    $_SESSION['profil_farbe'] = $backgroundColor;
 
    $fehlzeiten_query = "SELECT `lehrer_id`, `beginn`, `ende` FROM `lehrer_fehlzeiten` WHERE `datum` = '$aktuelles_datum'";
    $fehlzeiten_result = mysqli_query($conn, $fehlzeiten_query);
    $fehlende_lehrer = [];
     
    if ($fehlzeiten_result && $fehlzeiten_result->num_rows > 0) {
        while ($row = $fehlzeiten_result->fetch_assoc()) {
            $fehlende_lehrer[] = [
                'lehrer_id' => $row['lehrer_id'],
                'beginn' => $row['beginn'],
                'ende' => $row['ende']
            ];
        }
    }
    if($rang === 'schueler'){
        $stufe_id = $_SESSION['stufe_id']; // Diese Zeile ist nur für Schüler relevant
 
 
// Stundenplan mit Raumverlegung abrufen
$stundenplan_query = "
 
    SELECT
        sp.stunde_id,
        st.stunde,
        sp.fach_id,
        sp.lehrer_id,
        f.fach_name,
        r.raum_bezeichnung AS standard_raum,
        rv.raum_bezeichnung AS geaendert_raum,
        TIME_FORMAT(st.beginn, '%H:%i') AS beginn,
        TIME_FORMAT(st.ende, '%H:%i') AS ende,
        IF(rv.raum_id IS NOT NULL, 1, 0) AS raum_geaendert -- Markierung für geänderte Räume
    FROM
        stundenplan_schueler sp
    LEFT JOIN
        faecher f ON sp.fach_id = f.fach_id
    LEFT JOIN
        stunden st ON sp.stunde_id = st.stunde_id AND st.schule_id = '$schule_id' -- Stunden werden anhand der Schule geladen
    LEFT JOIN
        raum r ON sp.raum_id = r.raum_id -- Standardraum
    LEFT JOIN
        (
            SELECT rv.raum_id, rv.stunde_id, rv.lehrer_id, raum.raum_bezeichnung
            FROM raum_verlegung rv
            JOIN raum ON rv.raum_id = raum.raum_id
            WHERE rv.datum = '$aktuelles_datum'
        ) rv ON sp.lehrer_id = rv.lehrer_id AND sp.stunde_id = rv.stunde_id
    WHERE
        sp.schueler_id = '$user_id'
        AND sp.tag_id = '$wochentag'
    ORDER BY
        st.stunde ASC
";
 
$stundenplan_result = mysqli_query($conn, $stundenplan_query);
 
$stundenplan = [];
while ($row = $stundenplan_result->fetch_assoc()) {
    $stundenplan[] = $row;
}}
 
else if($rang === 'lehrer'){

    $stufe_id = 0;
    $stundenplan_query = "
 
   SELECT
        sp.stunde_id,
        st.stunde,
        sp.fach_id,
        f.fach_name,
        r.raum_bezeichnung AS standard_raum,
        rv.raum_bezeichnung AS geaendert_raum,
        TIME_FORMAT(st.beginn, '%H:%i') AS beginn,
        TIME_FORMAT(st.ende, '%H:%i') AS ende,
        IF(rv.raum_id IS NOT NULL, 1, 0) AS raum_geaendert -- Markierung für geänderte Räume
    FROM
        stundenplan_lehrer sp
    LEFT JOIN
        faecher f ON sp.fach_id = f.fach_id
    LEFT JOIN
        stunden st ON sp.stunde_id = st.stunde_id AND st.schule_id = '$schule_id' -- Stunden werden anhand der Schule geladen
    LEFT JOIN
        raum r ON sp.raum_id = r.raum_id -- Standardraum
    LEFT JOIN
        (
            SELECT rv.raum_id, rv.stunde_id, rv.lehrer_id, raum.raum_bezeichnung
            FROM raum_verlegung rv
            JOIN raum ON rv.raum_id = raum.raum_id
            WHERE rv.datum = '$aktuelles_datum'
        ) rv ON sp.lehrer_id = rv.lehrer_id AND sp.stunde_id = rv.stunde_id
    WHERE
        sp.lehrer_id = '$user_id'
 
        AND sp.tag_id = '$wochentag'
    ORDER BY
        st.stunde ASC
";
 
$stundenplan_result = mysqli_query($conn, $stundenplan_query);
 
$stundenplan = [];
$stundenplan_result = mysqli_query($conn, $stundenplan_query);
 
if (!$stundenplan_result) {
    die("Fehler in der Abfrage: " . mysqli_error($conn));
}
 
while ($row = $stundenplan_result->fetch_assoc()) {
    $stundenplan[] = $row;
}
}
 
else if($rang === schulleiter){
 
}
 
else if($rang === admin){
 
}
else {
    header('Location: index.php');
}
 
 
// Nachrichten für die spezifische Schule
$news_query = "SELECT `titel` FROM `news_allgemein` WHERE `schule_id` = $schule_id AND '$aktuelles_datum' BETWEEN `start_datum` AND `end_datum` AND `wichtigkeit` = 1";
$news_allgemein_result = mysqli_query($conn, $news_query);
 
// Nachrichten für die spezifische Stufe
$news_schule_query = "SELECT `titel` FROM `news_stufe` WHERE `schule_id` = $schule_id AND '$aktuelles_datum' BETWEEN `start_datum` AND `end_datum` AND `wichtigkeit` = 1 AND `stufe_id` = '$stufe_id'";
$news_schule_result = mysqli_query($conn, $news_schule_query);
 
// Nachrichten für das Land
$schule_query = "SELECT `schule_id`, `schule_name`, `land`, `bundesland` FROM `schulen` WHERE `schule_id` = $schule_id";
$schule_result = $conn->query($schule_query);
 
$schule_name = null;
$bundesland = null;
 
if ($schule_result && $schule_result->num_rows > 0) {
    $schule_data = $schule_result->fetch_assoc();
    $schule_name = $schule_data['schule_name'];
    $land = $schule_data['land'];
}
 
 
$news_land_query = "SELECT `titel` FROM `news_land` WHERE `land` = '$land' AND '$aktuelles_datum' BETWEEN `start_datum` AND `end_datum` AND `wichtigkeit` = 1";
$news_land_result = mysqli_query($conn, $news_land_query);
 
 
// Ferien abrufen (nach Bundesland gefiltert)
$ferien_query = "SELECT `ferien_name`, `anfangsdatum`, `enddatum` FROM `ferien` WHERE '$aktuelles_datum' BETWEEN `anfangsdatum` AND `enddatum` AND `bundesland` = '$bundesland'";
$ferien_result = mysqli_query($conn, $ferien_query);
$ferien_name = null;
 
if ($ferien_result && $ferien_result->num_rows > 0) {
    $ferien_data = $ferien_result->fetch_assoc();
    $ferien_name = $ferien_data['ferien_name'];
}
?>
 
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Mainpage</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        
        a {
            text-decoration: none !important;
        }
        .main-container {
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 1vw;
            margin-top: 6vw;
            padding: 2vw;
        }
        .content-top_mainpage {
            display: grid;
            grid-template-columns: 10fr 6fr;
            grid-gap: 1vw;
        }
        .content-left_mainpage, .content-right_mainpage {
            background-color: #F8F8F8;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .content-left_mainpage{
            padding-bottom: 10px;
            height: 410px;
        }
        .schule-content-left_mainpage {
            font-size: 2.5vw;
            font-weight: bold;
            color: #212121;
            margin-bottom: 10px;
        }
        .datum-content-left_mainpage {
            font-size: 1.5vw;
            font-weight: bold;
            color: #555;
            margin-bottom: 20px;
        }
        .news-box {
    display: block;
    width: 100%;
    text-align: left;
    border: 1px solid grey;
    border-radius: 10px;
    background: none;
    padding: 15px;
    cursor: pointer;
    font: inherit;
    outline: none;
    margin-bottom: 12px;
    height: 90px;

}



.news-box h3 {
    margin: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #CCC;
    font-size: 1.5vw;
    color: #444;
}

.news-title {
    font-weight: bold;
    font-size: 1.3vw;
    margin-top: 6px;
    color: red;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap; /* Falls nur eine Zeile erlaubt sein soll */
   
}





.news-box:hover {
    background-color: #F8F8F8;
    border: black solid 2px;
    transition: background-color 0.3s, border-color 0.3s;
}
.content-right_mainpage {
    display: flex;
    flex-direction: column;
    height: 400px; /* Feste Höhe für die Box */
}

.content-right_mainpage table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    height: 100%;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.content-right_mainpage thead {
    height: 50px; /* Fixe Höhe für die Kopfzeile */
    display: table-header-group;
    background-color: #f8f9fa;
}

.content-right_mainpage tbody {
    display: table-row-group;
    height: auto; /* Passt sich der Anzahl der Einträge an */
}

.content-right_mainpage th, 
.content-right_mainpage td {
    text-align: center;
    padding: 10px;
    vertical-align: middle;
    border: 1px solid #ddd;
}

.content-right_mainpage th {
    background-color: #e9ecef;
    font-weight: bold;
}

.content-right_mainpage tr:nth-child(even) {
    background-color: #f2f2f2;
}


        table:hover {
            background-color: #F1F1F1;
        }
        tr.krank {
            background-color: rgba(255, 0, 0, 0.5);
            color: white;
        }
        tr.krank:hover {
            background-color: rgba(200, 0, 0, 0.7);
        }
        .ferien {
            background-color: #FFEB3B;
            color: #212121;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            border-radius: 5px;
        }
        .ferien:hover {
            background-color: #FFC107;
        }
        .raum-geaendert {
            background-color: rgba(255, 255, 0, 0.5);
            font-weight: bold;
            text-align: center;
        }
        .raum-geaendert:hover {
            background-color: rgba(255, 215, 0, 0.7);
        }
        .down-under_mainpage {
            display: grid;
            grid-template-columns: 1fr 3fr;
            grid-gap: 1vw;
            margin-top: 3.2vw;
        }
        .navigation_mainpage {
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 1vw;
        }
        .navigation_mainpage a div {
            background-color: #F5F5F5;
            padding: 1vw;
            font-weight: bold;
            color: #333;
            border-radius: 5px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 1vw;
        }
        .navigation_mainpage i {
            font-size: 2.5vw;
        }
        
        .kalender_mainpage {
            background-color:rgb(245, 245, 245);
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            height: 100%;
        }
        
        
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<div class="main-container">
    <div class="content-top_mainpage">
        <div class="content-left_mainpage">
            <div class="schule-content-left_mainpage"><?php echo htmlspecialchars($schule_name ?? 'Unbekannte Schule'); ?></div>
            <div class="datum-content-left_mainpage">
                <?php
                setlocale(LC_TIME, 'de_DE.UTF-8');
                echo strftime('%A, %d.%m.%Y');
                ?>
            </div>
            <form method="POST" action="news_session.php">
    <button type="submit" name="news_kategorie" value="ALLGEMEIN" class="news-box">
        <h3>Allgemeine Infos</h3>
        <?php if ($news_allgemein_result && $news_allgemein_result->num_rows > 0): ?>
            <?php while ($news = $news_allgemein_result->fetch_assoc()): ?>
                <div class="news-title"><?php echo htmlspecialchars($news['titel']); ?></div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="news-hint">Keine allgemeinen News verfügbar.</div>
        <?php endif; ?>
    </button>
</form>

<form method="POST" action="news_session.php">
    <button type="submit" name="news_kategorie" value="STUFE" class="news-box">
        <h3>Stufennachrichten</h3>
        <?php if ($news_schule_result && $news_schule_result->num_rows > 0): ?>
            <?php while ($news = $news_schule_result->fetch_assoc()): ?>
                <div class="news-title"><?php echo htmlspecialchars($news['titel']); ?></div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="news-hint">Keine stufenbezogenen News verfügbar.</div>
        <?php endif; ?>
    </button>
</form>

<form method="POST" action="news_session.php">
    <button type="submit" name="news_kategorie" value="LAND" class="news-box">
        <h3>Landesnachrichten</h3>
        <?php if ($news_land_result && $news_land_result->num_rows > 0): ?>
            <?php while ($news = $news_land_result->fetch_assoc()): ?>
                <div class="news-title"><?php echo htmlspecialchars($news['titel']); ?></div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="news-hint">Keine landesbezogenen News verfügbar.</div>
        <?php endif; ?>
    </button>
</form>
        </div>
        <a href="stundenplan.php">
            <div class="content-right_mainpage">
                <?php if ($ferien_name): ?>
                    <div class="ferien">Keine Schule: <?php echo $ferien_name; ?></div>
                <?php endif; ?>
                <table>
                    <tr>
                        <th>Stunde</th>
                        <th>Fach</th>
                        <th>Raum</th>
                        <th>Beginn</th>
                        <th>Ende</th>
                    </tr>
                    <?php foreach ($stundenplan as $eintrag): ?>
                        <?php
                            $krank = false;
                            foreach ($fehlende_lehrer as $lehrer) {
                                if (
                                    $eintrag['lehrer_id'] == $lehrer['lehrer_id'] &&
                                    (
                                        ($eintrag['beginn'] >= $lehrer['beginn'] && $eintrag['beginn'] <= $lehrer['ende']) ||
                                        ($eintrag['ende'] >= $lehrer['beginn'] && $eintrag['ende'] <= $lehrer['ende'])
                                    )
                                ) {
                                    $krank = true;
                                    break;
                                }
                            }
                            $klasse = $krank ? 'krank' : '';
                        ?>
                        <tr class="<?php echo $klasse; ?>">
                            <td><?php echo $eintrag['stunde']; ?></td>
                            <td><?php echo $eintrag['fach_name']; ?></td>
                            <td class="<?php echo ($eintrag['raum_geaendert'] ? 'raum-geaendert' : ''); ?>">
                                <?php echo $eintrag['raum_geaendert'] ? $eintrag['geaendert_raum'] : $eintrag['standard_raum']; ?>
                            </td>
                            <td><?php echo $eintrag['beginn']; ?></td>
                            <td><?php echo $eintrag['ende']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </a>
    </div>
    <div class="down-under_mainpage">
        <div class="navigation_mainpage">
            <a href=""><div class="teams_mainpage"><i class='bx bxs-group'></i> TEAMS</div></a>
            <a href="news.php"><div class="news_mainpage"><i class='bx bxs-news'></i> NEWS</div></a>
            <a href=""><div class="chat_mainpage"><i class='bx bxs-message-dots'></i> CHAT</div></a>
            <a href=""><div class="edocs_mainpage"><i class='bx bxs-ghost'></i> E-DOCS</div></a>
            <a href=""><div class="noten_mainpage"><i class='bx bxs-star'></i> MEINE NOTEN</div></a>
            <a href="stundenplan.php"><div class="stundenplan_mainpage"><i class='bx bxs-calendar'></i> STUNDENPLAN</div></a>
        </div>
        <href="calendar.php">
            <div class="kalender_mainpage">
                <?php include 'calendar_mainpage.php'; ?>
            </div>   
    </div>
</div>
</body>
</html>