document.addEventListener("DOMContentLoaded", login);

function login() {
  const loginForm = document.getElementById("loginForm");
  const loginButton = document.getElementById("loginButton");
  const loginMessage = document.getElementById("loginMessage");

  loginForm.addEventListener("submit", function (event) {
    event.preventDefault();
    loginButton.disabled = true;
    loginButton.textContent = "Logging in...";
    loginMessage.textContent = "";

    const formData = new FormData(loginForm);
    const data = {
      action: "login",
      email: formData.get("email"),
      password: formData.get("password"),
    };

    fetch("/Org-Accreditation-System/backend/api/user_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        if (data.status === "success") {
          loginMessage.style.color = "green";
          loginMessage.textContent = data.message;
          setTimeout(() => {
            window.location.href = data.redirect;
          }, 1000);
        } else {
          loginMessage.style.color = "red";
          loginMessage.textContent = data.message;
        }
      })
      .catch((error) => {
        loginMessage.style.color = "red";
        loginMessage.textContent = "An error occurred: " + error.message;
      })
      .finally(() => {
        loginButton.disabled = false;
        loginButton.textContent = "Login";
      });
  });
}
