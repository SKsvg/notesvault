document.addEventListener("DOMContentLoaded", function () {
    // === Get all header elements ===
    const authButtons = document.getElementById("authButtons");
    const profileContainer = document.getElementById("profileContainer");
    const profileBtn = document.querySelector(".profile-btn");
    const profileDropdown = document.querySelector(".profile-dropdown");
    const logoutBtn = document.getElementById("logoutBtn");
    const themeToggle = document.querySelector(".theme-toggle");

    // === Mobile Menu Elements ===
    const menuToggle = document.querySelector(".menu-toggle");
    const mobileNav = document.querySelector(".mobile-nav");
    const overlay = document.querySelector(".overlay");
    const mobileNavLinks = document.querySelectorAll(".mobile-nav-link");
    const mobileAuthButtons = document.querySelector(".mobile-auth-buttons");
    const mobileLogoutBtn = document.getElementById("mobileLogoutBtn");

    // === Function to update header based on login status ===
    function updateHeader() {
        const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
        if (isLoggedIn) {
            if (authButtons) {
                authButtons.classList.add("hidden");
            }
            if (profileContainer) {
                profileContainer.classList.remove("hidden");
            }
            if (mobileAuthButtons) {
                mobileAuthButtons.classList.add("hidden");
            }
            if (mobileLogoutBtn) {
                mobileLogoutBtn.classList.remove("hidden");
            }
        } else {
            if (authButtons) {
                authButtons.classList.remove("hidden");
            }
            if (profileContainer) {
                profileContainer.classList.add("hidden");
            }
            if (mobileAuthButtons) {
                mobileAuthButtons.classList.remove("hidden");
            }
            if (mobileLogoutBtn) {
                mobileLogoutBtn.classList.add("hidden");
            }
        }
    }

    // === Profile Dropdown and Logout Logic ===
    if (profileBtn) {
        profileBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            if (profileDropdown) {
                profileDropdown.classList.toggle("show");
            }
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
            fetch('logout.php', { method: 'POST' })
                .then(() => {
                    localStorage.removeItem("isLoggedIn");
                    window.location.href = "login.html";
                })
                .catch(error => {
                    console.error('Logout failed:', error);
                    localStorage.removeItem("isLoggedIn");
                    window.location.href = "login.html";
                });
        });
    }

    window.addEventListener("click", (event) => {
        if (profileContainer && profileDropdown && !profileContainer.contains(event.target)) {
            profileDropdown.classList.remove("show");
        }
    });

    // === Theme Toggle Logic ===
    if (themeToggle) {
        const savedTheme = localStorage.getItem("theme") || "light";
        document.documentElement.setAttribute("data-theme", savedTheme);

        themeToggle.addEventListener("click", () => {
            let currentTheme = document.documentElement.getAttribute("data-theme");
            let newTheme = currentTheme === "light" ? "dark" : "light";

            document.documentElement.setAttribute("data-theme", newTheme);
            localStorage.setItem("theme", newTheme);
        });
    }

    // === Mobile Menu Toggle Logic ===
    if (menuToggle) {
        menuToggle.addEventListener("click", () => {
            menuToggle.classList.toggle("active");
            mobileNav.classList.toggle("active");
            overlay.classList.toggle("active");
        });
    }

    if (overlay) {
        overlay.addEventListener("click", () => {
            menuToggle.classList.remove("active");
            mobileNav.classList.remove("active");
            overlay.classList.remove("active");
        });
    }

    if (mobileNavLinks) {
        mobileNavLinks.forEach(link => {
            link.addEventListener("click", () => {
                menuToggle.classList.remove("active");
                mobileNav.classList.remove("active");
                overlay.classList.remove("active");
            });
        });
    }

    if (mobileLogoutBtn) {
        mobileLogoutBtn.addEventListener("click", () => {
            fetch('logout.php', { method: 'POST' })
                .then(() => {
                    localStorage.removeItem("isLoggedIn");
                    window.location.href = "login.html";
                })
                .catch(error => {
                    console.error('Logout failed:', error);
                    localStorage.removeItem("isLoggedIn");
                    window.location.href = "login.html";
                });
        });
    }

    // Run the function to set the initial state of the header
    updateHeader();
});