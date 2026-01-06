<?php 
include('../../includes/header.php'); 
require_once('../../config/database.php');

// Fetch complaints joined with usernames so you know who sent them
$sql = "SELECT complaints.*, users.username 
        FROM complaints 
        JOIN users ON complaints.user_id = users.id 
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Failed: " . mysqli_error($conn));
}
?>

<div class="dashboard-container">
    <div class="main-content">
        <h2>Customer Support Tickets</h2>
        <table border="1" style="width:100%; border-collapse: collapse; margin-top: 20px;">
            <tr style="background-color: #f8f8f8;">
                <th>User</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['username']; ?></td>
                    <td><?php echo $row['subject']; ?></td>
                    <td><?php echo $row['message']; ?></td>
                    <td><strong><?php echo $row['status']; ?></strong></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center; padding: 20px;">No complaints found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
