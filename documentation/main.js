document.addEventListener("DOMContentLoaded", function () {
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  const navLinks = document.querySelectorAll(".nav-links li");
  const sections = document.querySelectorAll("section[id]");

  const isMobile = () => window.innerWidth <= 768;

  // Flag to track if navigation was triggered by click
  let isNavigating = false;

  const toggleSidebar = () => {
    sidebar.classList.toggle("active");
    sidebarToggle.classList.toggle("active");
  };

  const closeSidebar = () => {
    if (isMobile() && sidebar.classList.contains("active")) {
      toggleSidebar();
    }
  };

  // Add click event to toggle button
  sidebarToggle.addEventListener("click", toggleSidebar);

  // Check for hash in URL on page load and scroll to that section
  function handleHashOnLoad() {
    if (window.location.hash) {
      const targetId = window.location.hash.substring(1); // Remove the # character
      const targetElement = document.getElementById(targetId);

      if (targetElement) {
        // Remove active class from all nav links
        navLinks.forEach((link) => link.classList.remove("active"));

        // Add active class to corresponding nav link
        const activeLink = document.querySelector(
          `.nav-links li[data-scroll-to="${targetId}"]`
        );
        if (activeLink) {
          activeLink.classList.add("active");
        }

        // Scroll to the section (with a slight delay to ensure proper positioning)
        setTimeout(() => {
          const targetPosition = targetElement.offsetTop - 50; // 50px offset
          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          });
        }, 100);
      }
    }
  }

  // Call the function on page load
  handleHashOnLoad();

  // Make nav items clickable and highlight active section
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      // Get the section ID from the data attribute
      const targetId = this.getAttribute("data-scroll-to");

      if (targetId) {
        e.preventDefault();
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
          // Set navigation flag
          isNavigating = true;

          // Remove active class from all nav links
          navLinks.forEach((link) => link.classList.remove("active"));

          // Add active class to clicked link
          this.classList.add("active");

          // Update URL hash without jumping (using history API)
          history.pushState(null, null, `#${targetId}`);

          // Scroll to the section
          const targetPosition = targetElement.offsetTop - 50; // 50px offset
          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          });

          // Reset navigation flag after scrolling completes
          setTimeout(() => {
            isNavigating = false;
          }, 1000); // Adjust timeout based on scroll duration

          closeSidebar();
        }
      }
    });
  });

  // Function to set active nav item based on scroll position
  function setActiveNavItem() {
    // Don't update URL if navigation was triggered by click
    if (isNavigating) {
      return;
    }

    // Check if user is at the very top of the page
    if (window.scrollY <= 10) {
      // Remove active class from all nav links
      navLinks.forEach((link) => link.classList.remove("active"));

      document
        .querySelector(".nav-links li:first-child")
        ?.classList.add("active");

      // Update URL to remove hash when at top
      if (history.replaceState && window.location.hash) {
        history.replaceState(null, null, window.location.pathname);
      }

      return;
    }

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

        // Update URL hash as user scrolls (without affecting scroll position)
        // Only update if the current hash is different
        if (
          history.replaceState &&
          window.location.hash !== `#${currentSection}`
        ) {
          history.replaceState(null, null, `#${currentSection}`);
        }
      }
    }
  }

  // Add scroll event listener to highlight current section
  window.addEventListener("scroll", setActiveNavItem);

  // Initial call to set the active section on page load
  setActiveNavItem();

  // Support for back/forward browser navigation
  window.addEventListener("popstate", function () {
    handleHashOnLoad();
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

  // Import spreadsheet drop downs
  var coll = document.getElementsByClassName("collapsible");
  var i;

  for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function () {
      this.classList.toggle("active");
      var content = this.nextElementSibling;
      if (content.style.maxHeight) {
        content.style.maxHeight = null;
      } else {
        content.style.maxHeight = content.scrollHeight + "px";
      }
    });
  }
});
