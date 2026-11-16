// ============================== MOBILE NAVIGATION ==============================

const menuBtn = document.getElementById("menu-btn");
const navMenu = document.getElementById("nav-menu");

if(menuBtn && navMenu){

    menuBtn.setAttribute("aria-expanded", "false");

    menuBtn.addEventListener("click", function(){

        const isOpen = navMenu.classList.toggle("show-menu");

        menuBtn.setAttribute("aria-expanded", String(isOpen));
        menuBtn.setAttribute("aria-label", isOpen ? "Close navigation menu" : "Open navigation menu");

    });

    navMenu.querySelectorAll("a").forEach(function(link){

        link.addEventListener("click", function(){

            navMenu.classList.remove("show-menu");
            menuBtn.setAttribute("aria-expanded", "false");
            menuBtn.setAttribute("aria-label", "Open navigation menu");

        });

    });

    document.addEventListener("keydown", function(event){

        if(event.key === "Escape"){

            navMenu.classList.remove("show-menu");
            menuBtn.setAttribute("aria-expanded", "false");
            menuBtn.setAttribute("aria-label", "Open navigation menu");

        }

    });

}


// ============================== THEME MODE ==============================

const themeMode = document.getElementById("theme-mode");

if(themeMode){

    themeMode.setAttribute("type", "button");
    themeMode.setAttribute("aria-label", "Toggle color theme");

    themeMode.addEventListener("click", function(){

        document.body.classList.toggle("light-mode");

        const isLightMode = document.body.classList.contains("light-mode");

        localStorage.setItem("theme", isLightMode ? "light" : "dark");
        themeMode.setAttribute("aria-pressed", String(isLightMode));

    });

}


// ============================== LOAD SAVED THEME ==============================

const savedTheme = localStorage.getItem("theme");

if(savedTheme === "light"){

    document.body.classList.add("light-mode");

}

if(themeMode){

    themeMode.setAttribute("aria-pressed", String(document.body.classList.contains("light-mode")));

}


// ============================== ACTIVE NAVIGATION ==============================

const currentPage = window.location.pathname.split("/").pop() || "index.html";

const navLinks = document.querySelectorAll(".navbar a");

navLinks.forEach(function(link){

    const linkPage = new URL(link.href).pathname.split("/").pop() || "index.html";
    const parentItem = link.parentElement;

    if(linkPage === currentPage){

        parentItem.classList.add("active");
        link.classList.add("active");
        link.setAttribute("aria-current", "page");

    }else{

        parentItem.classList.remove("active");
        link.classList.remove("active");
        link.removeAttribute("aria-current");

    }

});


// ============================== PROJECT FILTER ==============================

const filterButtons = document.querySelectorAll(".filter-btn");
const projectCards = document.querySelectorAll(".project-card");

filterButtons.forEach(function(button){

    button.addEventListener("click", function(){

        const filter = button.dataset.filter;

        filterButtons.forEach(function(btn){

            btn.classList.remove("active-filter");
            btn.setAttribute("aria-pressed", "false");

        });

        button.classList.add("active-filter");
        button.setAttribute("aria-pressed", "true");

        projectCards.forEach(function(project){

            const category = project.dataset.category;

            project.hidden = filter !== "all" && category !== filter;

        });

    });

});


// ============================== CONTACT FORM ==============================

const contactForm = document.getElementById("contact-form");

if(contactForm){

    contactForm.addEventListener("submit", function(event){

        event.preventDefault();

        const formSuccess = document.getElementById("form-success");
        const formData = new FormData(contactForm);
        const name = String(formData.get("name")).trim();
        const email = String(formData.get("email")).trim();
        const subject = String(formData.get("subject")).trim();
        const message = String(formData.get("message")).trim();

        formSuccess.textContent = "";

        const mailSubject = subject || `Portfolio message from ${name}`;
        const mailBody = `Name: ${name}\nEmail: ${email}\n\n${message}`;
        const mailto = `mailto:roejieadamso@gmail.com?subject=${encodeURIComponent(mailSubject)}&body=${encodeURIComponent(mailBody)}`;

        window.location.href = mailto;
        formSuccess.textContent = "Your email app should open with your message. Review it there, then send it.";

    });

}


// ============================== SCROLL TO TOP ==============================

const scrollTop = document.getElementById("scroll-top");

if(scrollTop){

    window.addEventListener("scroll", function(){

        scrollTop.classList.toggle("show-scroll", window.scrollY > 300);

    });


    scrollTop.addEventListener("click", function(){

        window.scrollTo({

            top: 0,
            behavior: "smooth"

        });

    });

}