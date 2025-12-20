const dayNightMoodBtn = document.getElementById("dayNightMoodBtn");


const savedTheme = sessionStorage.getItem("theme");
if (savedTheme) {
    document.documentElement.dataset.theme = savedTheme;
} else {
    document.documentElement.dataset.theme = "dark";
}


dayNightMoodBtn.addEventListener("click", toggleTheme);


function toggleTheme() {
    const html = document.documentElement;

    if (html.dataset.theme === "light") {
        html.dataset.theme = "dark";
        sessionStorage.setItem("theme", "dark");
        dayNightMoodBtn.innerText = "🌕";
    } else {
        html.dataset.theme = "light";
        sessionStorage.setItem("theme", "light"); // store in session
        dayNightMoodBtn.innerText = "🌙";
    }
}
