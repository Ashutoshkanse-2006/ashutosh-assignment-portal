// Faculty side - small helpers. No server logic here.

document.addEventListener("DOMContentLoaded", function () {

    // Ask "are you sure?" before submitting any form marked with data-confirm="message"
    document.querySelectorAll("form[data-confirm]").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            if (!window.confirm(form.getAttribute("data-confirm"))) {
                event.preventDefault();
            }
        });
    });

    // Make sure the due date cannot be left empty / invalid in the assignment form
    var due = document.getElementById("due_date");
    if (due && !due.value) {
        due.min = new Date().toISOString().split("T")[0];
    }

    // Marks box: keep the value between 0 and 100
    var marks = document.getElementById("marks");
    if (marks) {
        marks.addEventListener("input", function () {
            if (marks.value === "") return;
            var n = parseInt(marks.value, 10);
            if (isNaN(n) || n < 0) marks.value = 0;
            if (n > 100) marks.value = 100;
        });
    }
});
