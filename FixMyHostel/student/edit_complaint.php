<?php
session_start();
include("../includes/db.php");
include("../includes/functions.php");

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

$check = $conn->query("SELECT * FROM complaints WHERE complaint_id='$id' AND student_id='$user_id' AND status='Pending' LIMIT 1");

if($check->num_rows == 0){
    die("This complaint cannot be edited or does not exist.");
}

$row = $check->fetch_assoc();

if(isset($_POST['update']))
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
        student_name='$student_name',
        student_email='$student_email',
        phone='$phone',
        block_name='$block_name',
        floor_number='$floor_number',
        room_number='$room_number',
        bed_number='$bed_number',
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container login-wrapper d-flex align-items-center justify-content-center">
    <div class="col-lg-8">
        <div class="dashboard-card p-4">
            <h2 class="fw-bold mb-3">Edit Complaint #<?php echo $row['complaint_id']; ?></h2>

            <?php if($message != "") { ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student Name</label>
                        <input type="text" name="student_name" class="form-control" value="<?php echo $row['student_name']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student Email</label>
                        <input type="email" name="student_email" class="form-control" value="<?php echo $row['student_email']; ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $row['phone']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Block Name</label>
                        <select name="block_name" class="form-select" required>
                            <option value="Jati" <?php if($row['block_name']=='Jati') echo 'selected'; ?>>Jati</option>
                            <option value="Meranti" <?php if($row['block_name']=='Meranti') echo 'selected'; ?>>Meranti</option>
                            <option value="Cengal" <?php if($row['block_name']=='Cengal') echo 'selected'; ?>>Cengal</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Floor Number</label>
                        <select name="floor_number" class="form-select" required>
                            <option value="1" <?php if($row['floor_number']=='1') echo 'selected'; ?>>1</option>
                            <option value="2" <?php if($row['floor_number']=='2') echo 'selected'; ?>>2</option>
                            <option value="3" <?php if($row['floor_number']=='3') echo 'selected'; ?>>3</option>
                            <option value="4" <?php if($row['floor_number']=='4') echo 'selected'; ?>>4</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Room Number</label>
                        <input type="text" name="room_number" class="form-control" value="<?php echo $row['room_number']; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Bed Number</label>
                        <select name="bed_number" class="form-select" required>
                            <option value="A" <?php if($row['bed_number']=='A') echo 'selected'; ?>>A</option>
                            <option value="B" <?php if($row['bed_number']=='B') echo 'selected'; ?>>B</option>
                            <option value="C" <?php if($row['bed_number']=='C') echo 'selected'; ?>>C</option>
                            <option value="D" <?php if($row['bed_number']=='D') echo 'selected'; ?>>D</option>
                        </select>
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