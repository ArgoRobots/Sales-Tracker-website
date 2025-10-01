class DocumentationSearch {
    constructor() {
        this.searchInput = document.getElementById('docSearchInput');
        this.searchButton = document.getElementById('searchButton');
        this.searchResults = document.getElementById('searchResults');
        this.sections = [];
        this.searchIndex = [];
        
        this.init();
    }
    
    init() {
        this.buildSearchIndex();
        this.setupEventListeners();
    }
    
    buildSearchIndex() {
        // Get all documentation sections
        const sections = document.querySelectorAll('section[id].article');
        
        this.sections = Array.from(sections).map(section => {
            const id = section.id;
            const title = section.querySelector('h2')?.textContent || id;
            const content = this.getSectionContent(section);
            const navItem = document.querySelector(`[data-scroll-to="${id}"]`);
            const category = navItem ? navItem.closest('.nav-section').querySelector('h3').textContent : 'Documentation';
            
            return {
                id,
                title,
                content,
                category,
                element: section
            };
        });
    }
    
    getSectionContent(section) {
        // Remove elements that shouldn't be searched
        const clone = section.cloneNode(true);
        
        // Remove elements we don't want to search
        const elementsToRemove = clone.querySelectorAll('.info-box, .warning-box, .video-container, .version-cards, table, .steps-list li::before');
        elementsToRemove.forEach(el => el.remove());
        
        // Get clean text content
        return clone.textContent.replace(/\s+/g, ' ').trim();
    }
    
    setupEventListeners() {
        // Search on button click
        this.searchButton.addEventListener('click', () => this.performSearch());
        
        // Search on Enter key
        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });
        
        // Real-time search as user types (with debounce)
        let timeout;
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (e.target.value.length >= 2) {
                    this.performSearch();
                } else {
                    this.hideResults();
                }
            }, 300);
        });
        
        // Close results when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.searchInput.contains(e.target) && !this.searchResults.contains(e.target)) {
                this.hideResults();
            }
        });
    }
    
    performSearch() {
        const query = this.searchInput.value.trim().toLowerCase();
        
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        const results = this.searchSections(query);
        this.displayResults(results, query);
    }
    
    searchSections(query) {
        return this.sections.filter(section => {
            // Search in title and content
            const titleMatch = section.title.toLowerCase().includes(query);
            const contentMatch = section.content.toLowerCase().includes(query);
            
            return titleMatch || contentMatch;
        });
    }
    
    displayResults(results, query) {
        if (results.length === 0) {
            this.searchResults.innerHTML = `
                <div class="no-results">
                    <p>No results found for "<strong>${this.escapeHtml(query)}</strong>"</p>
                    <p style="margin-top: 0.5rem; font-size: 0.875rem;">Try different keywords or check the documentation menu.</p>
                </div>
            `;
            this.searchResults.style.display = 'block';
            return;
        }
        
        const resultsHtml = results.map(section => this.createResultItem(section, query)).join('');
        this.searchResults.innerHTML = resultsHtml;
        this.searchResults.style.display = 'block';
        
        // Add click handlers to result items
        this.searchResults.querySelectorAll('.search-result-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.navigateToSection(results[index].id);
            });
        });
    }
    
    createResultItem(section, query) {
        const titleHighlighted = this.highlightText(section.title, query);
        const preview = this.getContentPreview(section.content, query);
        const previewHighlighted = this.highlightText(preview, query);
        
        return `
            <div class="search-result-item" data-section="${section.id}">
                <div class="search-result-title">
                    <svg class="search-result-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12h6m-3-3v6m-9 0V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/>
                    </svg>
                    ${titleHighlighted}
                </div>
                <div class="search-result-section">${section.category}</div>
                <div class="search-result-preview">${previewHighlighted}</div>
            </div>
        `;
    }
    
    highlightText(text, query) {
        const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }
    
    getContentPreview(content, query) {
        const index = content.toLowerCase().indexOf(query.toLowerCase());
        if (index === -1) return content.substring(0, 150) + '...';
        
        const start = Math.max(0, index - 50);
        const end = Math.min(content.length, index + query.length + 100);
        let preview = content.substring(start, end);
        
        if (start > 0) preview = '...' + preview;
        if (end < content.length) preview = preview + '...';
        
        return preview;
    }
    
    navigateToSection(sectionId) {
        // Use your existing navigation system
        const navItem = document.querySelector(`[data-scroll-to="${sectionId}"]`);
        if (navItem) {
            navItem.click();
        }
        
        this.hideResults();
        this.searchInput.value = '';
    }
    
    hideResults() {
        this.searchResults.style.display = 'none';
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
}

// Initialize search when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new DocumentationSearch();
});