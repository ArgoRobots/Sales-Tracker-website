function preparePDFExport() {
  // Create a clone of the document to manipulate
  const contentClone = document.querySelector(".content").cloneNode(true);

  // Remove search box from the clone
  const searchContainer = contentClone.querySelector('.search-container');
  if (searchContainer) {
    searchContainer.remove();
  }

  // Create a clean document for printing
  const printWindow = window.open("", "_blank");
  printWindow.document.write("<html><head>");

  // Copy all stylesheets
  document.querySelectorAll('link[rel="stylesheet"]').forEach((styleSheet) => {
    printWindow.document.write(styleSheet.outerHTML);
  });

  // Add print-specific styles
  printWindow.document.write(`
    <style>
      @media print {
        body { 
          font-size: 12pt;
          line-height: 1.5;
          color: #000;
          background: #fff;
          margin: 0;
          padding: 15mm;
        }
        .article {
          page-break-inside: avoid;
          margin-bottom: 10mm;
          box-shadow: none;
          border: 1px solid #ddd;
        }
        h1, h2, h3 { page-break-after: avoid; }
        h1 { font-size: 24pt; }
        h2 { font-size: 18pt; }
        h3 { font-size: 14pt; }
        ul, ol { page-break-inside: avoid; }
        img { max-width: 100% !important; }
        .video-container { display: none; }
        a { color: #000; text-decoration: underline; }
        
        /* Footer with page numbers */
        @page {
          margin: 20mm;
          @bottom-center {
            content: "Page " counter(page) " of " counter(pages);
          }
        }
      }
    </style>
  `);

  printWindow.document.write("</head><body>");

  // Add a title page
  printWindow.document.write(`
    <div style="text-align: center; margin-top: 40mm; margin-bottom: 40mm;">
      <h1 style="font-size: 28pt; margin-bottom: 10mm;">Argo Books Documentation</h1>
      <p style="font-size: 14pt;">Generated on ${new Date().toLocaleDateString()}</p>
    </div>
  `);

  // Add table of contents
  printWindow.document.write(
    '<div style="page-break-after: always; text-align: center;">'
  );
  printWindow.document.write(
    '<h2 style="margin-bottom: 20px;">Table of Contents</h2>'
  );
  printWindow.document.write(
    '<ul style="list-style-type: none; padding-left: 0; display: inline-block; text-align: left; margin-bottom: 40px;">'
  );

  // Get all section titles from the original document
  const sections = document.querySelectorAll("section[id]");
  sections.forEach((section) => {
    const id = section.getAttribute("id");
    let title = section.querySelector("h2")?.textContent || id;
    printWindow.document.write(
      `<li style="margin-bottom: 10px;"><a href="#${id}">${title}</a></li>`
    );
  });

  printWindow.document.write("</ul>");
  printWindow.document.write("</div>");

  // Add the content
  printWindow.document.write(contentClone.innerHTML);

  printWindow.document.write("</body></html>");
  printWindow.document.close();

  // Trigger print after the content is loaded
  printWindow.onload = function () {
    // Small delay to ensure all stylesheets are loaded
    setTimeout(() => {
      printWindow.print();
      // Close the window after print dialog is closed
      printWindow.onafterprint = function () {
        printWindow.close();
      };
    }, 1000);
  };
}

// Add PDF export button to the DOM
function addPDFExportButton() {
  const button = document.createElement("button");
  button.id = "pdfExportBtn";
  button.className = "pdf-export-btn";
  button.innerHTML = `
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
      <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"/>
      <path d="M9 15h6"/>
      <path d="M12 15v-3"/>
      <path d="M9 11h6"/>
    </svg>
    <span>Save as PDF</span>
  `;

  button.addEventListener("click", preparePDFExport);

  // Insert button just below the sidebar toggle button
  const sidebarToggle = document.getElementById("sidebarToggle");
  if (sidebarToggle && sidebarToggle.parentNode) {
    sidebarToggle.parentNode.insertBefore(button, sidebarToggle.nextSibling);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  // Add PDF export button after a small delay to ensure other scripts have initialized
  setTimeout(addPDFExportButton, 100);
});
