<?php
SESSION_start();

$conn = new mysqli('localhost', 'root', '', 'educonnect');
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$letzte_seite = $_SESSION['letzte_seite'];
$user_id = $_SESSION['user_id'];
$schule_id = $_SESSION['schule_id'];
$rang = $_SESSION['rang']; // Rang des Nutzers abrufen (z. B. schüler, lehrer, schulleiter)
$aktuelles_datum = date('Y-m-d');
$heute = date('N'); // 1 = Montag, 2 = Dienstag, ...

// Standardmäßig 12 Stunden anzeigen
$max_stunde = 12;

// Stundenzeiten abrufen
$stunden_query = "
    SELECT stunde_id, TIME_FORMAT(beginn, '%H:%i') AS beginn, TIME_FORMAT(ende, '%H:%i') AS ende 
    FROM stunden WHERE schule_id = '$schule_id' ORDER BY stunde_id
";
$stunden_result = $conn->query($stunden_query);
$stundenzeiten = [];

while ($row = $stunden_result->fetch_assoc()) {
    $stundenzeiten[$row['stunde_id']] = ['beginn' => $row['beginn'], 'ende' => $row['ende']];
}

// Wochentage definieren
$wochentage = [
    1 => 'Montag',
    2 => 'Dienstag',
    3 => 'Mittwoch',
    4 => 'Donnerstag',
    5 => 'Freitag'
];

// Stundenplan abrufen
$stundenplan = [];

// Unterschiedliche Tabellen für Schüler und Lehrer nutzen
foreach ($wochentage as $index => $tag) {
    if ($rang === 'lehrer' || $rang === 'schulleiter') {
        // Stundenplan für Lehrer abrufen
        $stundenplan_query = "
            SELECT 
                sp.stunde_id,
                f.fach_name,
                r.raum_bezeichnung AS standard_raum,
                rv.raum_bezeichnung AS geaendert_raum
            FROM
                stundenplan_lehrer sp
            LEFT JOIN
                faecher f ON sp.fach_id = f.fach_id
            LEFT JOIN
                raum r ON sp.raum_id = r.raum_id
            LEFT JOIN
                (
                    SELECT rv.raum_id, rv.stunde_id, rv.lehrer_id, raum.raum_bezeichnung
                    FROM raum_verlegung rv
                    JOIN raum ON rv.raum_id = raum.raum_id
                    WHERE rv.datum = '$aktuelles_datum'
                ) rv ON sp.lehrer_id = rv.lehrer_id AND sp.stunde_id = rv.stunde_id
            WHERE
                sp.lehrer_id = '$user_id'
                AND sp.tag_id = '$index'
            ORDER BY
                sp.stunde_id ASC
        ";
    } else {
        // Stundenplan für Schüler abrufen
        $stundenplan_query = "
            SELECT 
                sp.stunde_id,
                f.fach_name,
                r.raum_bezeichnung AS standard_raum,
                rv.raum_bezeichnung AS geaendert_raum
            FROM
                stundenplan_schueler sp
            LEFT JOIN
                faecher f ON sp.fach_id = f.fach_id
            LEFT JOIN
                raum r ON sp.raum_id = r.raum_id
            LEFT JOIN
                (
                    SELECT rv.raum_id, rv.stunde_id, rv.lehrer_id, raum.raum_bezeichnung
                    FROM raum_verlegung rv
                    JOIN raum ON rv.raum_id = raum.raum_id
                    WHERE rv.datum = '$aktuelles_datum'
                ) rv ON sp.lehrer_id = rv.lehrer_id AND sp.stunde_id = rv.stunde_id
            WHERE
                sp.schueler_id = '$user_id'
                AND sp.tag_id = '$index'
            ORDER BY
                sp.stunde_id ASC
        ";
    }

    $result = $conn->query($stundenplan_query);
    if (!$result) {
        die("SQL-Fehler: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $stundenplan[$row['stunde_id']][$tag] = $row['fach_name'];
    }
}

// HTML-Anzeige des Stundenplans bleibt gleich
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wochenstundenplan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .stundenplan_container {
            width: 93vw;
            height: 84vh;
            margin-top: 2vw;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
        }

        .header_stundenplan {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .title_stundenplan {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
        }

        .button_stundenplan {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }

        .content_stundenplan {
            width: 100%;
            height: 100%;
            overflow-y: auto;
        }

        .table_stundenplan {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .th_stundenplan, .td_stundenplan {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
            font-size: 14px;
            line-height: 1.2;
            word-wrap: break-word;
            overflow: hidden;
        }

        .th_stundenplan {
            background-color: #f0f0f0;
        }

        .td_stundenplan:first-child, .th_stundenplan:first-child { 
            width: 80px;
            font-size: 14px;
        }

        .stunden_head_stundenplan { 
            font-weight: bold; 
            text-align: left; 
            padding-left: 5px;
        }

        .zeiten_stundenplan { 
            color: gray; 
            display: block;
            text-align: right;
            font-size: 12px;
        }

        .heute_spalte_stundenplan { 
            background-color: rgba(83, 83, 83, 0.4);
        }
    </style>
</head>
<body>

<div class="stundenplan_container">
    <div class="header_stundenplan">
        <a href="mainpage.php" class="button_stundenplan">Zurück</a>
        <h2 class="title_stundenplan">Wochenstundenplan</h2>
        <?php if ($rang === 'lehrer' || $rang === 'schulleiter'): ?>
            <a href="lehrerinsert.php" class="button_stundenplan">Teams erstellen/bearbeiten</a>
        <?php else: ?>
            <div style="width: 120px;"></div> <!-- Platzhalter für zentrierte Überschrift -->
        <?php endif; ?>
    </div>
    
    <div class="content_stundenplan">
        <table class="table_stundenplan">
            <tr>
                <th class="th_stundenplan">Stunde</th>
                <?php foreach ($wochentage as $index => $tag): ?>
                    <th class="th_stundenplan <?php echo ($index == $heute) ? 'heute_spalte_stundenplan' : ''; ?>">
                        <?php echo $tag; ?>
                    </th>
                <?php endforeach; ?>
            </tr>

            <?php 
            $max_stunde_dynamisch = !empty($stundenplan) ? max(array_keys($stundenplan)) : 0;


             if ($max_stunde_dynamisch > 0): ?>
                <?php for ($i = 1; $i <= $max_stunde_dynamisch; $i++): ?>
                    <tr>
                        <td class="td_stundenplan stunden_head_stundenplan">
                            <?php echo $i; ?>
                            <span class="zeiten_stundenplan">
                                <?php 
                                echo isset($stundenzeiten[$i]) 
                                    ? $stundenzeiten[$i]['beginn'] . " - " . $stundenzeiten[$i]['ende'] 
                                    : "-";
                                ?>
                            </span>
                        </td>
                        <?php foreach ($wochentage as $index => $tag): ?>
                            <td class="td_stundenplan <?php echo ($index == $heute) ? 'heute_spalte_stundenplan' : ''; ?>">
                                <?php echo isset($stundenplan[$i][$tag]) ? $stundenplan[$i][$tag] : '-'; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
            
        </table>
    </div>
</div>

</body>
</html>
