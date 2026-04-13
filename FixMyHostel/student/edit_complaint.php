<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: my_complaints.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];
$message = "";

$check = $conn->query("SELECT c.*, u.name AS student_name, u.email AS student_email, u.block_name, u.floor_number, u.room_number FROM complaints c JOIN users u ON c.student_id=u.id WHERE c.complaint_id='$id' AND c.student_id='$user_id' AND c.status='Pending' LIMIT 1");

if($check->num_rows == 0){
    die("This complaint cannot be edited or does not exist.");
}

$row = $check->fetch_assoc();

if(isset($_POST['update']))
{
    $phone          = $conn->real_escape_string(trim($_POST['phone']));
    $priority_level = $conn->real_escape_string(trim($_POST['priority_level']));
    $title          = $conn->real_escape_string(trim($_POST['title']));
    $category       = $conn->real_escape_string(trim($_POST['category']));
    $description    = $conn->real_escape_string(trim($_POST['description']));
    $department     = detectDepartment($title, $category, $description);

    $issue_image = $row['issue_image'];

    if(isset($_FILES['issue_image']) && $_FILES['issue_image']['name'] != "")
    {
        $target_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["issue_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if(in_array($ext, $allowed))
        {
            if(move_uploaded_file($_FILES["issue_image"]["tmp_name"], $target_file))
            {
                if(!empty($issue_image) && file_exists("../uploads/" . $issue_image)){
                    unlink("../uploads/" . $issue_image);
                }
                $issue_image = $file_name;
            }
        }
    }

    $sql = "UPDATE complaints SET
        phone='$phone',
        priority_level='$priority_level',
        title='$title',
        category='$category',
        description='$description',
        department='$department',
        issue_image='$issue_image'
        WHERE complaint_id='$id' AND student_id='$user_id' AND status='Pending'";

    if($conn->query($sql) === TRUE){
        header("Location: my_complaints.php");
        exit();
    } else {
        $message = "Failed to update complaint.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Complaint - FixMyHostel</title>
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
        <?php renderSidebar('my_complaints.php'); ?>
        <?php renderTopNavbar('dashboard.php'); ?>
    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="p-2 p-md-3">
    <div class="mx-auto" style="max-width: 960px;">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-3">Edit Complaint #<?php echo $row['complaint_id']; ?></h2>

            <?php if($message != "") { ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student Name</label>
                        <input type="text" class="form-control" value="<?php echo $row['student_name']; ?>" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student Email</label>
                        <input type="email" class="form-control" value="<?php echo $row['student_email']; ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" value="<?php echo $row['block_name'] . ' - Floor ' . $row['floor_number'] . ' - Room ' . $row['room_number']; ?>" readonly>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Priority Level</label>
                        <select name="priority_level" id="priority_level" class="form-select" required>
                            <option value="Low" <?php if($row['priority_level']=='Low') echo 'selected'; ?>>⚪ Low</option>
                            <option value="Medium" <?php if($row['priority_level']=='Medium') echo 'selected'; ?>>🟩 Medium</option>
                            <option value="High" <?php if($row['priority_level']=='High') echo 'selected'; ?>>🟧 High</option>
                            <option value="Urgent" <?php if($row['priority_level']=='Urgent') echo 'selected'; ?>>🚩 Urgent</option>
                        </select>
                        <div class="mt-2">
                            <span id="priorityPreview" class="priority-flag" style="display:inline-block;"></span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Replace Picture (Optional)</label>
                        <input type="file" name="issue_image" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Complaint Title</label>
                    <input type="text" name="title" class="form-control" value="<?php echo $row['title']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Issue Category</label>
                    <select name="category" class="form-select" required>
                        <option value="Electrical" <?php if($row['category']=='Electrical') echo 'selected'; ?>>Electrical</option>
                        <option value="Water Leakage" <?php if($row['category']=='Water Leakage') echo 'selected'; ?>>Water Leakage</option>
                        <option value="Furniture Damage" <?php if($row['category']=='Furniture Damage') echo 'selected'; ?>>Furniture Damage</option>
                        <option value="Cleanliness" <?php if($row['category']=='Cleanliness') echo 'selected'; ?>>Cleanliness</option>
                        <option value="WiFi Problem" <?php if($row['category']=='WiFi Problem') echo 'selected'; ?>>WiFi Problem</option>
                        <option value="Access / Key" <?php if($row['category']=='Access / Key') echo 'selected'; ?>>Access / Key</option>
                        <option value="Room Transfer / Placement" <?php if($row['category']=='Room Transfer / Placement') echo 'selected'; ?>>Room Transfer / Placement</option>
                        <option value="Hostel Rules / Discipline" <?php if($row['category']=='Hostel Rules / Discipline') echo 'selected'; ?>>Hostel Rules / Discipline</option>
                        <option value="Registration / Documentation" <?php if($row['category']=='Registration / Documentation') echo 'selected'; ?>>Registration / Documentation</option>
                        <option value="Payment / Hostel Fees" <?php if($row['category']=='Payment / Hostel Fees') echo 'selected'; ?>>Payment / Hostel Fees</option>
                        <option value="Visitor / Pass Issue" <?php if($row['category']=='Visitor / Pass Issue') echo 'selected'; ?>>Visitor / Pass Issue</option>
                        <option value="Lost Card / ID" <?php if($row['category']=='Lost Card / ID') echo 'selected'; ?>>Lost Card / ID</option>
                        <option value="Other" <?php if($row['category']=='Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label">Complaint Description</label>
                    <textarea name="description" class="form-control custom-textarea" required><?php echo $row['description']; ?></textarea>
                </div>

                <button type="submit" name="update" class="btn btn-main">Update Complaint</button>
                <a href="my_complaints.php" class="btn btn-secondary ms-2">Back</a>
            </form>
        </div>
    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/jquery/jquery.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/js/adminlte.min.js"></script>

<script>
const prioritySelect = document.getElementById('priority_level');
const priorityPreview = document.getElementById('priorityPreview');

function updatePriorityPreview() {
    const value = prioritySelect.value;
    priorityPreview.className = 'priority-flag';

    if (value === 'Urgent') {
        priorityPreview.classList.add('flag-urgent');
        priorityPreview.textContent = '🚩 Urgent';
    } else if (value === 'High') {
        priorityPreview.classList.add('flag-high');
        priorityPreview.textContent = '🟧 High';
    } else if (value === 'Medium') {
        priorityPreview.classList.add('flag-medium');
        priorityPreview.textContent = '🟩 Medium';
    } else {
        priorityPreview.classList.add('flag-low');
        priorityPreview.textContent = '⚪ Low';
    }
}

prioritySelect.addEventListener('change', updatePriorityPreview);
updatePriorityPreview();
</script>
</body>
</html>
