<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");
include("../includes/sidebar.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student")
{
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$user_id' LIMIT 1")->fetch_assoc();

$message = "";

if(isset($_POST['submit']))
{
    $phone          = $conn->real_escape_string(trim($_POST['phone']));
    $priority_level = $conn->real_escape_string(trim($_POST['priority_level']));
    $title          = $conn->real_escape_string(trim($_POST['title']));
    $category       = $conn->real_escape_string(trim($_POST['category']));
    $description    = $conn->real_escape_string(trim($_POST['description']));
    $department     = detectDepartment($title, $category, $description);

    $issue_image = "";

    if(isset($_FILES['issue_image']) && $_FILES['issue_image']['name'] != "")
    {
        $target_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["issue_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];

        if(in_array($ext, $allowed) && move_uploaded_file($_FILES["issue_image"]["tmp_name"], $target_file))
        {
            $issue_image = $file_name;
        }
    }

    $sql = "INSERT INTO complaints
        (student_id, phone, priority_level, title, category, description, department, issue_image, status)
        VALUES
        ('$user_id', '$phone', '$priority_level', '$title', '$category', '$description', '$department', '$issue_image', 'Pending')";

    if($conn->query($sql) === TRUE){
        $message = "Complaint submitted successfully.";
    } else {
        $message = "Failed to submit complaint.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - FixMyHostel</title>
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../AdminLTE/ColorlibHQ-AdminLTE-5988c4f/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
        <?php renderSidebar('submit_complaint.php'); ?>
        <?php renderTopNavbar('dashboard.php'); ?>

        <div class="content-wrapper">
            <section class="content pt-3">
                <div class="container-fluid">
        <div class="p-2 p-md-3">
            <div class="topbar">
                <h3 class="section-title mb-1">Submit Complaint</h3>
                <div class="small-muted">Welcome, <?php echo $_SESSION['name']; ?></div>
            </div>

            <div class="dashboard-card p-4">
                <?php if($message != "") { ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php } ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" value="<?php echo $user['name']; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Email</label>
                            <input type="email" class="form-control" value="<?php echo $user['email']; ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" value="<?php echo ' Block :' . $user['block_name'] . ' - Floor :' . $user['floor_number'] . ' - Room :' . $user['room_number']; ?>" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Priority Level</label>
                            <select name="priority_level" id="priority_level" class="form-select w-100" required>
                                <option value="">Select priority</option>
                                <option value="Low">⚪ Low</option>
                                <option value="Medium">🟩 Medium</option>
                                <option value="High">🟧 High</option>
                                <option value="Urgent">🚩 Urgent</option>
                            </select>
                            <div class="mt-2">
                                <span id="priorityPreview" class="priority-flag flag-low d-none">⚪ Low</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-6">
                                <!-- <label class="form-label">Upload Issue Picture (Optional)</label>
                                <input type="file" name="issue_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp"> -->

                                <div class="form-group">
                                    <label>Upload Issue Picture (Optional)</label>
                                    <div class="custom-file">
                                        <input type="file" name="issue_image" class="custom-file-input" id="issueImage"
                                            accept=".jpg,.jpeg,.png,.gif,.webp">
                                        <label class="custom-file-label" for="issueImage">Choose file</label>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="row">

                        <!-- Complaint Title -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Complaint Title</label>
                            <input type="text" name="title" class="form-control w-100" required>
                        </div>

                        <!-- Issue Category -->
                        <div class="col-12 mb-3">
                            <label class="form-label">Issue Category</label>
                            <select name="category" class="form-select w-100" required>
                                <option value="">Select category</option>
                                <option value="Electrical">Electrical</option>
                                <option value="Water Leakage">Water Leakage</option>
                                <option value="Furniture Damage">Furniture Damage</option>
                                <option value="Cleanliness">Cleanliness</option>
                                <option value="WiFi Problem">WiFi Problem</option>
                                <option value="Access / Key">Access / Key</option>
                                <option value="Room Transfer / Placement">Room Transfer / Placement</option>
                                <option value="Hostel Rules / Discipline">Hostel Rules / Discipline</option>
                                <option value="Registration / Documentation">Registration / Documentation</option>
                                <option value="Payment / Hostel Fees">Payment / Hostel Fees</option>
                                <option value="Visitor / Pass Issue">Visitor / Pass Issue</option>
                                <option value="Lost Card / ID">Lost Card / ID</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Complaint Description -->
                        <div class="col-12 mb-4">
                            <label class="form-label">Complaint Description</label>
                            <textarea name="description" class="form-control custom-textarea w-100" required></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Submit Button -->
                        <div class="col-xs-6 col-sm-2 col-md-3 col-lg-3">
                            <button type="submit" name="submit" class="btn btn-main w-100">
                                Submit Complaint
                            </button>
                        </div>
                    </div>
                </form>
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

    priorityPreview.style.display = value ? 'inline-block' : 'none';
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
    } else if (value === 'Low') {
        priorityPreview.classList.add('flag-low');
        priorityPreview.textContent = '⚪ Low';
    }
}

prioritySelect.addEventListener('change', updatePriorityPreview);
</script>
</body>
</html>
