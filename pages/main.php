<?php
require_once '../utilities/utils.php';
require_once '../model/user.php';
require_once '../model/equipment.php';
require_once '../model/session.php';
require_once '../model/attendance.php';

session_start();
if (!isset($_SESSION['loggedInUser']) && !isset($_SESSION['User'])) {
  headto('login.php');
  exit();
}

$login = $_SESSION['User'];

/*$studentUser = new User('Frince', 'Nacion', 'Student', 'frincefriesss', 'CCS', 'frince@dyci.edu.ph');*/

$facultyUser = new User('Ivan', 'Bratz', 'faculty', 'ivanbratsz', '2025-01232', 'bratz@dyci.edu.ph', 'asdasd');
$user = $login;


# Every page by role access
#   '📄 Reports' => [['faculty', 'Admin'], 'reports_page']
$role_access = [
  '📸 Attendance' => [['student', 'faculty', 'Admin'], 'attendance_page'],
  '📦 Borrow / Return' => [['student','faculty', 'Admin'], 'borrow_page'],
  '📩 Requests' => [['faculty', 'Admin'], 'requests_page']
];


if (isset($_POST['addEquipment'])) {
  $equipmentName = $_POST['equipment_name'];
  $equipmentCategory = $_POST['equipment_category'];
  $equipmentStatus = $_POST['equipment_status'];
  addEquipment($equipmentName, $equipmentCategory, $equipmentStatus);
}

// Add equipment
function addEquipment($equipment_name, $equipment_category, $equipment_status)
{
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
    :root {
      --navy: #0B1F51;
      --primary: #1E3A8A;
      --success: #059669;
      --bg: #F5F6F9;
    }

    body {
      background: var(--bg);
    }

    .sidebar {
      height: 100vh;
      background: var(--navy);
      color: #fff;
      min-width: 220px;
    }

    .sidebar .nav-link {
      color: rgba(255, 255, 255, 0.9);
    }

    .sidebar .nav-link.active {
      background: rgba(255, 255, 255, 0.06);
      border-radius: 8px;
    }

    .card-ghost {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(13, 17, 32, 0.06);
    }

    .badge-status-working {
      background: #d1fae5;
      color: var(--success);
      padding: .45rem;
      border-radius: 10px;
      font-weight: 600;
    }

    .badge-status-issue {
      background: #fff7ed;
      color: #b45309;
      padding: .45rem;
      border-radius: 10px;
      font-weight: 600;
    }

    .badge-status-pulled {
      background: #f3f4f6;
      color: #6b7280;
      padding: .45rem;
      border-radius: 10px;
      font-weight: 600;
    }

    .qr-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .5rem;
      padding: 1rem;
    }

    pre {
      white-space: pre-wrap;
    }

    /* small responsive tweak */
    @media (max-width: 991px) {
      .sidebar {
        position: fixed;
        z-index: 1030;
        transform: translateX(-100%);
        transition: transform .2s;
      }

      .sidebar.show {
        transform: translateX(0);
      }
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
          <div class=""><?= $user->getRole() . ' — ' . $user->getUsername() ?></div>
        </div>
        <div class="dropdown w-50">
          <a class="btn btn-sm btn-outline-secondary dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <img src="https://i.pinimg.com/236x/13/74/20/137420f5b9c39bc911e472f5d20f053e.jpg" class="rounded-circle me-1 img-fluid" style="height: auto; width: 15%;">Profile
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#" data-nav="profile">Settings</a></li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href='logout.php'>Logout</a></li>
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
          <?php if (in_array($user->getRole(), $value[0])): ?>
            <li><a class="nav-link" href="#" data-nav=<?= $value[1] ?>><?= $page_lbl ?></a></li>
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
          <?php if ($user->getRole() !== 'student'): ?>
          <div class="mb-4">
            <div class="btn-group">
              <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateQRModal">Generate QR</button>
              <button class="btn btn-outline-secondary" data-nav="requests">New Request</button>
            </div>
          </div>
        <?php endif; ?>
        </div>

        <!-- Stats cards -->
        <?php if ($user->getRole() !== 'student'): ?>
          <div class="row g-3 mb-3" id="facultyStats">
            <div class="col-md-3">
              <div class="p-3 card-ghost">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="small text-muted">Sessions Today</div>
                    <div class="h4 mb-0" id="statSessionsToday">--</div>
                  </div>
                  <div><span class="badge bg-info text-dark">Today</span></div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="p-3 card-ghost">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="small text-muted">Active Sessions</div>
                    <div class="h4 mb-0" id="statActiveSessions">--</div>
                  </div>
                  <div><span class="badge bg-success">Live</span></div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="p-3 card-ghost">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="small text-muted">Total Attendance</div>
                    <div class="h4 mb-0" id="statTotalAttendance">--</div>
                  </div>
                  <div><span class="badge bg-primary">Count</span></div>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="row g-3 mb-3" id="studentStats">
            <div class="col-md-3">
              <div class="p-3 card-ghost">
                <div class="d-flex justify-content-between">
                  <div>
                    <div class="small text-muted">Available Sessions</div>
                    <div class="h4 mb-0" id="statAvailableSessions">--</div>
                  </div>
                  <div><span class="badge bg-success">Open</span></div>
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
          </div>
        <?php endif; ?>

        <!-- Quick actions -->

      </section>

      <!-- ATTENDANCE -->
      <section id="page-attendance_page" class="page d-none">
        <?php if ($user->getRole() === 'faculty' || $user->getRole() === 'Admin'): ?>
          <!-- FACULTY VIEW -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Attendance Management</h3>
            <div>
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateQRModal">+ Generate QR</button>
            </div>
          </div>

          <!-- Active QR cards -->
          <div class="row g-3 mb-3" id="qrCards">
            <div class="col-12 text-center text-muted py-4">
              <div class="spinner-border spinner-border-sm me-2" role="status"></div>
              Loading sessions...
            </div>
          </div>

          <!-- Attendance table -->
          <div class="card-ghost p-3" style="margin-bottom: 3%;">
            <h5>Attendance Logs</h5>
            <div id="attendanceTableContainer">
              <table class="table table-hover mt-2">
                <thead class="table-light small">
                  <tr>
                    <th>Name</th>
                    <th>Student Number</th>
                    <th>PC No.</th>
                    <th>Timestamp</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody id="attendanceTableBody">
                  <tr><td colspan="7" class="text-center text-muted">Select a session to view attendance</td></tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-ghost p-3">
            <h5>Archive</h5>
            <div id="sessionTableContainer">
              <table class="table table-hover mt-2">
                <thead class="table-light small">
                  <tr>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>ComLab Room</th>
                    <th>Duration</th>
                    <th>View</th>
                  </tr>
                </thead>
                <tbody id="sessionTableBody">
                  <tr><td colspan="7" class="text-center text-muted">No archived session yet.</td></tr>
                </tbody>
              </table>
            </div>
        <?php else: ?>
          <!-- STUDENT VIEW -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Scan QR Code for Attendance</h3>
          </div>

          <!-- Available Sessions -->
          <div class="card-ghost p-3 mb-3">
            <h5>Available Sessions</h5>
            <div id="studentSessionsList" class="row g-3 mt-2">
              <div class="col-12 text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                Loading available sessions...
              </div>
            </div>
          </div>

          <!-- Instructions -->
          <div class="card-ghost p-3">
            <h6>How to Record Attendance:</h6>
            <ol class="mb-0">
              <li>Find your session in the list above</li>
              <li>Click on the session card to open the QR code</li>
              <li>Scan the QR code with your device</li>
              <li>Your attendance will be recorded automatically</li>
            </ol>
          </div>
        <?php endif; ?>
      </section>

      <!-- BORROW / RETURN -->
      <section id="page-borrow_page" class="page d-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3>Borrow / Return</h3>
        <!--Section where users can see their borrowed / to be returned items -->
        </div>

        <div class="card-ghost p-3">
          <table class="table mt-2">
            <thead class="table-light small">
              <tr>
                <th>Equipment</th>
                <th>Borrower ID</th>
                <th>Status</th>
                <th>Borrowed Date</th>
                <th>Returned Date</th>
              </tr>
            </thead>
            <tbody id="borrowTableBody">
            </tbody>
          </table>
        </div>
      </section>

      <!-- REQUESTS -->
      <section id="page-requests_page" class="page d-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3>Requests</h3>
        </div>

        <div class="card-ghost p-3">
          <h5>Submit a Request</h5>
          <form id="requestForm" class="row g-2">
            <div id="requestFormError" class="alert alert-danger d-none mt-2"></div>
            <div class="col-md-12">
              <label class="form-label small">Request Title</label>
              <input id="requestTitle" class="form-control form-control-sm" required></input>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Request Type</label>
              <select id="requestType" class="form-select form-select-sm" required>
                <option>Software Installation</option>
                <option>Purchase</option>
                <option>Peripheral</option>
                <option>Hardware</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small">Priority</label>
              <select id="requestPriority" class="form-select form-select-sm" required>
                <option>Low</option>
                <option>Medium</option>
                <option>High</option>
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label small">Description</label>
              <textarea id="requestDescription" maxlength="500" class="form-control form-control-sm" required></textarea>
            </div>
            <div class="col-12">
              <button id="createRequestBtn" class="btn btn-primary btn-sm" onclick="createRequest()">Submit Request</button>
            </div>
          </form>

          <hr>

          <h5 class="mt-3">Requests</h5>
          <table class="table mt-2">
            <thead class="table-light small">
              <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="requestTableBody">
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
              <div class="small text-muted"><?= 'Role: ' . $user->getRole() ?></div>
              <div class="mt-3"><button class="btn btn-outline-secondary btn-sm">Change Photo</button></div>
            </div>
            <div class="col-md-8">
              <form>
                <div class="mb-2">
                  <label class="form-label small">Full name</label>
                  <input disabled class="form-control form-control-sm" value="<?= $user->getFirstName() . ' ' . $user->getLastName() ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label small">Email</label>
                  <input disabled class="form-control form-control-sm" value=<?= $user->getEmail() ?>>
                </div>
                <div class="mb-2">
                  <label class="form-label small">Department / Program</label>
                  <input disabled class="form-control form-control-sm" value=<?= $user->getDepartment() ?>>
                </div>
                <div class="mb-2">
                  <label class="form-label small">Username</label>
                  <input disabled class="form-control form-control-sm" value=<?= $user->getUsername() ?>>
                </div>
                <div class="mb-2">
                  <label class="form-label small">Change Password</label>
                  <input class="form-control form-control-sm" placeholder="New password">
                </div>
                <div class="mt-3">
                  <button class="btn btn-primary btn-sm">Save</button>
                  <a class="btn btn-danger btn-sm ms-2" href='logout.php'>Logout</a>
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
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Generate Attendance QR</h5><button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="qrForm">
            <div class="mb-2">
              <label class="form-label small">Subject <span class="text-danger">*</span></label>
              <input id="qrSubject" class="form-control form-control-sm" placeholder="e.g., IT110 - Programming 1" required>
            </div>
            <div class="mb-2">
              <label class="form-label small">Section <span class="text-danger">*</span></label>
              <input id="qrSection" class="form-control form-control-sm" placeholder="e.g., BSIT-2A" required>
            </div>
            <div class="mb-2">
              <label class="form-label small">Lab Name</label>
              <select id="qrLabName" class="form-select form-select-sm">
                <option value="">Select Lab (optional)</option>
                <option value="Nexus Lab">Nexus Lab</option>
                <option value="Sandbox Lab">Sandbox Lab</option>
                <option value="Raise Lab">Raise Lab</option>
                <option value="EdTech Lab">EdTech Lab</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label small">Duration (minutes) <span class="text-danger">*</span></label>
              <input type="number" id="qrDuration" class="form-control form-control-sm" value="15" min="1" max="120" required>
              <small class="text-muted">Between 1 and 120 minutes</small>
            </div>
          </form>
          <div id="qrFormError" class="alert alert-danger d-none mt-2"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary btn-sm" onclick="createSession()" id="createSessionBtn">Generate</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Student QR Modal -->
  <div class="modal fade" id="studentQRModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Scan QR Code</h5><button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <div id="studentQRContent">
            <p class="small text-muted mb-2" id="studentQRSubject"></p>
            <div id="studentQRCode"></div>
            <p class="small text-muted mt-2">Scan this QR code with your device</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Manual Attendance Modal (Fill Up) -->
  <div class="modal fade" id="manualAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Manual Attendance</h5><button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="manualAttendanceForm">
            <input type="hidden" id="manualSessionCode">
            <div class="mb-2">
              <label class="form-label small">Subject / Session</label>
              <input id="manualSessionLabel" class="form-control form-control-sm" disabled>
            </div>
            <div class="mb-2">
              <label class="form-label small">PC Number / Computer Unit <span class="text-danger">*</span></label>
              <input id="manualPcNumber" class="form-control form-control-sm" placeholder="e.g., PC-12" required>
            </div>
          </form>
          <div id="manualAttendanceError" class="alert alert-danger d-none mt-2"></div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary btn-sm" onclick="submitManualAttendance()" id="manualAttendanceBtn">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple nav switching
    document.querySelectorAll('[data-nav]').forEach(el => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        const target = el.getAttribute('data-nav');
        showPage(target);
        // highlight sidebar links
        document.querySelectorAll('.sidebar .nav-link').forEach(n => n.classList.remove('active'));
        const link = document.querySelector('.sidebar .nav-link[data-nav="' + target + '"]');
        if (link) link.classList.add('active');
        // close sidebar on mobile
        document.getElementById('mainSidebar')?.classList.remove('show');
      });
    });

    function showPage(name) {
      document.querySelectorAll('.page').forEach(p => p.classList.add('d-none'));
      const page = document.getElementById('page-' + name);
      if (page) page.classList.remove('d-none');
      else document.getElementById('page-dashboard').classList.remove('d-none');
    }

    document.getElementById('sidebarToggle').addEventListener('click', () => {
      document.getElementById('mainSidebar').classList.toggle('show');
    });

    // ========== ATTENDANCE SYSTEM ==========
    const userRole = '<?= $user->getRole() ?>';
    let attendanceUpdateInterval = null;
    let currentViewingSessionId = null;

    // Load sessions when attendance page is shown
    document.querySelectorAll('[data-nav]').forEach(el => {
      el.addEventListener('click', (e) => {
        const target = el.getAttribute('data-nav');
        if (target === 'attendance_page') {
          setTimeout(() => {
            loadSessions();
            loadArchivedSessions();
            if (userRole === 'faculty' || userRole === 'Admin') {
              // Auto-refresh attendance every 5 seconds
              if (attendanceUpdateInterval) clearInterval(attendanceUpdateInterval);
              attendanceUpdateInterval = setInterval(() => {
                if (currentViewingSessionId) {
                  loadAttendanceForSession(currentViewingSessionId);
                }
              }, 5000);
            }
          }, 100);
        }else if(target === 'requests_page'){
          setTimeout(() => {
            loadRequests();
          }, 100);
        } else if(target === 'borrow_page'){
          setTimeout(() => {
            loadBorrowItems();
          }, 100);
        } else {
          if (attendanceUpdateInterval) {
            clearInterval(attendanceUpdateInterval);
            attendanceUpdateInterval = null;
          }
        }
      });
    });

    // Create new session
    async function createSession() {
      const subject = document.getElementById('qrSubject').value.trim();
      const section = document.getElementById('qrSection').value.trim();
      const labName = document.getElementById('qrLabName').value.trim();
      const duration = parseInt(document.getElementById('qrDuration').value);

      if (!subject || !section || !duration) {
        showError('qrFormError', 'Please fill in all required fields');
        return;
      }

      const btn = document.getElementById('createSessionBtn');
      btn.disabled = true;
      btn.textContent = 'Creating...';

      try {
        const response = await fetch('../api/create_session.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            subject: subject,
            section: section,
            lab_name: labName || null,
            duration_minutes: duration
          })
        });

        // Check if response is OK
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Try to parse JSON
        let data;
        try {
          data = await response.json();
        } catch (parseError) {
          const text = await response.text();
          throw new Error('Invalid JSON response: ' + text.substring(0, 200));
        }

        if (data.success) {
          const modal = bootstrap.Modal.getInstance(document.getElementById('generateQRModal'));
          modal.hide();
          
          // Reset form
          document.getElementById('qrForm').reset();
          document.getElementById('qrFormError').classList.add('d-none');
          
          // Reload sessions
          loadSessions();
          
          // Show success message
          alert('Session created successfully!');
        } else {
          showError('qrFormError', data.message || 'Failed to create session');
        }
      } catch (error) {
        console.error('Error creating session:', error.message );
        showError('qrFormError', 'Error: ' + error.message + '. Please check your connection and try again.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Generate';
      }
    }

    // Load sessions
    async function loadSessions() {
      const container = userRole === 'student' 
        ? document.getElementById('studentSessionsList')
        : document.getElementById('qrCards');

      if (!container) return;

      container.innerHTML = '<div class="col-12 text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading sessions...</div>';

      try {
        const response = await fetch('../api/get_sessions.php');
        const data = await response.json();

        if (data.success) {
          storeSessions(data.sessions);
          if (userRole === 'student') {
            renderStudentSessions(data.sessions, container);
            updateStudentStats(data.sessions);
          } else {
            renderFacultySessions(data.sessions, container);
            updateFacultyStats(data.sessions);
          }
        } else {
          container.innerHTML = '<div class="col-12 text-center text-muted py-4">No active sessions</div>';
        }
      } catch (error) {
        container.innerHTML = '<div class="col-12 text-center text-danger py-4">Error loading sessions</div>';
      }
    }

    async function loadArchivedSessions() {
      const tbody = document.getElementById('sessionTableBody');
      if (!tbody) return;

      try {
        const response = await fetch(`../api/get_archivedSessions.php`);
        const data = await response.json();
        console.log(data);

        if (data.success) {
          if (data.sessions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No archived sessions</td></tr>';
          } else {
            tbody.innerHTML = data.sessions.map(att => `
              <tr>
                <td>${escapeHtml(att.section)}</td>
                <td>${escapeHtml(att.subject || '—')}</td>
                <td>${escapeHtml(att.lab_name || '—')}</td>
                <td>${escapeHtml(att.duration_minutes)}</td>
                <td><button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewAttendance(${att.session_id})">View Attendance</button></td>
              </tr>`).join('');
          }
        }
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading items</td></tr>';
      }
    }

    // Render faculty sessions with QR codes
    function renderFacultySessions(sessions, container) {
      if (sessions.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-4">No active sessions. Create one to get started!</div>';
        return;
      }

      container.innerHTML = '';
      sessions.forEach(session => {
        const card = document.createElement('div');
        card.className = 'col-md-4';
        card.innerHTML = `
          <div class="card-ghost p-3 qr-card">
            <div class="w-100 d-flex justify-content-between">
              <div>
                <strong>${escapeHtml(session.subject)}</strong>
                <div class="small text-muted">${escapeHtml(session.section)}${session.lab_name ? ' • ' + escapeHtml(session.lab_name) : ''}</div>
              </div>
              <div><span class="badge bg-success">Active</span></div>
            </div>
            <div class="mt-2">
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(session.qr_url)}" alt="QR Code">
            </div>
            <div class="small text-muted">Expires in <span class="fw-bold" id="timer-${session.session_id}">${new Date(session.expires_at).toLocaleString()}</span></div>
            <div class="small text-muted">Attendees: <strong>${session.attendee_count || 0}</strong></div>
            <div class="mt-2 w-100 d-flex gap-2">
              <button class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewAttendance(${session.session_id})">View Attendance</button>
              <button class="btn btn-sm btn-outline-danger flex-grow-1" onclick="deactivateSession(${session.session_id})">Deactivate</button>
            </div>
          </div>
        `;
        container.appendChild(card);

      });
    }

    // Render student sessions
    function renderStudentSessions(sessions, container) {
      if (sessions.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-4">No active sessions available</div>';
        return;
      }

      container.innerHTML = '';
      sessions.forEach(session => {
        const card = document.createElement('div');
        card.className = 'col-md-6 mb-3';
        card.innerHTML = `
          <div class="card-ghost p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <h6 class="mb-1">${escapeHtml(session.subject)}</h6>
                <small class="text-muted">${escapeHtml(session.section)}</small>
                ${session.lab_name ? `<br><small class="text-muted">Lab: ${escapeHtml(session.lab_name)}</small>` : ''}
                ${session.pc_number ? `<br><small class="text-muted">PC: ${escapeHtml(session.pc_number)}</small>` : ''}
              </div>
              <span class="badge bg-success">Active</span>
            </div>
            <div class="small text-muted mb-2">
              By: ${escapeHtml(session.first_name + ' ' + session.last_name)}<br>
              Expires in: <span id="timer-${session.session_id}">${new Date(session.expires_at).toLocaleString()}</span>
            </div>
            <div class="d-flex flex-column gap-2">
              <button class="btn btn-primary btn-sm w-100" onclick="showStudentQR('${session.session_code}', '${escapeHtml(session.subject)}')">Scan QR Code</button>
              <button class="btn btn-outline-secondary btn-sm w-100" ${session.time_remaining_seconds <= 0 ? 'disabled' : ''} onclick="openManualAttendance('${session.session_code}', '${escapeHtml(session.subject)}')">Fill Up</button>
            </div>
          </div>
        `;
        container.appendChild(card);
      });
    }

    // Dashboard stats
    function updateFacultyStats(sessions) {
      const todayStr = new Date().toISOString().slice(0, 10);
      const sessionsToday = sessions.filter(s => (s.created_at || '').slice(0,10) === todayStr).length;
      const activeSessions = sessions.length;
      const totalAttendance = sessions.reduce((sum, s) => sum + (parseInt(s.attendee_count || 0)), 0);
      const endingSoon = sessions.filter(s => parseInt(s.time_remaining_seconds || 0) > 0 && parseInt(s.time_remaining_seconds || 0) <= 1800).length;
      setStat('statSessionsToday', sessionsToday);
      setStat('statActiveSessions', activeSessions);
      setStat('statTotalAttendance', totalAttendance);
      setStat('statEndingSoon', endingSoon);
    }

    function updateStudentStats(sessions) {
      const todayStr = new Date().toISOString().slice(0, 10);
      const availableSessions = sessions.length;
      const endingSoon = sessions.filter(s => parseInt(s.time_remaining_seconds || 0) > 0 && parseInt(s.time_remaining_seconds || 0) <= 1800).length;
      const sessionsToday = sessions.filter(s => (s.created_at || '').slice(0,10) === todayStr).length;
      // Without per-student attendance API, we show available info only
      setStat('statAvailableSessions', availableSessions);
      setStat('statStudentEndingSoon', endingSoon);
      setStat('statStudentSessionsToday', sessionsToday);
      setStat('statStudentAttendance', '—');
    }

    function setStat(id, value) {
      const el = document.getElementById(id);
      if (el) el.textContent = value;
    }

    // Show QR code for student
    function showStudentQR(sessionCode, subject) {
      const baseUrl = window.location.origin + window.location.pathname.replace('main.php', '');
      const qrUrl = baseUrl + 'scan_qr.php?code=' + encodeURIComponent(sessionCode);
      
      document.getElementById('studentQRSubject').textContent = subject;
      document.getElementById('studentQRCode').innerHTML = 
        `<img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrUrl)}" alt="QR Code" class="img-fluid">`;
      
      const modal = new bootstrap.Modal(document.getElementById('studentQRModal'));
      modal.show();
    }

    // Manual attendance (Fill Up)
    function openManualAttendance(sessionCode, subject) {
      const session = findSessionByCode(sessionCode);
      if (session && session.time_remaining_seconds <= 0) {
        alert('This session has expired. Attendance is closed.');
        return;
      }
      document.getElementById('manualSessionCode').value = sessionCode;
      document.getElementById('manualSessionLabel').value = subject;
      document.getElementById('manualPcNumber').value = '';
      document.getElementById('manualAttendanceError').classList.add('d-none');
      const modal = new bootstrap.Modal(document.getElementById('manualAttendanceModal'));
      modal.show();
    }

    async function submitManualAttendance() {
      const sessionCode = document.getElementById('manualSessionCode').value;
      const pcNumber = document.getElementById('manualPcNumber').value.trim();
      const errorEl = document.getElementById('manualAttendanceError');
      if (!pcNumber) {
        errorEl.textContent = 'PC Number is required.';
        errorEl.classList.remove('d-none');
        return;
      }
      try {
        const response = await fetch('../api/record_attendance.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ session_code: sessionCode, pc_number: pcNumber })
        });
        const data = await response.json();
        if (data.success) {
          errorEl.classList.add('d-none');
          bootstrap.Modal.getInstance(document.getElementById('manualAttendanceModal')).hide();
          alert('Attendance recorded.');
          loadSessions();
        } else {
          errorEl.textContent = data.message || 'Failed to record attendance.';
          errorEl.classList.remove('d-none');
        }
      } catch (err) {
        errorEl.textContent = 'Network error. Please try again.';
        errorEl.classList.remove('d-none');
      }
    }

    // Helper: find session by code from last fetched sessions
    let lastFetchedSessions = [];
    function storeSessions(sessions) {
      lastFetchedSessions = sessions || [];
    }
    function findSessionByCode(code) {
      return lastFetchedSessions.find(s => s.session_code === code);
    }

    // View attendance for a session
    async function viewAttendance(sessionId) {
      currentViewingSessionId = sessionId;
      await loadAttendanceForSession(sessionId);
    }

    // Load attendance for a specific session
    async function loadAttendanceForSession(sessionId) {
      const tbody = document.getElementById('attendanceTableBody');
      if (!tbody) return;

      try {
        const response = await fetch(`../api/get_attendance.php?session_id=${sessionId}`);
        const data = await response.json();

        if (data.success) {
          if (data.attendances.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No attendance recorded yet</td></tr>';
          } else {
            tbody.innerHTML = data.attendances.map(att => `
              <tr>
                <td>${escapeHtml(att.student_name)}</td>
                <td>${escapeHtml(att.student_number || '—')}</td>
                <td>${escapeHtml(att.pc_number || '—')}</td>
                <td>${new Date(att.timestamp).toLocaleString()}</td>
                <td><span class="badge bg-success">Present</span></td>
              </tr>
            `).join('');
          }
        }
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading attendance</td></tr>';
      }
    }

    // Load attendance logs with filters
    async function loadAttendanceLogs() {
      const date = document.getElementById('attendanceDateFilter')?.value || '';
      const subject = document.getElementById('attendanceSubjectFilter')?.value || '';
      const section = document.getElementById('attendanceSectionFilter')?.value || '';

      // This would require a new API endpoint for filtered attendance
      // For now, just show a message
      alert('Filter functionality coming soon. Please select a session to view its attendance.');
    }

    // Deactivate session
    async function deactivateSession(sessionId) {
      if (!confirm('Are you sure you want to deactivate this session?')) return;

      try {
        const response = await fetch('../api/deactivate_session.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ session_id: sessionId })
        });

        const data = await response.json();

        if (data.success) {
          loadSessions();
          if (currentViewingSessionId === sessionId) {
            currentViewingSessionId = null;
            document.getElementById('attendanceTableBody').innerHTML = 
              '<tr><td colspan="7" class="text-center text-muted">Select a session to view attendance</td></tr>';
          }
        } else {
          alert('Failed to deactivate session: ' + data.message);
        }
      } catch (error) {
        alert('Error deactivating session');
      }
    }

    async function loadBorrowItems(){
      const tbody = document.getElementById('borrowTableBody');
      if (!tbody) return;

      try {
        const response = await fetch(`../api/get_borrowed.php`);
        const data = await response.json();
        console.log(data);

        if (data.success) {
          if (data.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No equipments borrowed and returned</td></tr>';
          } else {
            tbody.innerHTML = data.items.map(att => `
              <tr>
                <td>${escapeHtml(att.equipment_name)}</td>
                <td>${escapeHtml(att.student_number || '—')}</td>
                <td>${escapeHtml(att.status || '—')}</td>
                <td>${new Date(att.borrow_date).toLocaleString() || '—'}</td>
                 <td>${(att.return_date !== null)?new Date(att.return_date).toLocaleString():'N/A'}</td>
              </tr>`).join('');
          }
        }
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading items</td></tr>';
      }
    }

    async function loadRequests() {
      const tbody = document.getElementById('requestTableBody');
      if (!tbody) return;

      try {
        const response = await fetch(`../api/get_requests.php`);
        const data = await response.json();
        console.log(data);

        if (data.success) {
          if (data.requests.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No request submitted yet</td></tr>';
          } else {
            tbody.innerHTML = data.requests.map(att => `
              <tr>
                <td>${escapeHtml(att.request_title)}</td>
                <td>${escapeHtml(att.request_type || '—')}</td>
                <td>${escapeHtml(att.request_priority	 || '—')}</td>
                <td>${escapeHtml(att.status) || '—'}</td>
                <td>${new Date(att.submitted_at).toLocaleString()}</td>
              </tr>`).join('');
          }
        }
      } catch (error) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading requests</td></tr>';
      }
    }

    // request-related functions
    async function createRequest(){
      const title = document.getElementById('requestTitle').value.trim();
      const type = document.getElementById('requestType').value;
      const priority = document.getElementById('requestPriority').value;
      const description = document.getElementById('requestDescription').value.trim();

      if(!title || !description){
        showError('requestFormError', 'Please fill in all required fields');
        return;
      }

      const btn = document.getElementById('createRequestBtn');
      btn.disabled = true;
      btn.textContent = 'Creating...';

      try{
        const response = await fetch('../api/create_request.php',{
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            title: title,
            type: type,
            priority: priority,
            description: description
          })
        })
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Try to parse JSON
        let data;
        try {
          data = await response.json();
        } catch (parseError) {
          const text = await response.text();
          throw new Error('Invalid JSON response: ' + text.substring(0, 200));
        }

        if (data.success) {
          // Reset form
          document.getElementById('requestForm').reset();
          document.getElementById('requestFormError').classList.add('d-none');
          
          // load requests
          loadRequests()
          // Show success message
          alert('Request created successfully!');
        }
      } catch (error) {
        console.error('Error creating session:', error.message );
        showError('requestFormError', 'Error: ' + error.message + '. Please check your connection and try again.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Generate';
      }
    }

    // Utility functions
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function showError(elementId, message) {
      const errorEl = document.getElementById(elementId);
      if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.remove('d-none');
      }
    }

    // initial page
    showPage('dashboard');
  </script>
</body>

</html>