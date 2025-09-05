<?php
// contact.php — server-side validation for contacts.html form
// Adjust $allowed_services if you change the <select> options in the HTML.

// Helper: trim all inputs safely
function input($key) {
    return isset($_POST[$key]) ? trim(stripslashes($_POST[$key])) : '';
}

$errors = [];
$old = [
    'name'    => input('name'),
    'email'   => input('email'),
    'service' => input('service'),
    'message' => input('message')
];

$allowed_services = [
    'Web Development',
    'Leaderships',
    'Software Quality Assurance',
    'UI/UX Designing',
    'Circuit Designing'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Name: required, allow common characters in names
    if ($old['name'] === '') {
        $errors['name'] = 'Name is required.';
    } elseif (!preg_match("/^[a-zA-Z\s'.-]{2,50}$/", $old['name'])) {
        $errors['name'] = 'Please enter a valid name (letters, spaces, . \' -).';
    }

    // Email: required & valid format
    if ($old['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Service: required & must be one of the allowed options
    if ($old['service'] === '') {
        $errors['service'] = 'Please select a service.';
    } elseif (!in_array($old['service'], $allowed_services, true)) {
        $errors['service'] = 'Invalid service selected.';
    }

    // Message: required & basic length check
    if ($old['message'] === '') {
        $errors['message'] = 'Message is required.';
    } elseif (mb_strlen($old['message']) < 10) {
        $errors['message'] = 'Message should be at least 10 characters.';
    }

    $success = empty($errors);
} else {
    $success = false;
}

// Basic HTML output with the same structure/classes as contacts.html
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact — Validation</title>
    <link rel="stylesheet" href="contacts.css" />
    <style>
        /* Ensure error text is red and sits under the input */
        .error { color: #e63946; font-size: 0.9rem; margin-top: 6px; display: block; }
        .success { background: #e7f9ed; border: 1px solid #34c759; color: #0f5132; padding: 12px 14px; border-radius: 6px; margin-bottom: 16px; }
        .invalid { border-color: #e63946 !important; }
    </style>
</head>
<body>
    <header>
        <nav class="nav-bar">
            <ul class="nav-links">
                <li class="nav-item"><a href="./index.html">Home</a></li>
                <li class="nav-item"><a href="education.html">Education</a></li>
                <li class="nav-item"><a href="experiences.html">Experiences</a></li>
                <li class="nav-item"><a href="projects.html">Projects</a></li>
                <li class="nav-item active"><a href="contacts.html">Contacts</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="contact-form">
            <h2>Contact Me</h2>

            <?php if ($success): ?>
                <div class="success">
                    Thanks, <strong><?php echo htmlspecialchars($old['name']); ?></strong>! Your message has been received.
                </div>
            <?php endif; ?>

            <form id="contactForm" action="contact.php" method="post" novalidate>
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your name"
                        value="<?php echo htmlspecialchars($old['name']); ?>"
                        class="<?php echo isset($errors['name']) ? 'invalid' : ''; ?>"
                        required
                    />
                    <?php if (isset($errors['name'])): ?>
                        <small class="error"><?php echo $errors['name']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="<?php echo htmlspecialchars($old['email']); ?>"
                        class="<?php echo isset($errors['email']) ? 'invalid' : ''; ?>"
                        required
                    />
                    <?php if (isset($errors['email'])): ?>
                        <small class="error"><?php echo $errors['email']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="service">What services do you want from me?</label>
                    <select
                        id="service"
                        name="service"
                        class="<?php echo isset($errors['service']) ? 'invalid' : ''; ?>"
                        required
                    >
                        <option value="">-- Select a service --</option>
                        <?php foreach ($allowed_services as $service): ?>
                            <option value="<?php echo htmlspecialchars($service); ?>"
                                <?php echo ($old['service'] === $service) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($service); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['service'])): ?>
                        <small class="error"><?php echo $errors['service']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="4"
                        placeholder="Enter your message"
                        class="<?php echo isset($errors['message']) ? 'invalid' : ''; ?>"
                        required
                    ><?php echo htmlspecialchars($old['message']); ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <small class="error"><?php echo $errors['message']; ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit">Submit</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <p>Copyright 2025. All Rights Reserved.</p>
    </footer>
</body>
</html>
