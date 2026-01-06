<?php 
include('../../includes/header.php');
require_once('../../models/Admin.php');
require_once('../../config/database.php');

$adminModel = new Admin($conn);
$users = $adminModel->viewAllUsers(); // READ Operation 
?>

<div class="card">
    <h2>User Management</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['role']; ?></td>
                <td>
                    <a href="delete_user.php?id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Are you sure?')" 
                       style="color:red;">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../../includes/footer.php'); ?>