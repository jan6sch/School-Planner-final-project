<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kalender</title>
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
<?php
include 'db_connection.php';
    session_start();

// Überprüfen, ob der Benutzer angemeldet ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Aktuelles Datum auf Deutsch
setlocale(LC_TIME, 'de_DE.UTF-8');
    if (isset($_GET['month']) && isset($_GET['year'])) {
        $month = $_GET['month'];
        $year = $_GET['year'];
    } else {
        $month = date('m');
        $year = date('Y');
    }

// Erster Tag des Monats
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$month_name = strftime('%B', $first_day); // Monat auf Deutsch
$day_of_week = date('N', $first_day); // 1 (Montag) bis 7 (Sonntag)

// Überprüfen, ob der Benutzer angemeldet ist bzw. ob der Rang stimmt
$rank = $_SESSION['rank'] ?? null;
$vorname = $_SESSION['vorname'] ?? 'Benutzer';
$nachname = $_SESSION['nachname'] ?? '';

// Anzahl der leeren Tage
$blank_days = $day_of_week - 1;

// Prüfen, ob ein neues Datum hinzugefügt wurde
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $rank == 1) {
    $exam_date = $_POST['exam_date'];
    $fach_id = $_POST['fach_id'];
    $lehrer_id = $_POST['lehrer_id'];
    $stunde_id = $_POST['stunde_id'];
    $description = $_POST['description'];

    $sql = "INSERT INTO exams (exam_date, fach_id, lehrer_id, stunde_id, description) VALUES ('$exam_date', '$fach_id', '$lehrer_id', '$stunde_id', '$description')";
    if (mysqli_query($conn, $sql)) {
        $message = '<div class="message success">Klausur / Überprüfung erfolgreich eingetragen!</div>';
    } else {
        $message = '<div class="message error">Error: ' . $sql . '<br>' . mysqli_error($conn) . '</div>';
    }
}

// Die aktuellen Klausuren abrufen
$sql = "SELECT exams.*, faecher.fach_name, lehrer.vorname, lehrer.nachname, stunden.beginn, stunden.ende 
        FROM exams 
        JOIN faecher ON exams.fach_id = faecher.fach_id 
        JOIN lehrer ON exams.lehrer_id = lehrer.lehrer_id 
        JOIN stunden ON exams.stunde_id = stunden.stunde_id 
        WHERE MONTH(exam_date) = '$month' AND YEAR(exam_date) = '$year'";
$result = mysqli_query($conn, $sql);
$exams = [];
while ($row = mysqli_fetch_assoc($result)) {
    $exams[$row['exam_date']][] = $row;
}

// Abmelden
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>

<div class="calendar-container">
    <div class="calendar-header">
        <h1><?php echo $month_name . ' ' . $year; ?></h1>
        <div>
            <a href="?month=<?php echo $month - 1; ?>&year=<?php echo $year; ?>">Letzter Monat</a>
            <a href="?month=<?php echo $month + 1; ?>&year=<?php echo $year; ?>">Nächster Monat</a>
            <a href="?logout=true"><?php echo htmlspecialchars($vorname . ' ' . $nachname); ?></a>
        </div>
    </div>
    <table>
        <tr>
            <th>Montag</th>
            <th>Dienstag</th>
            <th>Mittwoch</th>
            <th>Donnerstag</th>
            <th>Freitag</th>
            <th>Samstag</th>
            <th>Sonntag</th>
        </tr>
        <tr>
            <?php
            for ($i = 0; $i < $blank_days; $i++) {
                echo '<td></td>';
            }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $current_date = "$year-$month-$day";
                $class = ($current_date == date('Y-m-d')) ? 'today' : '';
                echo "<td class='$class'>$day";

                if (isset($exams[$current_date])) {
                    echo "<div class='tooltip'>";
                    foreach ($exams[$current_date] as $exam) {
                        echo "<strong>Fach:</strong> {$exam['fach_name']}<br>";
                        echo "<strong>Lehrer:</strong> {$exam['vorname']} {$exam['nachname']}<br>";
                        echo "<strong>Stunde:</strong> {$exam['beginn']} - {$exam['ende']}<br>";
                        echo "<strong>Beschreibung:</strong> {$exam['description']}<br>";
                    }
                    echo "</div>";
                }

                echo "</td>";

                if (($day + $blank_days) % 7 == 0) {
                    echo '</tr><tr>';
                }
            }

            while (($day + $blank_days) % 7 != 1) {
                echo '<td></td>';
                $day++;
            }
            ?>
        </tr>
    </table>

    <?php if ($rank == 1): ?>
        <div class="exam-form">
            <h2>Klausur hinzufügen</h2>
            <form method="post">
                <label for="exam_date">Datum:</label>
                <input type="date" id="exam_date" name="exam_date" required><br>
                <label for="fach_id">Fach:</label>
                <select id="fach_id" name="fach_id" required>
                    <?php
                    $sql = "SELECT * FROM faecher";
                    $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$row['fach_id']}'>{$row['fach_name']}</option>";
                        }
                    ?>
                </select><br>
                <label for="lehrer_id">Lehrer:</label>
                <select id="lehrer_id" name="lehrer_id" required>
                    <?php
                    $sql = "SELECT * FROM lehrer";
                    $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$row['lehrer_id']}'>{$row['vorname']} {$row['nachname']}</option>";
                        }
                    ?>
                </select><br>
                <label for="stunde_id">Stunde:</label>
                <select id="stunde_id" name="stunde_id" required>
                    <?php
                    $sql = "SELECT * FROM stunden";
                    $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$row['stunde_id']}'>{$row['beginn']} - {$row['ende']}</option>";
                        }
                    ?>
                </select><br>
                <label for="description">Beschreibung:</label>
                <textarea id="description" name="description" required></textarea><br>
                <button type="submit">Klausur hinzufügen</button>
            </form>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
