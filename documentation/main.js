document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  const navLinks = document.querySelectorAll(".nav-links li");
  const sections = document.querySelectorAll("section[id]");

  // Function to check if device is mobile
  const isMobile = () => window.innerWidth <= 768;

  // Function to handle sidebar state
  const toggleSidebar = () => {
    sidebar.classList.toggle("active");
    sidebarToggle.classList.toggle("active");
  };

  // Close sidebar
  const closeSidebar = () => {
    if (isMobile() && sidebar.classList.contains("active")) {
      toggleSidebar();
    }
  };

  // Add click event to toggle button
  sidebarToggle.addEventListener("click", toggleSidebar);

  // Make nav items clickable and highlight active section
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      // Get the section ID from the data attribute
      const targetId = this.getAttribute("data-scroll-to");

      if (targetId) {
        e.preventDefault();
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
          // Remove active class from all nav links
          navLinks.forEach((link) => link.classList.remove("active"));

          // Add active class to clicked link
          this.classList.add("active");

          // Scroll to the section
          const targetPosition = targetElement.offsetTop - 50; // 50px offset
          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          });
          closeSidebar();
        }
      }
    });
  });

  // Function to set active nav item based on scroll position
  function setActiveNavItem() {
    let currentSection = "";

    sections.forEach((section) => {
      const sectionTop = section.offsetTop - 500; // Offset to trigger earlier
      const sectionHeight = section.offsetHeight;
      const scrollPosition = window.scrollY;

      if (
        scrollPosition >= sectionTop &&
        scrollPosition < sectionTop + sectionHeight
      ) {
        currentSection = section.getAttribute("id");
      }
    });

    if (currentSection) {
      // Remove active class from all nav links
      navLinks.forEach((link) => link.classList.remove("active"));

      // Add active class to corresponding nav link
      const activeLink = document.querySelector(
        `.nav-links li[data-scroll-to="${currentSection}"]`
      );
      if (activeLink) {
        activeLink.classList.add("active");
      }
    }
  }

  // Add scroll event listener to highlight current section
  window.addEventListener("scroll", setActiveNavItem);

  // Initial call to set the active section on page load
  setActiveNavItem();

  // Close sidebar when clicking outside on mobile
  document.addEventListener("click", (e) => {
    if (
      isMobile() &&
      !sidebar.contains(e.target) &&
      !sidebarToggle.contains(e.target) &&
      sidebar.classList.contains("active")
    ) {
      toggleSidebar();
    }
  });

  // Handle resize events
  let lastWidth = window.innerWidth;
  window.addEventListener("resize", () => {
    if (lastWidth <= 768 && window.innerWidth > 768) {
      // Switching to desktop
      sidebar.classList.remove("active");
      sidebarToggle.classList.toggle("active");
    }
    lastWidth = window.innerWidth;
  });
});
