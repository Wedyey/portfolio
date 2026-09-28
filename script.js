// ============================== MOBILE NAVIGATION ==============================

const menuBtn = document.getElementById("menu-btn");
const navMenu = document.getElementById("nav-menu");

if(menuBtn && navMenu){

    menuBtn.addEventListener("click", function(){

        navMenu.classList.toggle("show-menu");

    });

}


// ============================== THEME MODE ==============================

const themeMode = document.getElementById("theme-mode");

if(themeMode){

    themeMode.addEventListener("click", function(){

        document.body.classList.toggle("light-mode");

        if(document.body.classList.contains("light-mode")){

            localStorage.setItem("theme", "light");

        }else{

            localStorage.setItem("theme", "dark");

        }

    });

}


// ============================== LOAD SAVED THEME ==============================

const savedTheme = localStorage.getItem("theme");

if(savedTheme === "light"){

    document.body.classList.add("light-mode");

}


// ============================== ACTIVE NAVIGATION ==============================

const currentPage = window.location.pathname.split("/").pop();

const navLinks = document.querySelectorAll(".navbar a");

navLinks.forEach(function(link){

    const linkPage = link.getAttribute("href");

    if(linkPage === currentPage){

        link.parentElement.classList.add("active");

    }

});


// ============================== PROJECT FILTER ==============================

const filterButtons = document.querySelectorAll(".filter-btn");
const projectCards = document.querySelectorAll(".project-card");

filterButtons.forEach(function(button){

    button.addEventListener("click", function(){

        const filter = button.dataset.filter;


        // Remove active style from every button

        filterButtons.forEach(function(btn){

            btn.classList.remove("active-filter");

        });


        // Add active style to clicked button

        button.classList.add("active-filter");


        // Show / hide projects

        projectCards.forEach(function(project){

            const category = project.dataset.category;

            if(filter === "all" || category === filter){

                project.style.display = "block";

            }else{

                project.style.display = "none";

            }

        });

    });

});


// ============================== CONTACT FORM ==============================

const contactForm = document.getElementById("contact-form");

if(contactForm){

    contactForm.addEventListener("submit", function(event){

        event.preventDefault();


        const name = document.getElementById("contact-name").value.trim();
        const email = document.getElementById("contact-email").value.trim();
        const message = document.getElementById("contact-message").value.trim();


        const nameError = document.getElementById("name-error");
        const emailError = document.getElementById("email-error");
        const messageError = document.getElementById("message-error");
        const formSuccess = document.getElementById("form-success");


        nameError.textContent = "";
        emailError.textContent = "";
        messageError.textContent = "";
        formSuccess.textContent = "";


        let valid = true;


        // Check name

        if(name === ""){

            nameError.textContent = "Please enter your name.";
            valid = false;

        }


        // Check email

        if(email === ""){

            emailError.textContent = "Please enter your email.";
            valid = false;

        }else if(!email.includes("@")){

            emailError.textContent = "Please enter a valid email.";
            valid = false;

        }


        // Check message

        if(message === ""){

            messageError.textContent = "Please enter a message.";
            valid = false;

        }


        // If everything is valid

        if(valid){

            formSuccess.textContent = "Message form passed validation!";

            contactForm.reset();

        }

    });

}


// ============================== SCROLL TO TOP ==============================

const scrollTop = document.getElementById("scroll-top");

if(scrollTop){

    window.addEventListener("scroll", function(){

        if(window.scrollY > 300){

            scrollTop.classList.add("show-scroll");

        }else{

            scrollTop.classList.remove("show-scroll");

        }

    });


    scrollTop.addEventListener("click", function(){

        window.scrollTo({

            top: 0,
            behavior: "smooth"

        });

    });

}