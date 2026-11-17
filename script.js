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

function applyProjectFilter(){

    const activeButton = document.querySelector(".filter-btn.active-filter");
    const filter = activeButton ? activeButton.dataset.filter : "all";

    document.querySelectorAll(".project-card").forEach(function(project){

        project.hidden = filter !== "all" && project.dataset.category !== filter;

    });

}

filterButtons.forEach(function(button){

    button.addEventListener("click", function(){

        filterButtons.forEach(function(btn){

            btn.classList.remove("active-filter");
            btn.setAttribute("aria-pressed", "false");

        });

        button.classList.add("active-filter");
        button.setAttribute("aria-pressed", "true");
        applyProjectFilter();

    });

});

function createProjectCard(project){

    const card = document.createElement("article");
    const category = String(project.category || "other").trim().toLowerCase();
    const title = String(project.title || "");
    const imagePath = String(project.image_path || "");
    const content = document.createElement("div");
    const heading = document.createElement("h2");
    const description = document.createElement("p");
    const technologies = document.createElement("p");
    const technologyLabel = document.createElement("strong");

    card.className = "project-card";
    card.dataset.category = ["web", "java", "other"].includes(category) ? category : "other";
    content.className = "project-content";
    heading.textContent = title;
    description.textContent = String(project.description || "");
    technologies.className = "technologies";
    technologyLabel.textContent = "Technologies: ";
    technologies.append(technologyLabel, document.createTextNode(String(project.technologies || "")));

    if (/^(img|uploads)\/[A-Za-z0-9._/-]+$/.test(imagePath) && !imagePath.split("/").includes("..")){
        const image = document.createElement("img");
        image.src = imagePath;
        image.alt = title;
        card.append(image);
    }

    content.append(heading, description, technologies);
    card.append(content);

    return card;

}

const dynamicProjects = document.querySelector(".projects-container[data-dynamic-projects]");

if(dynamicProjects){

    const loadStatus = document.createElement("p");
    loadStatus.setAttribute("role", "status");
    loadStatus.textContent = "Loading projects...";
    dynamicProjects.prepend(loadStatus);

    fetch("api/content.php?type=projects")
        .then(function(response){
            if(!response.ok){
                throw new Error("Project request failed with status " + response.status);
            }
            return response.json();
        })
        .then(function(projects){
            if(!Array.isArray(projects)){
                throw new Error("Project response was not a list.");
            }

            loadStatus.remove();
            dynamicProjects.querySelectorAll(".project-card").forEach(function(card){
                card.remove();
            });

            if(projects.length === 0){
                const emptyMessage = document.createElement("p");
                emptyMessage.textContent = "No projects have been added yet.";
                dynamicProjects.append(emptyMessage);
                return;
            }

            projects.forEach(function(project){
                dynamicProjects.append(createProjectCard(project));
            });
            applyProjectFilter();
        })
        .catch(function(error){
            console.error(error);
            loadStatus.textContent = "Live projects are unavailable. Showing the saved preview.";
        });

}

const dynamicSkills = document.querySelector(".skills-container[data-dynamic-skills]");

if(dynamicSkills){

    const loadStatus = document.createElement("p");
    loadStatus.setAttribute("role", "status");
    loadStatus.textContent = "Loading skills...";
    dynamicSkills.prepend(loadStatus);

    fetch("api/content.php?type=skills")
        .then(function(response){
            if(!response.ok){
                throw new Error("Skill request failed with status " + response.status);
            }
            return response.json();
        })
        .then(function(skills){
            if(!Array.isArray(skills)){
                throw new Error("Skill response was not a list.");
            }

            loadStatus.remove();
            dynamicSkills.querySelectorAll(".skill-card").forEach(function(card){
                card.remove();
            });

            if(skills.length === 0){
                const emptyMessage = document.createElement("p");
                emptyMessage.textContent = "No skills have been added yet.";
                dynamicSkills.append(emptyMessage);
                return;
            }

            skills.forEach(function(skill){
                const card = document.createElement("article");
                const heading = document.createElement("h2");
                const detail = document.createElement("p");
                const category = String(skill.category || "").trim();
                const proficiency = skill.proficiency === null ? "" : String(skill.proficiency) + "%";

                card.className = "skill-card";
                heading.textContent = String(skill.name || "");
                detail.textContent = [category, proficiency].filter(Boolean).join(" · ") || "Skill";
                card.append(heading, detail);
                dynamicSkills.append(card);
            });
        })
        .catch(function(error){
            console.error(error);
            loadStatus.textContent = "Live skills are unavailable. Showing the saved preview.";
        });

}


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