<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'educonnect');
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

if (!isset($_SESSION['team_id'])) {
    die("Kein Team ausgewählt! </br> <a href='lehrerinsert.php'>Hier gehts zurück</a>" );
}

$team_id = (int)$_SESSION['team_id'];
$schule_id = $_SESSION['schule_id'];
$lehrer_id = $_SESSION['user_id'];

$team_result = $conn->query("SELECT * FROM teams WHERE team_id = $team_id");
$team = $team_result->fetch_assoc();
if (!$team) {
    echo "<p class='error_message_lehrerstunden'>Team nicht gefunden!</p>";
    echo "<button class='back_button_lehrerstunden' onclick='window.history.back();'>⬅️ Zurück</button>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tag_id'], $_POST['stunde_id'], $_POST['raum_id'])) {
    $fach_id = $_SESSION['fach_id'];
    $tag_id = (int)$_POST['tag_id'];
    $stunde_id = (int)$_POST['stunde_id'];
    $raum_id = (int)$_POST['raum_id'];

    $stmt_check = $conn->prepare("SELECT * FROM stundenplan_lehrer WHERE lehrer_id = ? AND tag_id = ? AND stunde_id = ?");
    $stmt_check->bind_param("iii", $lehrer_id, $tag_id, $stunde_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<p class='error_message_lehrerstunden'>Fehler: Du hast bereits eine Stunde an diesem Tag zur gleichen Zeit!</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO stundenplan_lehrer (schule_id, lehrer_id, fach_id, tag_id, stunde_id, raum_id, team_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiiii", $schule_id, $lehrer_id, $fach_id, $tag_id, $stunde_id, $raum_id, $team_id);
        $stmt->execute();
    }
}

if (isset($_POST['delete_id'])) {
    $stundenplan_id = (int)$_POST['delete_id'];
    $stmt_delete = $conn->prepare("DELETE FROM stundenplan_lehrer WHERE stundenplan_id = ?");
    $stmt_delete->bind_param("i", $stundenplan_id);
    $stmt_delete->execute();
}

if (isset($_POST['delete_team'])) {
    $stmt_delete_stunden = $conn->prepare("DELETE FROM stundenplan_lehrer WHERE team_id = ?");
    $stmt_delete_stunden->bind_param("i", $team_id);
    $stmt_delete_stunden->execute();

    $stmt_delete_team = $conn->prepare("DELETE FROM teams WHERE team_id = ?");
    $stmt_delete_team->bind_param("i", $team_id);
    
    if ($stmt_delete_team->execute()) {
        unset($_SESSION['team_id']);
        header("Location: lehrerinsert.php");
        exit();
    }
}

$stundenplan_result = $conn->query("SELECT sl.*, t.tag_name, r.raum_bezeichnung FROM stundenplan_lehrer sl JOIN tage t ON sl.tag_id = t.tag_id JOIN raum r ON sl.raum_id = r.raum_id WHERE sl.team_id = $team_id ORDER BY sl.tag_id ASC, sl.stunde_id ASC");
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Stundenplan für <?php echo htmlspecialchars($team['team_name']); ?></title>
    <link rel="stylesheet" href="style_lehrerstunden.css">
</head>
<body>

<div class="container_lehrerstunden">
    <div class="button_container_lehrerstunden">
    <a href="lehrerinsert.php" class="back_button_lehrerstunden" >⬅️ Zurück</a>
        <form method="POST">
            <button type="submit" name="delete_team" class="delete_button_lehrerstunden">🚨 Team löschen</button>
        </form>
        
        
    </div>

    <h2>Stunden für <?php echo htmlspecialchars($team['team_name']); ?></h2>
    <form method="POST" class="form_lehrerstunden">
        <label for="tag_id">Tag auswählen:</label>
        <select name="tag_id" required>
            <option value="">Tag auswählen</option>
            <?php
            $tage_result = $conn->query("SELECT * FROM tage");
            while ($row = $tage_result->fetch_assoc()) {
                echo "<option value='{$row['tag_id']}'>{$row['tag_name']}</option>";
            }
            ?>
        </select>

        <label for="stunde_id">Stunde auswählen:</label>
        <select name="stunde_id" required>
            <option value="">Stunde auswählen</option>
            <?php
            $stunde_result = $conn->query("SELECT * FROM stunden");
            while ($row = $stunde_result->fetch_assoc()) {
                echo "<option value='{$row['stunde_id']}'>Stunde {$row['stunde_id']}</option>";
            }
            ?>
        </select>

        <label for="raum_id">Raum auswählen:</label>
        <select name="raum_id" required>
            <option value="">Raum auswählen</option>
            <?php
            $raum_result = $conn->query("SELECT * FROM raum");
            while ($row = $raum_result->fetch_assoc()) {
                echo "<option value='{$row['raum_id']}'>{$row['raum_bezeichnung']}</option>";
            }
            ?>
        </select>

        <button type="submit" class="submit_button_lehrerstunden">Stunde hinzufügen</button>
    </form>

    <h3>Aktueller Stundenplan</h3>
    <table class="table_lehrerstunden">
        <tr>
            <th>Tag</th>
            <th>Stunde</th>
            <th>Raum</th>
            <th>Aktion</th>
        </tr>
        <?php while ($row = $stundenplan_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['tag_name']); ?></td>
                <td><?php echo htmlspecialchars($row['stunde_id']); ?></td>
                <td><?php echo htmlspecialchars($row['raum_bezeichnung']); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="delete_id" value="<?php echo $row['stundenplan_id']; ?>">
                        <button type="submit" class="delete_button_lehrerstunden">Löschen</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>

<?php $conn->close(); ?>
<style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.container_lehrerstunden {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 50%;
    text-align: center;
    margin-top: 20px;
}

.button_container_lehrerstunden {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.back_button_lehrerstunden, .delete_button_lehrerstunden, .submit_button_lehrerstunden {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 5px;
    transition: background 0.3s;
}

.delete_button_lehrerstunden {
    background-color: #DC3545;
}

.back_button_lehrerstunden:hover, .delete_button_lehrerstunden:hover, .submit_button_lehrerstunden:hover {
    opacity: 0.8;
}

.form_lehrerstunden {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
}

label {
    font-weight: bold;
}

select, button {
    padding: 8px;
    width: 80%;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.table_lehrerstunden {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table_lehrerstunden th, .table_lehrerstunden td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
}

.table_lehrerstunden th {
    background-color: #007BFF;
    color: white;
}

.error_message_lehrerstunden {
    color: red;
    font-weight: bold;
}
</style>