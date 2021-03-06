<?php
include_once 'data.php';
include_once 'functions.php';

if (!empty($_GET['project'])) {
    $projectID = intval($_GET['project']);
} elseif (!empty($_POST['project'])) {
    $projectID = intval($_POST['project']);
} else {
    die();
}

database_connect($database_path, 'discussions');

$dbHandle->exec("CREATE TABLE IF NOT EXISTS projectdiscussion (id INTEGER PRIMARY KEY, projectID integer NOT NULL, user TEXT NOT NULL, timestamp TEXT NOT NULL, message TEXT NOT NULL)");

if(isset($_POST['newmessage']) && !empty($_POST['newmessage'])) {

	$stmt = $dbHandle->prepare("INSERT INTO projectdiscussion (projectID, user, timestamp, message) VALUES (:projectID, :user, :timestamp, :message)");

	$stmt->bindParam(':projectID', $projectID);
        $stmt->bindParam(':user', $user);
	$stmt->bindParam(':timestamp', $timestamp);
	$stmt->bindParam(':message', $message);

	$user = $_SESSION['user'];
	$timestamp = time();
	$message = $_POST['newmessage'];

	$insert = $stmt->execute();
	$dbHandle = null;
        if ($insert) die('OK');
}

if(isset($_GET['delete1']) && !empty($_GET['delete2'])) {

	$delete = $dbHandle->exec("DELETE FROM projectdiscussion WHERE projectID=" . $projectID);
	$dbHandle = null;
        die('OK');
}

if(isset($_GET['read'])) {

    $result = $dbHandle->query("SELECT * FROM projectdiscussion WHERE projectID=" . $projectID . " ORDER BY id DESC LIMIT 100");
    $dbHandle = null;

    print '<table>';

    while($message = $result->fetch(PDO::FETCH_ASSOC)) {

        $message['user'] = htmlspecialchars($message['user']);
	$message['message'] = htmlspecialchars($message['message']);
	$message['message'] = preg_replace('/(https?\:\/\/\S+)/i', '<a href="\\1" target="_blank">\\1</a>', $message['message']);
	$message['message'] = nl2br($message['message']);

	print "<tr><td style=\"white-space: nowrap;padding: 4px\"><b>".date("M j, Y, h:i:s A", $message['timestamp']).", ".$message['user'].":</b></td>";
	print "<td style=\"padding: 4px\">$message[message]</td></tr>".PHP_EOL;
    }

    print '</table>';
}
?>
