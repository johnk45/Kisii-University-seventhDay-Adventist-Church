<?php

session_start();
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.html");
    exit();
}
$servername = "localhost";
$username = "root";
$passwotd = "";
$dbname = "church_db";

$conn = new mysqli($servername,$username,$password,$dbname);

$result = $conn->query("SELECT * FROM members ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html>

<head>
    <title>Church Admin Dashboard</title>
    <style>
    table {
        border-collapse: collapse;
        width: 95%;
        margin: 20px auto;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background: #2c3e50;
        color: white;
    }

    .btn {
        padding: 5px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    .edit {
        background: #27ae60;
        color: white;
    }

    .delete {
        background: #c0392b;
        color: white;
    }
    </style>
</head>
<!---this meant to allow leaders to login and see all the reistered member--->

<body style="font-family:Arial;padding:20px;">
    <h2 style="text-align:center;">Welcome,<?php echo $_SESSION['admin'];?>
        <h3 style=text-align:center;">Registered Church Members</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Department</th>
                <th>Registered At</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $result->fetch_assoc()):?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['fullname']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['department']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <a href="edit_member.php?id=<?php echo $row['id']; ?>" class="btn edit">Wdit</a>
                    <a href="delete_member.php?id=<?php echo $row ['id']; ?>" class="btn delete" onclick=return
                        confirm('Are you sure?')">Delete</a>
                </td>

            </tr>
            <?php endwhile; ?>
        </table>

</html>