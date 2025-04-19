/**
 * Admin Feedback Management JavaScript
 *
 * This file contains the JavaScript functionality for the admin feedback management page.
 */

document.addEventListener("DOMContentLoaded", function () {
  // Add fullscreen image viewing CSS
  const style = document.createElement("style");
  style.textContent = `
        .fullscreen-image-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }
        
        .fullscreen-image {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
        
        .fullscreen-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .status-update-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .status-select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
            min-width: 150px;
        }
        
        .update-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .update-btn:hover {
            background-color: #1d4ed8;
        }
    `;
  document.head.appendChild(style);

  // Auto-hide status messages after 5 seconds
  const statusMessages = document.querySelectorAll(".status-message");
  if (statusMessages.length > 0) {
    setTimeout(() => {
      statusMessages.forEach((message) => {
        message.style.opacity = "0";
        message.style.transition = "opacity 0.5s ease";
        setTimeout(() => {
          message.style.display = "none";
        }, 500);
      });
    }, 5000);
  }

  // Add event listeners for filters
  const statusFilter = document.getElementById("status-filter");
  const searchInput = document.getElementById("search-input");

  // Add keyup event to search input for Enter key
  if (searchInput) {
    searchInput.addEventListener("keyup", function (event) {
      if (event.key === "Enter") {
        applyFilters();
      }
    });
  }

  // Make table rows clickable
  const titleCells = document.querySelectorAll(".title-cell a");
  titleCells.forEach((cell) => {
    cell.closest("tr").classList.add("clickable-row");

    // Get the row and exclude the actions cell
    const row = cell.closest("tr");
    const cells = row.querySelectorAll("td:not(:last-child)");

    cells.forEach((rowCell) => {
      if (!rowCell.querySelector("select, button, a")) {
        rowCell.addEventListener("click", () => {
          cell.click();
        });
      }
    });
  });

  // Handle back to filtered results
  const backToResultsLinks = document.querySelectorAll(".back-to-results");
  backToResultsLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      history.back();
    });
  });

  // Export functionality
  const exportButtons = document.querySelectorAll(".export-btn");
  exportButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const type = this.dataset.type;
      const id = this.dataset.id;

      fetch(`export_${type}.php?id=${id}`)
        .then((response) => response.blob())
        .then((blob) => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement("a");
          a.href = url;
          a.download = `${type}_${id}_export.csv`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          a.remove();
        })
        .catch((error) => {
          console.error("Export error:", error);
          alert("An error occurred during export.");
        });
    });
  });

  // Fullscreen image viewing functionality
  window.openImageFullscreen = function (src) {
    const fullscreenContainer = document.createElement("div");
    fullscreenContainer.className = "fullscreen-image-container";

    const img = document.createElement("img");
    img.src = src;
    img.className = "fullscreen-image";

    const closeBtn = document.createElement("span");
    closeBtn.className = "fullscreen-close";
    closeBtn.innerHTML = "&times;";
    closeBtn.onclick = function () {
      document.body.removeChild(fullscreenContainer);
    };

    fullscreenContainer.appendChild(img);
    fullscreenContainer.appendChild(closeBtn);

    fullscreenContainer.onclick = function (e) {
      if (e.target === fullscreenContainer) {
        document.body.removeChild(fullscreenContainer);
      }
    };

    document.body.appendChild(fullscreenContainer);
  };

  // Add keyboard navigation for modals
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      const bugModal = document.getElementById("bugModal");
      const featureModal = document.getElementById("featureModal");
      const fullscreenImage = document.querySelector(
        ".fullscreen-image-container"
      );

      if (fullscreenImage) {
        document.body.removeChild(fullscreenImage);
      } else if (bugModal && bugModal.style.display === "block") {
        bugModal.style.display = "none";
      } else if (featureModal && featureModal.style.display === "block") {
        featureModal.style.display = "none";
      }
    }
  });

  // Initialize tooltips
  const tooltipElements = document.querySelectorAll("[data-tooltip]");
  tooltipElements.forEach((element) => {
    element.setAttribute("title", element.dataset.tooltip);
  });
});

// Function to apply filters
function applyFilters() {
  const status = document.getElementById("status-filter").value;
  const search = document.getElementById("search-input").value.trim();
  const currentTab =
    new URLSearchParams(window.location.search).get("tab") || "bugs";

  let url = `?tab=${currentTab}`;

  if (status) {
    url += `&status=${encodeURIComponent(status)}`;
  }

  if (search) {
    url += `&search=${encodeURIComponent(search)}`;
  }

  window.location.href = url;
}

// Function to clear search
function clearSearch() {
  const status = document.getElementById("status-filter").value;
  const currentTab =
    new URLSearchParams(window.location.search).get("tab") || "bugs";

  let url = `?tab=${currentTab}`;

  if (status) {
    url += `&status=${encodeURIComponent(status)}`;
  }

  window.location.href = url;
}

// Function to view bug details
function viewBugDetails(id) {
  fetch(`get_bug_details.php?id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("bugDetails").innerHTML = data.html;
        document.getElementById("bugModal").style.display = "block";
      } else {
        alert("Error loading bug details: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while loading bug details.");
    });
}

// Function to close bug modal
function closeBugModal() {
  document.getElementById("bugModal").style.display = "none";
}

// Function to view feature details
function viewFeatureDetails(id) {
  fetch(`get_feature_details.php?id=${id}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("featureDetails").innerHTML = data.html;
        document.getElementById("featureModal").style.display = "block";
      } else {
        alert("Error loading feature details: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while loading feature details.");
    });
}

// Function to close feature modal
function closeFeatureModal() {
  document.getElementById("featureModal").style.display = "none";
}

// Function to export data
function exportData(type, format) {
  const statusFilter = document.getElementById("status-filter").value;
  const searchQuery = document.getElementById("search-input").value;

  let url = `export_${type}.php?format=${format}`;

  if (statusFilter) {
    url += `&status=${encodeURIComponent(statusFilter)}`;
  }

  if (searchQuery) {
    url += `&search=${encodeURIComponent(searchQuery)}`;
  }

  window.location.href = url;
}
