document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".form");
  const emailInput = form.querySelector("input[name='email']");

  form.addEventListener("submit", (e) => {
    const email = emailInput.value.trim();

    // Basic email format check
    if (!validateEmail(email)) {
      e.preventDefault();
      alert("❌ Please enter a valid email address.");
    }
  });

  function validateEmail(email) {
    const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
    return pattern.test(email.toLowerCase());
  }
});
