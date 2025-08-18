// Fake database
const database = {
    username: "user420",
    password: "pass420"
};

// Helper: get page name
function getCurrentPage() {
    const path = window.location.pathname;
    return path.substring(path.lastIndexOf("/") + 1);
}

// STEP 1: Contact Form Submission & Validation
if (getCurrentPage() === "contacts.html") {
    document.getElementById("contactForm")?.addEventListener("submit", function (e) {
        e.preventDefault();

        // Grab form data
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const service = document.getElementById("service").value;
        const message = document.getElementById("message").value.trim();

        // Validation
        if (!name || !email || !service || !message) {
            alert("⚠️ Please fill in all fields.");
            return;
        }

        // Save form data to localStorage
        localStorage.setItem("contactData", JSON.stringify({ name, email, service, message }));

        // Show credentials in console
        console.log(`Username: ${database.username}, Password: ${database.password}`);

        // Show credentials in alert
        alert(`🔐 Please remember your username and password. You’ll need them on the next page.
        \nYour Username is: ${database.username}\nYour Password is: ${database.password}`
        );


        // Show instructions in next alert
        // alert("ℹ️ ");

        // Redirect to login page
        window.location.href = "../HTML/Contact_Validation/login.html";
    });
}

// STEP 2: Login Page Logic
if (getCurrentPage() === "login.html") {
    console.log(`Username: ${database.username}, Password: ${database.password}`);

    const user = prompt("Enter your username:");
    const pass = prompt("Enter your password:");

    if (user === database.username && pass === database.password) {
        window.location.href = "../Contact_Validation/index.html";
    } else {
        alert("❌ Login failed. Try again.");
        window.location.reload();
    }
}

// STEP 3: Show data on final page
if (getCurrentPage() === "index.html") {
    const data = JSON.parse(localStorage.getItem("contactData"));

    if (data) {
        const container = document.getElementById("cardContainer");

        container.innerHTML = `
            <div class="card">
                <h2>✅ Submitted Contact Information</h2>
                <p><strong>Name:</strong> ${data.name}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Service:</strong> ${data.service}</p>
                <p><strong>Message:</strong> ${data.message}</p>
            </div>
        `;
    } else {
        document.body.innerHTML = "<h2>No submitted data found.</h2>";
    }
}

