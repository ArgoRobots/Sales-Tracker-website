document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  const navLinks = document.querySelectorAll(".nav-links a");

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

  // Handle nav link clicks with scroll offset
  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const targetId = link.getAttribute("href").slice(1);
      const targetElement = document.getElementById(targetId);

      if (targetElement) {
        const targetPosition = targetElement.offsetTop - 50; // 50px offset
        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        });
        closeSidebar();
      }
    });
  });

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
