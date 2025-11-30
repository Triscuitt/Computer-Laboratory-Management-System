<?php
    require_once 'objects/user.php';
    require_once 'objects/equipment.php';

    $studentUser = new User('Frince', 'Nacion', 'Student', 'frincefriesss', 'CCS', 'frince@dyci.edu.ph');
    $facultyUser = new User('Ana', 'Lizel', 'Faculty', 'analizel', 'CCS', 'analizel@dyci.edu.ph');
    $user = $facultyUser;

    # Every page by role access
    $role_access = [
      '📸 Attendance'=>[['Student', 'Faculty', 'Admin'],'attendance_page'], 
      '📦 Borrow / Return'=>[['Faculty', 'Admin'],'borrow_page'], 
      '🖥️ Lab Equipment'=>[['Faculty', 'Admin'],'lab_equipment_page'],
      '📩 Requests'=>[['Faculty', 'Admin'],'requests_page'],
      '📄 Reports'=>[['Faculty', 'Admin'],'reports_page']
    ];
    

    if (isset($_POST['addEquipment'])) {
      $equipmentName = $_POST['equipment_name'];
      $equipmentCategory = $_POST['equipment_category'];
      $equipmentStatus = $_POST['equipment_status'];
      addEquipment($equipmentName, $equipmentCategory, $equipmentStatus);
    }

    // Add equipment
    function addEquipment($equipment_name, $equipment_category, $equipment_status){
      $new_equipment = new Equipment($equipment_name, $equipment_category, $equipment_status);
      // insert to database
    }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lab Monitoring System - Prototype</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --navy: #0B1F51;
      --primary: #1E3A8A;
      --success: #059669;
      --bg: #F5F6F9;
    }
    body{ background: var(--bg); }
    .sidebar{
      height: 100vh;
      background: var(--navy);
      color: #fff;
      min-width: 220px;
    }
    .sidebar .nav-link{ color: rgba(255,255,255,0.9); }
    .sidebar .nav-link.active{ background: rgba(255,255,255,0.06); border-radius:8px; }
    .card-ghost{ background: #fff; border-radius:12px; box-shadow: 0 6px 18px rgba(13,17,32,0.06); }
    .badge-status-working{ background:#d1fae5; color:var(--success); padding:.45rem; border-radius:10px; font-weight:600;}
    .badge-status-issue{ background:#fff7ed; color:#b45309; padding:.45rem; border-radius:10px; font-weight:600;}
    .badge-status-pulled{ background:#f3f4f6; color:#6b7280; padding:.45rem; border-radius:10px; font-weight:600;}
    .qr-card{ display:flex; flex-direction:column; align-items:center; gap:.5rem; padding:1rem; }
    pre { white-space: pre-wrap; }
    /* small responsive tweak */
    @media (max-width: 991px){
      .sidebar{ position:fixed; z-index:1030; transform:translateX(-100%); transition:transform .2s; }
      .sidebar.show{ transform:translateX(0); }
    }
  </style>
</head>
<body>
<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <div class="d-flex align-items-center">
      <img src="" alt="logo natin" class="me-2">
      <span class="fw-bold" style="color:var(--primary)">Lab Monitoring System</span>
    </div>

    <button class="btn btn-outline-secondary d-lg-none" id="sidebarToggle">☰</button>

    <div class="d-flex flex-row align-items-center justify-content-end ms-auto">
      <div class="me-3 text-end">
        <div class="small text-muted">Role & Username</div>
        <div class=""><?=$user->getRole() . ' — ' . $user->getUsername()?></div>
      </div>
      <div class="dropdown w-50">
        <a class="btn btn-sm btn-outline-secondary dropdown-toggle" href="#" data-bs-toggle="dropdown">
          <img src="https://i.pinimg.com/236x/13/74/20/137420f5b9c39bc911e472f5d20f053e.jpg" class="rounded-circle me-1 img-fluid" style="height: auto; width: 15%;">Profile
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#" data-nav="profile">Settings</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="#">Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="d-flex">
  <!-- Sidebar -->
  <aside class="sidebar p-3" id="mainSidebar">
    <ul class="nav flex-column gap-1">
      <li><a class="nav-link active" href="#" data-nav="dashboard">🏠 Dashboard</a></li>
      <?php foreach ($role_access as $page_lbl => $value): ?>
        <?php if(in_array($user->getRole(), $value[0])): ?>
          <li><a class="nav-link" href="#" data-nav=<?= $value[1]?>><?= $page_lbl?></a></li>
        <?php endif; ?>
      <?php endforeach; ?>
      <li><a class="nav-link" href="#" data-nav="profile">👤 Profile</a></li>
    </ul>
    <div style="color: white" class="mt-auto small text-muted">Version 0.1 • First design</div>
  </aside>

  <!-- Main content -->
  <main class="flex-grow-1 p-4">
    <!-- DASHBOARD -->
    <section id="page-dashboard" class="page">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Dashboard</h3>
        <?php if($user->getRole() !== 'Student'): ?>
          <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#generateQRModal">Generate QR</button>
            <button class="btn btn-success" data-nav="borrow">Borrow Equipment</button>
          </div>
        <?php endif ?>
      </div>

      <!-- Stats cards -->
      <?php if($user->getRole() !== 'Student'): ?>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <div class="p-3 card-ghost">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="small text-muted">Active QR Sessions</div>
                  <div class="h4 mb-0"><?= '--' ?></div>
                </div>
                <div><span class="badge bg-success">Active</span></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 card-ghost">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="small text-muted">Borrowed Equip Today</div>
                  <div class="h4 mb-0"><?= '--' ?></div>
                </div>
                <div><span class="badge bg-warning text-dark">Due <?= '--' ?></span></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 card-ghost">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="small text-muted">Working / Issue</div>
                  <div class="h4 mb-0"><?= '-- / --' ?></div>
                </div>
                <div><span class="badge bg-info text-dark">Updated</span></div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 card-ghost">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="small text-muted">Pending Requests</div>
                  <div class="h4 mb-0"><?= '--' ?></div>
                </div>
                <div><span class="badge bg-secondary">Admin</span></div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Quick actions -->
      <?php if ($user->getRole() !== 'Student'): ?>
        <div class="mb-4">
          <div class="btn-group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateQRModal">Generate QR</button>
            <button class="btn btn-outline-success" data-nav="borrow">Borrow Equipment</button>
            <button class="btn btn-outline-secondary" data-nav="requests">New Request</button>
            <button class="btn btn-outline-dark" data-nav="reports">Reports</button>
          </div>
        </div>
      <?php endif; ?>

      <!-- Recent activity -->
      <div class="card-ghost p-3">
        <h5>Recent Activities</h5>
        <table class="table table-borderless table-hover mt-2">
          <thead class="small text-muted">
            <tr><th>Activity</th><th>User</th><th>Time</th></tr>
          </thead>
          <tbody>
            <tr><td>Activity 1</td><td>Nacion</td><td>2025-11-24 09:12</td></tr>
            <!--
              <tr><td>QR Generated (IT110)</td><td>Dr. Reyes</td><td>2025-11-24 09:12</td></tr>
              <tr><td>Mouse borrowed</td><td>Juan Dela Cruz</td><td>2025-11-24 10:02</td></tr>
              <tr><td>Monitor flagged (Issue)</td><td>Tech - Alex</td><td>2025-11-23 16:43</td></tr>
            -->
          </tbody>
        </table>
      </div>
    </section>

    <!-- ATTENDANCE -->
    <section id="page-attendance_page" class="page d-none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Attendance Management</h3>
        <div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateQRModal">+ Generate QR</button>
        </div>
      </div>

      <!-- Active QR cards -->
      <div class="row g-3 mb-3" id="qrCards">
        <!-- example card -->
        <div class="col-md-4">
          <div class="card-ghost p-3 qr-card">
            <div class="w-100 d-flex justify-content-between">
              <div><strong>OOP213 - Object-Oriented Porgramming</strong><div class="small text-muted">BSCS-2A • Ana Lizel</div></div>
              <div><span class="badge bg-success">Active</span></div>
            </div>
            <div class="mt-2">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=BSCS-2A" alt="qr">
            </div>
            <div class="small text-muted">Expires in <span class="fw-bold">00:12:34</span></div>
            <div class="mt-2 w-100 d-flex gap-2">
              <button class="btn btn-sm btn-outline-danger flex-grow-1">Deactivate</button>
              <button class="btn btn-sm btn-outline-secondary flex-grow-1">Extend</button>
            </div>
          </div>
        </div>

      </div>

      <!-- Attendance table -->
      <div class="card-ghost p-3">
        <h5>Attendance Logs</h5>
        <div class="mb-2 d-flex gap-2">
          <input type="date" class="form-control form-control-sm" style="max-width:200px">
          <select class="form-select form-select-sm" style="max-width:220px">
            <option>All Subjects</option>
            <option>IT110</option>
          </select>
          <select class="form-select form-select-sm" style="max-width:220px">
            <option>All Sections</option>
            <option>BSIT-2A</option>
          </select>
        </div>
        <table class="table table-hover mt-2">
          <thead class="table-light small">
            <tr><th>Name</th><th>Program/Section</th><th>Course</th><th>PC No.</th><th>Timestamp</th><th>Status</th></tr>
          </thead>
          <tbody>
            <!--
            <tr><td>Maria Santos</td><td>BSIT-2A</td><td>IT110</td><td>PC-07</td><td>2025-11-24 09:12</td><td><span class="badge bg-success">Present</span></td></tr>
            <tr><td>Juan Dela Cruz</td><td>BSIT-2A</td><td>IT110</td><td>PC-02</td><td>2025-11-24 09:15</td><td><span class="badge bg-warning text-dark">Late</span></td></tr>
            -->
          </tbody>
        </table>
      </div>

    </section>

    <!-- BORROW / RETURN -->
    <section id="page-borrow_page" class="page d-none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Borrow / Return</h3>
        <div>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#borrowModal">Borrow Item</button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-4">
          <div class="card-ghost p-3">
            <h5>Add Borrow Record</h5>
            <form id="borrowForm">
              <div class="mb-2">
                <label class="form-label small">Borrower Name</label>
                <input class="form-control form-control-sm" id="borrowerName" value="Juan Dela Cruz">
              </div>
              <div class="mb-2">
                <label class="form-label small">Equipment</label>
                <select class="form-select form-select-sm" id="borrowEquip">
                  <option>Mouse - USB</option>
                  <option>Keyboard - USB</option>
                  <option>Webcam</option>
                </select>
              </div>
              <div class="mb-2">
                <label class="form-label small">Remarks</label>
                <textarea class="form-control form-control-sm" rows="2"></textarea>
              </div>
              <button class="btn btn-primary btn-sm" type="button" onclick="addBorrowRecord()">Add Record</button>
            </form>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="card-ghost p-3">
            <h5>Borrowed Items</h5>
            <table class="table mt-2">
              <thead class="table-light small"><tr><th>Borrower</th><th>Item</th><th>Borrowed</th><th>Returned</th><th>Status</th><th>Action</th></tr></thead>
              <tbody id="borrowedList">
                <tr><td>Juan Dela Cruz</td><td>Mouse - USB</td><td>2025-11-24</td><td>—</td><td><span class="badge bg-danger">Not returned</span></td><td><button class="btn btn-sm btn-success" onclick="markReturned(this)">Mark Returned</button></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </section>

    <!-- EQUIPMENT PANEL -->
    <section id="page-lab_equipment_page" class="page d-none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Lab Equipment</h3>
        <div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">+ Add Equipment</button>
        </div>
      </div>

      <div class="card-ghost p-3">
        <table class="table">
          <thead class="table-light small"><tr><th>Name</th><th>Category</th><th>Status</th><th>Last Updated</th><th>Action</th></tr></thead>
          <tbody id="equipmentList">
            <tr><td>PC - 01</td><td>Desktop</td><td><span class="badge-status-working">Available & working</span></td><td>2025-11-20</td><td><button class="btn btn-sm btn-outline-secondary">Edit</button></td></tr>
            <tr><td>Monitor - 02</td><td>Monitor</td><td><span class="badge-status-issue">With Error</span></td><td>2025-11-23</td><td><button class="btn btn-sm btn-outline-secondary">Edit</button></td></tr>
            <tr><td>Printer - Lab</td><td>Peripheral</td><td><span class="badge-status-pulled">Pulled out</span></td><td>2025-11-01</td><td><button class="btn btn-sm btn-outline-secondary">Edit</button></td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- REQUESTS -->
    <section id="page-requests_page" class="page d-none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Requests</h3>
        <div>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#requestModal">New Request</button>
        </div>
      </div>

      <div class="card-ghost p-3">
        <h5>Submit a Request</h5>
        <form id="requestForm" class="row g-2">
          <div class="col-md-4">
            <label class="form-label small">Request Type</label>
            <select class="form-select form-select-sm">
              <option>Software Installation</option>
              <option>Purchase</option>
              <option>Peripheral</option>
              <option>Hardware</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Priority</label>
            <select class="form-select form-select-sm">
              <option>Low</option><option>Medium</option><option>High</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label small">Description</label>
            <textarea class="form-control form-control-sm"></textarea>
          </div>
          <div class="col-12">
            <button class="btn btn-primary btn-sm">Submit Request</button>
          </div>
        </form>

        <hr>

        <h5 class="mt-3">Requests</h5>
        <table class="table mt-2">
          <thead class="table-light small"><tr><th>Title</th><th>Type</th><th>Priority</th><th>Status</th><th>Requested By</th><th>Date</th></tr></thead>
          <tbody>
            <tr><td>Install VSCode</td><td>Software</td><td><span class="badge bg-secondary">Medium</span></td><td><span class="badge bg-warning text-dark">Pending</span></td><td>Dr. Reyes</td><td>2025-11-22</td></tr>
            <tr><td>Replace Mouse</td><td>Peripheral</td><td><span class="badge bg-danger">High</span></td><td><span class="badge bg-success">Completed</span></td><td>Alex (Tech)</td><td>2025-11-18</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- REPORTS -->
    <section id="page-reports_page" class="page d-none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Reports</h3>
        <div>
          <button class="btn btn-outline-secondary" onclick="alert('Export mock report')">Export PDF</button>
        </div>
      </div>

      <div class="card-ghost p-3">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="p-3">
              <h6>Attendance Report</h6>
              <p class="small text-muted">Generate attendance PDF for a date range.</p>
              <button class="btn btn-sm btn-primary">Generate</button>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3">
              <h6>Borrow/Return Report</h6>
              <p class="small text-muted">List of borrowed and returned equipment.</p>
              <button class="btn btn-sm btn-primary">Generate</button>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3">
              <h6>Equipment List</h6>
              <p class="small text-muted">Full inventory list.</p>
              <button class="btn btn-sm btn-primary">Generate</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- PROFILE -->
    <section id="page-profile" class="page d-none">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Profile & Settings</h3>
      </div>

      <div class="card-ghost p-3" style="max-width:900px">
        <div class="row">
          <div class="col-md-4 text-center">
            <img src="https://i.pinimg.com/236x/13/74/20/137420f5b9c39bc911e472f5d20f053e.jpg" class="rounded-circle mb-3">
            <div class="small text-muted"><?= 'Role: '. $user->getRole() ?></div>
            <div class="mt-3"><button class="btn btn-outline-secondary btn-sm">Change Photo</button></div>
          </div>
          <div class="col-md-8">
            <form>
              <div class="mb-2">
                <label class="form-label small">Full name</label>
                <input disabled class="form-control form-control-sm" value="<?= $user->getFirstName() .' '. $user->getLastName()?>">
              </div>
              <div class="mb-2">
                <label class="form-label small">Email</label>
                <input disabled class="form-control form-control-sm" value=<?= $user->getEmail()?>>
              </div>
              <div class="mb-2">
                <label class="form-label small">Department / Program</label>
                <input disabled class="form-control form-control-sm" value=<?= $user->getDepartment()?>>
              </div>
              <div class="mb-2">
                <label class="form-label small">Username</label>
                <input disabled class="form-control form-control-sm" value=<?= $user->getUsername()?>>
              </div>
              <div class="mb-2">
                <label class="form-label small">Change Password</label>
                <input class="form-control form-control-sm" placeholder="New password">
              </div>
              <div class="mt-3">
                <button class="btn btn-primary btn-sm">Save</button>
                <button class="btn btn-danger btn-sm ms-2">Logout</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </section>
  </main>
</div>

<!-- Modals -->
<!-- Generate QR Modal -->
<div class="modal fade" id="generateQRModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Generate Attendance QR</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="qrForm">
          <div class="mb-2">
            <label class="form-label small">Subject</label>
            <input class="form-control form-control-sm" value="IT110 - Programming 1">
          </div>
          <div class="mb-2">
            <label class="form-label small">Section</label>
            <input class="form-control form-control-sm" value="BSIT-2A">
          </div>
          <div class="mb-2">
            <label class="form-label small">PC / Lab</label>
            <input class="form-control form-control-sm" placeholder="PC-01">
          </div>
          <div class="mb-2">
            <label class="form-label small">Duration (minutes)</label>
            <input type="number" class="form-control form-control-sm" value="15">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" onclick="generateQR()">Generate</button>
      </div>
    </div>
  </div>
</div>

<!-- Borrow Modal -->
<div class="modal fade" id="borrowModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Borrow Item</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="borrowModalForm">
          <div class="mb-2"><label class="form-label small">Borrower</label><input class="form-control form-control-sm" value="Juan Dela Cruz"></div>
          <div class="mb-2"><label class="form-label small">Equipment</label><select class="form-select form-select-sm"><option>Mouse - USB</option><option>Keyboard</option></select></div>
          <div class="mb-2"><label class="form-label small">Remarks</label><textarea class="form-control form-control-sm"></textarea></div>
        </form>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button><button class="btn btn-primary btn-sm" onclick="addBorrowRecordFromModal()">Borrow</button></div>
    </div>
  </div>
</div>

<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Equipment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="equipForm" method="POST">
          <div class="mb-2">
            <label class="form-label small">Name</label>
            <input name="equipment_name" class="form-control form-control-sm">
          </div>
          <div class="mb-2">
            <label class="form-label small">Category</label>
            <input name="equipment_category" class="form-control form-control-sm">
          </div>
          <div class="mb-2">
            <label class="form-label small">Status</label>
            <select name="equipment_status" class="form-select form-select-sm">
              <option>Available & working</option>
              <option>With Error</option>
              <option>Pulled out</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            <input name="addEquipment" type="submit" class="btn btn-primary btn-sm" onclick="addEquipment()" value="Add">
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="requestModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">New Request</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="newReqForm">
          <div class="mb-2"><label class="form-label small">Type</label><select class="form-select form-select-sm"><option>Software Installation</option><option>Purchase</option></select></div>
          <div class="mb-2"><label class="form-label small">Priority</label><select class="form-select form-select-sm"><option>Low</option><option>High</option></select></div>
          <div class="mb-2"><label class="form-label small">Description</label><textarea class="form-control form-control-sm"></textarea></div>
        </form>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button><button class="btn btn-primary btn-sm" onclick="submitRequest()">Submit</button></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Simple nav switching
  document.querySelectorAll('[data-nav]').forEach(el=>{
    el.addEventListener('click', (e)=>{
      e.preventDefault();
      const target = el.getAttribute('data-nav');
      showPage(target);
      // highlight sidebar links
      document.querySelectorAll('.sidebar .nav-link').forEach(n => n.classList.remove('active'));
      const link = document.querySelector('.sidebar .nav-link[data-nav="'+target+'"]');
      if(link) link.classList.add('active');
      // close sidebar on mobile
      document.getElementById('mainSidebar')?.classList.remove('show');
    });
  });

  function showPage(name){
    document.querySelectorAll('.page').forEach(p=>p.classList.add('d-none'));
    const page = document.getElementById('page-'+name);
    if(page) page.classList.remove('d-none');
    else document.getElementById('page-dashboard').classList.remove('d-none');
  }

  document.getElementById('sidebarToggle').addEventListener('click', ()=> {
    document.getElementById('mainSidebar').classList.toggle('show');
  });

  function generateQR(){
    const modal = bootstrap.Modal.getInstance(document.getElementById('generateQRModal'));
    modal.hide();
    alert('QR Generated');
  }

  function addBorrowRecord(){
    const name = document.getElementById('borrowerName').value || 'Unknown';
    const equip = document.getElementById('borrowEquip').value || 'Item';
    const tbody = document.getElementById('borrowedList');
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${name}</td><td>${equip}</td><td>${new Date().toISOString().slice(0,10)}</td><td>—</td><td><span class="badge bg-danger">Not returned</span></td><td><button class="btn btn-sm btn-success" onclick="markReturned(this)">Mark Returned</button></td>`;
    tbody.prepend(tr);
    alert('Borrow record added.');
  }
  function addBorrowRecordFromModal(){
    const modal = bootstrap.Modal.getInstance(document.getElementById('borrowModal'));
    modal.hide();
    addBorrowRecord();
  }
  function markReturned(btn){
    const tr = btn.closest('tr');
    tr.cells[3].innerText = new Date().toISOString().slice(0,10);
    tr.cells[4].innerHTML = '<span class="badge bg-success">Returned</span>';
    btn.remove();
  }

  function addEquipment(){
    const modal = bootstrap.Modal.getInstance(document.getElementById('addEquipmentModal'));
    modal.hide();
    alert('Equipment added.');
  }

  function submitRequest(){
    const modal = bootstrap.Modal.getInstance(document.getElementById('requestModal'));
    modal.hide();
    alert('Request submitted');
  }

  // initial page
  showPage('dashboard');
</script>
</body>
</html>
