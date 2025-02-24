<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Kalender</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
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
?>
<a href="calendar.php">
<div class="calendar-container">
    <div class="calendar-header">
        <h1><?php echo $month_name . ' ' . $year; ?></h1>
        <div>
            <a href="?month=<?php echo $month - 1; ?>&year=<?php echo $year; ?>">Letzter Monat</a>
            <a href="?month=<?php echo $month + 1; ?>&year=<?php echo $year; ?>">Nächster Monat</a>
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
                // Hier ist die Hoverbox, bei der ich das Design nicht hinbekommen habe. Vielleicht könnt ihr das ja nochmal überarbeiten        
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
</div>
</a>
</body>
</html>
