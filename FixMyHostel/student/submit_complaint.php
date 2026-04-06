<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");

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
    $student_name   = trim($_POST['student_name']);
    $student_email  = trim($_POST['student_email']);
    $phone          = trim($_POST['phone']);
    $block_name     = trim($_POST['block_name']);
    $floor_number   = trim($_POST['floor_number']);
    $room_number    = trim($_POST['room_number']);
    $bed_number     = trim($_POST['bed_number']);
    $priority_level = trim($_POST['priority_level']);
    $title          = trim($_POST['title']);
    $category       = trim($_POST['category']);
    $description    = trim($_POST['description']);
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
        (student_id, student_name, student_email, phone, block_name, floor_number, room_number, bed_number, priority_level, title, category, description, department, issue_image)
        VALUES
        ('$user_id', '$student_name', '$student_email', '$phone', '$block_name', '$floor_number', '$room_number', '$bed_number', '$priority_level', '$title', '$category', '$description', '$department', '$issue_image')";

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar">
            <h4>FixMyHostel</h4>
            <a href="dashboard.php">Dashboard</a>
            <a href="submit_complaint.php" class="active">Submit Complaint</a>
            <a href="my_complaints.php">My Complaints</a>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
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
                            <input type="text" name="student_name" class="form-control" value="<?php echo $user['name']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Email</label>
                            <input type="email" name="student_email" class="form-control" value="<?php echo $user['email']; ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Block Name</label>
                            <select name="block_name" class="form-select" required>
                                <option value="Jati" <?php if($user['block_name']=='Jati') echo 'selected'; ?>>Jati</option>
                                <option value="Meranti" <?php if($user['block_name']=='Meranti') echo 'selected'; ?>>Meranti</option>
                                <option value="Cengal" <?php if($user['block_name']=='Cengal') echo 'selected'; ?>>Cengal</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Floor Number</label>
                            <select name="floor_number" class="form-select" required>
                                <option value="1" <?php if($user['floor_number']=='1') echo 'selected'; ?>>1</option>
                                <option value="2" <?php if($user['floor_number']=='2') echo 'selected'; ?>>2</option>
                                <option value="3" <?php if($user['floor_number']=='3') echo 'selected'; ?>>3</option>
                                <option value="4" <?php if($user['floor_number']=='4') echo 'selected'; ?>>4</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Room Number</label>
                            <input type="text" name="room_number" class="form-control" value="<?php echo $user['room_number']; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bed Number</label>
                            <select name="bed_number" class="form-select" required>
                                <option value="">Select bed</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority Level</label>
                            <select name="priority_level" id="priority_level" class="form-select" required>
                                <option value="">Select priority</option>
                                <option value="Low">⚪ Low</option>
                                <option value="Medium">🟩 Medium</option>
                                <option value="High">🟧 High</option>
                                <option value="Urgent">🚩 Urgent</option>
                            </select>
                            <div class="mt-2">
                                <span id="priorityPreview" class="priority-flag flag-low" style="display:none;">⚪ Low</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Issue Picture (Optional)</label>
                            <input type="file" name="issue_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Complaint Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Issue Category</label>
                        <select name="category" class="form-select" required>
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

                    <div class="mb-4">
                        <label class="form-label">Complaint Description</label>
                        <textarea name="description" class="form-control custom-textarea" required></textarea>
                    </div>

                    <button type="submit" name="submit" class="btn btn-main">Submit Complaint</button>
                </form>
            </div>
        </div>
    </div>
</div>

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