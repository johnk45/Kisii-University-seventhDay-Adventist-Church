/**list of sql <code>

CREATE DATABASE datanabe name;
USE database name;

CREATE TABLE table name(

    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    contact VARCHAR(100),
    message TEXT NOT NULL,
    is_confidential TINYINT(1) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Received',
    assigned_to VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    date_submitted DATETIME DEFAULT CURRENT_TIMESTAMP

);
<?php
$servername= "localhost";
$username="root";
$password="";
$dbname="name of the database";

$conn = new mysqli($servername,$username,$password,$dbname);
if($conn->connect_error){
    die("Connection failed:".$conn->connect_error);
}
$result = $conn->query("SELECT * FROM table_name ORDER BY date_submitted DESC");

?>

</code> */

<!Doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta  name="viewport" content="width=device-width,initial-scale=1.0">
       <title>KSUSDA Prayer Dashboard</title>
       <style>
        body{
            font-family:'Poppins',sans-serif;
            background:#f2f5f7;
            padding:20px;
        }
        table{
            border-collapse:collapse;
            width:100%;
            background:#fff;
            border-radius:10px;
            overflow:hidden;

        }
        th,td{
            border:1px solid #ccc;
            padding:10px;
            text-align:left;
        }
th{
    background:#007bff;
    color:white;
}
</style>
</head>
<body>
    <h2>KSUSDA PRAYER REQUEST DASHBOARD</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Contact</th>
            <th>Message</th>
            <th>Confidential</th>
            <th>Status</th>
            <th>Date Submitted</th>

</tr>
<?php while($row = $result->fetch_assoc()):?>
    <tr>
        <td><?=$row['id']?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['contact']) ?></td>
        <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
        <td><?= $row['is_confidential'] ? 'Yes' : 'No' ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td><?= $row['date_submitted'] ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>




