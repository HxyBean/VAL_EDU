
document.getElementById("darkModeToggle").addEventListener("click", toggleDarkMode);
document.getElementById("darkModeToggle").addEventListener("click", function(){
    this.classList.toggle("active");
});

function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle("dark-mode");
    //save dark mode 
    if (body.classList.contains("dark-mode")) {
        localStorage.setItem("mode", "dark");
    } else {
        localStorage.setItem("mode", "light");
    }
}
