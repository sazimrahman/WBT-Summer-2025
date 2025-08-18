document.querySelector("form").addEventListener("submit", function (event) {
  event.preventDefault();

  const hireOption = document.getElementById("hireOption").value;

  if (hireOption === "project") {
    alert(
      "Thank you for choosing to hire me for a project! I will get back to you shortly."
    );
  } else if (hireOption === "job") {
    alert(
      "Thank you for your interest in hiring me for a job! I will be in touch soon."
    );
  } else {
    alert("Please select a hire option.");
  }
});
