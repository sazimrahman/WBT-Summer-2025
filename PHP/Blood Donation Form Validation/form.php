<?php
// form.php — server-side validation with inline error messages under each field.
$errors = [];
$values = [];
function v($name){
  global $values;
  return htmlspecialchars($values[$name] ?? '', ENT_QUOTES);
}
function is_checked($name, $value){
  global $values;
  if(!isset($values[$name])) return false;
  if(is_array($values[$name])) return in_array($value, $values[$name]);
  return $values[$name] === $value;
}

// Handle Reset via query string to refresh page
if (isset($_GET['reset']) && $_GET['reset'] === '1') {
  header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
  exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  // Collect and trim inputs
  $fields = [
    'first_name','last_name','company','addr1','addr2','city','state','zip','country',
    'phone','fax','email','amount','other_amount','recurring','monthly_amount','months',
    'honor','ack_name','ack_addr','ack_city','ack_state','ack_zip',
    'pub_name','anonymous','matching','no_thanks','comments','volunteer_with'
  ];
  foreach($fields as $f){
    $values[$f] = isset($_POST[$f]) ? (is_array($_POST[$f]) ? $_POST[$f] : trim($_POST[$f])) : null;
  }
  // Contact preferences (checkboxes)
  $values['contact'] = [
    'email'  => isset($_POST['contact_email']),
    'postal' => isset($_POST['contact_postal']),
    'phone'  => isset($_POST['contact_phone']),
    'fax'    => isset($_POST['contact_fax']),
  ];
  $values['newsletter'] = [
    'email'  => isset($_POST['newsletter_email']),
    'postal' => isset($_POST['newsletter_postal']),
  ];

  // ---- Donor Information (required) ----
  if($values['first_name'] === '') $errors['first_name'] = 'First name is required.';
  if($values['last_name']  === '') $errors['last_name']  = 'Last name is required.';
  if($values['addr1']      === '') $errors['addr1']      = 'Address 1 is required.';
  if($values['city']       === '') $errors['city']       = 'City is required.';
  if($values['state']      === '') $errors['state']      = 'State is required.';
  if($values['zip']        === '') $errors['zip']        = 'Zip code is required.';
  if($values['country']    === '') $errors['country']    = 'Country is required.';
  if($values['email']      === '') $errors['email']      = 'Email is required.';
  elseif(!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';

  // Optional formats
  if($values['phone'] && !preg_match('/^[0-9+\-\s().]{7,}$/', $values['phone'])) $errors['phone'] = 'Enter a valid phone number.';
  if($values['fax']   && !preg_match('/^[0-9+\-\s().]{7,}$/', $values['fax']))   $errors['fax']   = 'Enter a valid fax number.';
  if($values['zip']   && !preg_match('/^[A-Za-z0-9\- ]{3,10}$/', $values['zip'])) $errors['zip'] = 'Enter a valid zip/postal code.';

  // Donation Amount
  if($values['amount'] === null || $values['amount'] === ''){
    $errors['amount'] = 'Select a donation amount or choose Other and specify.';
  }
  if($values['amount'] === 'other'){
    if($values['other_amount'] === ''){
      $errors['other_amount'] = 'Enter your other amount.';
    } elseif(!is_numeric($values['other_amount']) || floatval($values['other_amount']) <= 0){
      $errors['other_amount'] = 'Other amount must be a positive number.';
    }
  }
  // Recurring
  if(isset($_POST['recurring'])){
    if($values['monthly_amount'] === '' || !is_numeric($values['monthly_amount']) || floatval($values['monthly_amount']) <= 0){
      $errors['monthly_amount'] = 'Monthly amount must be a positive number.';
    }
    if($values['months'] === '' || !ctype_digit($values['months']) || intval($values['months']) < 1){
      $errors['months'] = 'Enter number of months (1 or more).';
    }
  }

  // ---- Honorarium and Memorial Donation Information ----
  // Only validate subfields if honor radio selected
  if($values['honor'] === 'to_honor' || $values['honor'] === 'in_memory'){
    if($values['ack_name'] === '') $errors['ack_name'] = 'Please enter a name to acknowledge.';
    if($values['ack_addr'] === '') $errors['ack_addr'] = 'Please enter the address.';
    if($values['ack_city'] === '') $errors['ack_city'] = 'Please enter the city.';
    if($values['ack_state'] === '') $errors['ack_state'] = 'Please select a state.';
    if($values['ack_zip'] === '') $errors['ack_zip'] = 'Please enter the zip.';
    elseif(!preg_match('/^[A-Za-z0-9\- ]{3,10}$/', $values['ack_zip'])) $errors['ack_zip'] = 'Enter a valid zip/postal code.';
  }

  // ---- Additional Information ----
  // Require at least one contact method
  if(!$values['contact']['email'] && !$values['contact']['postal'] && !$values['contact']['phone'] && !$values['contact']['fax']){
    $errors['contact'] = 'Select at least one contact method.';
  }

  $is_valid = count($errors) === 0;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Donation Form</title>
  <link rel="stylesheet" href="form.css">
</head>
<body>
  <div class="wrapper">
    <div class="breadcrumb">
      <strong>&gt; 1 Donation</strong> &nbsp;&nbsp;&gt; 2 Confirmation &nbsp;&nbsp;&gt; Thank You!
    </div>
    <p class="req-note"><span class="req">*</span> <span class="req">Denotes Required Information</span></p>

    <?php if(isset($is_valid) && $is_valid): ?>
      <div class="success">All good! Your form is valid. (This demo stops here; integrate submission/redirect as needed.)</div>
    <?php endif; ?>

    <form method="post" action="">
      <!-- Donor Information -->
      <section class="section">
        <h2 class="section-title">Donor Information</h2>

        <div class="form-row">
          <div class="label">First Name<span class="required-star">*</span></div>
          <div class="field">
            <input type="text" name="first_name" value="<?php echo v('first_name'); ?>">
            <?php if(isset($errors['first_name'])): ?><div class="error"><?php echo $errors['first_name']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Last Name<span class="required-star">*</span></div>
          <div class="field">
            <input type="text" name="last_name" value="<?php echo v('last_name'); ?>">
            <?php if(isset($errors['last_name'])): ?><div class="error"><?php echo $errors['last_name']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Company</div>
          <div class="field">
            <input type="text" name="company" value="<?php echo v('company'); ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="label">Address 1<span class="required-star">*</span></div>
          <div class="field">
            <input type="text" name="addr1" value="<?php echo v('addr1'); ?>">
            <?php if(isset($errors['addr1'])): ?><div class="error"><?php echo $errors['addr1']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Address 2</div>
          <div class="field">
            <input type="text" name="addr2" value="<?php echo v('addr2'); ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="label">City<span class="required-star">*</span></div>
          <div class="field">
            <input type="text" name="city" value="<?php echo v('city'); ?>">
            <?php if(isset($errors['city'])): ?><div class="error"><?php echo $errors['city']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">State<span class="required-star">*</span></div>
          <div class="field">
            <select name="state">
              <?php
                $states = ['', 'AL','AK','AZ','AR','CA','CO','CT','DC','DE','FL','GA','HI','IA','ID','IL','IN','KS','KY','LA','MA','MD','ME','MI','MN','MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VA','VT','WA','WI','WV','WY'];
                foreach($states as $st){
                  $label = $st === '' ? 'Select a State' : $st;
                  $sel = (v('state') === $st) ? 'selected' : '';
                  echo "<option value=\"$st\" $sel>$label</option>";
                }
              ?>
            </select>
            <?php if(isset($errors['state'])): ?><div class="error"><?php echo $errors['state']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Zip Code<span class="required-star">*</span></div>
          <div class="field">
            <input type="text" name="zip" value="<?php echo v('zip'); ?>">
            <?php if(isset($errors['zip'])): ?><div class="error"><?php echo $errors['zip']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Country<span class="required-star">*</span></div>
          <div class="field">
            <select name="country">
              <?php
                $countries = ['', 'United States','Canada','United Kingdom','Australia'];
                foreach($countries as $c){
                  $label = $c === '' ? 'Select a Country' : $c;
                  $sel = (v('country') === $c) ? 'selected' : '';
                  echo "<option value=\"$c\" $sel>$label</option>";
                }
              ?>
            </select>
            <?php if(isset($errors['country'])): ?><div class="error"><?php echo $errors['country']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Phone</div>
          <div class="field">
            <input type="tel" name="phone" value="<?php echo v('phone'); ?>">
            <?php if(isset($errors['phone'])): ?><div class="error"><?php echo $errors['phone']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Fax</div>
          <div class="field">
            <input type="text" name="fax" value="<?php echo v('fax'); ?>">
            <?php if(isset($errors['fax'])): ?><div class="error"><?php echo $errors['fax']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Email<span class="required-star">*</span></div>
          <div class="field">
            <input type="email" name="email" value="<?php echo v('email'); ?>">
            <?php if(isset($errors['email'])): ?><div class="error"><?php echo $errors['email']; ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="label">Donation Amount<span class="required-star">*</span></div>
          <div class="field">
            <div class="inline-group">
              <?php
                $amounts = ['none'=>'None','50'=>'$50','75'=>'$75','100'=>'$100','250'=>'$250','other'=>'Other'];
                foreach($amounts as $val=>$label){
                  $checked = is_checked('amount', $val) ? 'checked' : '';
                  echo "<label><input type=\"radio\" name=\"amount\" value=\"$val\" $checked> $label</label>";
                }
              ?>
            </div>
            <?php if(isset($errors['amount'])): ?><div class="error"><?php echo $errors['amount']; ?></div><?php endif; ?>
            <div class="inline-note">(Check a button or type in your amount)</div>
            <div class="inline-group" style="margin-top:6px;">
              <div class="label" style="width:auto; min-width:0; font-weight:normal;">Other Amount $</div>
              <input type="text" name="other_amount" style="width:100px" value="<?php echo v('other_amount'); ?>">
            </div>
            <?php if(isset($errors['other_amount'])): ?><div class="error"><?php echo $errors['other_amount']; ?></div><?php endif; ?>

            <div class="checkbox-row" style="margin-top:6px;">
              <label><input type="checkbox" name="recurring" <?php echo isset($_POST['recurring']) ? 'checked' : ''; ?>> I am interested in giving on a regular basis.</label>
              <div class="inline-group">
                <div>Monthly Credit Card $</div>
                <input type="text" name="monthly_amount" style="width:90px" value="<?php echo v('monthly_amount'); ?>">
                <div>For</div>
                <input type="text" name="months" style="width:70px" value="<?php echo v('months'); ?>">
                <div>Months</div>
              </div>
              <?php if(isset($errors['monthly_amount'])): ?><div class="error"><?php echo $errors['monthly_amount']; ?></div><?php endif; ?>
              <?php if(isset($errors['months'])): ?><div class="error"><?php echo $errors['months']; ?></div><?php endif; ?>
            </div>
          </div>
        </div>
      </section>

      <!-- Honorarium and Memorial Donation Information -->
      <section class="section">
        <h2 class="section-title">Honorarium and Memorial Donation Information</h2>

        <div class="form-row">
          <div class="label">I would like to make this donation</div>
          <div class="field">
            <div class="inline-group">
              <label><input type="radio" name="honor" value="to_honor" <?php echo is_checked('honor','to_honor')?'checked':''; ?>> To Honor</label>
              <label><input type="radio" name="honor" value="in_memory" <?php echo is_checked('honor','in_memory')?'checked':''; ?>> In Memory of</label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="label">Acknowledge Donation to</div>
          <div class="field">
            <input type="text" name="ack_name" value="<?php echo v('ack_name'); ?>">
            <?php if(isset($errors['ack_name'])): ?><div class="error"><?php echo $errors['ack_name']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Address</div>
          <div class="field">
            <input type="text" name="ack_addr" value="<?php echo v('ack_addr'); ?>">
            <?php if(isset($errors['ack_addr'])): ?><div class="error"><?php echo $errors['ack_addr']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">City</div>
          <div class="field">
            <input type="text" name="ack_city" value="<?php echo v('ack_city'); ?>">
            <?php if(isset($errors['ack_city'])): ?><div class="error"><?php echo $errors['ack_city']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">State</div>
          <div class="field">
            <select name="ack_state">
              <option value="">Select a State</option>
              <?php
                foreach($states as $st){
                  if($st==='') continue;
                  $sel = (v('ack_state') === $st) ? 'selected' : '';
                  echo "<option value=\"$st\" $sel>$st</option>";
                }
              ?>
            </select>
            <?php if(isset($errors['ack_state'])): ?><div class="error"><?php echo $errors['ack_state']; ?></div><?php endif; ?>
          </div>
        </div>
        <div class="form-row">
          <div class="label">Zip</div>
          <div class="field">
            <input type="text" name="ack_zip" value="<?php echo v('ack_zip'); ?>">
            <?php if(isset($errors['ack_zip'])): ?><div class="error"><?php echo $errors['ack_zip']; ?></div><?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Additional Information -->
      <section class="section">
        <h2 class="section-title">Additional Information</h2>
        <p class="help-text">Please enter your name, company or organization as you would like it to appear in our publications:</p>

        <div class="form-row">
          <div class="label">Name</div>
          <div class="field">
            <input type="text" name="pub_name" value="<?php echo v('pub_name'); ?>">
          </div>
        </div>

        <div class="checkbox-row">
          <label><input type="checkbox" name="anonymous" <?php echo isset($_POST['anonymous'])?'checked':''; ?>> I would like my gift to remain anonymous.</label>
          <label><input type="checkbox" name="matching" <?php echo isset($_POST['matching'])?'checked':''; ?>> My employer offers a matching gift program. I will mail the matching gift form.</label>
          <label><input type="checkbox" name="no_thanks" <?php echo isset($_POST['no_thanks'])?'checked':''; ?>> Please save the cost of acknowledging this gift by not mailing a thank you letter.</label>
        </div>

        <div class="form-row" style="align-items:flex-start;">
          <div class="label">Comments</div>
          <div class="field">
            <textarea name="comments" placeholder="(Please type any questions or feedback here)"><?php echo v('comments'); ?></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="label">How may we contact you?</div>
          <div class="field">
            <div class="checkbox-row">
              <label><input type="checkbox" name="contact_email" <?php echo $values['contact']['email']??false ? 'checked':''; ?>> E-mail</label>
              <label><input type="checkbox" name="contact_postal" <?php echo $values['contact']['postal']??false ? 'checked':''; ?>> Postal Mail</label>
              <label><input type="checkbox" name="contact_phone" <?php echo $values['contact']['phone']??false ? 'checked':''; ?>> Telephone</label>
              <label><input type="checkbox" name="contact_fax" <?php echo $values['contact']['fax']??false ? 'checked':''; ?>> Fax</label>
            </div>
            <?php if(isset($errors['contact'])): ?><div class="error"><?php echo $errors['contact']; ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="label">I would like to receive newsletters and information about special events by:</div>
          <div class="field">
            <div class="checkbox-row">
              <label><input type="checkbox" name="newsletter_email" <?php echo $values['newsletter']['email']??false ? 'checked':''; ?>> E-mail</label>
              <label><input type="checkbox" name="newsletter_postal" <?php echo $values['newsletter']['postal']??false ? 'checked':''; ?>> Postal Mail</label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="label">I would like information about volunteering with the</div>
          <div class="field">
            <input type="text" name="volunteer_with" value="<?php echo v('volunteer_with'); ?>">
          </div>
        </div>

        <div class="controls">
          <button type="button" onclick="window.location='?reset=1'">Reset</button>
          <input type="submit" value="Continue">
        </div>

        <div class="footer-note">Donate online with confidence. You are on a secure server.</div>
        <div class="footer-note">If you have any problems or questions, please contact support.</div>
      </section>
    </form>
  </div>
</body>
</html>
