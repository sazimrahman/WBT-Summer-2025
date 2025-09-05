<?php
// error_reporting(E_ALL); ini_set('display_errors', 1);

function e($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$errors = [];
$old = [];

// Map month label -> number for real date validation
$monthMap = [
  'Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,
  'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Trim inputs
  $old['roll']       = trim($_POST['roll'] ?? '');
  $old['first_name'] = trim($_POST['first_name'] ?? '');
  $old['last_name']  = trim($_POST['last_name'] ?? '');
  $old['father']     = trim($_POST['father'] ?? '');
  $old['dob_day']    = $_POST['dob_day']   ?? 'Day';
  $old['dob_month']  = $_POST['dob_month'] ?? 'Month';
  $old['dob_year']   = $_POST['dob_year']  ?? 'Year';
  $old['cc']         = $_POST['cc']        ?? '+91';
  $old['phone']      = trim($_POST['phone'] ?? '');
  $old['email']      = trim($_POST['email'] ?? '');
  $old['pwd']        = $_POST['pwd']        ?? '';
  $old['gender']     = $_POST['gender']     ?? '';
  $old['dept']       = $_POST['dept']       ?? []; // array
  $old['course']     = $_POST['course']     ?? '------------------------ Select Current Course\'s ------------------------';
  $old['city']       = trim($_POST['city'] ?? '');
  $old['address']    = trim($_POST['address'] ?? '');

  // --- Roll no. ---
  if ($old['roll'] === '') {
    $errors['roll'] = 'Roll number is required.';
  } elseif (!preg_match('/^[A-Za-z0-9\-\/]{2,20}$/', $old['roll'])) {
    $errors['roll'] = 'Use 2–20 chars (letters, numbers, -, /).';
  }

  // --- Student name ---
  if ($old['first_name'] === '') {
    $errors['first_name'] = 'First name is required.';
  } elseif (!preg_match('/^[A-Za-z][A-Za-z\s\.\'-]{1,49}$/', $old['first_name'])) {
    $errors['first_name'] = 'Enter a valid first name.';
  }
  if ($old['last_name'] === '') {
    $errors['last_name'] = 'Last name is required.';
  } elseif (!preg_match('/^[A-Za-z][A-Za-z\s\.\'-]{1,49}$/', $old['last_name'])) {
    $errors['last_name'] = 'Enter a valid last name.';
  }

  // --- Father's name ---
  if ($old['father'] === '') {
    $errors['father'] = "Father's name is required.";
  } elseif (!preg_match('/^[A-Za-z][A-Za-z\s\.\'-]{1,60}$/', $old['father'])) {
    $errors['father'] = "Enter a valid father's name.";
  }

  // --- DOB ---
  if ($old['dob_day'] === 'Day' || $old['dob_month'] === 'Month' || $old['dob_year'] === 'Year') {
    $errors['dob'] = 'Please select a complete date of birth.';
  } else {
    $d = (int)$old['dob_day'];
    $m = $monthMap[$old['dob_month']] ?? 0;
    $y = (int)$old['dob_year'];
    if (!$m || !checkdate($m, $d, $y)) {
      $errors['dob'] = 'Date of birth is invalid.';
    }
  }

  // --- Phone ---
  if ($old['phone'] === '') {
    $errors['phone'] = 'Phone number is required.';
  } elseif (!preg_match('/^[0-9]{6,15}$/', $old['phone'])) {
    $errors['phone'] = 'Digits only (6–15).';
  }

  // --- Email ---
  if ($old['email'] === '') {
    $errors['email'] = 'Email is required.';
  } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email format is invalid.';
  }

  // --- Password ---
  if ($old['pwd'] === '') {
    $errors['pwd'] = 'Password is required.';
  } elseif (strlen($old['pwd']) < 6) {
    $errors['pwd'] = 'Use at least 6 characters.';
  }

  // --- Gender ---
  if (!in_array($old['gender'], ['male','female'], true)) {
    $errors['gender'] = 'Please choose your gender.';
  }

  // --- Department (at least one) ---
  if (!is_array($old['dept']) || count($old['dept']) === 0) {
    $errors['dept'] = 'Select at least one department.';
  } else {
    $allowedDept = ['CSE','IT','ECE','Civil','Mech'];
    foreach ($old['dept'] as $dpt) {
      if (!in_array($dpt, $allowedDept, true)) {
        $errors['dept'] = 'Invalid department selected.';
        break;
      }
    }
  }

  // --- Course ---
  if ($old['course'] === "------------------------ Select Current Course's ------------------------") {
    $errors['course'] = 'Please select a course.';
  } else {
    $allowedCourses = ['B.Tech','M.Tech','B.Sc','M.Sc','Diploma'];
    if (!in_array($old['course'], $allowedCourses, true)) {
      $errors['course'] = 'Invalid course selected.';
    }
  }

  // --- City ---
  if ($old['city'] === '') {
    $errors['city'] = 'City is required.';
  } elseif (!preg_match('/^[A-Za-z][A-Za-z\s\.\'-]{1,60}$/', $old['city'])) {
    $errors['city'] = 'Enter a valid city.';
  }

  // --- Address ---
  if ($old['address'] === '') {
    $errors['address'] = 'Address is required.';
  } elseif (strlen($old['address']) < 5) {
    $errors['address'] = 'Address looks too short.';
  }

  // --- Photo upload ---
  $uploadedPath = null;
  if (!isset($_FILES['photo']) || ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $errors['photo'] = 'Please upload a student photo.';
  } else {
    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
      $errors['photo'] = 'File upload failed. Please try again.';
    } else {
      if ($file['size'] > 2 * 1024 * 1024) {
        $errors['photo'] = 'Max file size is 2 MB.';
      } else {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowed = [
          'image/jpeg' => 'jpg',
          'image/png'  => 'png',
          'image/webp' => 'webp'
        ];
        if (!isset($allowed[$mime])) {
          $errors['photo'] = 'Allowed types: JPG, PNG, WEBP.';
        } else {
          $targetDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
          if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
          }
          $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
          $ext = $allowed[$mime];
          $dest = $targetDir . DIRECTORY_SEPARATOR . $safeBase . '_' . time() . '.' . $ext;
          if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors['photo'] = 'Could not save uploaded file.';
          } else {
            $uploadedPath = $dest;
          }
        }
      }
    }
  }

  $isValid = empty($errors);
} else {
  // Direct GET visit: behave like empty submission (no form-level error)
  $isValid = false;
  $old = [
    'roll'=>'','first_name'=>'','last_name'=>'','father'=>'',
    'dob_day'=>'Day','dob_month'=>'Month','dob_year'=>'Year',
    'cc'=>'+91','phone'=>'','email'=>'','pwd'=>'',
    'gender'=>'','dept'=>[],'course'=>"------------------------ Select Current Course's ------------------------",
    'city'=>'','address'=>''
  ];
}

function sel($a, $b) { return $a === $b ? ' selected' : ''; }
function chk($arr, $val) { return in_array($val, (array)$arr, true) ? ' checked' : ''; }
function rdo($val, $cur) { return $val === $cur ? ' checked' : ''; }

function uploadedPublicUrl($absPath) {
  if (!$absPath) return null;
  return '../uploads/' . basename($absPath);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Student Registration Validation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../CSS/From_validation.css">
  <style>
    /* Inline error styling */
    .error{
      color:#c1121f;
      font-size:14px;
      margin-top:6px;
      display:block;
    }
    /* Key fix: when an error <span> is placed directly in the grid (outside .field),
       ensure it sits beneath the input column (column 2), not under the label column. */
    form > .error{ grid-column: 2 / 3; }

    .ok{ color:#0a7a25; }
    .card{ background:#fff; border:1px solid #3b3b3b; border-radius:6px; padding:16px; }
    .preview{ max-width:160px; height:auto; border:1px solid #ccc; border-radius:4px; padding:2px; background:#fafafa; }
    .actions{ display:flex; gap:12px; align-items:center; }
    .actions a, .actions button{ text-decoration:none; }

    /* ===== Page (moved from below into <head>) ===== */
    :root{
      --bg:#f6cfcf; --ink:#111; --muted:#6b6b6b; --field:#fff; --border:#3b3b3b;
    }
    html,body{height:100%}
    body{ margin:0; background:var(--bg); color:var(--ink); font:16px/1.4 "Times New Roman", Times, serif; }
    .wrap{ max-width:980px; margin:100px auto 120px; padding:0 24px; }
    h1{ text-align:center; font-size:52px; font-weight:700; letter-spacing:.4px; margin:0 0 36px; }

    form{
      display:grid;
      grid-template-columns: 210px 1fr;
      column-gap:18px;
      row-gap:18px;
      align-items:center;
    }
    label{font-size:20px}
    .field{ max-width:620px; }

    input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea{
      width:100%; background:var(--field); border:1px solid var(--border); border-radius:3px;
      padding:8px 10px; font:16px "Times New Roman", Times, serif; box-sizing:border-box;
    }
    textarea{min-height:120px; resize:vertical}

    .inline{display:flex; align-items:center; gap:10px}
    .sep{margin:0 2px}
    .w-day{width:84px} .w-month{width:110px} .w-year{width:120px}
    .w-cc{width:88px} .w-phone{flex:1}

    .choices{display:flex; align-items:center; gap:18px; flex-wrap:wrap}
    .choices input{margin-right:6px}
    .note{font-style:italic; color:var(--muted); margin-left:8px}
    .file{display:flex; align-items:center; gap:10px}

    .actions{
      grid-column: 1 / -1;
      display:flex; justify-content:center;
      margin-top:10px; margin-bottom:60px;
    }
    button{
      background:#eee; border:1px solid var(--border); border-radius:4px;
      padding:8px 18px; font:16px "Times New Roman", Times, serif; cursor:pointer;
    }
    button:active{transform:translateY(1px)}
    .long{max-width:620px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Student Registration Form</h1>

<?php if ($isValid): ?>
    <!-- SUCCESS VIEW -->
    <div class="card">
      <p class="ok"><strong>Success!</strong> Your registration has been submitted.</p>
      <h3>Submitted Details</h3>
      <ul>
        <li><strong>Roll no.:</strong> <?= e($old['roll']) ?></li>
        <li><strong>Student name:</strong> <?= e($old['first_name'].' '.$old['last_name']) ?></li>
        <li><strong>Father's name:</strong> <?= e($old['father']) ?></li>
        <li><strong>Date of birth:</strong> <?= e(sprintf('%02d-%s-%04d', (int)$old['dob_day'], $old['dob_month'], (int)$old['dob_year'])) ?></li>
        <li><strong>Mobile no.:</strong> <?= e($old['cc']).' - '.e($old['phone']) ?></li>
        <li><strong>Email:</strong> <?= e($old['email']) ?></li>
        <li><strong>Gender:</strong> <?= e(ucfirst($old['gender'])) ?></li>
        <li><strong>Department:</strong> <?= e(implode(', ', $old['dept'])) ?></li>
        <li><strong>Course:</strong> <?= e($old['course']) ?></li>
        <li><strong>City:</strong> <?= e($old['city']) ?></li>
        <li><strong>Address:</strong> <?= e($old['address']) ?></li>
      </ul>
      <?php if ($uploadedPath): ?>
        <p><strong>Photo:</strong></p>
        <img class="preview" src="<?= e(uploadedPublicUrl($uploadedPath)) ?>" alt="Student photo preview">
      <?php endif; ?>
      <div class="actions" style="margin-top:12px;">
        <a href="../HTML/Form_validation.html"><button type="button">Submit Another Response</button></a>
      </div>
    </div>

<?php else: ?>
    <!-- FORM WITH STICKY VALUES & INLINE ERRORS -->
    <form action="../PHP/validation.php" method="post" enctype="multipart/form-data">
      <!-- Roll no. -->
      <label for="roll">Roll no. :</label>
      <div class="field">
        <input id="roll" name="roll" type="text" class="long" value="<?= e($old['roll']) ?>" />
        <?php if (isset($errors['roll'])): ?><span class="error"><?= e($errors['roll']) ?></span><?php endif; ?>
      </div>

      <!-- Student name -->
      <label>Student name :</label>
      <div class="field inline">
        <input type="text" name="first_name" placeholder="First Name" aria-label="First name" style="width:220px" value="<?= e($old['first_name']) ?>" />
        <span class="sep">-</span>
        <input type="text" name="last_name" placeholder="Last Name" aria-label="Last name" style="width:220px" value="<?= e($old['last_name']) ?>" />
      </div>
      <?php if (isset($errors['first_name'])): ?><span class="error"><?= e($errors['first_name']) ?></span><?php endif; ?>
      <?php if (isset($errors['last_name'])): ?><span class="error"><?= e($errors['last_name']) ?></span><?php endif; ?>

      <!-- Father's name -->
      <label for="father">Father's name :</label>
      <div class="field">
        <input id="father" name="father" type="text" class="long" value="<?= e($old['father']) ?>" />
        <?php if (isset($errors['father'])): ?><span class="error"><?= e($errors['father']) ?></span><?php endif; ?>
      </div>

      <!-- Date of birth -->
      <label>Date of birth :</label>
      <div class="field inline">
        <select name="dob_day" class="w-day" aria-label="Day">
          <option<?= sel($old['dob_day'],'Day') ?>>Day</option>
          <?php for ($i=1;$i<=31;$i++): $d = sprintf('%02d',$i); ?>
            <option<?= sel($old['dob_day'],$d) ?>><?= $d ?></option>
          <?php endfor; ?>
        </select>
        <span class="sep">-</span>
        <select name="dob_month" class="w-month" aria-label="Month">
          <?php
            $months = array_keys($monthMap);
            echo '<option'.sel($old['dob_month'],'Month').'>Month</option>';
            foreach ($months as $m) echo '<option'.sel($old['dob_month'],$m).'>'.$m.'</option>';
          ?>
        </select>
        <span class="sep">-</span>
        <select name="dob_year" class="w-year" aria-label="Year">
          <option<?= sel($old['dob_year'],'Year') ?>>Year</option>
          <?php for ($y=1997; $y<=2008; $y++): ?>
            <option<?= sel((string)$old['dob_year'],(string)$y) ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
        <span class="note">(DD-MM-YYYY)</span>
      </div>
      <?php if (isset($errors['dob'])): ?><span class="error"><?= e($errors['dob']) ?></span><?php endif; ?>

      <!-- Mobile no. -->
      <label>Mobile no. :</label>
      <div class="field inline">
        <select name="cc" class="w-cc" aria-label="Country code">
          <?php foreach (['+91','+1','+44','+61'] as $cc): ?>
            <option<?= sel($old['cc'],$cc) ?>><?= $cc ?></option>
          <?php endforeach; ?>
        </select>
        <span class="sep">-</span>
        <input type="text" name="phone" class="w-phone" inputmode="numeric" aria-label="Phone number" value="<?= e($old['phone']) ?>" />
      </div>
      <?php if (isset($errors['phone'])): ?><span class="error"><?= e($errors['phone']) ?></span><?php endif; ?>

      <!-- Email -->
      <label for="email">Email id :</label>
      <div class="field">
        <input id="email" name="email" type="email" class="long" value="<?= e($old['email']) ?>" />
        <?php if (isset($errors['email'])): ?><span class="error"><?= e($errors['email']) ?></span><?php endif; ?>
      </div>

      <!-- Password -->
      <label for="pwd">Password :</label>
      <div class="field">
        <input id="pwd" name="pwd" type="password" class="long" value="<?= e($old['pwd']) ?>" />
        <?php if (isset($errors['pwd'])): ?><span class="error"><?= e($errors['pwd']) ?></span><?php endif; ?>
      </div>

      <!-- Gender -->
      <label>Gender :</label>
      <div class="choices">
        <label><input type="radio" name="gender" value="male"   <?= rdo('male',$old['gender']) ?> /> Male</label>
        <label><input type="radio" name="gender" value="female" <?= rdo('female',$old['gender']) ?> /> Female</label>
      </div>
      <?php if (isset($errors['gender'])): ?><span class="error"><?= e($errors['gender']) ?></span><?php endif; ?>

      <!-- Department -->
      <label>Department :</label>
      <div class="choices">
        <label><input type="checkbox" name="dept[]" value="CSE"  <?= chk($old['dept'],'CSE') ?> /> CSE</label>
        <label><input type="checkbox" name="dept[]" value="IT"   <?= chk($old['dept'],'IT') ?> /> IT</label>
        <label><input type="checkbox" name="dept[]" value="ECE"  <?= chk($old['dept'],'ECE') ?> /> ECE</label>
        <label><input type="checkbox" name="dept[]" value="Civil"<?= chk($old['dept'],'Civil') ?> /> Civil</label>
        <label><input type="checkbox" name="dept[]" value="Mech" <?= chk($old['dept'],'Mech') ?> /> Mech</label>
      </div>
      <?php if (isset($errors['dept'])): ?><span class="error"><?= e($errors['dept']) ?></span><?php endif; ?>

      <!-- Course -->
      <label for="course">Course :</label>
      <div class="field">
        <select id="course" name="course">
          <option<?= sel($old['course'],"------------------------ Select Current Course's ------------------------") ?>>------------------------ Select Current Course's ------------------------</option>
          <?php foreach (['B.Tech','M.Tech','B.Sc','M.Sc','Diploma'] as $c): ?>
            <option<?= sel($old['course'],$c) ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['course'])): ?><span class="error"><?= e($errors['course']) ?></span><?php endif; ?>
      </div>

      <!-- Photo -->
      <label>Student photo :</label>
      <div class="file">
        <input type="file" name="photo" />
        <span class="note">Allowed: JPG, PNG, WEBP (max 2 MB)</span>
      </div>
      <?php if (isset($errors['photo'])): ?><span class="error"><?= e($errors['photo']) ?></span><?php endif; ?>

      <!-- City -->
      <label for="city">City :</label>
      <div class="field">
        <input id="city" name="city" type="text" class="long" value="<?= e($old['city']) ?>" />
        <?php if (isset($errors['city'])): ?><span class="error"><?= e($errors['city']) ?></span><?php endif; ?>
      </div>

      <!-- Address -->
      <label for="address">Address :</label>
      <div class="field">
        <textarea id="address" name="address" class="long"><?= e($old['address']) ?></textarea>
        <?php if (isset($errors['address'])): ?><span class="error"><?= e($errors['address']) ?></span><?php endif; ?>
      </div>

      <!-- Submit -->
      <div class="actions" style="grid-column:1/-1;">
        <button type="submit">Register</button>
        <a href="../HTML/Form_validation.html"><button type="button">Reset</button></a>
      </div>
    </form>
<?php endif; ?>

  </div>
</body>
</html>
