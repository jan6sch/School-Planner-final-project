<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "educonnect"); // Datenbankverbindung anpassen

if (!$conn) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

// Standardwerte setzen
$schule_id = $_SESSION['schule_id'];
$stufe_id = $_SESSION['stufe_id'];

// Falls eine Kategorie per Button geändert wird, in der Session speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategorie'])) {
    $_SESSION['news_kategorie'] = $_POST['kategorie'];
    header("Location: news.php"); // Seite neu laden
    exit;
}

// Standardkategorie auf 'alle' setzen, wenn keine gewählt wurde
$newsKategorie = $_SESSION['news_kategorie'] ?? 'ALLE';

// Die Abfrage basierend auf der gewählten Kategorie erstellen
switch ($newsKategorie) {
    case 'ALLGEMEIN':
        $news_query = "SELECT `titel`, `inhalt`, `start_datum` FROM `news_allgemein`
                       WHERE `schule_id` = $schule_id
                       ORDER BY `start_datum` DESC";
        break;

    case 'STUFE':
        $news_query = "SELECT `titel`, `inhalt`, `start_datum` FROM `news_stufe`
                       WHERE `schule_id` = $schule_id
                       AND `stufe_id` = '$stufe_id'
                       ORDER BY `start_datum` DESC";
        break;

    case 'LAND':
        // Land abrufen
        $schule_query = "SELECT `land` FROM `schulen` WHERE `schule_id` = $schule_id";
        $schule_result = mysqli_query($conn, $schule_query);
        $land = ($schule_result && mysqli_num_rows($schule_result) > 0) ? mysqli_fetch_assoc($schule_result)['land'] : '';

        $news_query = "SELECT `titel`, `inhalt`, `start_datum` FROM `news_land`
                       WHERE `land` = '$land'
                       ORDER BY `start_datum` DESC";
        break;

    case 'ALLE':
        $news_query = "(SELECT `titel`, `inhalt`, `start_datum` FROM `news_allgemein` WHERE `schule_id` = $schule_id)
                       UNION
                       (SELECT `titel`, `inhalt`, `start_datum` FROM `news_stufe` WHERE `schule_id` = $schule_id AND `stufe_id` = '$stufe_id')
                       UNION
                       (SELECT `titel`, `inhalt`, `start_datum` FROM `news_land` WHERE `land` = (SELECT `land` FROM `schulen` WHERE `schule_id` = $schule_id))
                       ORDER BY `start_datum` DESC";
        break;

    default:
        $news_query = "";
}

// News abrufen
$news_result = mysqli_query($conn, $news_query);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>News</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 7vw;
        }
        h1 {
            color: #333;
        }
        .buttons {
            margin-bottom: 20px;
        }
        .buttons button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .buttons button:hover {
            background: #0056b3;
        }
        .news-list {
            list-style: none;
            padding: 0;
        }
        .news-list li {
            background: #fff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }
        .news-title {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .news-content {
            font-size: 14px;
            color: #333;
            text-align: left;
            margin-bottom: 5px;
        }
        .news-date {
            font-size: 12px;
            color: gray;
            align-self: flex-end;
        }
    </style>
</head>
<body>
<?php include 'navigation.php'; ?>
<div class="container">
    <h1>News</h1>

    <!-- Buttons zum Wechseln der Kategorie -->
    <div class="buttons">
        <form method="post">
            <button type="submit" name="kategorie" value="ALLE">Alle News</button>
            <button type="submit" name="kategorie" value="ALLGEMEIN">Allgemeine News</button>
            <button type="submit" name="kategorie" value="STUFE">Stufen-News</button>
            <button type="submit" name="kategorie" value="LAND">Landes-News</button>
        </form>
    </div>

    <!-- Aktuelle Kategorie anzeigen -->
    <p>Du hast die Kategorie <strong><?php echo htmlspecialchars($newsKategorie); ?></strong> ausgewählt.</p>

    <!-- News anzeigen -->
    <ul class="news-list">
        <?php
        if ($news_result && mysqli_num_rows($news_result) > 0) {
            while ($row = mysqli_fetch_assoc($news_result)) {
                echo "<li>
                        <div class='news-title'>" . htmlspecialchars($row['titel']) . "</div>
                        <div class='news-content'>" . nl2br(htmlspecialchars($row['inhalt'])) . "</div>
                        <div class='news-date'>" . $row['start_datum'] . "</div>
                      </li>";
            }
        } else {
            echo "<p>Keine News verfügbar.</p>";
        }
        ?>
    </ul>
</div>

</body>
</html>

<?php
// Datenbankverbindung schließen
mysqli_close($conn);
?>
