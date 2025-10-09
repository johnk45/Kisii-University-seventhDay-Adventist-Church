<?php
// 🛠 Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ksusda";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KUSDA Events</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f5f6fa;
      margin: 0;
      padding: 0;
    }
    .title {
      text-align: center;
      font-size: 2em;
      margin: 30px 0;
      color: #004d40;
    }
    .event-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
      padding: 20px;
    }
    .event-card {
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }
    .event-card:hover {
      transform: scale(1.02);
    }
    .event-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }
    .event-details {
      padding: 15px;
    }
    .event-details h3 {
      color: #00796b;
      margin-bottom: 10px;
    }
    .event-date {
      color: #555;
      font-style: italic;
    }
    .countdown {
      font-weight: bold;
      color: #d32f2f;
      margin: 10px 0;
    }
    .event-actions a {
      margin-right: 10px;
      text-decoration: none;
      color: white;
      background: #00796b;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.9em;
      transition: background 0.3s;
    }
    .event-actions a:hover {
      background: #004d40;
    }
  </style>
</head>
<body>
  <section class="events-section">
    <h2 class="title">📅 Upcoming Events</h2>
    <div class="event-container">
      <?php
      $sql = "SELECT * FROM events WHERE status='Upcoming' ORDER BY event_date ASC";
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $date = date("F d, Y", strtotime($row['event_date']));
          echo "
          <div class='event-card'>
            <img src='../{$row['banner_image']}' alt='Event Image'>
            <div class='event-details'>
              <h3>{$row['title']}</h3>
              <p>{$row['description']}</p>
              <p class='event-date'>📆 $date at {$row['start_time']}</p>
              <div id='countdown{$row['id']}' class='countdown' data-date='{$row['event_date']}'></div>
              <div class='event-actions'>
                <a href='https://wa.me/?text=Join%20me%20for%20{$row['title']}%20on%20$date!' target='_blank'>WhatsApp</a>
                <a href='https://twitter.com/intent/tweet?text=Join%20me%20for%20{$row['title']}%20on%20$date!' target='_blank'>X</a>
                <a href='https://www.facebook.com/sharer/sharer.php?u=https://ksusda.org/events.php?id={$row['id']}' target='_blank'>Facebook</a>
              </div>
            </div>
          </div>
          ";
        }
      } else {
        echo "<p style='text-align:center;color:#666;'>No upcoming events found.</p>";
      }
      ?>
    </div>
  </section>

  <script>
  // Countdown Timer Script
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".countdown").forEach(function (timer) {
      const date = new Date(timer.getAttribute("data-date")).getTime();
      const interval = setInterval(function () {
        const now = new Date().getTime();
        const distance = date - now;

        if (distance <= 0) {
          timer.innerHTML = "🎉 Event Ongoing!";
          clearInterval(interval);
          return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

        timer.innerHTML = `⏳ ${days}d ${hours}h ${minutes}m remaining`;
      }, 1000);
    });
  });
  </script>
</body>
</html>
