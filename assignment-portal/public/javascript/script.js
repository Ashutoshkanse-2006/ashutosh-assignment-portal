// Student Assignment Submission Portal - script.js
// Layout helpers only. No form or server logic lives here.

document.addEventListener("DOMContentLoaded", function () {

    // 1. Mobile menu: the hamburger button shows / hides the links
    var toggle = document.querySelector(".nav-toggle");
    var links = document.querySelector(".nav-links");

    if (toggle && links) {
        toggle.addEventListener("click", function () {
            var isOpen = links.classList.toggle("open");
            toggle.setAttribute("aria-expanded", isOpen);
        });
    }

    // 2. Highlight the navbar link that matches the current page
    var currentPage = window.location.pathname.split("/").pop() || "index.php";
    var navLinks = document.querySelectorAll(".nav-links a");

    navLinks.forEach(function (link) {
        var linkPage = link.getAttribute("href").split("/").pop();
        if (linkPage === currentPage && !link.classList.contains("nav-cta")) {
            link.classList.add("active");
        }
    });
});
